@extends('layouts.app')

@section('title', 'Inventory Movements Ledger')

@section('content')
<div class="container-fluid py-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">
                <i class="fas fa-chart-line me-2"></i> Inventory Movements Ledger
            </h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.inventory.movements.export', request()->query()) }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-download me-2"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.inventory.movements') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="product_id" class="form-label">Product</label>
                    <select name="product_id" id="product_id" class="form-select">
                        <option value="">All Products</option>
                        @foreach($products as $id => $name)
                            <option value="{{ $id }}" {{ request('product_id') == $id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="type" class="form-label">Type</label>
                    <select name="type" id="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="stock_in" {{ request('type') === 'stock_in' ? 'selected' : '' }}>STOCK IN</option>
                        <option value="stock_out" {{ request('type') === 'stock_out' ? 'selected' : '' }}>STOCK OUT</option>
                        <option value="sale" {{ request('type') === 'sale' ? 'selected' : '' }}>SALE</option>
                        <option value="damage" {{ request('type') === 'damage' ? 'selected' : '' }}>DAMAGE</option>
                        <option value="return" {{ request('type') === 'return' ? 'selected' : '' }}>RETURN</option>
                        <option value="purchase" {{ request('type') === 'purchase' ? 'selected' : '' }}>PURCHASE</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="reference_type" class="form-label">Reference</label>
                    <select name="reference_type" id="reference_type" class="form-select">
                        <option value="">All References</option>
                        @foreach($referenceTypes as $refType)
                            <option value="{{ $refType }}" {{ request('reference_type') === $refType ? 'selected' : '' }}>
                                {{ $refType }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" 
                        value="{{ request('date_from') }}">
                </div>

                <div class="col-md-2">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" 
                        value="{{ request('date_to') }}">
                </div>

                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                </div>
            </form>

            @if(request()->anyFilled(['product_id', 'type', 'reference_type', 'date_from', 'date_to']))
                <div class="mt-3">
                    <a href="{{ route('admin.inventory.movements') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-times me-1"></i> Clear Filters
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Movements Table -->
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date/Time</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th class="text-center">Quantity</th>
                        <th>Reference</th>
                        <th>Ref. ID</th>
                        <th>Notes</th>
                        <th>Created By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr>
                            <td>
                                @php $movementDate = $movement->movement_date ?? $movement->created_at; @endphp
                                <small class="text-muted d-block">
                                    {{ $movementDate ? $movementDate->format('M d, Y') : '-' }}
                                </small>
                                <small>{{ $movementDate ? $movementDate->format('H:i A') : '-' }}</small>
                            </td>
                            <td>
                                <strong>{{ $movement->inventory->product->name ?? '-' }}</strong>
                            </td>
                            <td>
                                @php
                                    $movementType = strtolower((string) $movement->type);
                                    $typeClass = 'bg-secondary';
                                    if (in_array($movementType, ['stock_in', 'purchase'], true)) {
                                        $typeClass = 'bg-success';
                                    } elseif (in_array($movementType, ['stock_out', 'sale', 'damage'], true)) {
                                        $typeClass = 'bg-danger';
                                    } elseif ($movementType === 'return') {
                                        $typeClass = 'bg-warning text-dark';
                                    }
                                @endphp
                                <span class="badge {{ $typeClass }}">{{ strtoupper(str_replace('_', ' ', (string) $movement->type)) }}</span>
                            </td>
                            <td class="text-center">
                                @php $qty = (int) $movement->quantity_change; @endphp
                                <strong class="{{ $qty >= 0 ? 'text-success' : 'text-danger' }}">{{ $qty > 0 ? '+' : '' }}{{ $qty }}</strong>
                            </td>
                            <td>
                                @if($movement->reference_type)
                                    <span class="badge bg-info">{{ $movement->reference_type }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($movement->reference_id)
                                    @if($movement->reference_type === 'order' && $movement->order())
                                        <a href="{{ route('admin.orders.show', $movement->reference_id) }}" 
                                            class="text-decoration-none">
                                            #{{ $movement->reference_id }}
                                        </a>
                                    @else
                                        {{ $movement->reference_id }}
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($movement->notes)
                                    <small>{{ Str::limit($movement->notes, 50) }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($movement->creator)
                                    <small>{{ $movement->creator->name }}</small>
                                @else
                                    <span class="text-muted">System</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                No inventory movements found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($movements->hasPages())
            <div class="card-footer">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
