<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #1e293b; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1e40af; padding-bottom: 10px; }
        .header h1 { color: #1e40af; font-size: 18px; margin: 0; }
        .header p { color: #64748b; margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #1e40af; color: white; padding: 8px; text-align: left; }
        td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; }
        tr:nth-child(even) { background: #f8fafc; }
        .text-right { text-align: right; }
        .text-green { color: #16a34a; }
        .text-red { color: #dc2626; }
        .text-amber { color: #d97706; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Personal Budget Monitoring System</h1>
        <p>{{ $title }}</p>
        <p>Generated: {{ $generated_at }}</p>
    </div>
    <table>
        <thead><tr><th>Category</th><th class="text-right">Budget (₱)</th><th class="text-right">Spent (₱)</th><th class="text-right">Variance (₱)</th><th class="text-right">% Used</th></tr></thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td>{{ $row['category_name'] }}</td>
                <td class="text-right">{{ number_format($row['limit_amount'], 2) }}</td>
                <td class="text-right">{{ number_format($row['actual_spent'], 2) }}</td>
                <td class="text-right {{ $row['variance'] >= 0 ? 'text-green' : 'text-red' }}">{{ number_format($row['variance'], 2) }}</td>
                <td class="text-right {{ $row['status'] === 'safe' ? 'text-green' : ($row['status'] === 'warning' ? 'text-amber' : 'text-red') }}">{{ $row['percent'] }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
