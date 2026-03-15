<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Report - {{ $customer->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        .meta { color: #555; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        th { background: #2F5496; color: #fff; font-weight: bold; }
        .text-right { text-align: right; }
        .totals { margin-top: 16px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Customer Report – {{ $customer->name }}</h1>
    <p class="meta">Period: {{ $startDate }} to {{ $endDate }}</p>
    <p class="meta">Generated on: {{ date('Y-m-d H:i:s') }}</p>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Customer</th>
                <th>Brand</th>
                <th class="text-right">Quantity</th>
                <th class="text-right">Total Amount</th>
                <th class="text-right">Remaining</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
            <tr>
                <td>{{ $row['date'] }}</td>
                <td>{{ $row['customer'] }}</td>
                <td>{{ $row['brand'] }}</td>
                <td class="text-right">{{ $row['quantity'] }}</td>
                <td class="text-right">Rs {{ $row['total_amount'] }}</td>
                <td class="text-right">Rs {{ $row['remaining'] }}</td>
                <td>{{ $row['status'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <p>Total Paid: Rs {{ number_format($totalPaid, 0) }}</p>
        <p>Total Unpaid: Rs {{ number_format($totalUnpaid, 0) }}</p>
    </div>
</body>
</html>
