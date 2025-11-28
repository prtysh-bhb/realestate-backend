<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #10B981; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .appointment-info { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #10B981; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Follow-up on Your Property Visit</h1>
        </div>
        
        <div class="content">
            <p>Dear {{ $reminder->customer->name }},</p>
            
            <p>Thank you for visiting the property. I hope you found the {{ $appointment ? $appointment->type : 'visit' }} helpful.</p>
            
            @if($property)
            <div class="appointment-info">
                <h3>{{ $property->title }}</h3>
                <p><strong>Location:</strong> {{ $property->address }}</p>
                @if($appointment)
                <p><strong>Visit Date:</strong> {{ $appointment->scheduled_at->format('M d, Y h:i A') }}</p>
                @endif
            </div>
            @endif
            
            <p>{{ $reminder->description }}</p>
            
            <p>I would love to hear your feedback and answer any questions you might have about the property.</p>
            
            <p>Contact me:</p>
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
            <p>This is an automated follow-up email.</p>
        </div>
    </div>
</body>
</html>