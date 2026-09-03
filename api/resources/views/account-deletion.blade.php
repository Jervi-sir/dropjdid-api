@php
    $targetParam = strtolower(trim((string) ($target ?? request()->route('target') ?? request()->query('target', 'dropjdid'))));
    $isOctaprize = ($targetParam === 'octaprize');
    $appName = $isOctaprize ? 'Octaprize' : 'Dropjdid';
    $appSlug = $isOctaprize ? 'octaprize' : 'dropjdid';
    $developerName = $isOctaprize ? 'Octaprize Inc.' : 'Dropjdid Technologies';
    $supportEmail = $isOctaprize ? 'support@octaprize.com' : 'support@dropjdid.com';
    $privacyEmail = $isOctaprize ? 'privacy@octaprize.com' : 'privacy@dropjdid.com';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $appName }} - Account Deletion Request</title>
    <meta name="description" content="Request account and personal data deletion for the {{ $appName }} mobile application in compliance with Google Play developer policies.">
    <meta name="robots" content="index, follow">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-page: #0f172a;
            --bg-card: #1e293b;
            --bg-subtle: rgba(255, 255, 255, 0.04);
            --border-color: rgba(255, 255, 255, 0.08);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --accent-primary: #ef4444;
            --accent-gradient: linear-gradient(135deg, #f43f5e 0%, #e11d48 50%, #be123c 100%);
            --badge-bg: rgba(239, 68, 68, 0.12);
            --badge-border: rgba(239, 68, 68, 0.25);
            --badge-text: #fca5a5;
        }

        @media (prefers-color-scheme: light) {
            :root {
                --bg-page: #f8fafc;
                --bg-card: #ffffff;
                --bg-subtle: #f1f5f9;
                --border-color: #e2e8f0;
                --text-primary: #0f172a;
                --text-secondary: #475569;
                --text-muted: #64748b;
                --accent-primary: #dc2626;
                --accent-gradient: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
                --badge-bg: rgba(220, 38, 38, 0.08);
                --badge-border: rgba(220, 38, 38, 0.2);
                --badge-text: #b91c1c;
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
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 24px 80px 24px;
        }

        .header {
            margin-bottom: 36px;
            padding-bottom: 28px;
            border-bottom: 1px solid var(--border-color);
        }

        .nav-brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
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
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.03em;
            margin-bottom: 8px;
        }

        .title span {
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 24px;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title svg {
            color: var(--accent-primary);
        }

        ol {
            margin: 14px 0 16px 24px;
        }

        li {
            margin-bottom: 10px;
        }

        li strong {
            color: var(--text-primary);
        }

        .btn-delete {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--accent-primary);
            color: #ffffff;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            margin-top: 10px;
        }

        .btn-delete:hover {
            opacity: 0.92;
        }

        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }

        .table-data th, .table-data td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.9rem;
        }

        .table-data th {
            color: var(--text-primary);
            background: var(--bg-subtle);
            font-weight: 600;
        }

        .footer {
            text-align: center;
            padding-top: 36px;
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
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="nav-brand">
                <div class="app-pill">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    <span>{{ $appName }} Account Portal</span>
                </div>

                <div class="app-switcher">
                    <span>Target:</span>
                    <a href="{{ url('/account-deletion/dropjdid') }}" class="switcher-link {{ !$isOctaprize ? 'active' : '' }}">Dropjdid</a>
                    <a href="{{ url('/account-deletion/octaprize') }}" class="switcher-link {{ $isOctaprize ? 'active' : '' }}">Octaprize</a>
                </div>
            </div>

            <h1 class="title">Delete Your <span>{{ $appName }}</span> Account</h1>
            <p>
                In compliance with Google Play Developer Policy, this page details the steps and procedures to delete your account and associated personal data from <strong>{{ $appName }}</strong> (by {{ $developerName }}).
            </p>
        </header>

        <!-- Method 1: Within the App -->
        <div class="card">
            <h2 class="card-title">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                    <line x1="12" y1="18" x2="12.01" y2="18"></line>
                </svg>
                Option 1: Delete Directly in the App (Instant)
            </h2>
            <p>You can permanently delete your account directly from within the {{ $appName }} mobile app at any time:</p>
            <ol>
                <li>Open the <strong>{{ $appName }}</strong> app on your device and log in.</li>
                <li>Navigate to <strong>Settings</strong> or tap on your <strong>Profile</strong>.</li>
                <li>Select <strong>Account Management</strong> or <strong>Security</strong>.</li>
                <li>Tap <strong>Delete Account</strong>.</li>
                <li>Confirm your request by entering your password or verification code.</li>
            </ol>
            <p style="font-size: 0.9rem; color: var(--text-muted);">Upon confirmation, your session is terminated immediately and account data is scheduled for irreversible deletion.</p>
        </div>

        <!-- Method 2: Online Request -->
        <div class="card">
            <h2 class="card-title">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
                Option 2: Submit a Deletion Request via Email
            </h2>
            <p>If you no longer have access to the app, you can request account deletion by emailing our support team with the email address registered to your {{ $appName }} account:</p>
            <a href="mailto:{{ $supportEmail }}?subject={{ rawurlencode('Account Deletion Request - ' . $appName) }}&body={{ rawurlencode('Hello ' . $appName . ' Team,\n\nI would like to request the permanent deletion of my ' . $appName . ' account and all associated personal data.\n\nRegistered Email: \nFull Name: \nReason (Optional): \n\nThank you.') }}" class="btn-delete">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
                <span>Email {{ $supportEmail }}</span>
            </a>
            <p style="font-size: 0.9rem; color: var(--text-muted); margin-top: 14px;">Our privacy team will verify your identity and process the deletion request within 30 business days.</p>
        </div>

        <!-- Data Retention & Deletion Summary Table (Google Play Requirement) -->
        <div class="card">
            <h2 class="card-title">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                What Data Is Deleted vs. Retained
            </h2>
            <p>Here is an overview of what happens to your data upon account deletion:</p>

            <table class="table-data">
                <thead>
                    <tr>
                        <th>Data Category</th>
                        <th>Action Taken</th>
                        <th>Retention Period</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Profile & Credentials</strong> (Name, Email, Password, Profile photo)</td>
                        <td>Permanently purged from database</td>
                        <td>Deleted immediately upon confirmation</td>
                    </tr>
                    <tr>
                        <td><strong>Device & Session Tokens</strong> (Push tokens, refresh tokens)</td>
                        <td>Invalidated and revoked</td>
                        <td>Deleted immediately</td>
                    </tr>
                    <tr>
                        <td><strong>App Activity & Preferences</strong></td>
                        <td>Anonymized or removed</td>
                        <td>Purged within 30 days</td>
                    </tr>
                    <tr>
                        <td><strong>Financial & Invoicing Records</strong></td>
                        <td>Retained strictly for tax and statutory auditing compliance</td>
                        <td>Retained as required by law (e.g. 5-7 years)</td>
                    </tr>
                </tbody>
            </table>
        </div>

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
