<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body{font-family:sans-serif;font-size:12px;color:#111;margin:0;padding:20px}
    h1{font-size:20px;font-weight:900;margin-bottom:4px;color:#ff6b00}
    .sub{font-size:11px;color:#666;margin-bottom:20px}
    table{width:100%;border-collapse:collapse;margin-bottom:20px}
    th{background:#f3f4f6;padding:8px 12px;text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:#666;border-bottom:2px solid #e5e7eb}
    td{padding:8px 12px;border-bottom:1px solid #e5e7eb;font-size:12px}
    tr:last-child td{border-bottom:none}
    .total-row td{font-weight:900;font-size:13px;color:#ff6b00;background:#fff7f0;border-top:2px solid #ff6b00}
    .footer{margin-top:24px;font-size:10px;color:#999;text-align:center}
</style>
</head>
<body>
    <h1>OrangeCrush Car Rentals — Revenue Report</h1>
    <div class="sub">
        Period: {{ $dateFrom->format('M d, Y') }} — {{ $dateTo->format('M d, Y') }} &nbsp;|&nbsp;
        Generated: {{ now()->format('M d, Y h:i A') }}
    </div>

    <table>
        <thead>
            <tr><th>Month</th><th>Payments</th><th>Revenue (₱)</th></tr>
        </thead>
        <tbody>
            @foreach($monthly as $row)
            <tr>
                <td>{{ date('F Y', strtotime($row->month . '-01')) }}</td>
                <td>{{ $row->count }}</td>
                <td>₱{{ number_format($row->total, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2">TOTAL</td>
                <td>₱{{ number_format($totalRevenue, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">OrangeCrush Car Rental System · Confidential</div>
</body>
</html>
