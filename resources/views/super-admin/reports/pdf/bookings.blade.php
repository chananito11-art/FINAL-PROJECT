<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body{font-family:sans-serif;font-size:11px;color:#111;margin:0;padding:16px}
    h1{font-size:18px;font-weight:900;margin-bottom:4px;color:#ff6b00}
    .sub{font-size:10px;color:#666;margin-bottom:16px}
    table{width:100%;border-collapse:collapse}
    th{background:#f3f4f6;padding:6px 10px;text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.04em;color:#666;border-bottom:2px solid #e5e7eb}
    td{padding:6px 10px;border-bottom:1px solid #f0f0f0;font-size:11px}
    tr:nth-child(even) td{background:#fafafa}
    .footer{margin-top:20px;font-size:9px;color:#999;text-align:center}
    .badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:9px;font-weight:700}
    .s-confirmed,.s-completed{background:#dcfce7;color:#166534}
    .s-cancelled,.s-rejected{background:#fee2e2;color:#991b1b}
    .s-ongoing{background:#fff7ed;color:#c2410c}
    .s-pending,.s-awaiting{background:#fef9c3;color:#854d0e}
</style>
</head>
<body>
    <h1>OrangeCrush Car Rentals — Booking Report</h1>
    <div class="sub">
        Generated: {{ now()->format('M d, Y h:i A') }} &nbsp;|&nbsp; {{ $bookings->count() }} bookings
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th><th>Customer</th><th>Vehicle</th><th>Pickup</th><th>Return</th><th>Amount (₱)</th><th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $b)
            <tr>
                <td>#{{ $b->id }}</td>
                <td>{{ $b->user?->first_name }} {{ $b->user?->last_name }}</td>
                <td>{{ $b->vehicle?->name }}</td>
                <td>{{ $b->pickup_date?->format('M d, Y') }}</td>
                <td>{{ $b->return_date?->format('M d, Y') }}</td>
                <td>₱{{ number_format($b->total_amount, 0) }}</td>
                <td><span class="badge s-{{ explode('_',$b->status)[0] }}">{{ ucwords(str_replace('_',' ',$b->status)) }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">OrangeCrush Car Rental System · Confidential</div>
</body>
</html>
