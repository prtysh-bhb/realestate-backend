<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <table role="presentation" style="width: 600px; border-collapse: collapse; background-color: #ffffff; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 40px 30px; background-color: #2563eb; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px;">Real Estate Platform</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="margin: 0 0 20px 0; color: #333333; font-size: 24px;">Reset Your Password</h2>
                            
                            <p style="margin: 0 0 20px 0; color: #666666; line-height: 1.6; font-size: 16px;">
                                Hello <strong>{{ $user->name }}</strong>,
                            </p>
                            
                            <p style="margin: 0 0 20px 0; color: #666666; line-height: 1.6; font-size: 16px;">
                                You recently requested to reset your password for your Real Estate Platform account. Click the button below to reset it.
                            </p>
                            
                            <!-- Button -->
                            <table role="presentation" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $resetUrl }}" style="display: inline-block; padding: 14px 40px; background-color: #2563eb; color: #ffffff; text-decoration: none; border-radius: 5px; font-size: 16px; font-weight: bold;">Reset Password</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin: 20px 0; color: #666666; line-height: 1.6; font-size: 14px;">
                                Or copy and paste this link into your browser:
                            </p>
                            
                            <p style="margin: 0 0 20px 0; padding: 15px; background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 5px; word-break: break-all; font-size: 14px; color: #2563eb;">
                                {{ $resetUrl }}
                            </p>
                            
                            <p style="margin: 20px 0 0 0; padding: 20px; background-color: #fff3cd; border-left: 4px solid #ffc107; color: #856404; font-size: 14px; line-height: 1.6;">
                                <strong>Security Notice:</strong> This link will expire in 24 hours. If you didn't request a password reset, please ignore this email or contact support if you have concerns.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 30px; background-color: #f8f9fa; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0; color: #999999; font-size: 12px; text-align: center; line-height: 1.6;">
                                © {{ date('Y') }} Real Estate Platform. All rights reserved.<br>
                                This is an automated email, please do not reply.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>