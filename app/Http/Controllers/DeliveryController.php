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

        return redirect()->back()->with('success', 'Rider assigned to order.');
    }

    // Rider: view assigned delivery details
    public function show(Delivery $delivery)
    {
        $delivery->load(['order.orderItems', 'order.user']);

        return view('rider.delivery', compact('delivery'));
    }

    // Rider: update delivery status
    public function updateStatus(Request $request, Delivery $delivery)
    {
        $validated = $request->validate([
            'status' => 'required|in:assigned,picked_up,out_for_delivery,delivered,failed',
        ]);

        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'picked_up') {
            $updateData['picked_up_at'] = now();
        }

        if ($validated['status'] === 'delivered') {
            $updateData['delivered_at'] = now();
            $delivery->order->update([
                'status'       => 'delivered',
                'delivered_at' => now(),
            ]);
        }

        $delivery->update($updateData);

        return redirect()->back()->with('success', 'Delivery status updated.');
    }

    // Rider: update current GPS location
    public function updateLocation(Request $request, Delivery $delivery)
    {
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

    // Rider: upload proof of delivery
    public function uploadProof(Request $request, Delivery $delivery)
    {
        $validated = $request->validate([
            'proof_photo' => 'required|image|max:2048',
        ]);

        $path = $request->file('proof_photo')->store('delivery-proofs', 'public');
        $delivery->update(['proof_photo' => $path]);

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
