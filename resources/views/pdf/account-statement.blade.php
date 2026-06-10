<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1e293b; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1e40af; padding-bottom: 10px; }
        .header h1 { color: #1e40af; font-size: 18px; margin: 0; }
        .header p { color: #64748b; margin: 4px 0; }
        .summary { margin: 15px 0; padding: 10px; background: #f8fafc; border: 1px solid #e2e8f0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #1e40af; color: white; padding: 8px; text-align: left; }
        td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; }
        tr:nth-child(even) { background: #f8fafc; }
        .text-right { text-align: right; }
        .text-green { color: #16a34a; }
        .text-red { color: #dc2626; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Personal Budget Monitoring System</h1>
        <p>{{ $title }}@if(isset($account)) — {{ $account->name }}@if($account->person) ({{ $account->person->name }})@endif @endif</p>
        <p>Generated: {{ $generated_at }}</p>
    </div>
    @if($data)
    <div class="summary">
        <strong>Opening Balance:</strong> ₱ {{ number_format($data['opening_balance'], 2) }} |
        <strong>Closing Balance:</strong> ₱ {{ number_format($data['closing_balance'], 2) }}
    </div>
    <table>
        <thead><tr><th>Date</th><th>Description</th><th>Category</th><th>Type</th><th class="text-right">Amount (₱)</th><th class="text-right">Balance (₱)</th></tr></thead>
        <tbody>
            @foreach($data['transactions'] as $row)
            <tr>
                <td>{{ $row['date'] }}</td>
                <td>{{ $row['description'] }}</td>
                <td>{{ $row['category'] }}</td>
                <td>{{ ucfirst($row['type']) }}</td>
                <td class="text-right {{ $row['type'] === 'income' ? 'text-green' : 'text-red' }}">{{ number_format($row['amount'], 2) }}</td>
                <td class="text-right">{{ number_format($row['balance'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</body>
</html>
