@extends('layouts.admin')

@section('title', 'Expiry Report')

@section('styles')
<style>
    .report-header {
        background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
    }
    
    .section-title {
        color: #333;
        font-weight: 600;
        padding: 15px 0;
        margin-top: 30px;
        margin-bottom: 20px;
        border-bottom: 2px solid #f39c12;
        display: flex;
        align-items: center;
    }
    
    .section-title i {
        margin-right: 10px;
        color: #e67e22;
    }
    
    .report-table {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 30px;
    }
    
    .table {
        margin: 0;
    }
    
    .table th {
        background: #f8f9fa;
        font-weight: 600;
        border: none;
        padding: 14px !important;
    }
    
    .table td {
        padding: 14px !important;
        vertical-align: middle;
        border-color: #f0f0f0;
    }
    
    .table tbody tr:hover {
        background: #f8f9fa;
    }
    
    .expired-rows thead th {
        color: #c0392b;
    }
    
    .expiring-soon-rows thead th {
        color: #f39c12;
    }
    
    .expired-row {
        background: #ffe5e5;
        border-left: 4px solid #c0392b;
    }
    
    .expired-row td:first-child {
        color: #c0392b;
        font-weight: 600;
    }
    
    .expiring-row {
        background: #fff8e5;
        border-left: 4px solid #f39c12;
    }
    
    .expiring-row td:first-child {
        color: #d68910;
        font-weight: 600;
    }
    
    .badge-expired {
        background: #c0392b;
        color: white;
    }
    
    .badge-expiring {
        background: #e67e22;
        color: white;
    }
    
    .days-remaining {
        font-weight: 600;
        font-size: 1rem;
    }
    
    .days-critical {
        color: #c0392b;
    }
    
    .days-warning {
        color: #f39c12;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }
    
    .empty-state i {
        font-size: 3rem;
        color: #ddd;
        margin-bottom: 16px;
    }
    
    .summary-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .summary-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border-top: 4px solid #f39c12;
    }
    
    .summary-card.danger {
        border-top-color: #c0392b;
    }
    
    .summary-card .number {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 10px 0;
    }
    
    .summary-card.danger .number {
        color: #c0392b;
    }
    
    .summary-card .label {
        color: #666;
        font-size: 0.9rem;
    }
    
    .alert-info-custom {
        background: #e3f2fd;
        border-left: 4px solid #2196f3;
        padding: 16px;
        border-radius: 4px;
        margin-bottom: 24px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-4">
    <div class="report-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-2"><i class="fas fa-hourglass-end me-2"></i>Expiry Report</h1>
                <p class="mb-0">Track product expiry dates and upcoming expirations</p>
            </div>
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i>Back to Inventory
            </a>
        </div>
    </div>

    @php
        $expiredItems = \App\Models\Inventory::with('product')
            ->where('expiry_date', '<', now())
            ->where('status', '!=', 'discontinued')
            ->orderBy('expiry_date', 'asc')
            ->get();
        
        $expiringItems = \App\Models\Inventory::with('product')
            ->whereBetween('expiry_date', [now(), now()->addMonths(1)])
            ->where('status', '!=', 'discontinued')
            ->orderBy('expiry_date', 'asc')
            ->get();
        
        $totalExpired = $expiredItems->count();
        $totalExpiring = $expiringItems->count();
    @endphp

    @if($totalExpired > 0 || $totalExpiring > 0)
        <div class="summary-cards">
            @if($totalExpired > 0)
                <div class="summary-card danger">
                    <i class="fas fa-exclamation-circle" style="font-size: 2rem; color: #c0392b;"></i>
                    <div class="number">{{ $totalExpired }}</div>
                    <div class="label">Expired Products</div>
                </div>
            @endif
            
            <div class="summary-card">
                <i class="fas fa-warning" style="font-size: 2rem; color: #f39c12;"></i>
                <div class="number">{{ $totalExpiring }}</div>
                <div class="label">Expiring Soon (30 Days)</div>
            </div>
        </div>

        <div class="alert-info-custom">
            <i class="fas fa-info-circle me-2" style="color: #2196f3;"></i>
            <strong>Action Required:</strong> 
            Remove expired products from inventory immediately. For products expiring soon, prioritize sales or mark for disposal.
        </div>
    @endif

    <!-- EXPIRED PRODUCTS -->
    @if($totalExpired > 0)
        <h5 class="section-title">
            <i class="fas fa-times-circle"></i>Expired Products ({{ $totalExpired }})
        </h5>
        
        <div class="report-table expired-rows">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th class="text-center">Current Stock</th>
                        <th class="text-center">Expiry Date</th>
                        <th class="text-center">Days Expired</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expiredItems as $item)
                        @php
                            $daysExpired = now()->diffInDays($item->expiry_date);
                        @endphp
                        <tr class="expired-row">
                            <td>
                                <strong>{{ $item->product->name }}</strong>
                            </td>
                            <td class="text-center">
                                {{ $item->quantity_on_hand }} units
                            </td>
                            <td class="text-center">
                                <span class="badge badge-expired">{{ $item->expiry_date->format('M d, Y') }}</span>
                            </td>
                            <td class="text-center">
                                <span class="days-remaining days-critical">
                                    {{ $daysExpired }} days ago
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.inventory.show', $item) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.inventory.edit', $item) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- EXPIRING SOON -->
    @if($totalExpiring > 0)
        <h5 class="section-title">
            <i class="fas fa-clock"></i>Expiring Soon - Next 30 Days ({{ $totalExpiring }})
        </h5>
        
        <div class="report-table expiring-soon-rows">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th class="text-center">Current Stock</th>
                        <th class="text-center">Expiry Date</th>
                        <th class="text-center">Days Remaining</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expiringItems as $item)
                        @php
                            $daysRemaining = $item->expiry_date->diffInDays(now());
                            $urgency = $daysRemaining <= 7 ? 'critical' : 'warning';
                        @endphp
                        <tr class="expiring-row">
                            <td>
                                <strong>{{ $item->product->name }}</strong>
                            </td>
                            <td class="text-center">
                                {{ $item->quantity_on_hand }} units
                            </td>
                            <td class="text-center">
                                <span class="badge badge-expiring">{{ $item->expiry_date->format('M d, Y') }}</span>
                            </td>
                            <td class="text-center">
                                <span class="days-remaining days-{{ $urgency }}">
                                    {{ $daysRemaining }} days
                                    @if($urgency === 'critical')
                                        <i class="fas fa-exclamation ms-1"></i>
                                    @endif
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.inventory.show', $item) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.inventory.edit', $item) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($totalExpired === 0 && $totalExpiring === 0)
        <div class="empty-state">
            <i class="fas fa-check-circle"></i>
            <h5>All Products Healthy</h5>
            <p class="text-muted">No expired or soon-to-expire products.</p>
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-gasgo mt-3">
                <i class="fas fa-arrow-left me-2"></i>Back to Inventory
            </a>
        </div>
    @endif
</div>
@endsection
