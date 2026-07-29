@extends('layouts.admin')

@section('title', 'Homepage Maintenance - GasGo Admin')
@section('page-title', 'Homepage Maintenance')
@section('nav-settings', 'active')

@section('content')
<div class="container-fluid px-0">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger mb-4">
            <div class="fw-bold mb-1">Please fix the following:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-palette me-2" style="color:var(--gasgo-orange);"></i>Homepage Controls
            </h5>
            <a href="{{ route('admin.settings') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Settings
            </a>
        </div>
        <div class="card-body">
            <form id="siteThemeForm" action="{{ route('admin.settings.homepage.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Brand Name (Primary)</label>
                        <input type="text" name="brand_name_primary" class="form-control" value="{{ old('brand_name_primary', $settings->brand_name_primary) }}" required>
                        <div class="form-text">Shown as first part of brand text in navbar/footer.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Brand Name (Accent)</label>
                        <input type="text" name="brand_name_accent" class="form-control" value="{{ old('brand_name_accent', $settings->brand_name_accent) }}" required>
                        <div class="form-text">Shown as highlighted part of the brand text.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Hero Title Prefix</label>
                        <input type="text" name="hero_title_prefix" class="form-control" value="{{ old('hero_title_prefix', $settings->hero_title_prefix) }}" placeholder="Fast, Reliable">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Hero Highlight</label>
                        <input type="text" name="hero_title_highlight" class="form-control" value="{{ old('hero_title_highlight', $settings->hero_title_highlight) }}" placeholder="LPG Delivery">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Hero Title Suffix</label>
                        <input type="text" name="hero_title_suffix" class="form-control" value="{{ old('hero_title_suffix', $settings->hero_title_suffix) }}" placeholder="to Your Door">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Hero Subtitle</label>
                        <textarea name="hero_subtitle" rows="2" class="form-control">{{ old('hero_subtitle', $settings->hero_subtitle) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Hero Primary Button Label</label>
                        <input type="text" name="hero_primary_button_label" class="form-control" value="{{ old('hero_primary_button_label', $settings->hero_primary_button_label) }}" placeholder="Browse Products">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Products Section Title</label>
                        <input type="text" name="products_section_title" class="form-control" value="{{ old('products_section_title', $settings->products_section_title) }}" placeholder="Our Products">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Products Section Subtitle</label>
                        <input type="text" name="products_section_subtitle" class="form-control" value="{{ old('products_section_subtitle', $settings->products_section_subtitle) }}" placeholder="Choose from our range...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">View All Button Label</label>
                        <input type="text" name="products_view_all_label" class="form-control" value="{{ old('products_view_all_label', $settings->products_view_all_label) }}" placeholder="View All Products">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Promo Title</label>
                        <input type="text" name="promo_title" class="form-control" value="{{ old('promo_title', $settings->promo_title) }}" placeholder="New User? Get FREE Delivery...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Promo Subtitle</label>
                        <input type="text" name="promo_subtitle" class="form-control" value="{{ old('promo_subtitle', $settings->promo_subtitle) }}" placeholder="Register now and start earning...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Promo Button Label</label>
                        <input type="text" name="promo_button_label" class="form-control" value="{{ old('promo_button_label', $settings->promo_button_label) }}" placeholder="Register Now">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Navbar Logo</label>
                        <input type="file" name="navbar_logo" class="form-control" accept="image/*">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="remove_navbar_logo" value="1" id="removeNavbarLogo">
                            <label class="form-check-label" for="removeNavbarLogo">Remove custom navbar logo</label>
                        </div>
                        <div class="mt-2 small text-muted">Current: {{ $settings->navbar_logo_url }}</div>
                        <img src="{{ $settings->navbar_logo_url }}" alt="Navbar Logo" style="height:48px; margin-top:8px;">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Footer Logo</label>
                        <input type="file" name="footer_logo" class="form-control" accept="image/*">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="remove_footer_logo" value="1" id="removeFooterLogo">
                            <label class="form-check-label" for="removeFooterLogo">Remove custom footer logo</label>
                        </div>
                        <div class="mt-2 small text-muted">Current: {{ $settings->footer_logo_url }}</div>
                        <img src="{{ $settings->footer_logo_url }}" alt="Footer Logo" style="height:48px; margin-top:8px;">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Homepage Hero Image</label>
                        <input type="file" name="home_hero_image" class="form-control" accept="image/*">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="remove_home_hero_image" value="1" id="removeHeroImage">
                            <label class="form-check-label" for="removeHeroImage">Remove custom hero image</label>
                        </div>
                        @if($settings->home_hero_image_url)
                            <img src="{{ $settings->home_hero_image_url }}" alt="Hero Image" style="max-height:120px; margin-top:8px; border-radius:8px;">
                        @else
                            <div class="small text-muted mt-2">No custom hero image uploaded.</div>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Promo Banner Image</label>
                        <input type="file" name="promo_banner_image" class="form-control" accept="image/*">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="remove_promo_banner_image" value="1" id="removePromoImage">
                            <label class="form-check-label" for="removePromoImage">Remove custom promo background</label>
                        </div>
                        @if($settings->promo_banner_image_url)
                            <img src="{{ $settings->promo_banner_image_url }}" alt="Promo Banner" style="max-height:120px; margin-top:8px; border-radius:8px;">
                        @else
                            <div class="small text-muted mt-2">No custom promo image uploaded.</div>
                        @endif
                    </div>

                    <div class="col-12">                        <label class="form-label fw-semibold">Primary Color</label>
                        <input type="color" name="primary_color" class="form-control form-control-color" value="{{ old('primary_color', $settings->primary_color ?? '#1a6db0') }}" title="Choose primary color">
                        <div class="form-text">Used for primary buttons, accents, and highlights.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Accent Color</label>
                        <input type="color" name="accent_color" class="form-control form-control-color" value="{{ old('accent_color', $settings->accent_color ?? '#f7941d') }}" title="Choose accent color">
                        <div class="form-text">Used for secondary accents and status highlights.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Background Color</label>
                        <input type="color" name="background_color" class="form-control form-control-color" value="{{ old('background_color', $settings->background_color ?? '#f4f7fb') }}" title="Choose background color">
                        <div class="form-text">Page background for admin/customer areas.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Sidebar Background</label>
                        <input type="color" name="sidebar_bg_color" class="form-control form-control-color" value="{{ old('sidebar_bg_color', $settings->sidebar_bg_color ?? '#111b35') }}" title="Choose sidebar background color">
                        <div class="form-text">Sidebar background color for admin navigation.</div>
                    </div>

                    <div class="col-12">                        <label class="form-label fw-semibold">Footer Description</label>
                        <textarea name="footer_description" rows="4" class="form-control">{{ old('footer_description', $settings->footer_description) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Contact Address</label>
                        <input type="text" name="contact_address" class="form-control" value="{{ old('contact_address', $settings->contact_address) }}" placeholder="Office address">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Contact Phone</label>
                        <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone', $settings->contact_phone) }}" placeholder="+63 ...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Contact Email</label>
                        <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $settings->contact_email) }}" placeholder="info@example.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Contact Hours</label>
                        <input type="text" name="contact_hours" class="form-control" value="{{ old('contact_hours', $settings->contact_hours) }}" placeholder="Mon-Sun: 6AM - 10PM">
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end">
                    <button class="btn" style="background:var(--gasgo-orange);color:#fff;border-radius:10px;font-weight:600;">
                        <i class="fas fa-save me-2"></i>Save Homepage Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('siteThemeForm');
        if (!form) {
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const basePath = window.location.pathname.includes('/public/')
            ? window.location.pathname.split('/public/')[0] + '/public'
            : '';
        const themeApiUrl = basePath + '/api/theme';

        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            const formData = new FormData(form);
            const navbarLogo = formData.get('navbar_logo');

            if (navbarLogo instanceof File && navbarLogo.size > 0) {
                formData.set('logo', navbarLogo);
            }

            formData.set('primaryColor', formData.get('primary_color') || '#1a6db0');
            formData.set('accentColor', formData.get('accent_color') || '#f7941d');
            formData.set('backgroundColor', formData.get('background_color') || '#f4f7fb');
            formData.set('sidebarBackground', formData.get('sidebar_bg_color') || '#111b35');
            formData.set('footerDescription', formData.get('footer_description') || '');
            formData.set('contactAddress', formData.get('contact_address') || '');
            formData.set('contactPhone', formData.get('contact_phone') || '');

            try {
                const response = await fetch(themeApiUrl, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData,
                });

                if (!response.ok) {
                    throw new Error('Theme API update failed.');
                }

                form.submit();
            } catch (error) {
                console.error(error);
                alert('Theme update could not be synced. Please try again.');
            }
        });
    });
</script>
@endsection
