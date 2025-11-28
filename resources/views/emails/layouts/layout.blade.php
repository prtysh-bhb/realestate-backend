<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width" />
    <title>RealEstate — @yield('title', 'Notification')</title>
</head>

<body style="margin:0; padding:0; background:#eef6fb; font-family: Arial, Helvetica, sans-serif; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;">
    <!-- Outer wrapper -->
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#eef6fb; width:100%; padding:36px 0;">
        <tr>
            <td align="center">
                <!-- Center card -->
                <table width="600" cellpadding="0" cellspacing="0" role="presentation"
                    style="background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #e6ecf3; box-shadow: 0 6px 24px rgba(15,23,42,0.06);">

                    @include('emails.layouts.header')

                    <!-- Body -->
                    <tr>
                        <td style="padding:30px 34px 26px 34px; color:#222; font-size:15px; line-height:1.68;">
                            @yield('content')
                        </td>
                    </tr>

                    @include('emails.layouts.footer')

                </table>
                <!-- End center card -->
            </td>
        </tr>
    </table>
</body>

</html>