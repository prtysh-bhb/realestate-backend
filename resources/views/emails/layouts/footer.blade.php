<!-- footer separator -->
<tr>
    <td style="height:1px; background:#eef2f6;"></td>
</tr>

<!-- Dark footer (brand) -->
<tr>
    <td style="background:#081226; padding:22px 28px; color:#b8c6da; text-align:left; font-size:13px;">
        <!-- Footer content table -->
        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <!-- left column: brand + brief -->
                <td valign="top" style="width:55%; padding-right:12px;">
                    <div style="display:flex; align-items:center;">
                        <img src="https://real-estate-bhub.vercel.app/assets/logo.jpg" alt="RealEstate" width="40"
                            style="display:block; border:0; outline:none; text-decoration:none; margin-right:10px;">
                        <div style="color:#ffffff; font-weight:700; font-size:15px;">RealEstate</div>
                    </div>

                    <div style="margin-top:10px; color:#9fb3cf; font-size:13px; line-height:1.6;">
                        Your trusted partner for premium listings, trusted agents, and seamless rentals & sales.
                    </div>

                    <div style="margin-top:12px; font-size:13px; color:#9fb3cf;">
                        <div style="margin-bottom:6px;"><strong style="color:#cfe7ff;">Address:</strong> D101 T-Sq Thaltej Ahmedabad, Gujarat, India</div>
                        <div style="margin-bottom:6px;"><strong style="color:#cfe7ff;">Phone:</strong> +91 88532 17658</div>
                        <div><strong style="color:#cfe7ff;">Email:</strong> contact@bhub.com</div>
                    </div>
                </td>

                <!-- right column: links & subscribe -->
                <td valign="top" style="width:45%; padding-left:12px;">
                    <div style="margin-bottom:10px; color:#cfe7ff; font-weight:600;">Quick Links</div>
                    <div style="font-size:13px; color:#9fb3cf; line-height:1.8;">
                        <a href="{{ config('app.frontend_url') }}/pricing" style="color:#9fb3cf; text-decoration:none;">Pricing Plans</a><br>
                        <a href="{{ config('app.frontend_url') }}/services" style="color:#9fb3cf; text-decoration:none;">Our Services</a><br>
                        <a href="{{ config('app.frontend_url') }}/about" style="color:#9fb3cf; text-decoration:none;">About Us</a><br>
                        <a href="{{ config('app.frontend_url') }}/contact" style="color:#9fb3cf; text-decoration:none;">Contact</a>
                    </div>
                </td>
            </tr>
        </table>
    </td>
</tr>

<!-- copyright -->
<tr>
    <td style="background:#061426; text-align:center; padding:12px 18px; color:#7f98b3; font-size:12px;">
        © {{ date('Y') }} RealEstate. All rights reserved. &nbsp; • &nbsp; 
        <a href="{{ config('app.frontend_url') }}/terms" style="color:#7f98b3; text-decoration:none;">Terms</a> &nbsp; • &nbsp; 
        <a href="{{ config('app.frontend_url') }}/privacy" style="color:#7f98b3; text-decoration:none;">Privacy</a>
        <div style="margin-top:6px; font-size:11px; color:#6b829d;">
            This is an automated message — replies are not monitored.
        </div>
    </td>
</tr>