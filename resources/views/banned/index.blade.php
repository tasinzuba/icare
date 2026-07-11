<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Banned - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl w-full space-y-8">
            <!-- Ban Alert -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="flex items-center mb-6">
                    <div class="flex-shrink-0">
                        <svg class="h-12 w-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-2xl font-bold text-gray-900">
                            Your Account Has Been {{ Auth::user()->isPermanentlyBanned() ? 'Permanently' : 'Temporarily' }} Banned
                        </h2>
                    </div>
                </div>
                
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                    <div class="flex">
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                <strong>Reason:</strong> {{ Auth::user()->ban_reason }}
                            </p>
                            @if(Auth::user()->isTemporarilyBanned() && Auth::user()->ban_expires_at)
                                <p class="text-sm text-red-700 mt-1">
                                    <strong>Ban expires on:</strong> {{ Auth::user()->getBanExpiryDate() }}
                                </p>
                            @endif
                            @if(Auth::user()->bannedBy)
                                <p class="text-sm text-red-700 mt-1">
                                    <strong>Banned by:</strong> {{ Auth::user()->bannedBy->name }}
                                </p>
                            @endif
                            <p class="text-sm text-red-700 mt-1">
                                <strong>Date:</strong> {{ Auth::user()->banned_at ? \Carbon\Carbon::parse(Auth::user()->banned_at)->format('F j, Y g:i A') : 'Unknown' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Appeal Section -->
                <div class="mt-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Submit an Appeal</h3>
                    
                    @if(session('success'))
                        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-4">
                            <p class="text-green-700">{{ session('success') }}</p>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
                            <p class="text-red-700">{{ session('error') }}</p>
                        </div>
                    @endif

                    <div class="text-center py-6 text-gray-600">
                        <p class="mb-4">Please contact the admin if you believe this is a mistake.</p>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-blue-600 hover:text-blue-800 underline">
                                Logout
                            </button>
                        </form>
                    </div>
                    @if(false)
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="mt-2 text-gray-600">
                                Your appeal is currently being reviewed. Please check back later.
                            </p>
                            <form action="{{ route('logout') }}" method="POST" class="mt-4">
                                @csrf
                                <button type="submit" class="text-blue-600 hover:text-blue-800">
                                    Logout
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                <!-- Information Section -->
                <div class="mt-8 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                    <h3 class="text-sm font-semibold text-blue-800 mb-2">What happens next?</h3>
                    <ul class="list-disc list-inside text-xs text-blue-700 space-y-1">
                        <li>An administrator will review your appeal within 24-48 hours</li>
                        <li>You will be notified via email once a decision is made</li>
                        <li>If approved, you will regain access to your account immediately</li>
                        <li>If rejected, you may submit another appeal after 7 days</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
