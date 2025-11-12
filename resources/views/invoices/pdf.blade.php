<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        @page {
            margin: 40px 40px 60px 40px;
        }
        body {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            color: #242424;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            margin-bottom: 50px;
            padding-bottom: 25px;
            border-bottom: 1px solid #d6d6d6;
        }
        .header-flex {
            display: table;
            width: 100%;
        }
        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 60%;
        }
        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 40%;
        }
        .logo {
            max-width: 180px;
            max-height: 70px;
            margin-bottom: 12px;
        }
        .company-name {
            font-size: 28px;
            font-weight: 600;
            color: #000;
            margin: 0 0 5px 0;
            letter-spacing: -0.5px;
        }
        .company-tagline {
            font-size: 13px;
            color: #5f6368;
            margin: 0;
        }
        .invoice-title {
            font-size: 42px;
            color: #000;
            margin: 0;
            font-weight: 300;
            letter-spacing: -1px;
        }
        .invoice-number {
            font-size: 16px;
            color: #5f6368;
            margin: 5px 0 0 0;
            font-weight: 400;
        }
        .info-section {
            display: table;
            width: 100%;
            margin: 40px 0;
        }
        .info-column {
            display: table-cell;
            width: 48%;
            vertical-align: top;
        }
        .info-spacer {
            display: table-cell;
            width: 4%;
        }
        .info-box {
            margin-bottom: 25px;
        }
        .info-label {
            font-size: 11px;
            color: #5f6368;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }
        .info-content {
            font-size: 14px;
            color: #242424;
            line-height: 1.7;
        }
        .info-content strong {
            font-weight: 600;
        }
        .detail-grid {
            display: table;
            width: 100%;
            margin: 8px 0;
        }
        .detail-row {
            display: table-row;
        }
        .detail-label {
            display: table-cell;
            font-size: 13px;
            color: #5f6368;
            padding: 6px 0;
            width: 40%;
        }
        .detail-value {
            display: table-cell;
            font-size: 13px;
            color: #242424;
            padding: 6px 0;
            font-weight: 500;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #d6d6d6;
            background: #f8f9fa;
            color: #242424;
        }
        .items-section {
            margin: 50px 0;
        }
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #000;
            margin-bottom: 20px;
            letter-spacing: -0.3px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .items-table thead {
            border-bottom: 2px solid #242424;
        }
        .items-table th {
            padding: 12px 0;
            text-align: left;
            font-size: 11px;
            color: #5f6368;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .items-table th.text-right {
            text-align: right;
        }
        .items-table td {
            padding: 20px 0;
            border-bottom: 1px solid #e8eaed;
            font-size: 14px;
            color: #242424;
        }
        .items-table td.text-right {
            text-align: right;
        }
        .item-description {
            font-weight: 500;
            margin-bottom: 6px;
        }
        .item-details {
            font-size: 12px;
            color: #5f6368;
            line-height: 1.6;
        }
        .totals-section {
            margin-top: 40px;
            float: right;
            width: 350px;
        }
        .total-row {
            display: table;
            width: 100%;
            margin: 12px 0;
        }
        .total-label {
            display: table-cell;
            font-size: 14px;
            color: #5f6368;
            text-align: right;
            padding-right: 30px;
        }
        .total-value {
            display: table-cell;
            font-size: 14px;
            color: #242424;
            text-align: right;
            font-weight: 500;
            width: 120px;
        }
        .grand-total {
            border-top: 2px solid #242424;
            padding-top: 18px;
            margin-top: 18px;
        }
        .grand-total .total-label {
            font-size: 16px;
            color: #000;
            font-weight: 600;
        }
        .grand-total .total-value {
            font-size: 22px;
            color: #000;
            font-weight: 600;
        }
        .balance-due {
            background: #f8f9fa;
            padding: 15px 20px;
            margin-top: 15px;
            border: 1px solid #d6d6d6;
        }
        .balance-due .total-label,
        .balance-due .total-value {
            color: #000;
            font-weight: 600;
        }
        .notes-section {
            clear: both;
            margin-top: 60px;
            padding: 20px;
            background: #f8f9fa;
            border-left: 3px solid #242424;
        }
        .notes-title {
            font-size: 12px;
            color: #5f6368;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }
        .notes-content {
            font-size: 13px;
            color: #242424;
            line-height: 1.7;
        }
        .footer {
            margin-top: 60px;
            padding-top: 25px;
            border-top: 1px solid #d6d6d6;
            text-align: center;
        }
        .footer-title {
            font-size: 14px;
            font-weight: 600;
            color: #242424;
            margin-bottom: 8px;
        }
        .footer-text {
            font-size: 12px;
            color: #5f6368;
            line-height: 1.7;
        }
        .divider {
            height: 1px;
            background: #e8eaed;
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-flex">
                <div class="header-left">
                    @if(file_exists(public_path('images/logo.png')))
                        <img src="{{ public_path('images/logo.png') }}" alt="Sunstar Logistics" class="logo">
                    @endif
                    <h1 class="company-name">Sunstar Logistics</h1>
                    <p class="company-tagline">Professional Logistics Solutions</p>
                </div>
                <div class="header-right">
                    <div class="invoice-title">Invoice</div>
                    <p class="invoice-number">{{ $invoice->invoice_number }}</p>
                </div>
            </div>
        </div>

        <!-- Client and Invoice Info -->
        <div class="info-section">
            <div class="info-column">
                <div class="info-box">
                    <div class="info-label">Bill To</div>
                    <div class="info-content">
                        <strong>{{ $invoice->client->business_name }}</strong><br>
                        {{ $invoice->client->first_name }} {{ $invoice->client->last_name }}<br>
                        @if($invoice->client->address)
                            {{ $invoice->client->address }}<br>
                        @endif
                        {{ $invoice->client->email }}
                    </div>
                </div>
            </div>
            <div class="info-spacer"></div>
            <div class="info-column">
                <div class="info-box">
                    <div class="info-label">Invoice Details</div>
                    <div class="detail-grid">
                        <div class="detail-row">
                            <div class="detail-label">Invoice Date</div>
                            <div class="detail-value">{{ $invoice->invoice_date->format('M d, Y') }}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Due Date</div>
                            <div class="detail-value">{{ $invoice->due_date->format('M d, Y') }}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Currency</div>
                            <div class="detail-value">
                                @php
                                    $symbol = $invoice->getCurrencySymbol();
                                    $problematicSymbols = ['₹', '¥', '₩', '₽', '฿'];
                                    $displaySymbol = in_array($symbol, $problematicSymbols) ? '' : '(' . $symbol . ')';
                                @endphp
                                {{ $invoice->currency_code }} {{ $displaySymbol }}
                            </div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Status</div>
                            <div class="detail-value">
                                <span class="status-badge">{{ strtoupper($invoice->status) }}</span>
                            </div>
                        </div>
                        @if($invoice->booking)
                        <div class="detail-row">
                            <div class="detail-label">Booking Reference</div>
                            <div class="detail-value">{{ $invoice->booking->booking_number }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Section -->
        <div class="items-section">
            <div class="section-title">Services</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-right" style="width: 20%;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="item-description">Logistics Service</div>
                            @if($invoice->booking)
                            <div class="item-details">
                                Booking Reference: {{ $invoice->booking->booking_number }}<br>
                                Pickup Location: {{ $invoice->booking->pickupLocation->name ?? 'N/A' }}<br>
                                Delivery Location: {{ $invoice->booking->deliveryLocation->name ?? 'N/A' }}<br>
                                Container: {{ $invoice->booking->container->container_number ?? 'N/A' }}
                            </div>
                            @endif
                        </td>
                        <td class="text-right">{{ $invoice->formatAmountForPdf($invoice->amount) }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Totals -->
            <div class="totals-section">
                <div class="total-row">
                    <div class="total-label">Subtotal</div>
                    <div class="total-value">{{ $invoice->formatAmountForPdf($invoice->amount) }}</div>
                </div>
                <div class="total-row">
                    <div class="total-label">Tax</div>
                    <div class="total-value">{{ $invoice->formatAmountForPdf($invoice->tax_amount) }}</div>
                </div>
                <div class="total-row grand-total">
                    <div class="total-label">Total</div>
                    <div class="total-value">{{ $invoice->formatAmountForPdf($invoice->total_amount) }}</div>
                </div>
                @if($invoice->amount_paid > 0)
                    <div class="divider" style="margin: 15px 0;"></div>
                    <div class="total-row">
                        <div class="total-label">Amount Paid</div>
                        <div class="total-value">-{{ $invoice->formatAmountForPdf($invoice->amount_paid) }}</div>
                    </div>
                    <div class="total-row balance-due">
                        <div class="total-label">Balance Due</div>
                        <div class="total-value">{{ $invoice->formatAmountForPdf($invoice->total_amount - $invoice->amount_paid) }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Notes -->
        @if($invoice->notes)
        <div class="notes-section">
            <div class="notes-title">Notes</div>
            <div class="notes-content">{{ $invoice->notes }}</div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div class="footer-title">Thank you for your business</div>
            <div class="footer-text">
                For questions about this invoice, please contact us at support@sunstarlogistics.com<br>
               
            </div>
        </div>
    </div>
</body>
</html>

