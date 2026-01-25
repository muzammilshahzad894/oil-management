<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - Sale #{{ $sale->id }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }
            
            body {
                margin: 0;
                padding: 10px;
            }
            
            .no-print {
                display: none !important;
            }
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            max-width: 80mm;
            margin: 0 auto;
            padding: 10px;
            background: white;
        }
        
        .receipt-container {
            width: 100%;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .header p {
            font-size: 10px;
            margin: 2px 0;
        }
        
        .info-section {
            margin: 10px 0;
            padding: 5px 0;
            border-bottom: 1px dashed #ccc;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }
        
        .info-label {
            font-weight: bold;
        }
        
        .items-section {
            margin: 10px 0;
        }
        
        .item-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            padding: 3px 0;
        }
        
        .item-name {
            flex: 1;
        }
        
        .item-details {
            text-align: right;
        }
        
        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        
        .total-section {
            margin: 10px 0;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            font-weight: bold;
        }
        
        .total-row.grand {
            font-size: 14px;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 5px 0;
            margin: 10px 0;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px dashed #000;
            font-size: 10px;
        }
        
        .payment-status {
            text-align: center;
            margin: 10px 0;
            padding: 5px;
            font-weight: bold;
        }
        
        .payment-status.paid {
            background: #d4edda;
            color: #155724;
        }
        
        .payment-status.unpaid {
            background: #f8d7da;
            color: #721c24;
        }
        
        .print-button {
            text-align: center;
            margin: 20px 0;
        }
        
        .btn-print {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 5px;
        }
        
        .btn-print:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <h1>Oil Management</h1>
            <p>Sale Receipt</p>
            <p>Receipt #{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</p>
        </div>
        
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Date:</span>
                <span>{{ $sale->sale_date->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Time:</span>
                <span>{{ $sale->created_at->format('h:i A') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Customer:</span>
                <span>
                    {{ $sale->customer->name }}
                    @if($sale->customer->trashed())
                        <small style="color: #dc3545;">(Deleted)</small>
                    @endif
                </span>
            </div>
            @if($sale->customer->phone)
            <div class="info-row">
                <span class="info-label">Phone:</span>
                <span>{{ $sale->customer->phone }}</span>
            </div>
            @endif
        </div>
        
        <div class="divider"></div>
        
        <div class="items-section">
            <div class="item-row">
                <div class="item-name">
                    <strong>{{ $sale->brand->name }}</strong>
                    <div style="font-size: 10px; margin-top: 2px;">
                        Qty: {{ $sale->quantity }}
                    </div>
                </div>
                <div class="item-details">
                    <div>{{ number_format($sale->price, 0) }}</div>
                </div>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <div class="total-section">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>{{ number_format($sale->price, 0) }}</span>
            </div>
            <div class="total-row grand">
                <span>TOTAL:</span>
                <span>{{ number_format($sale->price, 0) }}</span>
            </div>
        </div>
        
        <div class="payment-status {{ $sale->is_paid ? 'paid' : 'unpaid' }}">
            {{ $sale->is_paid ? '✓ PAID' : '● UNPAID' }}
        </div>
        
        @if($sale->notes)
        <div class="info-section">
            <div class="info-label">Notes:</div>
            <div style="margin-top: 5px; font-size: 11px;">{{ $sale->notes }}</div>
        </div>
        @endif
        
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>Generated: {{ now()->format('d/m/Y h:i A') }}</p>
        </div>
    </div>
    
    <div class="print-button no-print">
        <button class="btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Print Receipt
        </button>
    </div>
    
    <script>
        // Auto print when page loads (if coming from save and print)
        @if(session('success') && request()->has('autoprint'))
            window.onload = function() {
                window.print();
            };
        @endif
    </script>
</body>
</html>
