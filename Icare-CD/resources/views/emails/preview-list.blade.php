<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Preview List</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #FFF5F5;
            margin: 0;
            padding: 40px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        h1 {
            color: #DC2626;
            margin: 0 0 32px 0;
        }
        .preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 16px;
        }
        .preview-card {
            background: #FEF2F2;
            border: 1px solid #FEE2E2;
            border-radius: 12px;
            padding: 20px;
            text-decoration: none;
            color: #374151;
            transition: all 0.2s;
        }
        .preview-card:hover {
            background: #FEE2E2;
            border-color: #DC2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.1);
        }
        .preview-card h3 {
            margin: 0 0 8px 0;
            color: #DC2626;
            font-size: 18px;
        }
        .preview-card p {
            margin: 0;
            font-size: 14px;
            color: #6B7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Email Template Previews</h1>
        <p style="color: #6B7280; margin-bottom: 32px;">
            Click on any template to preview the email design
        </p>
        
        <div class="preview-grid">
            @foreach($previews as $route => $title)
                <a href="/emails/preview/{{ $route }}" class="preview-card" target="_blank">
                    <h3>{{ $title }}</h3>
                    <p>{{ $route }}.blade.php</p>
                </a>
            @endforeach
        </div>
        
        <div style="margin-top: 40px; padding-top: 32px; border-top: 1px solid #E5E7EB;">
            <p style="color: #9CA3AF; font-size: 14px; text-align: center;">
                These previews are only available in development environment
            </p>
        </div>
    </div>
</body>
</html>
