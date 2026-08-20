@extends('layouts.customer')

@section('title', 'Customer Reviews & Ratings')

@section('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, var(--gasgo-blue) 0%, #2196f3 100%);
        color: white;
        padding: 50px 0 60px;
        margin-bottom: -30px;
        position: relative;
    }
    .page-header::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 60px;
        background: #ffffff;
        clip-path: ellipse(55% 100% at 50% 100%);
    }

    /* Scorecard styling */
    .rating-summary-card {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.06);
        padding: 32px;
        border: 1px solid #f0f0f0;
        margin-bottom: 30px;
    }
    .overall-score {
        font-size: 3.5rem;
        font-weight: 800;
        color: var(--gasgo-blue);
        line-height: 1;
    }
    .overall-stars {
        color: #f7941d;
        font-size: 1.3rem;
        margin-top: 6px;
    }
    .rating-bar-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 8px;
        font-size: 0.85rem;
        color: #64748b;
    }
    .rating-bar-progress {
        flex: 1;
        height: 8px;
        border-radius: 4px;
        background: #f1f5f9;
        overflow: hidden;
    }
    .rating-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #f7941d, #ff6b35);
        border-radius: 4px;
        transition: width 0.5s ease;
    }

    /* Filter Pills */
    .filter-pills-wrap {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 25px;
    }
    .filter-pill {
        padding: 8px 18px;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #475569;
        transition: all 0.25s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .filter-pill:hover, .filter-pill.active {
        background: var(--gasgo-orange);
        color: white;
        border-color: var(--gasgo-orange);
        box-shadow: 0 4px 12px rgba(247, 148, 29, 0.25);
    }

    /* Review Card */
    .review-item-card {
        background: #ffffff;
        border-radius: 18px;
        padding: 24px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 4px 16px rgba(0,0,0,0.04);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        transition: all 0.3s ease;
    }
    .review-item-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.08);
        border-color: #e2e8f0;
    }
    .review-stars {
        color: #f7941d;
        font-size: 0.95rem;
        margin-bottom: 12px;
    }
    .review-comment-text {
        color: #334155;
        font-size: 0.92rem;
        line-height: 1.6;
        margin-bottom: 14px;
    }
    .review-tag-badge {
        display: inline-block;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
        margin-right: 5px;
        margin-bottom: 6px;
    }
    .reviewer-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--gasgo-blue), #2196f3);
        color: white;
        font-weight: 700;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .reviewer-avatar.anonymous {
        background: linear-gradient(135deg, #64748b, #94a3b8);
    }
</style>
@endsection

@section('content')
<!-- Page Header -->
<section class="page-header">
    <div class="container text-center">
        <h1 class="fw-bold"><i class="fas fa-star me-2" style="color: #f7941d;"></i>Customer Reviews & Ratings</h1>
        <p class="mb-0" style="opacity:.9;">Verified feedback from valued households and businesses</p>
    </div>
</section>

<section class="container section-padding" style="position:relative; z-index:2; margin-top: 40px; margin-bottom: 60px;">
    <!-- Rating Summary Scorecard -->
    <div class="rating-summary-card" data-aos="fade-up">
        <div class="row align-items-center g-4">
            <div class="col-md-4 text-center border-end-md">
                <div class="overall-score">{{ number_format($averageRating, 1) }}</div>
                <div class="overall-stars">
                    @for($s = 1; $s <= 5; $s++)
                        @if($s <= round($averageRating))
                            <i class="fas fa-star"></i>
                        @else
                            <i class="far fa-star"></i>
                        @endif
                    @endfor
                </div>
                <p class="text-muted small mb-0 mt-2">Based on <strong>{{ $totalReviews }}</strong> verified reviews</p>
            </div>
            <div class="col-md-8 ps-md-4">
                <h6 class="fw-bold text-dark mb-3">Rating Breakdown</h6>
                @foreach([5, 4, 3, 2, 1] as $star)
                    @php
                        $count = $starCounts[$star] ?? 0;
                        $pct = $totalReviews > 0 ? ($count / $totalReviews) * 100 : 0;
                    @endphp
                    <div class="rating-bar-row">
                        <span style="width: 50px;" class="fw-semibold">{{ $star }} Stars</span>
                        <div class="rating-bar-progress">
                            <div class="rating-bar-fill" style="width: {{ $pct }}%;"></div>
                        </div>
                        <span style="width: 45px;" class="text-end fw-bold">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Rating Filters -->
    <div class="filter-pills-wrap" data-aos="fade-up">
        <a href="{{ route('reviews.index') }}" class="filter-pill {{ !request()->filled('rating') ? 'active' : '' }}">
            <i class="fas fa-list-ul"></i> All Reviews ({{ $totalReviews }})
        </a>
        @foreach([5, 4, 3, 2, 1] as $star)
            <a href="{{ route('reviews.index', ['rating' => $star]) }}" class="filter-pill {{ request('rating') == $star ? 'active' : '' }}">
                <i class="fas fa-star text-warning"></i> {{ $star }} Stars ({{ $starCounts[$star] ?? 0 }})
            </a>
        @endforeach
    </div>

    <!-- Reviews Grid -->
    <div class="row g-4">
        @forelse($reviews as $review)
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="{{ ($loop->iteration % 3) * 100 }}">
                <div class="review-item-card">
                    <div>
                        <!-- Header: Stars + Verified -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="review-stars mb-0">
                                @for($s = 1; $s <= 5; $s++)
                                    @if($s <= $review->rating)
                                        <i class="fas fa-star"></i>
                                    @else
                                        <i class="far fa-star text-muted"></i>
                                    @endif
                                @endfor
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1" style="font-size:0.72rem;">
                                <i class="fas fa-check-circle me-1"></i>Verified Order
                            </span>
                        </div>

                        <!-- Comment / Text -->
                        @if(!empty($review->comment))
                            <p class="review-comment-text">"{{ $review->comment }}"</p>
                        @else
                            <p class="review-comment-text text-muted fst-italic" style="font-size: 0.85rem;">
                                <i class="fas fa-thumbs-up me-1 text-primary"></i>Rated {{ $review->rating }} out of 5 stars
                            </p>
                        @endif

                        <!-- Service Tags -->
                        @if(!empty($review->service_tags) && is_array($review->service_tags))
                            <div class="mb-3">
                                @foreach($review->service_tags as $tag)
                                    <span class="review-tag-badge"><i class="fas fa-tag me-1 text-warning"></i>{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Footer: Author & Formatted Date -->
                    <div class="d-flex align-items-center gap-3 pt-3 border-top mt-2">
                        @if($review->is_anonymous)
                            <div class="reviewer-avatar anonymous">
                                <i class="fas fa-user-shield"></i>
                            </div>
                        @else
                            <div class="reviewer-avatar">
                                {{ strtoupper(substr($review->user->name ?? 'Customer', 0, 2)) }}
                            </div>
                        @endif
                        <div>
                            <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.9rem;">{{ $review->masked_author_name }}</h6>
                            <small class="text-muted" style="font-size: 0.78rem;">{{ $review->created_at->format('M d, Y') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="p-5 bg-white rounded-4 border shadow-sm d-inline-block" style="max-width: 500px;">
                    <i class="fas fa-star-half-alt text-warning fs-1 mb-3 d-block opacity-50"></i>
                    <h5 class="fw-bold text-dark">No Reviews Found</h5>
                    <p class="text-muted small mb-0">No reviews found matching your selected rating filter.</p>
                    <a href="{{ route('reviews.index') }}" class="btn btn-outline-primary btn-sm rounded-pill mt-3 px-4">View All Reviews</a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($reviews->hasPages())
        <div class="d-flex justify-content-center mt-5">
            {{ $reviews->links() }}
        </div>
    @endif
</section>
@endsection
