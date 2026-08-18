@extends('layouts.customer')

@section('title', 'Privacy Policy - ' . trim(($homepageSettings->brand_name_primary ?? 'Gas') . ' ' . ($homepageSettings->brand_name_accent ?? 'Go')))

@section('styles')
<style>
    /* Scroll Progress Bar */
    #readingProgress {
        position: fixed;
        top: 0;
        left: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--gasgo-blue), var(--gasgo-orange));
        width: 0%;
        z-index: 9999;
        transition: width 0.1s ease;
    }

    /* Hero Header */
    .legal-hero {
        background: linear-gradient(135deg, #0f2e4a 0%, #1a6db0 60%, #2196f3 100%);
        color: white;
        padding: 60px 20px 75px 20px;
        position: relative;
        overflow: hidden;
    }
    .legal-hero::before {
        content: '';
        position: absolute;
        width: 380px;
        height: 380px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(247,148,29,0.25) 0%, rgba(247,148,29,0) 70%);
        top: -100px;
        right: -80px;
        pointer-events: none;
    }
    .legal-hero::after {
        content: '';
        position: absolute;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(33,150,243,0.3) 0%, rgba(33,150,243,0) 70%);
        bottom: -80px;
        left: -60px;
        pointer-events: none;
    }
    .legal-hero-content {
        position: relative;
        z-index: 2;
        max-width: 900px;
        margin: 0 auto;
        text-align: center;
    }
    .legal-breadcrumb {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.12);
        backdrop-filter: blur(8px);
        padding: 6px 16px;
        border-radius: 30px;
        font-size: 0.82rem;
        margin-bottom: 18px;
        border: 1px solid rgba(255,255,255,0.15);
    }
    .legal-breadcrumb a {
        color: #fff;
        text-decoration: none;
        opacity: 0.85;
        transition: opacity 0.2s;
    }
    .legal-breadcrumb a:hover { opacity: 1; }
    .legal-breadcrumb span { opacity: 0.5; }
    .legal-hero h1 {
        font-weight: 800;
        font-size: 2.4rem;
        letter-spacing: -0.5px;
        margin-bottom: 12px;
    }
    .legal-hero p {
        font-size: 1.05rem;
        color: rgba(255,255,255,0.9);
        max-width: 650px;
        margin: 0 auto 20px auto;
        line-height: 1.6;
    }
    .legal-meta-badges {
        display: flex;
        justify-content: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .meta-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.2);
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    /* Layout */
    .legal-wrapper {
        max-width: 1200px;
        margin: -40px auto 80px auto;
        padding: 0 20px;
        position: relative;
        z-index: 3;
    }
    .legal-grid {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 32px;
        align-items: start;
    }

    /* Sticky Sidebar */
    .legal-sidebar {
        background: white;
        border-radius: 20px;
        padding: 24px 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border: 1px solid #f1f5f9;
        position: sticky;
        top: 90px;
    }
    .sidebar-title {
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #94a3b8;
        margin-bottom: 14px;
        padding-left: 8px;
    }
    .toc-nav {
        list-style: none;
        padding: 0;
        margin: 0 0 20px 0;
    }
    .toc-nav li {
        margin-bottom: 4px;
    }
    .toc-nav a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 12px;
        color: #475569;
        font-size: 0.88rem;
        font-weight: 600;
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .toc-nav a i {
        font-size: 0.95rem;
        width: 18px;
        color: #94a3b8;
        transition: color 0.2s;
    }
    .toc-nav a:hover, .toc-nav a.active {
        background: #f0f7ff;
        color: var(--gasgo-blue);
    }
    .toc-nav a.active i, .toc-nav a:hover i {
        color: var(--gasgo-blue);
    }
    .sidebar-actions {
        border-top: 1px solid #f1f5f9;
        padding-top: 16px;
    }
    .btn-print {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #475569;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-print:hover {
        background: #f1f5f9;
        color: #1e293b;
        border-color: #cbd5e1;
    }

    /* Main Content Card */
    .legal-content-card {
        background: white;
        border-radius: 24px;
        padding: 44px 40px;
        box-shadow: 0 10px 35px rgba(0,0,0,0.05);
        border: 1px solid #f1f5f9;
    }
    .intro-banner {
        background: linear-gradient(135deg, #f0f7ff 0%, #e0f2fe 100%);
        border-left: 4px solid var(--gasgo-blue);
        border-radius: 14px;
        padding: 20px 24px;
        margin-bottom: 36px;
    }
    .intro-banner h4 {
        color: var(--gasgo-blue);
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 6px;
    }
    .intro-banner p {
        margin: 0;
        color: #334155;
        font-size: 0.92rem;
        line-height: 1.6;
    }

    .doc-section {
        margin-bottom: 44px;
        scroll-margin-top: 100px;
    }
    .doc-section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 12px;
    }
    .section-icon-badge {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--gasgo-blue), #2196f3);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        flex-shrink: 0;
    }
    .doc-section-header h3 {
        font-size: 1.35rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }
    .doc-section p, .doc-section li {
        color: #475569;
        font-size: 0.95rem;
        line-height: 1.7;
    }
    .doc-section ul {
        padding-left: 20px;
        margin-bottom: 20px;
    }
    .doc-section li {
        margin-bottom: 8px;
    }

    /* Category Cards Grid */
    .data-cards-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 18px;
        margin: 24px 0;
    }
    .data-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 22px 20px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .data-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.04);
        border-color: #cbd5e1;
    }
    .data-card-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        margin-bottom: 12px;
    }
    .icon-user { background: #e0f2fe; color: #0284c7; }
    .icon-location { background: #fef3c7; color: #d97706; }
    .icon-order { background: #dcfce7; color: #16a34a; }
    .icon-device { background: #f3e8ff; color: #9333ea; }

    .data-card h5 {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 6px;
    }
    .data-card p {
        font-size: 0.85rem;
        color: #64748b;
        margin: 0;
        line-height: 1.5;
    }

    /* Checklist Grid */
    .checklist-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
        margin: 20px 0;
    }
    .checklist-item {
        background: #fafafa;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        padding: 14px 16px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }
    .checklist-item i {
        color: #10b981;
        margin-top: 3px;
        font-size: 0.95rem;
    }
    .checklist-item span {
        font-size: 0.88rem;
        font-weight: 600;
        color: #334155;
    }

    /* Accordion FAQ */
    .faq-accordion .accordion-item {
        border: 1px solid #e2e8f0;
        border-radius: 14px !important;
        margin-bottom: 12px;
        overflow: hidden;
    }
    .faq-accordion .accordion-button {
        font-weight: 700;
        font-size: 0.95rem;
        color: #1e293b;
        background: #f8fafc;
        padding: 16px 20px;
    }
    .faq-accordion .accordion-button:not(.collapsed) {
        background: #f0f7ff;
        color: var(--gasgo-blue);
        box-shadow: none;
    }
    .faq-accordion .accordion-body {
        font-size: 0.9rem;
        color: #475569;
        line-height: 1.6;
        padding: 18px 20px;
        background: #ffffff;
    }

    /* Contact Card */
    .contact-cta-card {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: white;
        border-radius: 18px;
        padding: 30px;
        margin-top: 30px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 20px;
    }
    .contact-cta-card h4 {
        font-size: 1.15rem;
        font-weight: 700;
        margin-bottom: 4px;
    }
    .contact-cta-card p {
        margin: 0;
        color: #94a3b8;
        font-size: 0.88rem;
    }
    .contact-cta-links {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .btn-contact-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 30px;
        font-size: 0.85rem;
        font-weight: 700;
        text-decoration: none;
        background: rgba(255,255,255,0.12);
        color: white;
        border: 1px solid rgba(255,255,255,0.2);
        transition: all 0.2s;
    }
    .btn-contact-pill:hover {
        background: var(--gasgo-orange);
        border-color: var(--gasgo-orange);
        color: white;
    }

    /* Responsive */
    @media (max-width: 991px) {
        .legal-grid {
            grid-template-columns: 1fr;
        }
        .legal-sidebar {
            position: relative;
            top: 0;
            margin-bottom: 20px;
        }
        .legal-content-card {
            padding: 30px 20px;
        }
        .data-cards-grid, .checklist-grid {
            grid-template-columns: 1fr;
        }
    }
    @media print {
        .legal-sidebar, .legal-breadcrumb, .btn-print, header, footer, .scroll-to-top {
            display: none !important;
        }
        .legal-wrapper {
            margin: 0;
            padding: 0;
            max-width: 100%;
        }
        .legal-grid {
            display: block;
        }
        .legal-content-card {
            box-shadow: none;
            border: none;
            padding: 0;
        }
    }
</style>
@endsection

@section('content')
<!-- Scroll Progress Indicator -->
<div id="readingProgress"></div>

<!-- Hero Section -->
<div class="legal-hero">
    <div class="legal-hero-content">
        <div class="legal-breadcrumb">
            <a href="{{ route('customer.dashboard') }}"><i class="fas fa-home me-1"></i>Home</a>
            <span>/</span>
            <strong>Privacy Policy</strong>
        </div>
        <h1>Privacy Policy</h1>
        <p>Learn how {{ trim(($homepageSettings->brand_name_primary ?? 'Gas') . ' ' . ($homepageSettings->brand_name_accent ?? 'Go')) }} protects your personal information, delivery data, and digital security.</p>
        
        <div class="legal-meta-badges">
            <div class="meta-badge"><i class="fas fa-shield-alt text-warning"></i> DPA 2012 Compliant</div>
            <div class="meta-badge"><i class="fas fa-clock"></i> 5 min read</div>
            <div class="meta-badge"><i class="fas fa-sync-alt"></i> Updated: {{ date('F Y') }}</div>
        </div>
    </div>
</div>

<!-- Main Content Area -->
<div class="legal-wrapper">
    <div class="legal-grid">
        
        <!-- Sticky Table of Contents -->
        <aside class="legal-sidebar">
            <div class="sidebar-title">Table of Contents</div>
            <ul class="toc-nav" id="tocNav">
                <li><a href="#info-collection" class="active"><i class="fas fa-database"></i>1. Information Collected</a></li>
                <li><a href="#info-usage"><i class="fas fa-cogs"></i>2. How We Use Data</a></li>
                <li><a href="#data-security"><i class="fas fa-lock"></i>3. Security & Storage</a></li>
                <li><a href="#third-parties"><i class="fas fa-plug"></i>4. Third-Party Integrations</a></li>
                <li><a href="#user-rights"><i class="fas fa-user-shield"></i>5. Your Privacy Rights</a></li>
                <li><a href="#faq"><i class="fas fa-question-circle"></i>6. Frequently Asked</a></li>
                <li><a href="#contact"><i class="fas fa-headset"></i>7. Contact & Inquiries</a></li>
            </ul>

            <div class="sidebar-actions">
                <button type="button" class="btn-print" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Document
                </button>
            </div>
        </aside>

        <!-- Main Legal Content -->
        <main class="legal-content-card">
            
            <div class="intro-banner">
                <h4><i class="fas fa-user-lock me-2"></i>Our Commitment to Your Privacy</h4>
                <p>{{ trim(($homepageSettings->brand_name_primary ?? 'Gas') . ' ' . ($homepageSettings->brand_name_accent ?? 'Go')) }} values your trust. This Privacy Policy sets out how we handle your personal details, geolocations, and transactions when ordering LPG tanks, accessories, and requesting doorstep deliveries.</p>
            </div>

            <!-- Section 1: Information Collected -->
            <section class="doc-section" id="info-collection">
                <div class="doc-section-header">
                    <div class="section-icon-badge"><i class="fas fa-database"></i></div>
                    <h3>1. Information We Collect</h3>
                </div>
                <p>When you register an account, browse products, or checkout on {{ trim(($homepageSettings->brand_name_primary ?? 'Gas') . ' ' . ($homepageSettings->brand_name_accent ?? 'Go')) }}, we collect essential data to ensure seamless LPG fulfillment:</p>

                <div class="data-cards-grid">
                    <div class="data-card">
                        <div class="data-card-icon icon-user"><i class="fas fa-user"></i></div>
                        <h5>Account Identity</h5>
                        <p>Full name, email address, mobile number, and password hashes for authentication.</p>
                    </div>
                    <div class="data-card">
                        <div class="data-card-icon icon-location"><i class="fas fa-map-marker-alt"></i></div>
                        <h5>Geolocation & Delivery Pin</h5>
                        <p>Complete delivery address, barangay, landmarks, and precise GPS coordinates for mapping.</p>
                    </div>
                    <div class="data-card">
                        <div class="data-card-icon icon-order"><i class="fas fa-receipt"></i></div>
                        <h5>Orders & Loyalty</h5>
                        <p>Ordered cylinder sizes, payment method (COD or GCash ref ID), and accumulated reward points.</p>
                    </div>
                    <div class="data-card">
                        <div class="data-card-icon icon-device"><i class="fas fa-shield-alt"></i></div>
                        <h5>Security & Session Logs</h5>
                        <p>Encrypted session tokens, IP address, and login timestamps to protect against unauthorized access.</p>
                    </div>
                </div>
            </section>

            <!-- Section 2: How We Use Data -->
            <section class="doc-section" id="info-usage">
                <div class="doc-section-header">
                    <div class="section-icon-badge"><i class="fas fa-cogs"></i></div>
                    <h3>2. How We Use Your Data</h3>
                </div>
                <p>We process your personal information strictly for legitimate business and fulfillment functions:</p>

                <div class="checklist-grid">
                    <div class="checklist-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Order Processing & Verification</span>
                    </div>
                    <div class="checklist-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Real-Time Turn-by-Turn GPS Rider Tracking</span>
                    </div>
                    <div class="checklist-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Automated 6-Digit Password Reset Codes</span>
                    </div>
                    <div class="checklist-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Loyalty Points & Reward Voucher Calculation</span>
                    </div>
                    <div class="checklist-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Inventory & Refill Stock Optimization</span>
                    </div>
                    <div class="checklist-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Customer Support & Safety Alerts</span>
                    </div>
                </div>
            </section>

            <!-- Section 3: Data Security -->
            <section class="doc-section" id="data-security">
                <div class="doc-section-header">
                    <div class="section-icon-badge"><i class="fas fa-lock"></i></div>
                    <h3>3. Security & Storage Safeguards</h3>
                </div>
                <p>We implement stringent technical and organizational security protocols to prevent unauthorized access, loss, or disclosure:</p>
                <ul>
                    <li><strong>Bcrypt Password Hashing:</strong> User passwords are encrypted with strong cryptographic salt algorithms and are never stored in plaintext.</li>
                    <li><strong>Encrypted Connections (HTTPS/TLS):</strong> All communication between your device and our servers is encrypted in transit.</li>
                    <li><strong>Role-Based Access Control (RBAC):</strong> Riders only have temporary access to the delivery pin and contact number of orders assigned to them.</li>
                    <li><strong>Temporary Token Invalidation:</strong> Password reset OTP codes expire automatically within <strong>5 minutes</strong>.</li>
                </ul>
            </section>

            <!-- Section 4: Third-Party Integrations -->
            <section class="doc-section" id="third-parties">
                <div class="doc-section-header">
                    <div class="section-icon-badge"><i class="fas fa-plug"></i></div>
                    <h3>4. Third-Party Integrations</h3>
                </div>
                <p>To provide modern navigation and messaging capabilities, we interface with reliable third parties:</p>
                <ul>
                    <li><strong>Google OAuth:</strong> Facilitates secure, one-click login without exposing your Google password.</li>
                    <li><strong>OpenStreetMap & OSRM:</strong> Powers address geolocation lookup and interactive routing.</li>
                    <li><strong>Gmail SMTP:</strong> Transmits transactional verification and security messages securely.</li>
                </ul>
            </section>

            <!-- Section 5: Your Rights -->
            <section class="doc-section" id="user-rights">
                <div class="doc-section-header">
                    <div class="section-icon-badge"><i class="fas fa-user-shield"></i></div>
                    <h3>5. Your Privacy Rights</h3>
                </div>
                <p>In accordance with the Philippine Data Privacy Act of 2012 (RA 10173), you have the right to:</p>
                <ul>
                    <li><strong>Access & Review:</strong> View your profile data, transaction receipts, and reward points history.</li>
                    <li><strong>Modify & Update:</strong> Update your mobile phone number, delivery address, or password via your Account Profile.</li>
                    <li><strong>Data Erasure:</strong> Request the deletion of your account and removal of personal identifiers from our active records.</li>
                </ul>
            </section>

            <!-- Section 6: FAQ Accordion -->
            <section class="doc-section" id="faq">
                <div class="doc-section-header">
                    <div class="section-icon-badge"><i class="fas fa-question-circle"></i></div>
                    <h3>6. Frequently Asked Questions</h3>
                </div>

                <div class="accordion faq-accordion" id="privacyFaq">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Does GasGo store my GCash PIN or banking passwords?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#privacyFaq">
                            <div class="accordion-body">
                                <strong>No.</strong> We never ask for or store GCash MPINs or banking credentials. We only record the transaction reference number you provide for order verification.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Who can see my live location when ordering?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#privacyFaq">
                            <div class="accordion-body">
                                Only our dispatch administration and the assigned delivery rider can view your delivery drop-off location to fulfill the active order.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                How long is my password reset code valid?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#privacyFaq">
                            <div class="accordion-body">
                                For security purposes, every 6-digit verification code expires within <strong>5 minutes</strong> from the moment it is generated.
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section 7: Contact -->
            <section class="doc-section" id="contact">
                <div class="doc-section-header">
                    <div class="section-icon-badge"><i class="fas fa-headset"></i></div>
                    <h3>7. Contact Our Data Protection Team</h3>
                </div>
                <p>If you have any questions or data requests, our team is ready to assist you:</p>

                <div class="contact-cta-card">
                    <div>
                        <h4>Need privacy assistance?</h4>
                        <p>{{ $homepageSettings->contact_address ?? 'PNR Site Estacion San Miguel Calasiao Pangasinan' }}</p>
                    </div>
                    <div class="contact-cta-links">
                        <a href="tel:{{ preg_replace('/\s+/', '', $homepageSettings->contact_phone ?? '+639123456789') }}" class="btn-contact-pill">
                            <i class="fas fa-phone"></i> {{ $homepageSettings->contact_phone ?? 'Call Support' }}
                        </a>
                        <a href="mailto:{{ $homepageSettings->contact_email ?? 'gasgolpg@gmail.com' }}" class="btn-contact-pill">
                            <i class="fas fa-envelope"></i> Email Us
                        </a>
                    </div>
                </div>
            </section>

        </main>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Reading Progress Indicator
    window.addEventListener('scroll', function() {
        const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
        const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrolled = (winScroll / height) * 100;
        document.getElementById('readingProgress').style.width = scrolled + '%';
    });

    // ScrollSpy for Table of Contents
    const sections = document.querySelectorAll('.doc-section');
    const navLinks = document.querySelectorAll('#tocNav a');

    window.addEventListener('scroll', function() {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop - 120;
            if (pageYOffset >= sectionTop) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('active');
            }
        });
    });
</script>
@endsection
