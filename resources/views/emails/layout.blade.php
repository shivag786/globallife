{{-- Shared shell for account emails. Inline styles only: mail clients strip <style>. --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f5;font-family:Arial,Helvetica,sans-serif;color:#1f2d27;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f5;padding:24px 0;">
        <tr><td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e6ebe8;">
                <tr>
                    <td style="background:#1b3c2c;padding:24px 32px;color:#ffffff;">
                        <span style="font-size:20px;font-weight:bold;letter-spacing:.3px;">{{ config('app.name') }}</span>
                        <p style="margin:6px 0 0;color:#a7c3b4;font-size:13px;">@yield('tagline')</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px 32px;font-size:14px;line-height:1.6;color:#4a5a52;">
                        @yield('body')
                    </td>
                </tr>
                <tr>
                    <td style="padding:16px 32px 24px;border-top:1px solid #e6ebe8;font-size:12px;color:#8a9a92;line-height:1.5;">
                        This is an automated message from {{ config('app.name') }}. Please do not reply to this email.
                    </td>
                </tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
