<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Order;
use App\Services\InventoryService;
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

            // Create stock OUT movements for each order item (delivered tanks)
            // and increment empty_on_hand (tanks collected during exchange)
            DB::transaction(function () use ($delivery) {
                foreach ($delivery->order->orderItems as $item) {
                    try {
                        // Decrement full tanks (quantity_on_hand)
                        InventoryService::stockOut(
                            productId: $item->product_id,
                            quantity: $item->quantity,
                            movementDate: now(),
                            referenceType: 'order',
                            referenceId: $delivery->order_id,
                            notes: 'Delivery completed - Full tank(s) delivered to customer',
                            userId: Auth::id()
                        );

                        // Increment empty tanks collected (exchange-only) for tank-like categories.
                        $product = $item->product;
                        $category = strtolower((string) ($product->category ?? ''));
                        if ($category !== '' && str_contains($category, 'tank')) {
                            $inventory = \App\Models\Inventory::where('product_id', $item->product_id)
                                ->lockForUpdate()
                                ->first();
                            if ($inventory) {
                                $inventory->increment('empty_on_hand', $item->quantity);

                                // Log the empty tank collection in inventory_movements
                                \App\Models\InventoryMovement::create([
                                    'product_id'     => $item->product_id,
                                    'movement_date'  => now(),
                                    'type'           => 'IN',
                                    'quantity'       => $item->quantity,
                                    'reference_type' => 'order_exchange',
                                    'reference_id'   => $delivery->order_id,
                                    'notes'          => 'Empty tank(s) collected during delivery exchange',
                                    'created_by'     => Auth::id(),
                                ]);

                                // Log the empty tank collection in stock_movements for History tracking
                                \App\Models\StockMovement::create([
                                    'inventory_id'    => $inventory->id,
                                    'movement_date'   => now(),
                                    'type'            => 'empty_return',
                                    'quantity_change' => -$item->quantity,
                                    'reference'       => $delivery->order->order_number ?? 'ORD-' . $delivery->order_id,
                                    'notes'           => 'Auto-logged from delivery by ' . (Auth::user()->name ?? 'Rider'),
                                    'created_by'      => Auth::id(),
                                ]);
                            }
                        }
                    } catch (\Exception $e) {
                        // Log the error but don't fail the delivery update
                        Log::warning(
                            "Failed to record inventory movement for order {$delivery->order_id}, product {$item->product_id}: " . $e->getMessage()
                        );
                    }
                }
            });
        }

        if ($validated['status'] === 'failed') {
            // Update order status to cancelled when delivery fails
            $delivery->order->update(['status' => 'cancelled']);
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
