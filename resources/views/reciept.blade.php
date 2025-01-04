<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .receipt {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            max-width: 120px;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 20px;
            margin: 0;
        }
        .header p {
            font-size: 12px;
            margin: 0;
            color: #555;
        }
        .table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f8f8f8;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <img src="{{ $logo }}" alt="Logo">
            <h1>{{ $businessName }}</h1>
            <p>{{ $businessAddress }}</p>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>₦{{ number_format($item['price'], 2) }}</td>
                        <td>₦{{ number_format($item['quantity'] * $item['price'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            <p>Total: <strong>₦{{ number_format($total, 2) }}</strong></p>
            <p>Thank you for your patronage!</p>
        </div>
    </div>
</body>
</html>
