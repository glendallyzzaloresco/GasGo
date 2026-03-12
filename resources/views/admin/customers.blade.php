@extends('layouts.admin')

@section('title', 'GasGo Admin - Customers')
@section('nav-customers', 'active')
@section('page-title', 'Customer Management')

@section('admin-styles')
<style>
    .search-box { position:relative; max-width:320px; }
    .search-box input {
        border-radius:25px; padding:10px 20px 10px 42px; border:2px solid #e0e0e0;
        font-size:.88rem; width:100%; transition:border-color .3s;
    }
    .search-box input:focus { border-color:var(--gasgo-blue); outline:none; box-shadow:none; }
    .search-box i { position:absolute; left:16px; top:50%; transform:translateY(-50%); color:#aaa; }
    .customer-avatar {
        width:40px; height:40px; border-radius:50%; display:flex; align-items:center;
        justify-content:center; font-weight:700; color:#fff; font-size:.88rem; flex-shrink:0;
    }
</style>
@endsection

@section('content')
<!-- Stats -->
<div class="row g-4 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div><p>Total Customers</p><h3>{{ $totalCustomers }}</h3></div>
                <div class="stat-icon blue"><i class="fas fa-users"></i></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div><p>Active This Month</p><h3>{{ $activeThisMonth }}</h3></div>
                <div class="stat-icon green"><i class="fas fa-user-check"></i></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div><p>Loyalty Members</p><h3>{{ $loyaltyMembers }}</h3></div>
                <div class="stat-icon orange"><i class="fas fa-id-card"></i></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div><p>New This Month</p><h3>{{ $newThisMonth }}</h3></div>
                <div class="stat-icon red"><i class="fas fa-user-plus"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Search & Table -->
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchCustomers" placeholder="Search customers..." onkeyup="searchCustomers()">
    </div>
    <select class="form-select form-select-sm" style="border-radius:10px;width:auto;">
        <option>All Customers</option>
        <option>Loyalty Members</option>
        <option>Active This Month</option>
        <option>Inactive</option>
    </select>
</div>

<div class="gasgo-table">
    <table class="table" id="customersTable">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Total Orders</th>
                <th>Total Spent</th>
                <th>Loyalty</th>
                <th>Last Order</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customerStats as $stat)
                @php
                    $initials = strtoupper(substr($stat['customer']->name, 0, 1) . (strpos($stat['customer']->name, ' ') !== false ? substr(substr($stat['customer']->name, strpos($stat['customer']->name, ' ') + 1), 0, 1) : ''));
                    $colorClasses = ['bg-primary', 'bg-warning', 'bg-success', 'bg-info', 'bg-danger'];
                    $colorIndex = abs(crc32($stat['customer']->id)) % count($colorClasses);
                @endphp
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="customer-avatar" style="background:linear-gradient(135deg,#1a6db0,#2196f3);">{{ $initials }}</div>
                            <div>
                                <div class="fw-bold">{{ $stat['customer']->name }}</div>
                                <small class="text-muted">{{ $stat['customer']->email }}</small>
                            </div>
                        </div>
                    </td>
                    <td>{{ $stat['customer']->phone ?? 'N/A' }}</td>
                    <td style="font-size:.82rem;">{{ $stat['customer']->address ?? 'No address' }}</td>
                    <td class="fw-bold">{{ $stat['totalOrders'] }}</td>
                    <td class="fw-bold" style="color:var(--gasgo-orange);">₱{{ number_format($stat['totalSpent'], 2) }}</td>
                    <td>
                        @if($stat['loyaltyTier'])
                            <span class="badge bg-{{ $stat['loyaltyBadge'] }}" style="font-size:.72rem;"><i class="fas fa-star me-1"></i>{{ $stat['loyaltyTier'] }}</span>
                        @else
                            <span class="text-muted" style="font-size:.78rem;">—</span>
                        @endif
                    </td>
                    <td style="font-size:.82rem;">
                        @if($stat['lastOrder'])
                            {{ $stat['lastOrder']->created_at->format('M d, Y') }}
                        @else
                            No orders
                        @endif
                    </td>
                    <td>
                        <button class="btn btn-sm" style="background:var(--gasgo-blue-light);color:var(--gasgo-blue);border-radius:8px;font-weight:600;font-size:.78rem;">View</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="fas fa-inbox me-2"></i>No customers found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
<script>
    function searchCustomers() {
        const q = document.getElementById('searchCustomers').value.toLowerCase();
        document.querySelectorAll('#customersTable tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    }
</script>
@endsection
