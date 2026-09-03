@php
    $targetParam = strtolower(trim((string) ($target ?? request()->route('target') ?? request()->query('target', 'dropjdid'))));
    $isOctaprize = ($targetParam === 'octaprize');
    $appName = $isOctaprize ? 'Octaprize' : 'Dropjdid';
    $appSlug = $isOctaprize ? 'octaprize' : 'dropjdid';
    $developerName = $isOctaprize ? 'Octaprize Inc.' : 'Dropjdid Technologies';
    $contactEmail = $isOctaprize ? 'support@octaprize.com' : 'support@dropjdid.com';
    $privacyEmail = $isOctaprize ? 'privacy@octaprize.com' : 'privacy@dropjdid.com';
    $lastUpdated = 'September 2, 2026';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $appName }} - Privacy Policy</title>
    <meta name="description" content="Privacy Policy for {{ $appName }} mobile application. Learn how we collect, handle, and protect your information.">
    <meta name="robots" content="index, follow">

    <!-- Open Graph / Meta -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $appName }} - Privacy Policy">
    <meta property="og:description" content="Official Privacy Policy and Data Handling Terms for the {{ $appName }} application.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-page: #0f172a;
            --bg-card: #1e293b;
            --bg-subtle: rgba(255, 255, 255, 0.04);
            --border-color: rgba(255, 255, 255, 0.08);
            --border-hover: rgba(255, 255, 255, 0.16);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --accent-primary: #6366f1;
            --accent-gradient: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #ec4899 100%);
            --accent-glow: rgba(99, 102, 241, 0.15);
            --badge-bg: rgba(99, 102, 241, 0.12);
            --badge-border: rgba(99, 102, 241, 0.25);
            --badge-text: #a5b4fc;
            --callout-bg: rgba(99, 102, 241, 0.06);
            --callout-border: rgba(99, 102, 241, 0.2);
        }

        @media (prefers-color-scheme: light) {
            :root {
                --bg-page: #f8fafc;
                --bg-card: #ffffff;
                --bg-subtle: #f1f5f9;
                --border-color: #e2e8f0;
                --border-hover: #cbd5e1;
                --text-primary: #0f172a;
                --text-secondary: #475569;
                --text-muted: #64748b;
                --accent-primary: #4f46e5;
                --accent-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #db2777 100%);
                --accent-glow: rgba(79, 70, 229, 0.08);
                --badge-bg: rgba(79, 70, 229, 0.08);
                --badge-border: rgba(79, 70, 229, 0.2);
                --badge-text: #4338ca;
                --callout-bg: rgba(79, 70, 229, 0.04);
                --callout-border: rgba(79, 70, 229, 0.18);
            }
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-page);
            color: var(--text-secondary);
            line-height: 1.7;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 24px 80px 24px;
        }

        /* Header section */
        .header {
            margin-bottom: 40px;
            padding-bottom: 32px;
            border-bottom: 1px solid var(--border-color);
        }

        .nav-brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .app-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 9999px;
            background: var(--badge-bg);
            border: 1px solid var(--badge-border);
            color: var(--badge-text);
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        .app-switcher {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.82rem;
            color: var(--text-muted);
        }

        .switcher-link {
            padding: 5px 12px;
            border-radius: 6px;
            text-decoration: none;
            color: var(--text-secondary);
            background: var(--bg-subtle);
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }

        .switcher-link:hover, .switcher-link.active {
            color: var(--text-primary);
            border-color: var(--accent-primary);
            background: var(--badge-bg);
        }

        .title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.03em;
            margin-bottom: 12px;
            line-height: 1.2;
        }

        .title span {
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .meta-bar {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Highlight notice banner */
        .callout-box {
            background: var(--callout-bg);
            border: 1px solid var(--callout-border);
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 36px;
        }

        .callout-title {
            font-weight: 700;
            color: var(--text-primary);
            font-size: 1rem;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .callout-text {
            font-size: 0.925rem;
            color: var(--text-secondary);
        }

        /* Content cards */
        .policy-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .section-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            letter-spacing: -0.01em;
        }

        .section-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: var(--badge-bg);
            color: var(--badge-text);
            font-size: 0.8rem;
            font-weight: 700;
        }

        .policy-card p {
            margin-bottom: 14px;
            font-size: 0.975rem;
        }

        .policy-card p:last-child {
            margin-bottom: 0;
        }

        .policy-card ul {
            margin: 14px 0 18px 24px;
        }

        .policy-card li {
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .policy-card strong {
            color: var(--text-primary);
            font-weight: 600;
        }

        .grid-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 14px;
            margin: 16px 0;
        }

        .sub-card {
            background: var(--bg-subtle);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 16px;
        }

        .sub-card-title {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.9rem;
            margin-bottom: 6px;
        }

        .sub-card-desc {
            font-size: 0.85rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        /* Buttons & links */
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--accent-primary);
            color: #ffffff !important;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: opacity 0.2s ease, transform 0.1s ease;
            margin-top: 12px;
        }

        .btn-action:hover {
            opacity: 0.92;
            transform: translateY(-1px);
        }

        .contact-box {
            background: var(--bg-subtle);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            margin-top: 16px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }

        .contact-item:last-child {
            margin-bottom: 0;
        }

        .contact-link {
            color: var(--badge-text);
            text-decoration: none;
            font-weight: 600;
        }

        .contact-link:hover {
            text-decoration: underline;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding-top: 40px;
            font-size: 0.85rem;
            color: var(--text-muted);
            border-top: 1px solid var(--border-color);
            margin-top: 48px;
        }

        .footer a {
            color: var(--text-secondary);
            text-decoration: none;
        }

        .footer a:hover {
            color: var(--text-primary);
            text-decoration: underline;
        }

        @media (max-width: 640px) {
            .container {
                padding: 24px 16px 60px 16px;
            }

            .title {
                font-size: 1.85rem;
            }

            .policy-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Top Navigation / Switcher -->
        <header class="header">
            <div class="nav-brand">
                <div class="app-pill">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <span>{{ $appName }} Application</span>
                </div>

                <div class="app-switcher">
                    <span>Target:</span>
                    <a href="{{ url('/privacy/dropjdid') }}" class="switcher-link {{ !$isOctaprize ? 'active' : '' }}">Dropjdid</a>
                    <a href="{{ url('/privacy/octaprize') }}" class="switcher-link {{ $isOctaprize ? 'active' : '' }}">Octaprize</a>
                </div>
            </div>

            <h1 class="title">Privacy Policy for <span>{{ $appName }}</span></h1>
            <div class="meta-bar">
                <div class="meta-item">
                    <span>Published by: <strong>{{ $developerName }}</strong></span>
                </div>
                <div class="meta-item">•</div>
                <div class="meta-item">
                    <span>Effective Date: <strong>{{ $lastUpdated }}</strong></span>
                </div>
            </div>
        </header>

        <!-- Google Play Console Notice Box -->
        <div class="callout-box">
            <div class="callout-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                <span>Commitment to User Privacy</span>
            </div>
            <p class="callout-text">
                This Privacy Policy describes how <strong>{{ $appName }}</strong> (operated by {{ $developerName }}) collects, uses, protects, and discloses information gathered when you download, install, or use our mobile application and related web services. We do not sell your personal information.
            </p>
        </div>

        <!-- Section 1: Information We Collect -->
        <div class="policy-card">
            <h2 class="section-title">
                <span class="section-num">1</span>
                Information We Collect
            </h2>
            <p>
                When you use the <strong>{{ $appName }}</strong> mobile application, we may collect the following types of information to provide and enhance our services:
            </p>

            <div class="grid-cards">
                <div class="sub-card">
                    <div class="sub-card-title">Account & Contact Data</div>
                    <div class="sub-card-desc">Information you provide upon registration, such as your full name, email address, phone number, and authentication credentials.</div>
                </div>

                <div class="sub-card">
                    <div class="sub-card-title">Device & Hardware Info</div>
                    <div class="sub-card-desc">Device model, operating system version, unique device identifiers, network state, and general hardware configuration.</div>
                </div>

                <div class="sub-card">
                    <div class="sub-card-title">Log & Usage Diagnostics</div>
                    <div class="sub-card-desc">App access timestamps, feature usage, crash logs, diagnostic metrics, and system performance telemetry.</div>
                </div>

                <div class="sub-card">
                    <div class="sub-card-title">User Provided Content</div>
                    <div class="sub-card-desc">Photos, documents, or support messages you choose to upload or transmit through the {{ $appName }} app features.</div>
                </div>
            </div>
        </div>

        <!-- Section 2: App Permissions -->
        <div class="policy-card">
            <h2 class="section-title">
                <span class="section-num">2</span>
                Device Permissions Requested
            </h2>
            <p>
                The <strong>{{ $appName }}</strong> app requests only the minimum runtime permissions necessary to deliver core features:
            </p>
            <ul>
                <li><strong>Camera / Photos:</strong> Requested only when you upload a profile image, scan a code, or attach media inside the app. You can grant or revoke this access anytime in your device settings.</li>
                <li><strong>Push Notifications:</strong> Used to deliver critical account alerts, verification codes, and service notifications.</li>
                <li><strong>Network & Internet:</strong> Required for secure communication with our cloud API servers via encrypted TLS connections.</li>
            </ul>
        </div>

        <!-- Section 3: How We Use Your Information -->
        <div class="policy-card">
            <h2 class="section-title">
                <span class="section-num">3</span>
                How We Use Your Information
            </h2>
            <p>
                We use collected information solely for legitimate operational purposes, including:
            </p>
            <ul>
                <li>Creating and managing your user account across <strong>{{ $appName }}</strong> services.</li>
                <li>Facilitating secure login, multi-factor authentication, and account recovery.</li>
                <li>Providing customer support and responding to technical issues or feedback.</li>
                <li>Monitoring app stability, diagnosing crash reports, and preventing fraudulent or unauthorized activities.</li>
                <li>Complying with legal obligations, regulatory requirements, and Google Play Developer Policies.</li>
            </ul>
        </div>

        <!-- Section 4: Data Sharing & Third-Party Disclosure -->
        <div class="policy-card">
            <h2 class="section-title">
                <span class="section-num">4</span>
                Data Sharing and Third Parties
            </h2>
            <p>
                <strong>We do not sell, rent, or trade your personal data to third parties or data brokers.</strong>
            </p>
            <p>
                We may share information only with vetted third-party service providers bound by strict confidentiality obligations, strictly to operate our platform:
            </p>
            <ul>
                <li><strong>Cloud & Database Hosting:</strong> Secure cloud infrastructure providers that host our backend databases and APIs with encryption at rest.</li>
                <li><strong>Google Play Services:</strong> Core Android system libraries used for authentication, notifications, and application updates.</li>
                <li><strong>Crash Analytics:</strong> Anonymized diagnostic tools to identify and fix crashes and performance issues.</li>
                <li><strong>Legal Compliance:</strong> When required by law, subpoena, or valid legal process to protect our rights, users, or the public.</li>
            </ul>
        </div>

        <!-- Section 5: Data Security & Retention -->
        <div class="policy-card">
            <h2 class="section-title">
                <span class="section-num">5</span>
                Data Security and Retention
            </h2>
            <p>
                We implement industry-standard administrative, technical, and physical security safeguards to protect your personal information:
            </p>
            <ul>
                <li>All data transmitted between the <strong>{{ $appName }}</strong> mobile app and our servers is encrypted using Transport Layer Security (TLS/HTTPS).</li>
                <li>Passwords and sensitive tokens are cryptographically hashed using modern salted algorithms (e.g., Bcrypt).</li>
                <li>Access to user databases is restricted to authorized personnel following the principle of least privilege.</li>
            </ul>
            <p>
                We retain your information only for as long as your account remains active or as needed to provide you with {{ $appName }} services and comply with applicable laws.
            </p>
        </div>

        <!-- Section 6: User Rights & Account / Data Deletion (Google Play Requirement) -->
        <div class="policy-card">
            <h2 class="section-title">
                <span class="section-num">6</span>
                Account and Data Deletion
            </h2>
            <p>
                In compliance with Google Play Data Safety requirements, GDPR, and CCPA, you have full control over your personal data:
            </p>
            <ul>
                <li><strong>Access and Export:</strong> You may request a copy of the personal information associated with your account.</li>
                <li><strong>Correction:</strong> You can update or correct your profile information directly within the app settings.</li>
                <li><strong>Permanent Account Deletion:</strong> You may request permanent deletion of your {{ $appName }} account and all associated personal data at any time.</li>
            </ul>
            <p>
                To submit an account or data deletion request, you can use our dedicated web page or contact our data protection team:
            </p>
            <a href="{{ url('/account-deletion/' . $appSlug) }}" class="btn-action">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    <line x1="10" y1="11" x2="10" y2="17"></line>
                    <line x1="14" y1="11" x2="14" y2="17"></line>
                </svg>
                <span>Request Account & Data Deletion for {{ $appName }}</span>
            </a>
        </div>

        <!-- Section 7: Children's Privacy -->
        <div class="policy-card">
            <h2 class="section-title">
                <span class="section-num">7</span>
                Children's Privacy (COPPA)
            </h2>
            <p>
                The <strong>{{ $appName }}</strong> application is not directed toward children under the age of 13 (or under 16 in the European Union / EEA). We do not knowingly collect or solicit personal information from children. If we discover that a child has provided us with personal information without parental consent, we will promptly delete such information from our records.
            </p>
        </div>

        <!-- Section 8: Policy Updates -->
        <div class="policy-card">
            <h2 class="section-title">
                <span class="section-num">8</span>
                Changes to This Privacy Policy
            </h2>
            <p>
                We may periodically update this Privacy Policy to reflect changes in our app features, technology, or legal requirements. When changes are made, we will revise the "Effective Date" at the top of this page. We encourage you to review this page periodically to stay informed of our privacy practices.
            </p>
        </div>

        <!-- Section 9: Contact Information -->
        <div class="policy-card">
            <h2 class="section-title">
                <span class="section-num">9</span>
                Contact Us
            </h2>
            <p>
                If you have questions, concerns, or requests regarding this Privacy Policy or your data in <strong>{{ $appName }}</strong>, please reach out to us:
            </p>

            <div class="contact-box">
                <div class="contact-item">
                    <strong>Organization:</strong>
                    <span>{{ $developerName }}</span>
                </div>
                <div class="contact-item">
                    <strong>Privacy Inquiries:</strong>
                    <a href="mailto:{{ $privacyEmail }}" class="contact-link">{{ $privacyEmail }}</a>
                </div>
                <div class="contact-item">
                    <strong>General Support:</strong>
                    <a href="mailto:{{ $contactEmail }}" class="contact-link">{{ $contactEmail }}</a>
                </div>
                <div class="contact-item">
                    <strong>Account Deletion:</strong>
                    <a href="{{ url('/account-deletion/' . $appSlug) }}" class="contact-link">Account Deletion Page</a>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <p>© {{ date('Y') }} {{ $developerName }}. All rights reserved.</p>
            <p style="margin-top: 8px;">
                <a href="{{ url('/privacy/' . $appSlug) }}">Privacy Policy</a> •
                <a href="{{ url('/account-deletion/' . $appSlug) }}">Account Deletion</a>
            </p>
        </footer>
    </div>
</body>
</html>
