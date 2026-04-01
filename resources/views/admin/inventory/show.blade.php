@extends('layouts.admin')

@section('title', 'Inventory Details - ' . $inventory->product->name)

@section('admin-styles')
<style>
    .inv-page-header {
        background: linear-gradient(135deg, #1a6db0 0%, #2196f3 100%);
        color: #fff;
        padding: 26px 28px;
        border-radius: 14px;
        margin-bottom: 20px;
        box-shadow: 0 8px 24px rgba(26, 109, 176, 0.2);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
    }

    .inv-page-header h1 {
        margin: 0 0 6px;
        font-weight: 700;
        letter-spacing: 0.2px;
    }

    .inv-page-header p {
        margin: 0;
        opacity: 0.92;
        font-size: 0.95rem;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 22px;
    }

    .summary-card {
        background: #fff;
        border-radius: 12px;
        padding: 14px 16px;
        border: 1px solid #e8eef5;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
    }

    .summary-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        color: #6b7a8b;
        font-weight: 700;
        letter-spacing: 0.6px;
        margin-bottom: 4px;
    }

    .summary-value {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1e293b;
    }

    .details-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px 20px 10px;
        box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
        border: 1px solid #edf2f7;
        margin-bottom: 18px;
    }

    .details-card h5 {
        color: var(--gasgo-blue);
        font-weight: 700;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding: 13px 0;
        border-bottom: 1px dashed #e8edf3;
        align-items: center;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        font-weight: 600;
        color: #2f4a63;
        min-width: 150px;
    }

    .detail-value {
        font-size: 0.96rem;
        color: #1f2937;
        text-align: right;
    }

    .type-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .type-purchase {
        background: #d4edda;
        color: #155724;
    }
    
    .type-sale {
        background: #cfe2ff;
        color: #084298;
    }
    
    .type-adjustment {
        background: #e7d4f5;
        color: #5a189a;
    }
    
    .type-damage {
        background: #f8d7da;
        color: #721c24;
    }
    
    .type-return {
        background: #fff3cd;
        color: #856404;
    }
    
    .qty-positive {
        color: #27ae60;
        font-weight: 600;
    }
    
    .qty-negative {
        color: #e74c3c;
        font-weight: 600;
    }



    .movements-table {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
        border: 1px solid #edf2f7;
    }

    .movements-table .table th {
        background: #f3f8ff;
        color: #1a4f7d;
        border: none;
        padding: 13px !important;
        font-size: 0.85rem;
    }

    .movements-table .table td {
        padding: 13px !important;
        vertical-align: middle;
        border-top: 1px solid #eef3f8;
    }

    .movements-table .table tbody tr:hover {
        background: #f8fbff;
    }

    .status-chip {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 700;
        color: #fff;
    }

    .status-chip.status-active {
        background: #27ae60;
    }

    .status-chip.status-discontinued {
        background: #e74c3c;
    }

    .status-chip.status-damaged {
        background: #f39c12;
    }

    @media (max-width: 991px) {
        .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .inv-page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .detail-row {
            flex-direction: column;
            align-items: flex-start;
        }

        .detail-value {
            text-align: left;
        }
    }

    @media (max-width: 575px) {
        .summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-4">
    @php
        $resolveImageUrl = function (?string $path): ?string {
            if (! $path) {
                return null;
            }
            $normalized = ltrim($path, '/');
            if (str_starts_with($normalized, 'http://') || str_starts_with($normalized, 'https://')) {
                return $path;
            }
            if (str_starts_with($normalized, 'storage/') || str_starts_with($normalized, 'images/')) {
                return asset($normalized);
            }
            return asset('storage/' . $normalized);
        };
        $productImageUrl = $resolveImageUrl($inventory->product->image);
    @endphp
    
    <div class="inv-page-header">
        @if($productImageUrl)
        <div style="display:flex;align-items:center;gap:16px;flex:1;">
            <img src="{{ $productImageUrl }}" alt="{{ $inventory->product->name }}" style="height:70px;width:70px;object-fit:contain;background:#fff;padding:8px;border-radius:8px;">
            <div>
                <h1 class="mb-2">{{ $inventory->product->name }}</h1>
                <p class="mb-0">Inventory details and stock movements</p>
            </div>
        </div>
        @else
        <div>
            <h1 class="mb-2">{{ $inventory->product->name }}</h1>
            <p class="mb-0">Inventory details and stock movements</p>
        </div>
        @endif
        <div>
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">Current Stock</div>
            <div class="summary-value {{ $inventory->quantity_on_hand <= $inventory->reorder_level ? 'text-danger' : 'text-success' }}">
                {{ $inventory->quantity_on_hand }}
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Reorder Level</div>
            <div class="summary-value">{{ $inventory->reorder_level }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Status</div>
            <div class="summary-value" style="font-size: 1rem;">
                <span class="status-chip status-{{ $inventory->status }}">
                    {{ ucfirst($inventory->status) }}
                </span>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Supplier</div>
            <div class="summary-value" style="font-size: 1rem;">{{ $inventory->supplier ?? '-' }}</div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-6">
            <div class="details-card">
                <h5 class="mb-4"><i class="fas fa-info-circle me-2"></i>Product Information</h5>
                
                <div class="detail-row">
                    <span class="detail-label">Product Name</span>
                    <span class="detail-value fw-bold">{{ $inventory->product->name }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">SKU</span>
                    <span class="detail-value">{{ $inventory->product->sku ?? 'N/A' }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Price</span>
                    <span class="detail-value">₱{{ number_format($inventory->product->price, 2) }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Cost Price</span>
                    <span class="detail-value">₱{{ number_format($inventory->product->cost_price ?? 0, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="details-card">
                <h5 class="mb-4"><i class="fas fa-warehouse me-2"></i>Stock Information</h5>
                
                <div class="detail-row">
                    <span class="detail-label">Current Stock</span>
                    <span class="detail-value fw-bold {{ $inventory->quantity_on_hand <= $inventory->reorder_level ? 'text-danger' : 'text-success' }}">
                        {{ $inventory->quantity_on_hand }} units
                    </span>
                </div>
                
                @if(strtolower($inventory->product->category) === 'tank')
                <div class="detail-row">
                    <span class="detail-label">Empty Tanks Collected</span>
                    <span class="detail-value fw-bold text-warning">
                        {{ $inventory->empty_on_hand ?? 0 }} units
                    </span>
                </div>
                @endif
                
                <div class="detail-row">
                    <span class="detail-label">Reorder Level</span>
                    <span class="detail-value">{{ $inventory->reorder_level }} units</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Status</span>
                    <span class="detail-value">
                        <span class="status-chip status-{{ $inventory->status }}">
                            {{ ucfirst($inventory->status) }}
                        </span>
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Supplier</span>
                    <span class="detail-value">{{ $inventory->supplier ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="details-card">
                <h5 class="mb-4"><i class="fas fa-calendar me-2"></i>Tracking Information</h5>
                
                <div class="detail-row">
                    <span class="detail-label">Batch Number</span>
                    <span class="detail-value">{{ $inventory->batch_number ?? '-' }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Expiry Date</span>
                    <span class="detail-value">
                        @if($inventory->expiry_date)
                            {{ $inventory->expiry_date->format('M d, Y') }}
                            @if($inventory->expiry_date->isPast())
                                <span class="badge bg-danger ms-2">EXPIRED</span>
                            @elseif($inventory->expiry_date->diffInDays() <= 30)
                                <span class="badge bg-warning ms-2">EXPIRING SOON</span>
                            @endif
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Last Restocked</span>
                    <span class="detail-value">{{ $inventory->last_restocked?->format('M d, Y H:i') ?? 'Never' }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Created</span>
                    <span class="detail-value">{{ $inventory->created_at->format('M d, Y H:i') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Settings Section -->
    <div class="row mt-4">
        <div class="col-lg-12">
            <div class="details-card" style="background: #f8f9fa; border-left: 4px solid var(--gasgo-blue);">
                <h5 class="mb-4"><i class="fas fa-sliders-h me-2"></i>Inventory Settings</h5>
                
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h6 class="alert-heading"><i class="fas fa-exclamation-circle me-2"></i>Validation Errors</h6>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                <form action="{{ route('admin.inventory.update', $inventory) }}" method="POST" id="settingsForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-bell me-2"></i>Reorder Level</label>
                                <div class="input-group">
                                    <input type="number" name="reorder_level" class="form-control @error('reorder_level') is-invalid @enderror"
                                           value="{{ old('reorder_level', $inventory->reorder_level) }}" 
                                           min="0" required>
                                    <span class="input-group-text">units</span>
                                </div>
                                <small class="form-text text-muted">Alert when stock falls below this level</small>
                                @error('reorder_level')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-check-circle me-2"></i>Status</label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="">Select status...</option>
                                    <option value="active" {{ old('status', $inventory->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="discontinued" {{ old('status', $inventory->status) === 'discontinued' ? 'selected' : '' }}>Discontinued</option>
                                    <option value="damaged" {{ old('status', $inventory->status) === 'damaged' ? 'selected' : '' }}>Damaged</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-building me-2"></i>Supplier</label>
                                <input type="text" name="supplier" class="form-control @error('supplier') is-invalid @enderror"
                                       value="{{ old('supplier', $inventory->supplier) }}"
                                       placeholder="e.g., Premium Gas Co."
                                       maxlength="255">
                                <small class="form-text text-muted">Optional: Supplier name or contact</small>
                                @error('supplier')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-barcode me-2"></i>Batch Number</label>
                                <input type="text" name="batch_number" class="form-control @error('batch_number') is-invalid @enderror"
                                       value="{{ old('batch_number', $inventory->batch_number) }}"
                                       placeholder="e.g., BATCH2024001"
                                       maxlength="255">
                                <small class="form-text text-muted">Optional: For tracking and QC</small>
                                @error('batch_number')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-calendar me-2"></i>Expiry Date</label>
                                <input type="date" name="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror"
                                       value="{{ old('expiry_date', $inventory->expiry_date?->format('Y-m-d')) }}">
                                <small class="form-text text-muted">Optional: Expiration date</small>
                                @error('expiry_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 justify-content-end mt-3">
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-undo me-2"></i>Reset
                        </button>
                        <button type="submit" class="btn btn-gasgo">
                            <i class="fas fa-save me-2"></i>Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <div class="movements-table">
        <div style="padding: 20px; border-bottom: 1px solid #f0f0f0;">
            <h5><i class="fas fa-history me-2"></i>Stock Movement History</h5>
        </div>
        
        @if($movements->isEmpty())
            <div class="text-center py-5">
                <p class="text-muted">No stock movements recorded yet</p>
            </div>
        @else
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Type</th>
                        <th class="text-center">Quantity Change</th>
                        <th>Created By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movements as $movement)
                        <tr>
                            <td>
                                <small>{{ $movement->movement_date->format('M d, Y H:i') ?? $movement->created_at->format('M d, Y H:i') }}</small>
                            </td>
                            <td>
                                <span class="type-badge type-{{ $movement->type }}">
                                    {{ ucfirst(str_replace('_', ' ', $movement->type)) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="{{ $movement->quantity_change > 0 ? 'qty-positive' : 'qty-negative' }}">
                                    {{ $movement->quantity_change > 0 ? '+' : '' }}{{ $movement->quantity_change }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $movement->creator->name ?? 'System' }}</small>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="d-flex justify-content-center p-3" style="border-top: 1px solid #f0f0f0;">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
