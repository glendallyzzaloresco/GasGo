@extends('layouts.admin')

@section('title', 'Inventory Settings - ' . $inventory->product->name)

@section('styles')
<style>
    .edit-form {
        background: white;
        border-radius: 8px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .form-section {
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .form-section:last-child {
        border-bottom: none;
    }
    
    .form-section h5 {
        color: var(--gasgo-blue);
        margin-bottom: 20px;
        font-weight: 600;
    }
    
    .header-section {
        background: linear-gradient(135deg, var(--gasgo-blue) 0%, #2196f3 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
    }
    
    .product-info {
        background: #f8f9fa;
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 24px;
        border-left: 4px solid var(--gasgo-blue);
    }
    
    .product-info strong {
        color: var(--gasgo-blue);
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-4">
    <div class="header-section">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-2">Inventory Settings</h1>
                <p class="mb-0">Configure settings for {{ $inventory->product->name }}</p>
            </div>
            <a href="{{ route('admin.inventory.show', $inventory) }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5 class="alert-heading"><i class="fas fa-exclamation-circle me-2"></i>Validation Errors</h5>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="edit-form">
        <div class="product-info">
            <strong>{{ $inventory->product->name }}</strong>
        </div>

        <form action="{{ route('admin.inventory.update', $inventory) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Stock Overview (Read-Only) -->
            <div class="form-section">
                <h5><i class="fas fa-warehouse me-2"></i>Stock Overview</h5>
                
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Stock Management:</strong> All stock adjustments must be made through the "Adjust Stock" modal to maintain complete audit trails.
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Full Tanks Available</label>
                            <div class="input-group">
                                <input type="number" class="form-control" value="{{ $inventory->quantity_on_hand }}" disabled>
                                <span class="input-group-text">units</span>
                            </div>
                            <small class="form-text text-muted">Read-only: Managed via Adjust Stock modal</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Empty Tanks Collected</label>
                            <div class="input-group">
                                <input type="number" class="form-control" value="{{ $inventory->empty_on_hand }}" disabled>
                                <span class="input-group-text">units</span>
                            </div>
                            <small class="form-text text-muted">Read-only: Tracked from deliveries</small>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Status & Supplier -->
            <div class="form-section">
                <h5><i class="fas fa-cogs me-2"></i>Status & Supplier</h5>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="">Select status...</option>
                                <option value="active" {{ old('status', $inventory->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="discontinued" {{ old('status', $inventory->status) === 'discontinued' ? 'selected' : '' }}>Discontinued</option>
                                <option value="damaged" {{ old('status', $inventory->status) === 'damaged' ? 'selected' : '' }}>Damaged</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Supplier</label>
                            <input type="text" name="supplier" class="form-control @error('supplier') is-invalid @enderror"
                                   value="{{ old('supplier', $inventory->supplier) }}"
                                   placeholder="e.g., Premium Gas Co."
                                   maxlength="255">
                            <small class="form-text text-muted">Optional: Supplier name or contact</small>
                            @error('supplier')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tracking Information -->
            <div class="form-section">
                <h5><i class="fas fa-barcode me-2"></i>Tracking Information</h5>
                
            </div>
            <!-- Form Actions -->
            <div class="d-flex gap-2 justify-content-between mt-4">
                <a href="{{ route('admin.inventory.show', $inventory) }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
                <button type="submit" class="btn btn-gasgo">
                    <i class="fas fa-save me-2"></i>Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
