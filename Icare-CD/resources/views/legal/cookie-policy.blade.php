@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="container mx-auto px-6 max-w-4xl">
        <!-- Breadcrumb -->
        <nav class="mb-8">
            <ol class="flex items-center space-x-2 text-sm">
                <li><a href="{{ route('home') }}" class="text-gray-500 hover:text-red-500">Home</a></li>
                <li><span class="text-gray-500">/</span></li>
                <li><span class="text-gray-900 font-medium">Cookie Policy</span></li>
            </ol>
        </nav>
        
        <!-- Content -->
        <div class="bg-white rounded-2xl shadow-lg p-8 md:p-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-8">Cookie Policy</h1>
            <p class="text-gray-600 mb-8">Last updated: {{ date('F d, Y') }}</p>
            
            <!-- Introduction -->
            <section class="mb-8">
                <p class="text-gray-700 leading-relaxed">
                    CD IELTS uses cookies to enhance your experience on our platform. This Cookie Policy explains what cookies are, how we use them, and your choices regarding cookies.
                </p>
            </section>
            
            <!-- What Are Cookies -->
            <section class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">What Are Cookies?</h2>
                <p class="text-gray-700 leading-relaxed">
                    Cookies are small text files that are placed on your device when you visit a website. They help websites remember information about your visit, making your online experience easier and more personalized.
                </p>
            </section>
            
            <!-- How We Use Cookies -->
            <section class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">How We Use Cookies</h2>
                <p class="text-gray-700 leading-relaxed mb-4">We use cookies for the following purposes:</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Essential Cookies</h3>
                <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4 mb-4">
                    <li>To maintain your session when you log in</li>
                    <li>To remember your preferences and settings</li>
                    <li>To ensure website security</li>
                </ul>
                
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Performance Cookies</h3>
                <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4 mb-4">
                    <li>To analyze how you use our platform</li>
                    <li>To improve website performance</li>
                    <li>To track error messages</li>
                </ul>
                
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Functionality Cookies</h3>
                <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                    <li>To remember your test progress</li>
                    <li>To save your preferences</li>
                    <li>To provide personalized features</li>
                </ul>
            </section>
            
            <!-- Managing Cookies -->
            <section class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Managing Cookies</h2>
                <p class="text-gray-700 leading-relaxed mb-4">
                    You can control and manage cookies in various ways:
                </p>
                <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                    <li>Most browsers allow you to refuse or accept cookies</li>
                    <li>You can delete cookies stored on your device</li>
                    <li>You can set your browser to notify you when cookies are sent</li>
                </ul>
                <p class="text-gray-700 leading-relaxed mt-4">
                    Please note that disabling cookies may affect the functionality of our website.
                </p>
            </section>
            
            <!-- Contact -->
            <section class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Contact Us</h2>
                <p class="text-gray-700 leading-relaxed mb-4">
                    If you have questions about our Cookie Policy, please contact us:
                </p>
                <ul class="text-gray-700 space-y-2">
                    <li><strong>Email:</strong> privacy@cdielts.com</li>
                    <li><strong>Phone:</strong> +880 1234-567890</li>
                </ul>
            </section>
        </div>
    </div>
</div>
@endsection