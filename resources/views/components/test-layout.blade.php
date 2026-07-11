<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-2EDLFCQKLH"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-2EDLFCQKLH');
    </script>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'IELTS Test' }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tippy.js for vocabulary popups -->
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>
    <link rel="stylesheet" href="https://unpkg.com/tippy.js@6/themes/light-border.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Test Layout Override Styles -->
    <style>
        /* Reset all question styles */
        .question-box,
        .question-number,
        .options-list,
        .option-item,
        .option-radio {
            all: unset;
            display: revert;
            box-sizing: border-box;
        }
        
        /* IELTS Official Format */
        .ielts-question-item {
            margin-bottom: 24px !important;
            padding: 0 !important;
            background: none !important;
            border: none !important;
        }
        
        .ielts-q-number {
            font-weight: 700 !important;
            font-size: 14px !important;
            color: #000000 !important;
            line-height: 1.5 !important;
            margin-bottom: 10px !important;
            display: block !important;
            padding: 0 !important;
            background: none !important;
            border: none !important;
            text-align: left !important;
        }
        
        .ielts-options {
            margin-left: 24px !important;
            margin-top: 8px !important;
            padding: 0 !important;
            background: none !important;
        }
        
        .ielts-option {
            margin-bottom: 6px !important;
            display: flex !important;
            align-items: center !important;
            padding: 0 !important;
            background: none !important;
            border: none !important;
        }
        
        .ielts-radio {
            -webkit-appearance: radio !important;
            -moz-appearance: radio !important;
            appearance: radio !important;
            margin: 0 !important;
            margin-right: 8px !important;
            width: 14px !important;
            height: 14px !important;
            cursor: pointer !important;
            padding: 0 !important;
            background: none !important;
            border: 1px solid #000 !important;
            border-radius: 50% !important;
        }
        
        .ielts-option label {
            cursor: pointer !important;
            font-size: 14px !important;
            color: #000000 !important;
            font-weight: normal !important;
            margin: 0 !important;
            padding: 0 !important;
            background: none !important;
            line-height: 1.4 !important;
        }
        
        /* Ensure radio buttons work properly */
        input[type="radio"].ielts-radio:checked {
            background-color: #000 !important;
        }
        
        /* IELTS Dropdown Styling */
        .ielts-options select {
            width: 100% !important;
            max-width: 300px !important;
            padding: 8px 12px !important;
            border: 1px solid #d1d5db !important;
            border-radius: 4px !important;
            font-size: 14px !important;
            background-color: white !important;
            color: #000000 !important;
            cursor: pointer !important;
            appearance: auto !important;
            -webkit-appearance: menulist !important;
            -moz-appearance: menulist !important;
            background-image: none !important;
            line-height: 1.5 !important;
            height: auto !important;
            font-family: inherit !important;
        }
        
        .ielts-options select:hover {
            border-color: #9ca3af !important;
        }
        
        .ielts-options select:focus {
            outline: 2px solid #3b82f6 !important;
            outline-offset: 1px !important;
            border-color: #3b82f6 !important;
        }
        
        /* Ensure select doesn't get Tailwind resets */
        select.ielts-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
            background-position: right 0.5rem center !important;
            background-repeat: no-repeat !important;
            background-size: 1.5em 1.5em !important;
            padding-right: 2.5rem !important;
        }
    </style>
    
    <!-- Test specific meta tags -->
    @if(isset($meta))
        {{ $meta }}
    @endif
</head>
<body class="font-sans antialiased">
    <!-- No navigation header for test pages -->
    {{ $slot }}
    
    @stack('scripts')
</body>
</html>