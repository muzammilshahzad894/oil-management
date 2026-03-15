<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ledger - {{ $customer->name }}</title>
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
    <h1>Ledger – {{ $customer->name }}</h1>
    <p class="meta">Exported on: {{ date('Y-m-d H:i:s') }}</p>
    <p class="meta">Current balance: Rs {{ number_format(abs($customer->balance), 0) }} ({{ $balanceLabel }})</p>

    <table>
        <thead>
            <tr>
                <th>Entry</th>
                <th>Description</th>
                <th class="text-right">You gave</th>
                <th class="text-right">You get</th>
                <th class="text-right">Balance after</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
            <tr>
                <td>{{ $row['date'] }}</td>
                <td>{{ $row['description'] }}</td>
                <td class="text-right">{{ $row['you_gave'] }}</td>
                <td class="text-right">{{ $row['you_get'] }}</td>
                <td class="text-right">{{ $row['balance'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <p>Total you received (You get): Rs {{ number_format($customer->total_received, 0) }}</p>
        <p>Total you gave (You gave): Rs {{ number_format($customer->total_gave, 0) }}</p>
    </div>
</body>
</html>
