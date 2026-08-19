@extends('layouts.admin')

@section('title', 'Customer Reviews & Feedback - GasGo Admin')
@section('page-title', 'Customer Reviews & Ratings')
@section('nav-reviews', 'active')

@section('content')
<div class="container-fluid px-0">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p>Total Reviews</p>
                        <h3>{{ $totalReviews }}</h3>
                    </div>
                    <div class="stat-icon blue"><i class="fas fa-comments"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p>Average Rating</p>
                        <h3>⭐ {{ number_format($averageRating, 1) }} / 5</h3>
                    </div>
                    <div class="stat-icon orange"><i class="fas fa-star"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p>Featured on Home</p>
                        <h3>{{ \App\Models\ServiceReview::where('is_featured', true)->count() }}</h3>
                    </div>
                    <div class="stat-icon green"><i class="fas fa-home"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p>5-Star Reviews</p>
                        <h3>{{ \App\Models\ServiceReview::where('rating', 5)->count() }}</h3>
                    </div>
                    <div class="stat-icon purple" style="background:linear-gradient(135deg, #8b5cf6, #6366f1);"><i class="fas fa-award text-white"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 px-4 border-0 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <h5 class="fw-bold mb-0 text-dark">Reviews Management</h5>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-sm {{ !request()->hasAny(['rating', 'status']) ? 'btn-primary' : 'btn-light' }} rounded-pill px-3">
                    All Reviews
                </a>
                <a href="{{ route('admin.reviews.index', ['status' => 'featured']) }}" class="btn btn-sm {{ request('status') === 'featured' ? 'btn-primary' : 'btn-light' }} rounded-pill px-3">
                    <i class="fas fa-star text-warning me-1"></i>Featured on Home
                </a>
                <a href="{{ route('admin.reviews.index', ['rating' => 5]) }}" class="btn btn-sm {{ request('rating') == 5 ? 'btn-primary' : 'btn-light' }} rounded-pill px-3">
                    5 Stars
                </a>
                <a href="{{ route('admin.reviews.index', ['rating' => 4]) }}" class="btn btn-sm {{ request('rating') == 4 ? 'btn-primary' : 'btn-light' }} rounded-pill px-3">
                    4 Stars
                </a>
                <a href="{{ route('admin.reviews.index', ['rating' => 3]) }}" class="btn btn-sm {{ request('rating') == 3 ? 'btn-primary' : 'btn-light' }} rounded-pill px-3">
                    3 Stars or below
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Customer</th>
                        <th>Order</th>
                        <th>Rating</th>
                        <th>Feedback & Tags</th>
                        <th>Assigned Rider</th>
                        <th>Date</th>
                        <th>Homepage</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width:36px; height:36px; font-size:0.85rem;">
                                    {{ strtoupper(substr($review->user->name ?? 'C', 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $review->user->name ?? 'Deleted User' }}</div>
                                    <small class="text-muted">{{ $review->user->email ?? '' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($review->order)
                                <a href="{{ route('admin.orders.show', $review->order) }}" class="fw-semibold text-decoration-none">
                                    #{{ $review->order->order_number }}
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="text-warning text-nowrap">
                                @for($s = 1; $s <= 5; $s++)
                                    @if($s <= $review->rating)
                                        <i class="fas fa-star"></i>
                                    @else
                                        <i class="far fa-star text-muted"></i>
                                    @endif
                                @endfor
                                <span class="fw-bold ms-1 text-dark">({{ $review->rating }}/5)</span>
                            </div>
                        </td>
                        <td style="max-width: 280px;">
                            @if(!empty($review->service_tags) && is_array($review->service_tags))
                                <div class="d-flex flex-wrap gap-1 mb-1">
                                    @foreach($review->service_tags as $tag)
                                        <span class="badge bg-light text-dark border" style="font-size:0.7rem;">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            @endif
                            <div class="text-truncate text-secondary small" title="{{ $review->comment }}">
                                {{ $review->comment ?: 'No written comment' }}
                            </div>
                        </td>
                        <td>
                            @if($review->rider)
                                <span class="badge bg-info text-dark">{{ $review->rider->name }}</span>
                            @else
                                <span class="text-muted small">Standard Delivery</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $review->created_at->format('M d, Y') }}</small>
                        </td>
                        <td>
                            <form action="{{ route('admin.reviews.toggleFeatured', $review) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $review->is_featured ? 'btn-warning text-white' : 'btn-outline-secondary' }} rounded-pill" style="font-size:0.75rem;">
                                    <i class="fas fa-star me-1"></i>{{ $review->is_featured ? 'Featured' : 'Not Featured' }}
                                </button>
                            </form>
                        </td>
                        <td>
                            <form action="{{ route('admin.reviews.toggleApproval', $review) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $review->is_approved ? 'btn-success-subtle text-success border-success-subtle' : 'btn-secondary' }} rounded-pill" style="font-size:0.75rem;">
                                    {{ $review->is_approved ? 'Approved' : 'Hidden' }}
                                </button>
                            </form>
                        </td>
                        <td class="text-end pe-4">
                            <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this review?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Review">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-star-half-alt fs-1 text-secondary mb-3 d-block opacity-50"></i>
                            <h6 class="fw-bold">No Reviews Found</h6>
                            <p class="small mb-0">Customer delivery reviews will appear here once submitted.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reviews->hasPages())
        <div class="card-footer bg-white p-3 border-0">
            {{ $reviews->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
