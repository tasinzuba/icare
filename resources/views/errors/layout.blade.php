<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Error') - I-Care</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: #FFFFFF;
            color: #111827;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
            text-align: center;
        }

        .logo {
            margin-bottom: 48px;
        }

        .logo img {
            height: 28px;
        }

        .logo-text {
            font-size: 18px;
            font-weight: 700;
            color: #C8102E;
            text-decoration: none;
        }

        .error-code {
            font-size: 120px;
            font-weight: 700;
            color: #E5E7EB;
            line-height: 1;
            margin-bottom: 16px;
        }

        .error-title {
            font-size: 20px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 8px;
        }

        .error-message {
            font-size: 15px;
            color: #6B7280;
            margin-bottom: 32px;
        }

        .error-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .btn {
            display: inline-block;
            padding: 10px 24px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.15s ease;
        }

        .btn-primary {
            background: #111827;
            color: #FFFFFF;
        }

        .btn-primary:hover {
            background: #1F2937;
        }

        .btn-secondary {
            color: #6B7280;
        }

        .btn-secondary:hover {
            color: #111827;
        }

        @media (max-width: 480px) {
            .error-code {
                font-size: 80px;
            }

            .error-title {
                font-size: 18px;
            }

            .error-actions {
                flex-direction: column;
                width: 100%;
                max-width: 200px;
            }
        }
    </style>
</head>
<body>
    @php
        $websiteSetting = \App\Models\WebsiteSetting::first();
        $logoUrl = $websiteSetting && $websiteSetting->logo_url ? $websiteSetting->logo_url : null;
    @endphp

    <a href="{{ url('/') }}" class="logo">
        @if($logoUrl)
            <img src="{{ $logoUrl }}" alt="I-Care">
        @else
            <span class="logo-text">I-Care</span>
        @endif
    </a>

    <div class="error-code">@yield('code')</div>
    <h1 class="error-title">@yield('title')</h1>
    <p class="error-message">@yield('message')</p>
    <div class="error-actions">
        @yield('actions')
    </div>

    <script>
        function goBack() {
            if (document.referrer && document.referrer !== window.location.href) {
                window.location.href = document.referrer;
            } else if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '{{ url('/') }}';
            }
        }
    </script>
</body>
</html>
