<?php

namespace App\Http\Controllers\Admin;

use App\Models\Voucher;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class VoucherController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::user()?->role !== 'admin') {
                return redirect('/')->with('error', 'Unauthorized access');
            }
            return $next($request);
        });
    }

    // Store a new voucher
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_amount' => 'required|numeric|min:0',
            'reward_points_required' => 'required|integer|min:0',
            'is_active' => 'nullable|boolean',
            'expires_at' => 'nullable|date',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $voucher = Voucher::create($validated);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Voucher created successfully.',
                'voucher_id' => $voucher->id,
            ]);
        }

        return redirect()->route('admin.rewards')->with('success', 'Voucher created successfully.');
    }

    // Update a voucher
    public function update(Request $request, Voucher $voucher)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_amount' => 'required|numeric|min:0',
            'reward_points_required' => 'required|integer|min:0',
            'is_active' => 'nullable|boolean',
            'expires_at' => 'nullable|date',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $voucher->update($validated);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Voucher updated successfully.',
            ]);
        }

        return redirect()->route('admin.rewards')->with('success', 'Voucher updated successfully.');
    }

    // Delete a voucher
    public function destroy(Voucher $voucher)
    {
        $voucher->delete();
        return redirect()->route('admin.rewards')->with('success', 'Voucher deleted successfully.');
    }
}
