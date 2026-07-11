<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Expired - CD IELTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full">
            {{-- Error Card --}}
            <div class="bg-white rounded-2xl shadow-sm p-8 text-center">
                {{-- Icon --}}
                <div class="mb-6">
                    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto">
                        <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </div>

                {{-- Title --}}
                <h1 class="text-2xl font-bold text-gray-900 mb-3">
                    {{ $title ?? 'Password Reset Link Expired' }}
                </h1>

                {{-- Message --}}
                <p class="text-gray-600 text-sm mb-6 leading-relaxed">
                    {{ $message ?? 'This password reset link has expired or has already been used. For security reasons, password reset links can only be used once.' }}
                </p>

                {{-- Status Badge --}}
                <div class="inline-flex items-center px-4 py-2 bg-red-50 border border-red-200 rounded-lg mb-6">
                    <svg class="w-4 h-4 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span class="text-sm font-medium text-red-800">Link No Longer Valid</span>
                </div>

                {{-- Divider --}}
                <div class="border-t border-gray-200 my-6"></div>

                {{-- Action Buttons --}}
                <div class="space-y-3">
                    <a href="{{ route('password.request') }}"
                       class="block w-full py-3 bg-red-500 text-white text-sm font-medium rounded-lg hover:bg-red-600 transition">
                        Request New Reset Link
                    </a>

                    <a href="{{ route('login') }}"
                       class="block w-full py-3 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
                        Back to Login
                    </a>
                </div>

                {{-- Help Text --}}
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-xs text-gray-500 mb-3">Why did this happen?</p>
                    <ul class="text-xs text-gray-600 space-y-2 text-left">
                        <li class="flex items-start">
                            <svg class="w-4 h-4 text-gray-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>The link was already used to reset your password</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 text-gray-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>The link expired (links are valid for 60 minutes)</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 text-gray-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>A newer reset link was requested for this account</span>
                        </li>
                    </ul>
                </div>

                {{-- Contact Support --}}
                <div class="mt-6">
                    <p class="text-xs text-gray-500">
                        Need help?
                        <a href="#" class="text-red-600 hover:text-red-500 font-medium">Contact Support</a>
                    </p>
                </div>
            </div>

            {{-- Footer --}}
            <div class="mt-6 text-center">
                <p class="text-xs text-gray-400">
                    © {{ date('Y') }} CD IELTS. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
