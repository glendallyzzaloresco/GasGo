<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Order;
use App\Services\OrderInventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeliveryController extends Controller
{
    // Admin: list all deliveries
    public function index()
    {
        $deliveries = Delivery::with(['order.user', 'rider'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.deliveries', compact('deliveries'));
    }

    // Admin: assign a rider to an order
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'nullable|exists:orders,id',
            'order_ids' => 'nullable|array|min:1',
            'order_ids.*' => 'integer|exists:orders,id',
            'rider_id' => 'required|exists:users,id',
        ]);

        $requestedOrderIds = collect();
        if (!empty($validated['order_id'])) {
            $requestedOrderIds->push((int) $validated['order_id']);
        }
        if (!empty($validated['order_ids'])) {
            $requestedOrderIds = $requestedOrderIds->merge($validated['order_ids']);
        }
        $requestedOrderIds = $requestedOrderIds->map(fn ($id) => (int) $id)->unique()->values();

        if ($requestedOrderIds->isEmpty()) {
            return response()->json([
                'message' => 'No orders selected for assignment.',
            ], 422);
        }

        $orders = Order::query()
            ->whereIn('id', $requestedOrderIds)
            ->where('status', 'approved')
            ->whereDoesntHave('delivery')
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'message' => 'Selected order(s) can no longer be assigned.',
            ], 422);
        }

        $assignedOrderIds = [];

        DB::transaction(function () use ($orders, $validated, &$assignedOrderIds) {
            foreach ($orders as $order) {
                Delivery::create([
                    'order_id'    => $order->id,
                    'rider_id'    => $validated['rider_id'],
                    'status'      => 'assigned',
                    'assigned_at' => now(),
                ]);

                $order->update(['status' => 'assigned']);
                $assignedOrderIds[] = (int) $order->id;
            }
        });

        $firstDelivery = Delivery::query()
            ->where('order_id', $assignedOrderIds[0])
            ->with('rider')
            ->first();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => count($assignedOrderIds) > 1
                    ? 'Rider assigned to selected orders.'
                    : 'Rider assigned to order.',
                'order_id' => (int) $assignedOrderIds[0],
                'order_ids' => $assignedOrderIds,
                'status' => 'assigned',
                'rider_name' => $firstDelivery?->rider?->name ?? 'Rider',
            ]);
        }

        return redirect()->back()->with('success', count($assignedOrderIds) > 1
            ? 'Rider assigned to selected orders.'
            : 'Rider assigned to order.');
    }

    // Rider: view assigned delivery details
    public function show(Delivery $delivery)
    {
        // Authorize: rider can only view their own deliveries
        if ($delivery->rider_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $delivery->load(['order.orderItems', 'order.user' => function ($query) {
            $query->with('orders', 'loyaltyPoints');
        }]);

        // Calculate customer stats similar to admin view
        $customer = $delivery->order->user;
        $totalOrders = $customer->orders->count();
        $deliveredOrders = $customer->orders->where('status', 'delivered');
        $productTotal = $deliveredOrders->sum(fn($order) => $order->fee_free_total);
        $deliveryTotal = $deliveredOrders->sum('delivery_fee');
        $totalSpent = $productTotal + $deliveryTotal;
        $loyaltyPoints = $customer->loyaltyPoints->where('type', 'earned')->sum('points') - 
                        $customer->loyaltyPoints->where('type', 'redeemed')->sum('points');
        
        // Determine loyalty tier based on points
        if ($loyaltyPoints >= 300) {
            $loyaltyTier = 'Gold';
            $loyaltyBadge = 'bg-success';
        } elseif ($loyaltyPoints >= 150) {
            $loyaltyTier = 'Silver';
            $loyaltyBadge = 'bg-primary';
        } elseif ($loyaltyPoints > 0) {
            $loyaltyTier = 'Member';
            $loyaltyBadge = 'bg-secondary';
        } else {
            $loyaltyTier = null;
            $loyaltyBadge = null;
        }
        
        $lastOrder = $customer->orders->sortByDesc('created_at')->skip(1)->first();
        
        $customerStats = compact('totalOrders', 'totalSpent', 'loyaltyPoints', 'loyaltyTier', 'loyaltyBadge', 'lastOrder');

        return view('rider.delivery', compact('delivery', 'customerStats'));
    }

    // Rider: update delivery status
    public function updateStatus(Request $request, Delivery $delivery)
    {
        // Authorize: rider can only update their own deliveries
        if ($delivery->rider_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'status' => 'required|in:assigned,picked_up,out_for_delivery,delivered,failed',
        ]);

        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'picked_up') {
            $updateData['picked_up_at'] = now();
        }

        if ($validated['status'] === 'out_for_delivery') {
            if (! $delivery->picked_up_at) {
                $updateData['picked_up_at'] = now();
            }
            $delivery->order->update(['status' => 'out_for_delivery']);
        }

        if ($validated['status'] === 'delivered') {
            $shouldApplyInventory = $delivery->order->status !== 'delivered';

            DB::transaction(function () use ($delivery, &$updateData, $shouldApplyInventory) {
                $updateData['delivered_at'] = now();

                if ($shouldApplyInventory) {
                    OrderInventoryService::applyOnCompletion($delivery->order, Auth::id());
                }

                $delivery->order->update([
                    'status'       => 'delivered',
                    'delivered_at' => now(),
                ]);

                \App\Models\Payment::where('order_id', $delivery->order_id)->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            });
        }

        if ($validated['status'] === 'failed') {
            // Update order status to cancelled when delivery fails
            $delivery->order->update(['status' => 'cancelled']);
            
            // Update payment status to failed when delivery fails
            \App\Models\Payment::where('order_id', $delivery->order_id)->update(['status' => 'failed']);
        }

        $delivery->update($updateData);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Delivery status updated.',
                'delivery_id' => $delivery->id,
                'status' => $delivery->status,
                'is_completed' => in_array($delivery->status, ['delivered', 'failed'], true),
                'delivered_at' => $delivery->delivered_at?->format('M d g:i A'),
            ]);
        }

        return redirect()->back()->with('success', 'Delivery status updated.');
    }

    // Rider: update current GPS location
    public function updateLocation(Request $request, Delivery $delivery)
    {
        // Authorize: rider can only update their own deliveries
        if ($delivery->rider_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $delivery->update([
            'latitude'  => $validated['latitude'],
            'longitude' => $validated['longitude'],
        ]);

        return response()->json(['message' => 'Location updated.']);
    }

    // Rider: broadcast real GPS location to riders table and all active deliveries
    public function updateRiderLiveLocation(Request $request)
    {
        $validated = $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $lat = (float) $validated['latitude'];
        $lng = (float) $validated['longitude'];

        // 1. Update the Rider's real-time location in riders table
        \App\Models\Rider::where('user_id', Auth::id())->update([
            'current_latitude'  => $lat,
            'current_longitude' => $lng,
        ]);

        // 2. Update all active deliveries for this rider in deliveries table
        $updatedCount = Delivery::where('rider_id', Auth::id())
            ->whereNotIn('status', ['delivered', 'failed'])
            ->update([
                'latitude'  => $lat,
                'longitude' => $lng,
            ]);

        return response()->json([
            'message' => 'Real location updated in riders and deliveries table.',
            'latitude' => $lat,
            'longitude' => $lng,
            'updated_deliveries' => $updatedCount,
        ]);
    }

    // Rider: get current delivery location (for map updates)
    public function getLocation(Delivery $delivery)
    {
        // Authorize: rider can only view their own deliveries
        if ($delivery->rider_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        return response()->json([
            'latitude' => $delivery->latitude ?? $delivery->order->latitude,
            'longitude' => $delivery->longitude ?? $delivery->order->longitude,
        ]);
    }

    // Rider: upload proof of delivery
    public function uploadProof(Request $request, Delivery $delivery)
    {
        // Authorize: rider can only update their own deliveries
        if ($delivery->rider_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'proof_photo'    => 'required|image|max:2048',
            'delivery_notes' => 'nullable|string|max:500',
        ]);

        $path = $request->file('proof_photo')->store('delivery-proofs', 'public');
        $delivery->update([
            'proof_photo'    => $path,
            'delivery_notes' => $validated['delivery_notes'] ?? null,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => 'Proof of delivery uploaded successfully.']);
        }

        return redirect()->back()->with('success', 'Proof of delivery uploaded.');
    }

    // Rider: delivery history
    public function riderHistory()
    {
        $deliveries = Delivery::with(['order.user'])
            ->where('rider_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('rider.history', compact('deliveries'));
    }

    // API: get all deliveries as JSON for real-time updates
    public function apiIndex()
    {
        $deliveries = Delivery::with(['order.user', 'rider'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($delivery) {
                return [
                    'id' => $delivery->id,
                    'order_id' => $delivery->order_id,
                    'rider_id' => $delivery->rider_id,
                    'status' => $delivery->status,
                    'created_at' => $delivery->created_at,
                    'updated_at' => $delivery->updated_at,
                ];
            });

        return response()->json([
            'deliveries' => $deliveries,
            'count' => $deliveries->count(),
        ]);
    }
}
