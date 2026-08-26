@extends('layouts.admin')

@section('title', 'Homepage Maintenance - Admin')
@section('page-title', 'Homepage Maintenance')
@section('nav-settings', 'active')

@section('admin-styles')
<style>
    .hp-controls-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(15, 23, 42, 0.06);
        background: #ffffff;
        overflow: hidden;
    }
    .hp-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #0f172a 100%);
        color: #ffffff;
        padding: 28px 32px;
        position: relative;
        overflow: hidden;
    }
    .hp-header::after {
        content: '';
        position: absolute;
        top: -50%; right: -20%;
        width: 300px; height: 300px;
        background: radial-gradient(circle, rgba(247, 148, 29, 0.15) 0%, transparent 70%);
        pointer-events: none;
    }
    .hp-header-title {
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: -0.4px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .hp-header-title i {
        background: rgba(247, 148, 29, 0.2);
        color: var(--gasgo-orange, #f7941d);
        width: 42px; height: 42px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.15rem;
    }
    .hp-nav-tabs-wrapper {
        background: #f8fafc;
        padding: 12px 24px;
        border-bottom: 1px solid #e2e8f0;
    }
    .hp-nav-tabs {
        border-bottom: none;
        gap: 10px;
    }
    .hp-nav-tabs .nav-link {
        border: none;
        color: #64748b;
        font-weight: 700;
        font-size: 0.92rem;
        padding: 10px 20px;
        border-radius: 12px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 9px;
        background: transparent;
    }
    .hp-nav-tabs .nav-link:hover {
        color: var(--gasgo-orange, #f7941d);
        background: rgba(247, 148, 29, 0.08);
    }
    .hp-nav-tabs .nav-link.active {
        color: var(--gasgo-orange, #f7941d);
        background: #ffffff;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.08);
    }

    @media (max-width: 768px) {
        .hp-header {
            padding: 16px 14px;
        }
        .hp-header-title {
            font-size: 1.1rem;
        }
        .hp-header-title i {
            width: 34px;
            height: 34px;
            font-size: 0.95rem;
        }
        .hp-nav-tabs-wrapper {
            padding: 8px 10px;
        }
        .hp-nav-tabs {
            display: flex !important;
            flex-wrap: nowrap !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            gap: 6px;
        }
        .hp-nav-tabs::-webkit-scrollbar { display: none; }
        .hp-nav-tabs .nav-link {
            flex-shrink: 0;
            padding: 7px 12px;
            font-size: 0.78rem;
            white-space: nowrap;
        }
        .preset-studio-box {
            padding: 14px 12px;
        }
    }
    
    /* Preset Studio Box */
    .preset-studio-box {
        background: linear-gradient(135deg, #fffdf5 0%, #fff7d6 100%);
        border: 2px solid #fce694;
        border-radius: 16px;
        padding: 22px;
        box-shadow: 0 6px 20px rgba(247, 148, 29, 0.06);
        position: relative;
        transition: all 0.3s ease;
    }
    .preset-studio-box:hover {
        border-color: #f7941d;
    }
    .btn-preset-card {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        color: #334155;
        font-weight: 700;
        font-size: 0.86rem;
        padding: 9px 18px;
        border-radius: 25px;
        transition: all 0.25s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    }
    .btn-preset-card:hover {
        transform: translateY(-2px);
        border-color: var(--gasgo-orange, #f7941d);
        box-shadow: 0 6px 16px rgba(247, 148, 29, 0.2);
        color: #0f172a;
    }

    .section-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 24px;
        height: 100%;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        transition: all 0.25s ease;
    }
    .section-box:hover {
        border-color: #cbd5e1;
        box-shadow: 0 8px 25px rgba(15, 23, 42, 0.05);
    }
    .section-title {
        font-size: 1.08rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .section-title-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        background: rgba(247, 148, 29, 0.12);
        color: var(--gasgo-orange, #f7941d);
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem;
    }

    .form-label {
        font-weight: 700;
        font-size: 0.88rem;
        color: #1e293b;
        margin-bottom: 6px;
    }
    .form-control, .form-select {
        border-radius: 12px;
        border: 1px solid #cbd5e1;
        padding: 11px 16px;
        font-size: 0.92rem;
        background: #fafbfc;
        transition: all 0.2s ease;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--gasgo-orange, #f7941d);
        box-shadow: 0 0 0 4px rgba(247, 148, 29, 0.15);
        background: #ffffff;
    }
    .form-text {
        font-size: 0.8rem;
        color: #64748b;
        margin-top: 5px;
    }

    .image-preview-card {
        background: #ffffff;
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        padding: 20px;
        text-align: center;
        position: relative;
        transition: all 0.25s ease;
    }
    .image-preview-card:hover {
        border-color: var(--gasgo-orange, #f7941d);
        background: #fdfefe;
    }
    .img-preview-holder {
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 14px;
        border: 1px solid #e2e8f0;
    }
    .img-preview-holder img {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
    }

    .color-swatch-input {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 6px 14px;
    }
    .color-swatch-input input[type="color"] {
        border: none;
        width: 38px;
        height: 38px;
        border-radius: 10px;
        cursor: pointer;
        background: transparent;
    }
    .color-swatch-input input[type="text"] {
        border: none;
        font-family: monospace;
        font-weight: 700;
        color: #1e293b;
        outline: none;
        width: 100%;
        font-size: 0.95rem;
    }

    .btn-save-main {
        background: linear-gradient(135deg, #f7941d 0%, #e07f0c 100%);
        color: #ffffff;
        border: none;
        font-weight: 800;
        font-size: 0.98rem;
        padding: 13px 32px;
        border-radius: 12px;
        box-shadow: 0 6px 20px rgba(247, 148, 29, 0.35);
        transition: all 0.25s ease;
    }
    .btn-save-main:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(247, 148, 29, 0.48);
        color: #ffffff;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-0">

    @if($errors->any())
        <div class="alert alert-danger mb-4 border-0 shadow-sm" style="border-radius: 14px;">
            <div class="fw-bold mb-1"><i class="fas fa-exclamation-triangle me-2"></i>Please resolve the following issue(s):</div>
            <ul class="mb-0 ps-3 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card hp-controls-card mb-4">
        <!-- Header -->
        <div class="hp-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div class="hp-header-title">
                    <i class="fas fa-sliders-h"></i>
                    Homepage Controls & Branding Studio
                </div>
                <small class="text-white-50 mt-1 d-block">Manage store titles, 1-click industry presets, promotional graphics, and theme styling site-wide.</small>
            </div>
            <a href="{{ route('admin.settings') }}" class="btn btn-light btn-sm px-3 rounded-pill fw-bold" style="color:#0f172a; transition: all 0.2s;">
                <i class="fas fa-arrow-left me-1"></i> Back to Settings
            </a>
        </div>

        <!-- Form Start -->
        <form id="homepageControlsForm" action="{{ route('admin.settings.homepage.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Navigation Tabs Wrapper -->
            <div class="hp-nav-tabs-wrapper">
                <ul class="nav nav-pills hp-nav-tabs" id="homepageTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="hero-tab" data-bs-toggle="tab" data-bs-target="#hero-tab-pane" type="button" role="tab">
                            <i class="fas fa-heading"></i> Brand & Hero Banner
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="content-tab" data-bs-toggle="tab" data-bs-target="#content-tab-pane" type="button" role="tab">
                            <i class="fas fa-bullhorn"></i> Content & Promotions
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="branding-tab" data-bs-toggle="tab" data-bs-target="#branding-tab-pane" type="button" role="tab">
                            <i class="fas fa-images"></i> Logos & Media Assets
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="theme-tab" data-bs-toggle="tab" data-bs-target="#theme-tab-pane" type="button" role="tab">
                            <i class="fas fa-paint-brush"></i> Theme Colors & Footer
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Tab Contents -->
            <div class="card-body p-4">
                <div class="tab-content" id="homepageTabContent">
                    
                    <!-- TAB 1: Brand & Hero Banner -->
                    <div class="tab-pane fade show active" id="hero-tab-pane" role="tabpanel" tabindex="0">
                        <div class="row g-4">
                            <!-- 1-Click Business Niche Presets -->
                            <div class="col-12">
                                <div class="preset-studio-box">
                                    <div class="d-flex align-items-center gap-2 text-warning fw-bold mb-2 fs-6">
                                        <i class="fas fa-wand-magic-sparkles fs-5"></i> 1-Click Business Niche Presets Studio
                                    </div>
                                    <p class="text-muted small mb-3">Click what you sell to instantly auto-tune store branding, slogans, color palettes, and headlines:</p>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button" class="btn btn-preset-card btn-preset" data-preset="lpg">
                                            <i class="fas fa-fire text-warning fs-6"></i> LPG Gas Business
                                        </button>
                                        <button type="button" class="btn btn-preset-card btn-preset" data-preset="water">
                                            <i class="fas fa-tint text-info fs-6"></i> Water Refilling Station
                                        </button>
                                        <button type="button" class="btn btn-preset-card btn-preset" data-preset="foods">
                                            <i class="fas fa-utensils text-danger fs-6"></i> Foods & Meals
                                        </button>
                                        <button type="button" class="btn btn-preset-card btn-preset" data-preset="appliances">
                                            <i class="fas fa-blender text-success fs-6"></i> Appliances
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Brand Name Settings -->
                            <div class="col-12">
                                <div class="section-box">
                                    <div class="section-title">
                                        <span class="section-title-icon"><i class="fas fa-shield-alt"></i></span> Store Identity & Brand Name
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Brand Name (Primary)</label>
                                            <input type="text" name="brand_name_primary" class="form-control" value="{{ old('brand_name_primary', $settings->brand_name_primary) }}" required>
                                            <div class="form-text">Primary text part (e.g. <code>Gas</code>, <code>Aqua</code>, <code>Quick</code>).</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Brand Name (Accent)</label>
                                            <input type="text" name="brand_name_accent" class="form-control" value="{{ old('brand_name_accent', $settings->brand_name_accent) }}" required>
                                            <div class="form-text">Color accent part (e.g. <code>Go</code>, <code>Pure</code>, <code>Mart</code>).</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Industry / Product Noun</label>
                                            <input type="text" name="industry_noun" class="form-control" value="{{ old('industry_noun', $settings->industry_noun ?? 'LPG') }}" placeholder="LPG, Water, Groceries...">
                                            <div class="form-text">Core product noun (e.g. <code>LPG</code>, <code>Water</code>, <code>Products</code>).</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Hero Main Heading -->
                            <div class="col-12">
                                <div class="section-box">
                                    <div class="section-title">
                                        <span class="section-title-icon"><i class="fas fa-star"></i></span> Hero Section Headline & Tagline
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Hero Title Prefix</label>
                                            <input type="text" name="hero_title_prefix" class="form-control" value="{{ old('hero_title_prefix', $settings->hero_title_prefix) }}" placeholder="Fast, Reliable">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Hero Highlight Word</label>
                                            <input type="text" name="hero_title_highlight" class="form-control" value="{{ old('hero_title_highlight', $settings->hero_title_highlight) }}" placeholder="LPG Delivery">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Hero Title Suffix</label>
                                            <input type="text" name="hero_title_suffix" class="form-control" value="{{ old('hero_title_suffix', $settings->hero_title_suffix) }}" placeholder="to Your Door">
                                        </div>

                                        <div class="col-md-8">
                                            <label class="form-label fw-semibold">Hero Subtitle</label>
                                            <textarea name="hero_subtitle" rows="2" class="form-control" placeholder="Fast, reliable LPG delivery right to your door...">{{ old('hero_subtitle', $settings->hero_subtitle) }}</textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Hero Primary Button Label</label>
                                            <input type="text" name="hero_primary_button_label" class="form-control" value="{{ old('hero_primary_button_label', $settings->hero_primary_button_label) }}" placeholder="Browse Products">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: Content & Promotions -->
                    <div class="tab-pane fade" id="content-tab-pane" role="tabpanel" tabindex="0">
                        <div class="row g-4">
                            <!-- Products Section -->
                            <div class="col-md-6">
                                <div class="section-box">
                                    <div class="section-title">
                                        <span class="section-title-icon"><i class="fas fa-boxes"></i></span> Products Section Controls
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Products Section Title</label>
                                        <input type="text" name="products_section_title" class="form-control" value="{{ old('products_section_title', $settings->products_section_title) }}" placeholder="Our Products">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Products Section Subtitle</label>
                                        <input type="text" name="products_section_subtitle" class="form-control" value="{{ old('products_section_subtitle', $settings->products_section_subtitle) }}" placeholder="Choose from our range of LPG tanks...">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">View All Button Text</label>
                                        <input type="text" name="products_view_all_label" class="form-control" value="{{ old('products_view_all_label', $settings->products_view_all_label) }}" placeholder="View All Products">
                                    </div>
                                </div>
                            </div>

                            <!-- Promo Banner Section -->
                            <div class="col-md-6">
                                <div class="section-box">
                                    <div class="section-title">
                                        <span class="section-title-icon"><i class="fas fa-tags"></i></span> Promotional Banner Controls
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Promo Headline Title</label>
                                        <input type="text" name="promo_title" class="form-control" value="{{ old('promo_title', $settings->promo_title) }}" placeholder="Get FREE Items on every purchase!">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Promo Description</label>
                                        <textarea name="promo_subtitle" rows="2" class="form-control" placeholder="Register now and start earning loyalty points...">{{ old('promo_subtitle', $settings->promo_subtitle) }}</textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Promo Action Button Label</label>
                                        <input type="text" name="promo_button_label" class="form-control" value="{{ old('promo_button_label', $settings->promo_button_label) }}" placeholder="Register Now">
                                    </div>
                                </div>
                            </div>

                            <!-- How It Works Section Controls -->
                            <div class="col-md-6">
                                <div class="section-box">
                                    <div class="section-title">
                                        <span class="section-title-icon"><i class="fas fa-list-ol"></i></span> "How It Works" Section Controls
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Section Heading Title</label>
                                        <input type="text" name="how_it_works_title" class="form-control" value="{{ old('how_it_works_title', $settings->how_it_works_title ?? 'How It Works') }}" placeholder="How It Works">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Section Subtitle</label>
                                        <input type="text" name="how_it_works_subtitle" class="form-control" value="{{ old('how_it_works_subtitle', $settings->how_it_works_subtitle ?? 'Order in 4 easy steps') }}" placeholder="Order in 4 easy steps">
                                    </div>
                                </div>
                            </div>

                            <!-- Why Choose Us Section Controls -->
                            <div class="col-md-6">
                                <div class="section-box">
                                    <div class="section-title">
                                        <span class="section-title-icon"><i class="fas fa-thumbs-up"></i></span> "Why Choose Us" Section Controls
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Section Heading Title</label>
                                        <input type="text" name="why_choose_title" class="form-control" value="{{ old('why_choose_title', $settings->why_choose_title ?? 'Why Choose Us') }}" placeholder="Why Choose Us">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Section Subtitle</label>
                                        <input type="text" name="why_choose_subtitle" class="form-control" value="{{ old('why_choose_subtitle', $settings->why_choose_subtitle ?? 'We make delivery convenient, safe, and rewarding') }}" placeholder="We make delivery convenient, safe, and rewarding">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: Logos & Media Assets -->
                    <div class="tab-pane fade" id="branding-tab-pane" role="tabpanel" tabindex="0">
                        <div class="row g-4">
                            <!-- Navbar Logo -->
                            <div class="col-md-6">
                                <div class="image-preview-card">
                                    <div class="fw-bold mb-2 text-dark d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-gem me-1 text-warning"></i> Navbar Logo</span>
                                        <span class="badge bg-light text-secondary border">Header</span>
                                    </div>
                                    <div class="img-preview-holder">
                                        <img id="navLogoPreview" src="{{ $settings->navbar_logo_url }}" alt="Navbar Logo">
                                    </div>
                                    <input type="file" name="navbar_logo" class="form-control mb-2 image-input-preview" data-preview="#navLogoPreview" accept="image/*">
                                    <div class="form-check text-start">
                                        <input class="form-check-input" type="checkbox" name="remove_navbar_logo" value="1" id="removeNavbarLogo">
                                        <label class="form-check-label text-muted small" for="removeNavbarLogo">Remove custom navbar logo</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Logo -->
                            <div class="col-md-6">
                                <div class="image-preview-card">
                                    <div class="fw-bold mb-2 text-dark d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-shoe-prints me-1 text-info"></i> Footer Logo</span>
                                        <span class="badge bg-light text-secondary border">Footer</span>
                                    </div>
                                    <div class="img-preview-holder">
                                        <img id="footerLogoPreview" src="{{ $settings->footer_logo_url }}" alt="Footer Logo">
                                    </div>
                                    <input type="file" name="footer_logo" class="form-control mb-2 image-input-preview" data-preview="#footerLogoPreview" accept="image/*">
                                    <div class="form-check text-start">
                                        <input class="form-check-input" type="checkbox" name="remove_footer_logo" value="1" id="removeFooterLogo">
                                        <label class="form-check-label text-muted small" for="removeFooterLogo">Remove custom footer logo</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Hero Graphic Image -->
                            <div class="col-md-6">
                                <div class="image-preview-card">
                                    <div class="fw-bold mb-2 text-dark d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-desktop me-1 text-primary"></i> Hero Graphic / Illustration</span>
                                        <span class="badge bg-light text-secondary border">Homepage Hero</span>
                                    </div>
                                    <div class="img-preview-holder">
                                        @if($settings->home_hero_image_url)
                                            <img id="heroImagePreview" src="{{ $settings->home_hero_image_url }}" alt="Hero Image">
                                        @else
                                            <img id="heroImagePreview" src="{{ asset('images/logo-gasgo.png') }}" alt="Default Graphic" style="opacity: 0.3;">
                                        @endif
                                    </div>
                                    <input type="file" name="home_hero_image" class="form-control mb-2 image-input-preview" data-preview="#heroImagePreview" accept="image/*">
                                    <div class="form-check text-start">
                                        <input class="form-check-input" type="checkbox" name="remove_home_hero_image" value="1" id="removeHeroImage">
                                        <label class="form-check-label text-muted small" for="removeHeroImage">Remove custom hero image</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Promo Banner Image -->
                            <div class="col-md-6">
                                <div class="image-preview-card">
                                    <div class="fw-bold mb-2 text-dark d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-ad me-1 text-success"></i> Promo Card Banner</span>
                                        <span class="badge bg-light text-secondary border">Promo Card</span>
                                    </div>
                                    <div class="img-preview-holder">
                                        @if($settings->promo_banner_image_url)
                                            <img id="promoBannerPreview" src="{{ $settings->promo_banner_image_url }}" alt="Promo Banner">
                                        @else
                                            <img id="promoBannerPreview" src="{{ asset('images/logo-gasgo.png') }}" alt="Default Banner" style="opacity: 0.3;">
                                        @endif
                                    </div>
                                    <input type="file" name="promo_banner_image" class="form-control mb-2 image-input-preview" data-preview="#promoBannerPreview" accept="image/*">
                                    <div class="form-check text-start">
                                        <input class="form-check-input" type="checkbox" name="remove_promo_banner_image" value="1" id="removePromoImage">
                                        <label class="form-check-label text-muted small" for="removePromoImage">Remove custom promo background</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: Theme Colors & Footer -->
                    <div class="tab-pane fade" id="theme-tab-pane" role="tabpanel" tabindex="0">
                        <div class="row g-4">
                            <!-- Theme Colors -->
                            <div class="col-12">
                                <div class="section-box">
                                    <div class="section-title">
                                        <span class="section-title-icon"><i class="fas fa-palette"></i></span> Color Palette Settings
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-3 col-sm-6">
                                            <label class="form-label fw-semibold">Primary Color</label>
                                            <div class="color-swatch-input">
                                                <input type="color" id="primaryColorPicker" value="{{ old('primary_color', $settings->primary_color ?? '#1a6db0') }}">
                                                <input type="text" name="primary_color" id="primaryColorText" value="{{ old('primary_color', $settings->primary_color ?? '#1a6db0') }}" maxlength="7">
                                            </div>
                                        </div>

                                        <div class="col-md-3 col-sm-6">
                                            <label class="form-label fw-semibold">Accent Color</label>
                                            <div class="color-swatch-input">
                                                <input type="color" id="accentColorPicker" value="{{ old('accent_color', $settings->accent_color ?? '#f7941d') }}">
                                                <input type="text" name="accent_color" id="accentColorText" value="{{ old('accent_color', $settings->accent_color ?? '#f7941d') }}" maxlength="7">
                                            </div>
                                        </div>

                                        <div class="col-md-3 col-sm-6">
                                            <label class="form-label fw-semibold">Page Background</label>
                                            <div class="color-swatch-input">
                                                <input type="color" id="bgColorPicker" value="{{ old('background_color', $settings->background_color ?? '#f4f7fb') }}">
                                                <input type="text" name="background_color" id="bgColorText" value="{{ old('background_color', $settings->background_color ?? '#f4f7fb') }}" maxlength="7">
                                            </div>
                                            <small class="text-muted d-block mt-1">Recommended light tints: <code>#f8f9fa</code>, <code>#f4f7fb</code>, <code>#eef2f7</code></small>
                                        </div>

                                        <div class="col-md-3 col-sm-6">
                                            <label class="form-label fw-semibold">Sidebar Background</label>
                                            <div class="color-swatch-input">
                                                <input type="color" id="sidebarBgColorPicker" value="{{ old('sidebar_bg_color', $settings->sidebar_bg_color ?? '#111b35') }}">
                                                <input type="text" name="sidebar_bg_color" id="sidebarBgColorText" value="{{ old('sidebar_bg_color', $settings->sidebar_bg_color ?? '#111b35') }}" maxlength="7">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer & Contact Details -->
                            <div class="col-12">
                                <div class="section-box">
                                    <div class="section-title">
                                        <span class="section-title-icon"><i class="fas fa-address-card"></i></span> Footer Description & Contact Details
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Footer About / Description</label>
                                            <textarea name="footer_description" rows="3" class="form-control" placeholder="We are your trusted delivery service provider...">{{ old('footer_description', $settings->footer_description) }}</textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Office Address</label>
                                            <input type="text" name="contact_address" class="form-control" value="{{ old('contact_address', $settings->contact_address) }}" placeholder="123 Store Street, City">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Contact Phone Number</label>
                                            <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone', $settings->contact_phone) }}" placeholder="+63 912 345 6789">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Contact Email Address</label>
                                            <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $settings->contact_email) }}" placeholder="support@gasgo.com">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Business Operating Hours</label>
                                            <input type="text" name="contact_hours" class="form-control" value="{{ old('contact_hours', $settings->contact_hours) }}" placeholder="Mon-Sun: 6:00 AM - 9:00 PM">
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <i class="fas fa-share-alt text-primary"></i>
                                        <h6 class="fw-bold mb-0">Social Media Links (Footer Icons)</h6>
                                    </div>
                                    <p class="text-muted small mb-3">Setup your official social media profile URLs. Leave blank to hide the icon from the customer footer.</p>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold"><i class="fab fa-facebook text-primary me-1"></i>Facebook URL</label>
                                            <input type="url" name="facebook_url" class="form-control" value="{{ old('facebook_url', $settings->facebook_url) }}" placeholder="https://facebook.com/yourbrand">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold"><i class="fab fa-twitter text-info me-1"></i>Twitter / X URL</label>
                                            <input type="url" name="twitter_url" class="form-control" value="{{ old('twitter_url', $settings->twitter_url) }}" placeholder="https://twitter.com/yourbrand">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold"><i class="fab fa-instagram text-danger me-1"></i>Instagram URL</label>
                                            <input type="url" name="instagram_url" class="form-control" value="{{ old('instagram_url', $settings->instagram_url) }}" placeholder="https://instagram.com/yourbrand">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold"><i class="fab fa-youtube text-danger me-1"></i>YouTube URL</label>
                                            <input type="url" name="youtube_url" class="form-control" value="{{ old('youtube_url', $settings->youtube_url) }}" placeholder="https://youtube.com/@yourbrand">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold"><i class="fab fa-tiktok text-dark me-1"></i>TikTok URL (Optional)</label>
                                            <input type="url" name="tiktok_url" class="form-control" value="{{ old('tiktok_url', $settings->tiktok_url) }}" placeholder="https://tiktok.com/@yourbrand">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Footer Save Actions -->
            <div class="card-footer bg-light border-top p-3 d-flex justify-content-between align-items-center">
                <div class="text-muted small ms-2">
                    <i class="fas fa-info-circle me-1"></i> Changes take effect immediately upon saving.
                </div>
                <button type="submit" class="btn btn-save-main">
                    <i class="fas fa-save me-2"></i> Save Homepage Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Image Live Preview
        document.querySelectorAll('.image-input-preview').forEach(input => {
            input.addEventListener('change', function () {
                const targetSelector = this.getAttribute('data-preview');
                const targetImg = document.querySelector(targetSelector);
                if (targetImg && this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        targetImg.src = e.target.result;
                        targetImg.style.opacity = '1';
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        });

        // Color Picker Binding
        function bindColorPicker(pickerId, textId) {
            const picker = document.getElementById(pickerId);
            const text = document.getElementById(textId);
            if (picker && text) {
                picker.addEventListener('input', function () {
                    text.value = this.value;
                });
                text.addEventListener('input', function () {
                    if (this.value.match(/^#[0-9A-Fa-f]{6}$/)) {
                        picker.value = this.value;
                    }
                });
            }
        }

        bindColorPicker('primaryColorPicker', 'primaryColorText');
        bindColorPicker('accentColorPicker', 'accentColorText');
        bindColorPicker('bgColorPicker', 'bgColorText');
        bindColorPicker('sidebarBgColorPicker', 'sidebarBgColorText');

        // Niche Preset Presets Map
        const presets = {
            lpg: {
                brand_primary: 'Gas', brand_accent: 'Go', industry_noun: 'LPG Tanks',
                prefix: 'Fast, Reliable', highlight: 'LPG Delivery', suffix: 'to Your Door',
                subtitle: 'Fast, reliable LPG delivery right to your door. Earn loyalty rewards with every order.',
                button_label: 'Browse LPG Tanks', products_title: 'Our LPG Products',
                products_subtitle: 'Choose from our range of 11kg, 22kg tanks and safety accessories',
                promo_title: 'Get FREE items with every order!',
                promo_subtitle: 'Register now and start earning points with every tank purchase.',
                footer_desc: 'Your trusted partner for fast, reliable LPG delivery. Real-time order tracking.',
                how_title: 'How It Works', how_sub: 'Order your LPG cylinder in 4 easy steps',
                why_title: 'Why Choose Us?', why_sub: 'Certified products with guaranteed safety',
                primary_color: '#1a6db0', accent_color: '#f7941d', bg_color: '#f4f7fb', sidebar_color: '#111b35'
            },
            water: {
                brand_primary: 'Aqua', brand_accent: 'Pure', industry_noun: 'Purified Water',
                prefix: 'Pure, Clean', highlight: 'Water Delivery', suffix: 'to Your Doorstep',
                subtitle: 'Fresh, purified drinking water delivered directly to your home or office.',
                button_label: 'Order Water Now', products_title: 'Our Water Products',
                products_subtitle: 'Choose from 5-Gallon round, slim containers, and bottled water',
                promo_title: 'Get 1 FREE Water Gallon Refill on First Order!',
                promo_subtitle: 'Register today and start earning water loyalty points.',
                footer_desc: 'Your trusted neighborhood water refilling station providing 100% pure, safe drinking water.',
                how_title: 'How It Works', how_sub: 'Order purified water in 4 simple steps',
                why_title: 'Why Choose AquaPure?', why_sub: 'Certified 21-stage purification process',
                primary_color: '#0088cc', accent_color: '#00b4d8', bg_color: '#f0f9ff', sidebar_color: '#03045e'
            },
            foods: {
                brand_primary: 'Food', brand_accent: 'Go', industry_noun: 'Food & Meals',
                prefix: 'Hot, Fresh', highlight: 'Food & Meals', suffix: 'Delivered to You',
                subtitle: 'Delicious meals, snacks, and refreshing drinks delivered hot and fast to your doorstep.',
                button_label: 'Order Food Now', products_title: 'Our Food & Menu',
                products_subtitle: 'Freshly cooked rice meals, tasty snacks, and refreshing beverages',
                promo_title: 'Free Drink with Every Meal Order!',
                promo_subtitle: 'Sign up now and start earning food reward points with every dish.',
                footer_desc: 'Your favorite local food & meal delivery service. Freshly cooked with love.',
                how_title: 'Order Food in 4 Steps', how_sub: 'Pick your favorites and get them delivered hot & fast',
                why_title: 'Why Choose FoodGo?', why_sub: 'Hot & fresh ingredients with express delivery',
                primary_color: '#e03131', accent_color: '#ff922b', bg_color: '#fff5f5', sidebar_color: '#2b1d1d'
            },
            appliances: {
                brand_primary: 'Appliance', brand_accent: 'Hub', industry_noun: 'Appliances',
                prefix: 'Durable, Modern', highlight: 'Appliances & Stoves', suffix: 'Delivered Home',
                subtitle: 'High quality gas stoves, burners, safety regulators, and kitchen appliances.',
                button_label: 'Explore Appliances', products_title: 'Our Appliances',
                products_subtitle: 'Single/double burner stoves, heavy-duty regulators, hoses, and kitchen tools',
                promo_title: '10% OFF Your First Kitchen Appliance Order!',
                promo_subtitle: 'Create an account for official warranty tracking and special discounts.',
                footer_desc: 'Your trusted source for quality gas stoves and certified appliances.',
                how_title: 'How It Works', how_sub: 'Order appliances in 4 simple steps',
                why_title: 'Why Choose ApplianceHub?', why_sub: 'Certified authentic brands with reliable warranty',
                primary_color: '#0ca678', accent_color: '#15aabf', bg_color: '#e6fcf5', sidebar_color: '#083329'
            }
        };

        document.querySelectorAll('.btn-preset').forEach(btn => {
            btn.addEventListener('click', async function () {
                const key = this.getAttribute('data-preset');
                const p = presets[key];
                if (!p) return;

                const confirmed = await window.gasgoConfirm({
                    title: 'Apply Theme Preset',
                    text: `Apply "${this.textContent.trim()}" preset values to the form?`,
                    icon: 'question',
                    confirmButtonText: 'Yes, Apply Preset'
                });

                if (confirmed) {
                    const setVal = (name, val) => {
                        const el = document.querySelector(`[name="${name}"]`);
                        if (el) el.value = val;
                    };

                    setVal('brand_name_primary', p.brand_primary);
                    setVal('brand_name_accent', p.brand_accent);
                    setVal('industry_noun', p.industry_noun);
                    setVal('hero_title_prefix', p.prefix);
                    setVal('hero_title_highlight', p.highlight);
                    setVal('hero_title_suffix', p.suffix);
                    setVal('hero_subtitle', p.subtitle);
                    setVal('hero_primary_button_label', p.button_label);
                    setVal('products_section_title', p.products_title);
                    setVal('products_section_subtitle', p.products_subtitle);
                    setVal('promo_title', p.promo_title);
                    setVal('promo_subtitle', p.promo_subtitle);
                    setVal('footer_description', p.footer_desc);
                    setVal('how_it_works_title', p.how_title);
                    setVal('how_it_works_subtitle', p.how_sub);
                    setVal('why_choose_title', p.why_title);
                    setVal('why_choose_subtitle', p.why_sub);

                    setVal('primary_color', p.primary_color);
                    setVal('accent_color', p.accent_color);
                    setVal('background_color', p.bg_color);
                    setVal('sidebar_bg_color', p.sidebar_color);

                    document.getElementById('primaryColorPicker').value = p.primary_color;
                    document.getElementById('accentColorPicker').value = p.accent_color;
                    document.getElementById('bgColorPicker').value = p.bg_color;
                    document.getElementById('sidebarBgColorPicker').value = p.sidebar_color;
                }
            });
        });
    });
</script>
@endsection
