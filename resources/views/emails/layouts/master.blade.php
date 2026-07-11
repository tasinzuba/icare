<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'CD IELTS' }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        table { border-collapse: collapse !important; }
        body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; }

        @media screen and (max-width: 600px) {
            .container { padding: 0 16px !important; }
            .content { padding: 24px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #F9FAFB;">

    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F9FAFB;">
        <tr>
            <td align="center" style="padding: 40px 16px;">

                <table class="container" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 560px;">

                    <!-- Logo -->
                    <tr>
                        <td style="padding-bottom: 24px;">
                            @php
                                $websiteSetting = \App\Models\WebsiteSetting::first();
                                $logoUrl = $websiteSetting && $websiteSetting->logo_url ? $websiteSetting->logo_url : null;
                            @endphp

                            @if($logoUrl)
                                <img src="{{ $logoUrl }}" alt="CD IELTS" style="height: 36px; max-width: 140px;">
                            @else
                                <span style="font-size: 18px; font-weight: 700; color: #C8102E;">CD IELTS</span>
                            @endif
                        </td>
                    </tr>

                    <!-- Main Card -->
                    <tr>
                        <td>
                            <table class="content" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #FFFFFF; border-radius: 8px; border: 1px solid #E5E7EB;">

                                <tr>
                                    <td style="background-color: #C8102E; height: 4px; border-radius: 8px 8px 0 0;"></td>
                                </tr>

                                <tr>
                                    <td style="padding: 32px;">
                                        @yield('content')
                                    </td>
                                </tr>

                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 0;">
                            <p style="margin: 0 0 8px 0; color: #9CA3AF; font-size: 13px;">
                                © {{ date('Y') }} CD IELTS. All rights reserved.
                            </p>
                            <p style="margin: 0; color: #9CA3AF; font-size: 13px;">
                                <a href="{{ url('/') }}" style="color: #C8102E; text-decoration: none;">Visit Website</a>
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
