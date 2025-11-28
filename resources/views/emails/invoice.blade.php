<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #4F46E5;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 5px 5px;
        }
        .button {
            display: inline-block;
            background: #4F46E5;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .details {
            background: white;
            padding: 20px;
            border-left: 4px solid #4F46E5;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Invoice for Your Subscription</h1>
        </div>
        
        <div class="content">
            <p>Dear {{ $payment->user->name }},</p>
            
            <p>Thank you for your payment! Your subscription has been successfully activated.</p>
            
            <div class="details">
                <h3>Subscription Details</h3>
                <p><strong>Plan:</strong> {{ $subscription->plan->name }}</p>
                <p><strong>Amount Paid:</strong> ${{ number_format($payment->amount, 2) }}</p>
                <p><strong>Valid From:</strong> {{ $subscription->starts_at->format('d M, Y') }}</p>
                <p><strong>Valid Until:</strong> {{ $subscription->ends_at->format('d M, Y') }}</p>
                <p><strong>Invoice Number:</strong> INV-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</p>
            </div>
            
            <p>Please find your invoice attached to this email.</p>
            
            <p>If you have any questions, please don't hesitate to contact our support team.</p>
            
            <p>Best regards,<br>Real Estate Platform Team</p>
        </div>
        
        <div class="footer">
            <p>This is an automated email. Please do not reply.</p>
        </div>
    </div>
</body>
</html>