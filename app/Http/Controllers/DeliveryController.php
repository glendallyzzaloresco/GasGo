<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            ->whereIn('status', ['pending', 'approved'])
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
                    'status'      => 'out_for_delivery',
                    'assigned_at' => now(),
                ]);

                $order->update(['status' => 'out_for_delivery']);
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
                'status' => 'out_for_delivery',
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

        $delivery->load(['order.orderItems', 'order.user']);

        return view('rider.delivery', compact('delivery'));
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
            $delivery->order->update(['status' => 'out_for_delivery']);
        }

        if ($validated['status'] === 'delivered') {
            $updateData['delivered_at'] = now();
            $delivery->order->update([
                'status'       => 'delivered',
                'delivered_at' => now(),
            ]);
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

    // Rider: broadcast current GPS location to all active deliveries
    public function updateRiderLiveLocation(Request $request)
    {
        $validated = $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $updated = Delivery::query()
            ->where('rider_id', Auth::id())
            ->whereNotIn('status', ['delivered', 'failed'])
            ->update([
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]);

        return response()->json([
            'message' => 'Live location updated.',
            'updated_deliveries' => $updated,
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
}
