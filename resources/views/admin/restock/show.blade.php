@extends('layouts.app')

@section('title', 'Restock #' . $restock->id)

@section('content')
<div class="container-fluid py-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">
                <i class="fas fa-box me-2"></i> Restock #{{ $restock->id }}
            </h1>
        </div>
        <div class="col-md-4 text-end">
            @if($restock->status === 'DRAFT')
                <a href="{{ route('admin.restock.edit', $restock) }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-edit me-2"></i> Edit
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Info Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Status</h6>
                    @if($restock->status === 'DRAFT')
                        <span class="badge bg-warning fs-6">Draft</span>
                    @else
                        <span class="badge bg-success fs-6">Received</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Supplier</h6>
                    <p class="mb-0">{{ $restock->supplier_name ?? '—' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Created By</h6>
                    <p class="mb-0">{{ $restock->creator->name }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Created Date</h6>
                    <p class="mb-0">{{ $restock->created_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Restock Items -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Restock Items ({{ $restock->items->count() }})</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th class="text-center">Category</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-center">Current Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($restock->items as $item)
                        <tr>
                            <td><strong>{{ $item->product->name }}</strong></td>
                            <td class="text-center">{{ $item->product->category ?? '—' }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-center">{{ $item->product->stock }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Inventory Movements (if received) -->
    @if($restock->status === 'RECEIVED' && $restock->movements->count() > 0)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Inventory Movements</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th class="text-center">Quantity</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($restock->movements as $movement)
                            <tr>
                                <td>{{ $movement->movement_date->format('M d, Y H:i') }}</td>
                                <td>{{ $movement->product->name }}</td>
                                <td class="text-center"><span class="badge bg-success">+{{ $movement->quantity }}</span></td>
                                <td>{{ $movement->reference_type }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Actions -->
    <div class="d-flex gap-2">
        @if($restock->status === 'DRAFT')
            <button type="button" class="btn btn-success" id="markReceivedBtn">
                <i class="fas fa-check me-2"></i> Mark as Received
            </button>
        @endif
        <a href="{{ route('admin.restock.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back to Restocks
        </a>
    </div>
</div>

@if($restock->status === 'DRAFT')
<script>
document.getElementById('markReceivedBtn').addEventListener('click', function() {
    if (confirm('Mark this restock as received? This will update inventory.')) {
        fetch('{{ route("admin.restock.mark-received", $restock) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }
});
</script>
@endif
@endsection
