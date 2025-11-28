<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #6366F1; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .reminder-box { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #6366F1; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $reminder->title }}</h1>
        </div>
        
        <div class="content">
            <p>Dear {{ $reminder->customer->name ?? 'Customer' }},</p>
            
            <div class="reminder-box">
                <p>{{ $reminder->description }}</p>
            </div>
            
            @if($reminder->notes)
            <p><strong>Additional Notes:</strong><br>{{ $reminder->notes }}</p>
            @endif
            
            <p>If you have any questions, please feel free to contact me:</p>
            <ul>
                <li><strong>Name:</strong> {{ $agent->name }}</li>
                <li><strong>Email:</strong> {{ $agent->email }}</li>
                @if($agent->phone)
                <li><strong>Phone:</strong> {{ $agent->phone }}</li>
                @endif
            </ul>
            
            <p>Best regards,<br>{{ $agent->name }}</p>
        </div>
        
        <div class="footer">
            <p>This is an automated reminder email.</p>
        </div>
    </div>
</body>
</html>