<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $payment->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
        }
        .header {
            display: table;
            width: 100%;
            margin-bottom: 40px;
            border-bottom: 3px solid #4F46E5;
            padding-bottom: 20px;
        }
        .header-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }
        .header-right {
            display: table-cell;
            width: 40%;
            text-align: right;
            vertical-align: top;
        }
        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #4F46E5;
            margin-bottom: 5px;
        }
        .company-details {
            font-size: 12px;
            color: #666;
            line-height: 1.8;
        }
        .invoice-title {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .invoice-details {
            font-size: 12px;
            color: #666;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #4F46E5;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 30px;
        }
        .info-table td {
            padding: 8px 0;
            vertical-align: top;
        }
        .info-table .label {
            font-weight: bold;
            width: 150px;
            color: #666;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #4F46E5;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        .items-table tr:last-child td {
            border-bottom: 2px solid #4F46E5;
        }
        .text-right {
            text-align: right;
        }
        .total-section {
            width: 300px;
            margin-left: auto;
            margin-top: 20px;
        }
        .total-row {
            display: table;
            width: 100%;
            padding: 10px 0;
        }
        .total-label {
            display: table-cell;
            font-weight: bold;
            color: #666;
        }
        .total-value {
            display: table-cell;
            text-align: right;
            font-weight: bold;
        }
        .grand-total {
            border-top: 2px solid #4F46E5;
            padding-top: 15px;
            margin-top: 10px;
        }
        .grand-total .total-label,
        .grand-total .total-value {
            font-size: 18px;
            color: #4F46E5;
        }
        .footer {
            margin-top: 60px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-success {
            background-color: #10B981;
            color: white;
        }
        .badge-pending {
            background-color: #F59E0B;
            color: white;
        }
        .badge-failed {
            background-color: #EF4444;
            color: white;
        }
        .features-list {
            list-style: none;
            padding: 0;
        }
        .features-list li {
            padding: 5px 0;
            padding-left: 20px;
            position: relative;
        }
        .features-list li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #10B981;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="company-name">Real Estate Platform</div>
                <div class="company-details">
                    123 Business Street<br>
                    Mumbai, Maharashtra 400001<br>
                    India<br>
                    Email: info@realestate.com<br>
                    Phone: +91 98765 43210<br>
                    GST: 27XXXXX1234X1ZX
                </div>
            </div>
            <div class="header-right">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-details">
                    <strong>Invoice #:</strong> INV-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}<br>
                    <strong>Date:</strong> {{ $payment->created_at->format('d M, Y') }}<br>
                    <strong>Status:</strong> 
                    @if($payment->status === 'succeeded')
                        <span class="badge badge-success">Paid</span>
                    @elseif($payment->status === 'pending')
                        <span class="badge badge-pending">Pending</span>
                    @else
                        <span class="badge badge-failed">Failed</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Bill To Section -->
        <div class="section">
            <div class="section-title">Bill To</div>
            <table class="info-table">
                <tr>
                    <td class="label">Name:</td>
                    <td>{{ $payment->user->name }}</td>
                </tr>
                <tr>
                    <td class="label">Email:</td>
                    <td>{{ $payment->user->email }}</td>
                </tr>
                @if($payment->user->phone)
                <tr>
                    <td class="label">Phone:</td>
                    <td>{{ $payment->user->phone }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label">User ID:</td>
                    <td>#{{ $payment->user->id }}</td>
                </tr>
            </table>
        </div>

        <!-- Items Table -->
        <div class="section">
            <div class="section-title">Subscription Details</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Duration</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>{{ $subscription->plan->name }} Plan</strong><br>
                            <span style="font-size: 12px; color: #666;">
                                {{ $subscription->plan->description }}
                            </span>
                        </td>
                        <td>{{ $subscription->plan->duration_days }} days</td>
                        <td class="text-right">${{ number_format($payment->amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Plan Features -->
        <div class="section">
            <div class="section-title">Plan Features</div>
            <ul class="features-list">
                <li>{{ $subscription->plan->property_limit > 0 ? $subscription->plan->property_limit . ' Properties' : 'Unlimited Properties' }}</li>
                <li>{{ $subscription->plan->featured_limit > 0 ? $subscription->plan->featured_limit . ' Featured Listings per month' : 'Unlimited Featured Listings' }}</li>
                <li>{{ $subscription->plan->image_limit }} Images per property</li>
                <li>{{ $subscription->plan->video_allowed ? 'Video Upload Allowed' : 'Video Upload Not Allowed' }}</li>
                @if($subscription->plan->priority_support)
                <li>Priority Support</li>
                @endif
            </ul>
        </div>

        <!-- Subscription Period -->
        <div class="section">
            <div class="section-title">Subscription Period</div>
            <table class="info-table">
                <tr>
                    <td class="label">Start Date:</td>
                    <td>{{ $subscription->starts_at->format('d M, Y') }}</td>
                </tr>
                <tr>
                    <td class="label">End Date:</td>
                    <td>{{ $subscription->ends_at->format('d M, Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Status:</td>
                    <td>
                        @if($subscription->status === 'active')
                            <span class="badge badge-success">Active</span>
                        @elseif($subscription->status === 'expired')
                            <span class="badge badge-failed">Expired</span>
                        @else
                            <span class="badge badge-pending">{{ ucfirst($subscription->status) }}</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- Total Section -->
        <div class="total-section">
            <div class="total-row">
                <div class="total-label">Subtotal:</div>
                <div class="total-value">${{ number_format($payment->amount, 2) }}</div>
            </div>
            <div class="total-row">
                <div class="total-label">Tax (0%):</div>
                <div class="total-value">$0.00</div>
            </div>
            <div class="total-row grand-total">
                <div class="total-label">Total Amount:</div>
                <div class="total-value">${{ number_format($payment->amount, 2) }}</div>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="section">
            <div class="section-title">Payment Information</div>
            <table class="info-table">
                <tr>
                    <td class="label">Payment Method:</td>
                    <td>Credit/Debit Card (Stripe)</td>
                </tr>
                <tr>
                    <td class="label">Transaction ID:</td>
                    <td>{{ $payment->stripe_payment_intent_id }}</td>
                </tr>
                @if($payment->stripe_charge_id)
                <tr>
                    <td class="label">Charge ID:</td>
                    <td>{{ $payment->stripe_charge_id }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label">Payment Date:</td>
                    <td>{{ $payment->created_at->format('d M, Y h:i A') }}</td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Thank you for your business!</strong></p>
            <p>This is a computer-generated invoice. No signature required.</p>
            <p>For any queries, please contact us at support@realestate.com</p>
        </div>
    </div>
</body>
</html>