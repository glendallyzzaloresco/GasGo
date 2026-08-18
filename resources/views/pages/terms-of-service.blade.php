@extends('layouts.customer')

@section('title', 'Terms of Service - ' . trim(($homepageSettings->brand_name_primary ?? 'Gas') . ' ' . ($homepageSettings->brand_name_accent ?? 'Go')))

@section('styles')
<style>
    /* Scroll Progress Bar */
    #readingProgress {
        position: fixed;
        top: 0;
        left: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--gasgo-orange), var(--gasgo-blue));
        width: 0%;
        z-index: 9999;
        transition: width 0.1s ease;
    }

    /* Hero Header */
    .legal-hero {
        background: linear-gradient(135deg, #1e1b4b 0%, #1a6db0 65%, #f7941d 100%);
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
        background: radial-gradient(circle, rgba(247,148,29,0.3) 0%, rgba(247,148,29,0) 70%);
        top: -100px;
        right: -80px;
        pointer-events: none;
    }
    .legal-hero::after {
        content: '';
        position: absolute;
        width: 320px;
        height: 320px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(33,150,243,0.25) 0%, rgba(33,150,243,0) 70%);
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
        background: #fff7ed;
        color: var(--gasgo-orange);
    }
    .toc-nav a.active i, .toc-nav a:hover i {
        color: var(--gasgo-orange);
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
        background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
        border-left: 4px solid var(--gasgo-orange);
        border-radius: 14px;
        padding: 20px 24px;
        margin-bottom: 36px;
    }
    .intro-banner h4 {
        color: #c2410c;
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 6px;
    }
    .intro-banner p {
        margin: 0;
        color: #7c2d12;
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
        background: linear-gradient(135deg, var(--gasgo-orange), #ff9800);
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

    /* Safety Cards Grid */
    .safety-cards-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 18px;
        margin: 24px 0;
    }
    .safety-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 22px 20px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .safety-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.04);
        border-color: #cbd5e1;
    }
    .safety-card-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        margin-bottom: 12px;
    }
    .icon-flame { background: #fee2e2; color: #dc2626; }
    .icon-shield { background: #dcfce7; color: #16a34a; }
    .icon-truck { background: #e0f2fe; color: #0284c7; }
    .icon-money { background: #fef3c7; color: #d97706; }

    .safety-card h5 {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 6px;
    }
    .safety-card p {
        font-size: 0.85rem;
        color: #64748b;
        margin: 0;
        line-height: 1.5;
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
        background: #fff7ed;
        color: var(--gasgo-orange);
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
        .safety-cards-grid {
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
            <strong>Terms of Service</strong>
        </div>
        <h1>Terms of Service</h1>
        <p>Guidelines, safety protocols, and ordering terms for using the {{ trim(($homepageSettings->brand_name_primary ?? 'Gas') . ' ' . ($homepageSettings->brand_name_accent ?? 'Go')) }} platform.</p>
        
        <div class="legal-meta-badges">
            <div class="meta-badge"><i class="fas fa-fire-extinguisher text-warning"></i> DOE Compliant</div>
            <div class="meta-badge"><i class="fas fa-clock"></i> 6 min read</div>
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
                <li><a href="#acceptance" class="active"><i class="fas fa-handshake"></i>1. Acceptance of Terms</a></li>
                <li><a href="#lpg-safety"><i class="fas fa-fire-extinguisher"></i>2. LPG Safety & Delivery</a></li>
                <li><a href="#pricing-payments"><i class="fas fa-receipt"></i>3. Pricing & Payment</a></li>
                <li><a href="#cancellations"><i class="fas fa-ban"></i>4. Order Cancellations</a></li>
                <li><a href="#loyalty"><i class="fas fa-gift"></i>5. Loyalty & Rewards</a></li>
                <li><a href="#account-security"><i class="fas fa-user-shield"></i>6. Account & Conduct</a></li>
                <li><a href="#faq"><i class="fas fa-question-circle"></i>7. Frequently Asked</a></li>
                <li><a href="#support"><i class="fas fa-headset"></i>8. Customer Support</a></li>
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
                <h4><i class="fas fa-shield-alt me-2"></i>Welcome to GasGo LPG Delivery</h4>
                <p>By creating an account, browsing products, or ordering LPG tanks on {{ trim(($homepageSettings->brand_name_primary ?? 'Gas') . ' ' . ($homepageSettings->brand_name_accent ?? 'Go')) }}, you agree to comply with these terms, safety regulations, and operational policies.</p>
            </div>

            <!-- Section 1: Acceptance -->
            <section class="doc-section" id="acceptance">
                <div class="doc-section-header">
                    <div class="section-icon-badge"><i class="fas fa-handshake"></i></div>
                    <h3>1. Acceptance of Terms & Eligibility</h3>
                </div>
                <p>These Terms of Service govern your access to the GasGo online ordering system and rider delivery network. You must be at least 18 years old or under adult supervision to place orders for combustible Liquified Petroleum Gas (LPG) products.</p>
                <p>We reserve the right to revise or update these terms at any time. Continued use of the platform after updates signifies acceptance of the revised policies.</p>
            </section>

            <!-- Section 2: LPG Safety -->
            <section class="doc-section" id="lpg-safety">
                <div class="doc-section-header">
                    <div class="section-icon-badge"><i class="fas fa-fire-extinguisher"></i></div>
                    <h3>2. LPG Handling & Safe Delivery Protocols</h3>
                </div>
                <p>LPG cylinders are pressurized and flammable commodities. We enforce strict Department of Energy (DOE) certified handling protocols:</p>

                <div class="safety-cards-grid">
                    <div class="safety-card">
                        <div class="safety-card-icon icon-shield"><i class="fas fa-certificate"></i></div>
                        <h5>Certified Authentic Cylinders</h5>
                        <p>All delivered cylinders have passed tare weight tests, hydro-testing, and feature unbroken official safety seals.</p>
                    </div>
                    <div class="safety-card">
                        <div class="safety-card-icon icon-flame"><i class="fas fa-search"></i></div>
                        <h5>Drop-off Leak Inspection</h5>
                        <p>Customers are advised to inspect the safety seal upon handover before the rider marks the order as delivered.</p>
                    </div>
                    <div class="safety-card">
                        <div class="safety-card-icon icon-truck"><i class="fas fa-motorcycle"></i></div>
                        <h5>Safe Upright Transport</h5>
                        <p>Riders transport all cylinders in secure, upright carriers compliant with standard road safety protocols.</p>
                    </div>
                    <div class="safety-card">
                        <div class="safety-card-icon icon-money"><i class="fas fa-map-pin"></i></div>
                        <h5>Accurate Pin Drop Required</h5>
                        <p>Accurate delivery addresses and reachable phone numbers are required to ensure safe handoff.</p>
                    </div>
                </div>
            </section>

            <!-- Section 3: Pricing & Payments -->
            <section class="doc-section" id="pricing-payments">
                <div class="doc-section-header">
                    <div class="section-icon-badge"><i class="fas fa-receipt"></i></div>
                    <h3>3. Pricing, Delivery Fees & Payments</h3>
                </div>
                <p>Transparency is fundamental to our service:</p>
                <ul>
                    <li><strong>Regulated LPG Pricing:</strong> Refill and brand new cylinder prices reflect current market pricing and official DOE advisories.</li>
                    <li><strong>Delivery Surcharges:</strong> Distance-based or priority rush delivery fees are calculated and explicitly displayed in the checkout summary.</li>
                    <li><strong>Accepted Payment Methods:</strong>
                        <ul>
                            <li><strong>Cash on Delivery (COD):</strong> Payable directly to the rider upon receipt of cylinder.</li>
                            <li><strong>GCash:</strong> Transfer to our official verified merchant GCash account with reference ID tracking.</li>
                        </ul>
                    </li>
                </ul>
            </section>

            <!-- Section 4: Cancellations -->
            <section class="doc-section" id="cancellations">
                <div class="doc-section-header">
                    <div class="section-icon-badge"><i class="fas fa-ban"></i></div>
                    <h3>4. Order Cancellations & Modification Rules</h3>
                </div>
                <p>Due to the logistical cost and safety sensitivity of transporting heavy LPG tanks:</p>
                <ul>
                    <li><strong>Pending Orders:</strong> You can cancel your order directly from the order tracking page without penalties while the status is <em>Pending</em>.</li>
                    <li><strong>Approved & Dispatched Orders:</strong> Once an order is marked <em>Approved</em> or <em>Out for Delivery</em>, the cylinder is already loaded on the rider's motorcycle. At this stage, cancellation is no longer available online.</li>
                    <li><strong>Failed Delivery:</strong> If a customer is unreachable after multiple attempts at the designated address, the order may be marked failed and returned to store inventory.</li>
                </ul>
            </section>

            <!-- Section 5: Loyalty -->
            <section class="doc-section" id="loyalty">
                <div class="doc-section-header">
                    <div class="section-icon-badge"><i class="fas fa-gift"></i></div>
                    <h3>5. Loyalty Points & Rewards Program</h3>
                </div>
                <p>We reward loyal customers on every delivered order:</p>
                <ul>
                    <li>Points are automatically calculated and awarded to your account upon successful delivery completion.</li>
                    <li>Claimed discount vouchers can be selected and applied at checkout prior to confirming your purchase.</li>
                    <li>Points and vouchers have no cash redemption value and cannot be transferred across accounts.</li>
                </ul>
            </section>

            <!-- Section 6: Account Security -->
            <section class="doc-section" id="account-security">
                <div class="doc-section-header">
                    <div class="section-icon-badge"><i class="fas fa-user-shield"></i></div>
                    <h3>6. User Accounts & Prohibited Conduct</h3>
                </div>
                <p>Users must maintain accurate account information and are prohibited from:</p>
                <ul>
                    <li>Placing fraudulent or prank LPG delivery orders.</li>
                    <li>Sharing malicious location coordinates or unreachable contacts.</li>
                    <li>Attempting unauthorized penetration or abuse of authentication endpoints.</li>
                </ul>
            </section>

            <!-- Section 7: FAQ Accordion -->
            <section class="doc-section" id="faq">
                <div class="doc-section-header">
                    <div class="section-icon-badge"><i class="fas fa-question-circle"></i></div>
                    <h3>7. Frequently Asked Questions</h3>
                </div>

                <div class="accordion faq-accordion" id="termsFaq">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#termsFaq1">
                                What should I do if my cylinder appears damaged or leaks?
                            </button>
                        </h2>
                        <div id="termsFaq1" class="accordion-collapse collapse show" data-bs-parent="#termsFaq">
                            <div class="accordion-body">
                                Immediately notify the delivery rider upon handover and do not accept the unit. You can also contact our hotline immediately for a prompt replacement.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#termsFaq2">
                                Can I change my delivery address after placing an order?
                            </button>
                        </h2>
                        <div id="termsFaq2" class="accordion-collapse collapse" data-bs-parent="#termsFaq">
                            <div class="accordion-body">
                                If the order is still <em>Pending</em>, you may cancel it and place a new one with the updated pin. If it is already <em>Out for Delivery</em>, please call your rider directly using the contact button on the tracking page.
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section 8: Support -->
            <section class="doc-section" id="support">
                <div class="doc-section-header">
                    <div class="section-icon-badge"><i class="fas fa-headset"></i></div>
                    <h3>8. Store Contacts & Inquiries</h3>
                </div>
                <p>For questions regarding our service policies, bulk orders, or commercial partnerships:</p>

                <div class="contact-cta-card">
                    <div>
                        <h4>Have questions about our terms?</h4>
                        <p>{{ $homepageSettings->contact_address ?? 'PNR Site Estacion San Miguel Calasiao Pangasinan' }}</p>
                    </div>
                    <div class="contact-cta-links">
                        <a href="tel:{{ preg_replace('/\s+/', '', $homepageSettings->contact_phone ?? '+639123456789') }}" class="btn-contact-pill">
                            <i class="fas fa-phone"></i> {{ $homepageSettings->contact_phone ?? 'Call Store' }}
                        </a>
                        <a href="mailto:{{ $homepageSettings->contact_email ?? 'gasgolpg@gmail.com' }}" class="btn-contact-pill">
                            <i class="fas fa-envelope"></i> Email Support
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
