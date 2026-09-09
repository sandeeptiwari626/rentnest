<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $notice->displayTitle() }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1c1917; font-size: 12px; line-height: 1.5; }
        .wrap { max-width: 720px; margin: 0 auto; padding: 28px; }
        .kicker { font-size: 10px; letter-spacing: 0.12em; text-transform: uppercase; color: #78716c; }
        h1 { font-size: 22px; margin: 8px 0 6px; }
        h2 { font-size: 13px; margin: 18px 0 6px; }
        .muted { color: #57534e; }
        .note { margin-top: 16px; padding: 12px; background: #fafaf9; border: 1px solid #e7e5e4; }
        p { margin: 0 0 8px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="kicker">Tenant portal</div>
    <h1>{{ $notice->displayTitle() }}</h1>
    <p class="muted">{{ $organization->name ?? 'Your landlord' }}</p>
    <p class="note">
        This notice explains how the tenant portal is used. It does not change your lease or tenancy agreement.
    </p>
    @foreach ($notice->sections as $section)
        <h2>{{ $section['heading'] ?? '' }}</h2>
        <p>{{ $section['body'] ?? '' }}</p>
    @endforeach
</div>
</body>
</html>
