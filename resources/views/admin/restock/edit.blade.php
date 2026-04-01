@extends('layouts.app')

@section('title', 'Edit Restock')

@section('content')
<div class="container-fluid py-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">
                <i class="fas fa-edit me-2"></i> Edit Restock #{{ $restock->id }}
            </h1>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.restock.update', $restock) }}" method="POST" id="restockForm">
                @csrf
                @method('PUT')

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="supplier_name" class="form-label">Supplier Name</label>
                        <input type="text" id="supplier_name" name="supplier_name" class="form-control @error('supplier_name') is-invalid @enderror" 
                            value="{{ old('supplier_name', $restock->supplier_name) }}" required>
                        @error('supplier_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Items Section -->
                <div class="mb-4">
                    <h5 class="mb-3">Restock Items</h5>
                    <div id="itemsContainer">
                        @foreach($restock->items as $index => $item)
                            <div class="restock-item card mb-3">
                                <div class="card-body">
                                    <div class="row align-items-end">
                                        <div class="col-md-8">
                                            <label class="form-label">Product</label>
                                            <select name="items[{{ $index }}][product_id]" class="form-select product-select" required>
                                                <option value="">Select a product</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" {{ $item->product_id === $product->id ? 'selected' : '' }}>
                                                        {{ $product->name }} (Stock: {{ $product->stock }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Quantity</label>
                                            <div class="input-group">
                                                <input type="number" name="items[{{ $index }}][quantity]" class="form-control" min="1" value="{{ $item->quantity }}" required>
                                                <button type="button" class="btn btn-outline-danger removeItem" onclick="this.closest('.restock-item').remove()">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" class="btn btn-outline-secondary mt-2" onclick="addItem()">
                        <i class="fas fa-plus me-2"></i> Add Another Item
                    </button>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Update Restock
                    </button>
                    <a href="{{ route('admin.restock.show', $restock) }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let itemCount = {{ $restock->items->count() }};

function addItem() {
    const container = document.getElementById('itemsContainer');
    const item = `
        <div class="restock-item card mb-3">
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label class="form-label">Product</label>
                        <select name="items[${itemCount}][product_id]" class="form-select product-select" required>
                            <option value="">Select a product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} (Stock: {{ $product->stock }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Quantity</label>
                        <div class="input-group">
                            <input type="number" name="items[${itemCount}][quantity]" class="form-control" min="1" value="1" required>
                            <button type="button" class="btn btn-outline-danger removeItem">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', item);
    
    // Add event listener to new remove button
    container.querySelector('.restock-item:last-child .removeItem').onclick = function() {
        this.closest('.restock-item').remove();
    };
    
    itemCount++;
}
</script>
@endsection
