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
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #1e40af; color: white; padding: 8px; text-align: left; }
        td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; }
        tr:nth-child(even) { background: #f8fafc; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Personal Budget Monitoring System</h1>
        <p>{{ $title }}</p>
        <p>Generated: {{ $generated_at }}</p>
    </div>
    <table>
        <thead><tr><th>Category</th><th class="text-right">Amount Spent (₱)</th><th class="text-right">% of Total</th></tr></thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td>{{ $row['category_name'] }}</td>
                <td class="text-right">{{ number_format($row['amount'], 2) }}</td>
                <td class="text-right">{{ $row['percentage'] }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
