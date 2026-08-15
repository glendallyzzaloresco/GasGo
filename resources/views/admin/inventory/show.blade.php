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

    /* Movement History Styles */
    .movement-filter-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #f0f4ff 100%);
        border-left: 4px solid var(--gasgo-blue);
    }

    .movement-filter-card .form-label {
        font-weight: 600;
        color: #2f4a63;
        font-size: 0.9rem;
        margin-bottom: 8px;
    }

    .movement-filter-card .form-control,
    .movement-filter-card .form-select {
        border-radius: 8px;
        border: 1px solid #d5e8f7;
        font-size: 0.9rem;
    }

    .movement-filter-card .form-control:focus,
    .movement-filter-card .form-select:focus {
        border-color: var(--gasgo-blue);
        box-shadow: 0 0 0 3px rgba(26, 109, 176, 0.1);
    }

    .movement-table {
        font-size: 0.9rem;
    }

    .movement-table thead th {
        background: linear-gradient(135deg, #f0f4ff 0%, #e3f2fd 100%);
        color: var(--gasgo-blue);
        font-weight: 700;
        border-bottom: 2px solid #d5e8f7;
    }

    .movement-table tbody tr:hover {
        background-color: rgba(26, 109, 176, 0.05);
    }

    .movement-table .badge {
        font-size: 0.75rem;
        padding: 6px 10px;
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
            if (str_starts_with($normalized, 'images/')) {
                return asset($normalized);
            }

            if (str_starts_with($normalized, 'storage/')) {
                $normalized = substr($normalized, 8);
            }

            return \Illuminate\Support\Facades\Storage::url($normalized);
        };
        $productImageUrl = $resolveImageUrl($inventory->product->image);
    @endphp
    
    <div class="inv-page-header">
        @if($productImageUrl)
        <div style="display:flex;align-items:center;gap:16px;flex:1;">
            <img src="{{ $productImageUrl }}" alt="{{ $inventory->product->name }}" style="height:70px;width:70px;object-fit:contain;background:#fff;padding:8px;border-radius:8px;">
            <div>
                <h1 class="mb-2">{{ $inventory->product->name }}</h1>
                <p class="mb-0">Inventory details and settings</p>
            </div>
        </div>
        @else
        <div>
            <h1 class="mb-2">{{ $inventory->product->name }}</h1>
            <p class="mb-0">Inventory details and settings</p>
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
            <div class="summary-value {{ $inventory->quantity_on_hand <= 5 ? 'text-danger' : 'text-success' }}">
                {{ $inventory->quantity_on_hand }}
            </div>
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
                    <span class="detail-value fw-bold {{ $inventory->quantity_on_hand <= 5 ? 'text-danger' : 'text-success' }}">
                        {{ $inventory->quantity_on_hand }} units
                    </span>
                </div>
                
                @if($inventory->supportsEmptyCylinderTracking())
                <div class="detail-row">
                    <span class="detail-label">Empty Tanks Collected</span>
                    <span class="detail-value fw-bold text-warning">
                        {{ $inventory->empty_on_hand ?? 0 }} units
                    </span>
                </div>
                @else
                <div class="detail-row">
                    <span class="detail-label">Empty Tanks Collected</span>
                    <span class="detail-value text-muted">N/A</span>
                </div>
                @endif
                
               
                
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

    <!-- Inventory Movement History -->
    <div class="row mt-4">
        <div class="col-lg-12">
            <div class="details-card">
                <h5 class="mb-4"><i class="fas fa-history me-2"></i>Movement History</h5>
                
                <!-- Date Filter -->
                <div class="card mb-4 movement-filter-card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.inventory.show', $inventory) }}" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="movement_date_from" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>From Date
                                </label>
                                <input type="date" name="movement_date_from" id="movement_date_from" class="form-control" 
                                    value="{{ request('movement_date_from') }}">
                            </div>

                            <div class="col-md-3">
                                <label for="movement_date_to" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>To Date
                                </label>
                                <input type="date" name="movement_date_to" id="movement_date_to" class="form-control" 
                                    value="{{ request('movement_date_to') }}">
                            </div>

                            <div class="col-md-2">
                                <label for="movement_type" class="form-label">Type</label>
                                <select name="movement_type" id="movement_type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="purchase" {{ request('movement_type') === 'purchase' ? 'selected' : '' }}>Purchase</option>
                                    <option value="sale" {{ request('movement_type') === 'sale' ? 'selected' : '' }}>Sale</option>
                                    <option value="adjustment" {{ request('movement_type') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                                    <option value="damage" {{ request('movement_type') === 'damage' ? 'selected' : '' }}>Damage</option>
                                    <option value="return" {{ request('movement_type') === 'return' ? 'selected' : '' }}>Return</option>
                                </select>
                            </div>

                            <div class="col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1">
                                    <i class="fas fa-search me-1"></i> Filter
                                </button>
                                @if(request()->anyFilled(['movement_date_from', 'movement_date_to', 'movement_type']))
                                    <a href="{{ route('admin.inventory.show', $inventory) }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i> Clear
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Movements Table -->
                @if(isset($movements) && $movements->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 movement-table">
                            <thead>
                                <tr>
                                    <th style="width: 15%;">Date/Time</th>
                                    <th style="width: 15%;">Type</th>
                                    <th style="width: 12%;" class="text-center">Quantity</th>
                                    <th style="width: 15%;">Reference</th>
                                    <th style="width: 12%;">Ref. ID</th>
                                    <th style="width: 20%;">Notes</th>
                                    <th style="width: 11%;">Created By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($movements as $movement)
                                    <tr>
                                        <td>
                                            @php $movementDate = $movement->movement_date ?? $movement->created_at; @endphp
                                            <small class="d-block text-muted">{{ $movementDate?->format('M d, Y') ?? '-' }}</small>
                                            <small>{{ $movementDate?->format('h:i A') ?? '-' }}</small>
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
                                            @if($movement->reference)
                                                <span class="badge bg-info text-white">{{ $movement->reference }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">—</small>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $movement->notes ?? '—' }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $movement->creator->name ?? 'System' }}</small>
                                            @php
                                                // Try to resolve an order and customer for sale movements referencing an order
                                                $order = null;
                                                $customerName = null;
                                                if ($movement->reference) {
                                                    $order = \App\Models\Order::where('order_number', $movement->reference)
                                                        ->orWhere('id', (int) filter_var($movement->reference, FILTER_SANITIZE_NUMBER_INT))
                                                        ->with('user')
                                                        ->first();
                                                }
                                                if ($order && $order->user) {
                                                    $customerName = $order->user->name;
                                                }
                                            @endphp
                                            @if($customerName)
                                                <div class="mt-1"><small class="text-muted">Customer: <strong>{{ $customerName }}</strong></small></div>
                                            @endif
                                            {{-- Show mark returned button only for new_cylinder sale movements on cylinder products --}}
                                            @php
                                                $notesLower = strtolower((string) $movement->notes);
                                                $isSaleType = ($movement->type === 'sale' || strtolower((string) $movement->type) === 'sale');
                                                $notesIndicateNew = str_contains($notesLower, 'new_cylinder') || str_contains($notesLower, 'new cylinder');
                                                $isCylinderProduct = ($movement->inventory?->product?->isCylinder() ?? false);

                                                // Fallback: resolve related order's transaction_type
                                                $orderTransactionIsNew = false;
                                                if (empty($notesIndicateNew) && isset($order) && $order) {
                                                    $orderTransactionIsNew = (($order->transaction_type ?? '') === 'new_cylinder');
                                                }

                                                $showMarkReturned = $isSaleType && $isCylinderProduct && ($notesIndicateNew || $orderTransactionIsNew);
                                            @endphp
                                            @if($showMarkReturned)
                                                <div class="mt-2">
                                                    <form method="POST" action="{{ route('admin.inventory.movement.mark-returned', $movement) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-primary">Mark Returned</button>
                                                    </form>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        @if(request()->anyFilled(['movement_date_from', 'movement_date_to', 'movement_type']))
                            No movements found for the selected filters.
                        @else
                            No movement history available for this product.
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
