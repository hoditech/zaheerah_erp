<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $receiptNumber ?? '' }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Arial, sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 24px;
            color: #111827;
        }
        .receipt {
            max-width: 560px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(90deg, #059669, #0891b2);
            color: #fff;
            padding: 24px;
            text-align: center;
        }
        .header h1 { margin: 0 0 4px; font-size: 22px; letter-spacing: .08em; }
        .header p { margin: 2px 0; font-size: 12px; opacity: .9; }
        .body { padding: 24px; }
        .meta { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 16px; }
        .meta span { color: #6b7280; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { text-align: left; color: #6b7280; font-weight: 500; padding-bottom: 8px; }
        th.right, td.right { text-align: right; }
        td { padding: 8px 0; border-top: 1px solid #f3f4f6; }
        .total { display: flex; justify-content: space-between; margin-top: 16px; padding-top: 12px; border-top: 2px solid #e5e7eb; font-size: 18px; font-weight: 700; color: #059669; }
        .footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 16px; text-align: center; font-size: 11px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h1>ZAHEERAH VENTURES</h1>
            <p>{{ $store['address'] ?? '' }}</p>
            <p>{{ $store['contact'] ?? '' }} &middot; {{ $store['phone'] ?? '' }}</p>
        </div>

        <div class="body">
            <div class="meta">
                <div><span>Date</span><br>{{ ($date ?? now())->format('M j, Y H:i') }}</div>
                <div style="text-align:right">
                    <span>Receipt #</span><br>{{ $receiptNumber ?? 'N/A' }}
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="right">Qty</th>
                        <th class="right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td class="right">{{ $item['quantity'] }}</td>
                            <td class="right">
                                &#8358;{{ number_format((float) $item['price'] * (int) $item['quantity'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">No items</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="total">
                <span>Total</span>
                <span>&#8358;{{ number_format((float) $total, 2) }}</span>
            </div>

            <p style="font-size:12px;color:#6b7280;margin-top:12px;">
                Payment method: {{ ucfirst(str_replace('_', ' ', $paymentMethod ?? 'cash')) }}
            </p>
        </div>

        <div class="footer">
            Thank you for your business.
        </div>
    </div>
</body>
</html>
