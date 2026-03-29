<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Rider;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RiderController extends Controller
{
    // Rider: dashboard with active deliveries and stats
    public function dashboard()
    {
        $activeDeliveries = Delivery::with('order.user', 'order.orderItems.product')
            ->where('rider_id', Auth::id())
            ->whereNotIn('status', ['delivered', 'failed'])
            ->get();

        $completedCount = Delivery::where('rider_id', Auth::id())
            ->where('status', 'delivered')
            ->whereDate('delivered_at', today())
            ->count();

        return view('rider.dashboard', compact('activeDeliveries', 'completedCount'));
    }

    // Rider: accept an available order
    public function acceptOrder(Request $request, $orderId)
    {
        $order = \App\Models\Order::findOrFail($orderId);

        // Check if order is still available
        if ($order->status !== 'approved' || $order->delivery()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This order is no longer available.'
            ], 400);
        }

        // Check rider availability
        $rider = Rider::where('user_id', Auth::id())->first();
        if (!$rider || $rider->availability !== 'available') {
            return response()->json([
                'success' => false,
                'message' => 'Please set your status to "Available" before accepting orders.'
            ], 400);
        }

        // Create delivery and assign to this rider
        $delivery = Delivery::create([
            'order_id' => $order->id,
            'rider_id' => Auth::id(),
            'status' => 'out_for_delivery',
            'assigned_at' => now(),
            'latitude' => null,
            'longitude' => null,
        ]);

        // Update order status
        $order->update(['status' => 'out_for_delivery']);

        return response()->json([
            'success' => true,
            'message' => 'Order accepted successfully!',
            'delivery_id' => $delivery->id
        ]);
    }

    // Rider: view and update own profile
    public function profile()
    {
        $rider = Rider::where('user_id', Auth::id())->first();

        return view('rider.profile', compact('rider'));
    }

    // Rider: update profile details
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'vehicle_type'   => 'nullable|string|max:255',
            'plate_number'   => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:255',
            'availability'   => 'required|in:available,busy,offline',
        ]);

        Rider::updateOrCreate(
            ['user_id' => Auth::id()],
            $validated
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Profile updated successfully.',
                'availability' => $validated['availability'],
            ]);
        }

        return redirect()->route('rider.profile')->with('success', 'Profile updated.');
    }

    // Admin: list all riders
    public function adminIndex()
    {
        $riders = User::where('role', 'rider')
            ->with('rider')
            ->get();

        // Delivery stats per rider
        $deliveryStats = Delivery::selectRaw("
                rider_id,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as completed
            ")
            ->groupBy('rider_id')
            ->pluck('total', 'rider_id');

        $completedStats = Delivery::where('status', 'delivered')
            ->selectRaw('rider_id, COUNT(*) as completed')
            ->groupBy('rider_id')
            ->pluck('completed', 'rider_id');

        // Today's deliveries per rider
        $todayDeliveries = Delivery::whereDate('created_at', today())
            ->selectRaw('rider_id, COUNT(*) as total')
            ->groupBy('rider_id')
            ->pluck('total', 'rider_id');

        // Active delivery per rider
        $activeDeliveries = Delivery::whereNotIn('status', ['delivered', 'failed'])
            ->with('order')
            ->get()
            ->keyBy('rider_id');

        return view('admin.riders', compact('riders', 'deliveryStats', 'completedStats', 'todayDeliveries', 'activeDeliveries'));
    }

    // Admin: create a new rider account
    public function storeRider(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|max:255|unique:users,email',
            'phone'          => 'required|string|max:20',
            'password'       => 'required|string|min:6',
            'address'        => 'nullable|string|max:500',
            'vehicle_type'   => 'nullable|string|max:255',
            'plate_number'   => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => strtolower($validated['email']),
            'phone'    => $validated['phone'],
            'password' => $validated['password'],
            'address'  => $validated['address'] ?? null,
            'role'     => 'rider',
        ]);

        Rider::create([
            'user_id'        => $user->id,
            'vehicle_type'   => $validated['vehicle_type'] ?? null,
            'plate_number'   => $validated['plate_number'] ?? null,
            'license_number' => $validated['license_number'] ?? null,
            'availability'   => 'offline',
        ]);

        return redirect()->route('admin.riders')->with('success', 'Rider account created successfully!');
    }

    // Admin: view single rider details with delivery stats
    public function show(User $user)
    {
        $user->load('rider');

        $deliveryStats = Delivery::where('rider_id', $user->id)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
            ")
            ->first();

        return view('admin.rider-detail', compact('user', 'deliveryStats'));
    }

    // Admin: update rider availability
    public function updateAvailability(Request $request, Rider $rider)
    {
        $validated = $request->validate([
            'availability' => 'required|in:available,busy,offline',
        ]);

        $rider->update(['availability' => $validated['availability']]);

        return redirect()->back()->with('success', 'Rider availability updated.');
    }

    // Admin: delete rider account
    public function destroy(Rider $rider)
    {
        $user = User::find($rider->user_id);
        
        if ($user) {
            $riderName = $user->name;
            $user->delete(); // Will cascade delete the Rider record
            return redirect()->route('admin.riders')->with('success', "Rider '$riderName' account deleted successfully!");
        }

        return redirect()->route('admin.riders')->with('error', 'Rider not found.');
    }

    // Rider: view active route with all deliveries/waypoints
    public function route()
    {
        $activeDeliveries = Delivery::with('order.user')
            ->where('rider_id', Auth::id())
            ->whereNotIn('status', ['delivered', 'failed'])
            ->orderBy('assigned_at', 'asc')
            ->get();

        $deliveredCount = Delivery::where('rider_id', Auth::id())
            ->where('status', 'delivered')
            ->whereDate('delivered_at', today())
            ->count();

        $totalAmount = $activeDeliveries->sum(function($delivery) {
            return $delivery->order->total_amount ?? 0;
        });

        // Calculate approximate distance (simple estimation)
        $totalDistance = count($activeDeliveries) * 2.5; // 2.5km average per stop

        return view('rider.route', compact('activeDeliveries', 'deliveredCount', 'totalAmount', 'totalDistance'));
    }

    // Rider: Live route map view
    public function liveRouteMap()
    {
        $activeDeliveries = Delivery::with('order.user', 'order.orderItems.product')
            ->where('rider_id', Auth::id())
            ->whereNotIn('status', ['delivered', 'failed'])
            ->orderBy('assigned_at', 'asc')
            ->get();

        return view('rider.route-map', compact('activeDeliveries'));
    }

    // Rider: Full-screen turn-by-turn navigation
    public function navigation(Delivery $delivery)
    {
        // Verify rider owns this delivery
        if ($delivery->rider_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $delivery->load('order.user', 'order.orderItems.product');

        return view('rider.navigation', compact('delivery'));
    }

    // Rider: get route waypoints as JSON (for map rendering)
    public function routeWaypoints()
    {
        $deliveries = Delivery::with('order')
            ->where('rider_id', Auth::id())
            ->whereNotIn('status', ['delivered', 'failed'])
            ->orderBy('assigned_at', 'asc')
            ->get();

        $waypoints = $deliveries->map(function($delivery, $index) {
            return [
                'id' => $delivery->id,
                'order_number' => $delivery->order->order_number,
                'customer_name' => $delivery->order->user->name,
                'address' => $delivery->order->delivery_address,
                'latitude' => $delivery->order->latitude,
                'longitude' => $delivery->order->longitude,
                'status' => $delivery->status,
                'amount' => $delivery->order->total_amount,
                'waypoint_number' => $index + 1,
                'contact' => $delivery->order->contact_number,
            ];
        });

        return response()->json([
            'waypoints' => $waypoints,
            'total' => count($waypoints),
        ]);
    }
}
