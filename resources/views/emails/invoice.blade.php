<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #000;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #000;
            color: white;
            padding: 30px;
            text-align: center;
            border: 3px solid #000;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            letter-spacing: 1px;
        }
        .header .logo {
            max-width: 150px;
            max-height: 60px;
            margin-bottom: 15px;
        }
        .content {
            background: #f5f5f5;
            padding: 30px;
            border: 3px solid #000;
            border-top: none;
        }
        .info-box {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border: 2px solid #000;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #ccc;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #000;
        }
        .total-amount {
            font-size: 24px;
            color: #000;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: white;
            border: 3px solid #000;
        }
        .button {
            display: inline-block;
            background: #000;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            margin: 20px 0;
            font-weight: bold;
            border: 2px solid #000;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 3px solid #000;
            color: #666;
            font-size: 14px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            border: 2px solid #000;
            background: white;
            color: #000;
        }
    </style>
</head>
<body>
    <div class="header">
        @if(file_exists(public_path('images/logo-white.png')))
            <img src="{{ asset('images/logo-white.png') }}" alt="Sunstar Logistics" class="logo">
        @elseif(file_exists(public_path('images/logo.png')))
            <img src="{{ asset('images/logo.png') }}" alt="Sunstar Logistics" class="logo" style="filter: brightness(0) invert(1);">
        @endif
        <h1>SUNSTAR LOGISTICS</h1>
        <p style="margin: 10px 0 0 0; font-size: 18px; font-weight: bold; letter-spacing: 2px;">INVOICE</p>
    </div>

    <div class="content">
        <p>Dear {{ $invoice->client->first_name }} {{ $invoice->client->last_name }},</p>
        
        <p>Thank you for your business! Please find attached your invoice for the logistics services provided.</p>

        <div class="info-box">
            <div class="info-row">
                <span class="info-label">Invoice Number:</span>
                <span>{{ $invoice->invoice_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Invoice Date:</span>
                <span>{{ $invoice->invoice_date->format('M d, Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Due Date:</span>
                <span>{{ $invoice->due_date->format('M d, Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span>
                    <span class="status-badge">
                        {{ strtoupper($invoice->status) }}
                    </span>
                </span>
            </div>
            @if($invoice->booking)
            <div class="info-row">
                <span class="info-label">Booking Number:</span>
                <span>{{ $invoice->booking->booking_number }}</span>
            </div>
            @endif
        </div>

        <div class="total-amount">
            Total Amount: {{ $invoice->formatAmount($invoice->total_amount) }}
        </div>

        @if($invoice->amount_paid > 0)
            <p style="text-align: center; color: #16a34a;">
                Amount Paid: {{ $invoice->formatAmount($invoice->amount_paid) }}<br>
                <span style="color: #dc2626; font-weight: bold;">
                    Balance Due: {{ $invoice->formatAmount($invoice->total_amount - $invoice->amount_paid) }}
                </span>
            </p>
        @endif

        <p>The invoice is attached to this email as a PDF file. Please review it and process payment by the due date.</p>

        @if($invoice->notes)
            <div class="info-box" style="background: #f5f5f5;">
                <p style="margin: 0;"><strong>Note:</strong></p>
                <p style="margin: 10px 0 0 0;">{{ $invoice->notes }}</p>
            </div>
        @endif

        <p>If you have any questions about this invoice, please don't hesitate to contact us.</p>

        <div class="footer">
            <p><strong>Thank you for your business!</strong></p>
            <p>Sunstar Logistics<br>
            Email: support@sunstarlogistics.com<br>
            This is an automated email, please do not reply directly.</p>
        </div>
    </div>
</body>
</html>

