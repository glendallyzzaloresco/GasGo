<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'order_id' => 'required|exists:orders,id',
            'rider_id' => 'required|exists:users,id',
        ]);

        $delivery = Delivery::create([
            'order_id'    => $validated['order_id'],
            'rider_id'    => $validated['rider_id'],
            'status'      => 'assigned',
            'assigned_at' => now(),
        ]);

        Order::where('id', $validated['order_id'])->update(['status' => 'assigned']);

        if ($request->expectsJson() || $request->ajax()) {
            $delivery->load(['rider', 'order']);

            return response()->json([
                'message' => 'Rider assigned to order.',
                'order_id' => (int) $validated['order_id'],
                'status' => 'assigned',
                'rider_name' => $delivery->rider->name ?? 'Rider',
            ]);
        }

        return redirect()->back()->with('success', 'Rider assigned to order.');
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
