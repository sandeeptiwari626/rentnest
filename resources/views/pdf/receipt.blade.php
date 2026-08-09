<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Rent Receipt {{ $payment->receipt_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; }
        .wrap { max-width: 700px; margin: 0 auto; padding: 24px; }
        .brand { font-size: 22px; font-weight: bold; color: #0f766e; letter-spacing: 0.5px; }
        .muted { color: #64748b; }
        .header { display: table; width: 100%; margin-bottom: 28px; }
        .header-left, .header-right { display: table-cell; vertical-align: top; }
        .header-right { text-align: right; }
        .badge {
            display: inline-block;
            background: #ccfbf1;
            color: #0f766e;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: bold;
        }
        h1 { font-size: 18px; margin: 0 0 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { padding: 10px 8px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { color: #64748b; font-weight: 600; font-size: 11px; text-transform: uppercase; }
        .amount { font-size: 24px; font-weight: bold; color: #0f766e; }
        .footer { margin-top: 36px; padding-top: 16px; border-top: 1px solid #e2e8f0; font-size: 11px; }
        .box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-top: 16px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <div class="header-left">
            <div class="brand">RentNest</div>
            <div class="muted">{{ $organization->name ?? 'Landlord' }}</div>
            @if(!empty($organization?->email))
                <div class="muted">{{ $organization->email }}</div>
            @endif
            @if(!empty($organization?->phone))
                <div class="muted">{{ $organization->phone }}</div>
            @endif
        </div>
        <div class="header-right">
            <span class="badge">PAID</span>
            <div style="margin-top: 10px;">
                <div class="muted">Receipt No.</div>
                <strong>{{ $payment->receipt_number }}</strong>
            </div>
            <div style="margin-top: 8px;">
                <div class="muted">Date</div>
                <strong>{{ optional($payment->payment_date)->format('d M Y') }}</strong>
            </div>
        </div>
    </div>

    <h1>Rent Receipt</h1>
    <p class="muted">Payment acknowledgement for {{ $payment->period_label ?: 'rent' }}</p>

    <div class="box">
        <div class="muted">Amount received</div>
        <div class="amount">₹{{ number_format((float) $payment->amount_paid, 2) }}</div>
    </div>

    <table>
        <tr>
            <th>Tenant</th>
            <td>{{ $payment->tenant?->name ?? '—' }}</td>
        </tr>
        <tr>
            <th>Property</th>
            <td>
                {{ $payment->property?->name ?? '—' }}
                @if($payment->property?->address)
                    <div class="muted">{{ $payment->property->address }}, {{ $payment->property->city }}</div>
                @endif
            </td>
        </tr>
        <tr>
            <th>Period</th>
            <td>{{ $payment->period_label ?: '—' }}</td>
        </tr>
        <tr>
            <th>Due date</th>
            <td>{{ optional($payment->due_date)->format('d M Y') }}</td>
        </tr>
        <tr>
            <th>Payment method</th>
            <td>{{ $payment->payment_method?->label() ?? '—' }}</td>
        </tr>
        <tr>
            <th>Reference</th>
            <td>{{ $payment->reference_number ?: '—' }}</td>
        </tr>
        <tr>
            <th>Total due</th>
            <td>₹{{ number_format((float) $payment->amount, 2) }}</td>
        </tr>
        <tr>
            <th>Amount paid</th>
            <td>₹{{ number_format((float) $payment->amount_paid, 2) }}</td>
        </tr>
    </table>

    @if($payment->notes)
        <div class="box">
            <div class="muted">Notes</div>
            <div>{{ $payment->notes }}</div>
        </div>
    @endif

    <div class="footer muted">
        This is a computer-generated receipt from RentNest. No signature is required.
    </div>
</div>
</body>
</html>
