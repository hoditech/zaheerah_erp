<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Receipt</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-lg bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100 transform transition-all hover:shadow-xl">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-emerald-600 to-cyan-500 p-8 text-center relative">
            <div class="absolute top-4 right-4 text-white/20">
                <i class="fas fa-receipt text-4xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2 tracking-tight">INVOICE</h1>
            <div class="space-y-1 text-cyan-100">
                <p class="text-sm font-light">123 Luxury Avenue • Paris, France</p>
                <p class="text-sm font-light">contact@premiumreceipt.com • +33 1 2345 6789</p>
            </div>
        </div>

        <!-- Body Content -->
        <div class="p-8 space-y-6">
            <!-- Metadata Grid -->
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500 font-medium">Date Issued</p>
                    <p class="text-gray-700">{{ now()->format('M j, Y') }}</p>
                </div>
                <div>
                    <p class="text-gray-500 font-medium">Receipt #</p>
                    <p class="text-gray-700">PR-{{ str_pad(12345, 6, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>

            <!-- Client & Payment Info -->
            <div class="flex justify-between items-start border-b pb-4 border-gray-100">
                <div>
                    <p class="text-gray-500 font-medium mb-1">Bill To</p>
                    <p class="text-gray-700">Client Name</p>
                    <p class="text-gray-500 text-sm">client@email.com</p>
                </div>
                <div class="text-right">
                    <p class="text-gray-500 font-medium mb-1">Payment Method</p>
                    <div class="flex items-center gap-2 justify-end">
                        <i class="fab fa-cc-visa text-2xl text-blue-900"></i>
                        <span class="text-gray-700">•••• 4242</span>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left pb-3 text-gray-500 font-medium">Description</th>
                            <th class="text-right pb-3 text-gray-500 font-medium">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-gray-100">
                            <td class="py-3 text-gray-700">Premium Subscription</td>
                            <td class="py-3 text-right text-gray-700">€49.00</td>
                        </tr>
                        <tr class="border-b border-gray-100">
                            <td class="py-3 text-gray-700">Technical Support</td>
                            <td class="py-3 text-right text-gray-700">€29.00</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Totals -->
            <div class="space-y-2 text-right">
                <div class="flex justify-between items-center">
                    <span class="text-gray-500">Subtotal:</span>
                    <span class="text-gray-700">€78.00</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-500">Tax (20%):</span>
                    <span class="text-gray-700">€15.60</span>
                </div>
                <div class="flex justify-between items-center pt-2">
                    <span class="font-bold text-lg text-emerald-600">Total Due:</span>
                    <span class="font-bold text-lg text-emerald-600">€93.60</span>
                </div>
            </div>

            <!-- Payment Status -->
            <div class="bg-emerald-50 p-4 rounded-lg flex items-center gap-3">
                <i class="fas fa-check-circle text-emerald-600"></i>
                <div>
                    <p class="text-emerald-600 font-medium">Payment Complete</p>
                    <p class="text-xs text-emerald-600">Transaction ID: {{ substr(md5(uniqid()), 0, 12) }}</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 p-6 text-center border-t border-gray-100">
            <div class="flex justify-center gap-4 mb-3 text-gray-400">
                <i class="fab fa-twitter"></i>
                <i class="fab fa-facebook"></i>
                <i class="fab fa-instagram"></i>
            </div>
            <p class="text-xs text-gray-400">Thank you for choosing Premium Receipt</p>
            <p class="text-xs text-gray-400 mt-1">This is an official digital receipt • Valid without signature</p>
        </div>
    </div>
</body>
</html>