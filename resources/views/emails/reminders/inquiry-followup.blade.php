<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4F46E5; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .property-info { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #4F46E5; }
        .button { background: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; display: inline-block; margin: 10px 0; border-radius: 4px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Follow-up on Your Property Inquiry</h1>
        </div>
        
        <div class="content">
            <p>Dear {{ $reminder->customer->name }},</p>
            
            <p>Thank you for your interest in our property. I wanted to follow up on your inquiry regarding:</p>
            
            @if($property)
            <div class="property-info">
                <h3>{{ $property->title }}</h3>
                <p><strong>Location:</strong> {{ $property->address }}, {{ $property->city }}</p>
                <p><strong>Price:</strong> ₹{{ number_format($property->price, 2) }}</p>
                <p><strong>Type:</strong> {{ ucfirst($property->type) }}</p>
            </div>
            @endif
            
            <p>{{ $reminder->description }}</p>
            
            <p>I would be happy to provide you with more information or schedule a property viewing at your convenience.</p>
            
            <p>Please feel free to reach out to me:</p>
            <ul>
                <li><strong>Name:</strong> {{ $agent->name }}</li>
                <li><strong>Email:</strong> {{ $agent->email }}</li>
                @if($agent->phone)
                <li><strong>Phone:</strong> {{ $agent->phone }}</li>
                @endif
            </ul>
            
            <p>Looking forward to hearing from you!</p>
            
            <p>Best regards,<br>{{ $agent->name }}</p>
        </div>
        
        <div class="footer">
            <p>This is an automated follow-up email. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>