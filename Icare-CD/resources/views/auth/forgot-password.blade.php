<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - CD IELTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        {{-- Left Side - Branding (Desktop Only) --}}
        <div class="hidden lg:flex lg:w-2/5 bg-gradient-to-br from-red-500 to-rose-600 items-center justify-center px-12">
            <div class="text-white text-center">
                <div class="mb-8">
                    <div class="w-24 h-24 bg-white/20 rounded-full flex items-center justify-center mx-auto backdrop-blur-sm">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                    </div>
                </div>
                <h1 class="text-4xl font-bold mb-4">Forgot Password?</h1>
                <p class="text-lg opacity-90">No worries! We'll help you reset it</p>
            </div>
        </div>

        {{-- Right Side - Form --}}
        <div class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8">
            <div class="w-full max-w-md">
                {{-- Mobile Header --}}
                <div class="lg:hidden text-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Forgot Password</h1>
                    <p class="text-sm text-gray-600 mt-1">Reset your password via email</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 lg:p-8">
                    {{-- Desktop Header --}}
                    <div class="hidden lg:block text-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Reset Your Password</h2>
                        <p class="text-sm text-gray-600 mt-1">Enter your email and we'll send you a reset link</p>
                    </div>

                    {{-- Success Message --}}
                    @if (session('status'))
                        <div class="mb-5 bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-green-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-sm">{{ session('status') }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Form --}}
                    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                        @csrf

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-xs font-medium text-gray-700 mb-1">Email Address</label>
                            <div class="relative">
                                <input id="email" 
                                       name="email" 
                                       type="email" 
                                       value="{{ old('email') }}" 
                                       required 
                                       autofocus
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent pr-10 @error('email') border-red-300 @enderror"
                                       placeholder="you@example.com">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                    </svg>
                                </div>
                            </div>
                            @error('email')
                                <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" 
                                class="w-full py-2.5 bg-red-500 text-white text-sm font-medium rounded-lg hover:bg-red-600 transition">
                            Send Reset Link
                        </button>

                        {{-- Back to Login --}}
                        <div class="text-center">
                            <a href="{{ route('login') }}" class="text-xs text-gray-600 hover:text-gray-700">
                                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Back to login
                            </a>
                        </div>
                    </form>

                    {{-- Divider --}}
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="text-center">
                            <p class="text-xs text-gray-500 mb-3">Need help?</p>
                            <div class="flex justify-center space-x-4 text-xs">
                                <a href="{{ route('register') }}" class="text-red-600 hover:text-red-500">Create Account</a>
                                <span class="text-gray-300">•</span>
                                <a href="#" class="text-red-600 hover:text-red-500">Contact Support</a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer Text --}}
                <div class="mt-6 text-center">
                    <p class="text-xs text-gray-500">
                        We'll send a password reset link to your email address
                    </p>
                    <p class="text-xs text-gray-400 mt-2">
                        Make sure to check your spam folder
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>