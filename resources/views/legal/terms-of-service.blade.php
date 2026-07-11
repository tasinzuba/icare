<x-guest-layout>
    <x-slot name="title">Terms of Service</x-slot>
    
    <div class="min-h-screen bg-gradient-to-b from-white to-gray-50 py-12">
        <div class="container mx-auto px-6 max-w-4xl">
            <!-- Breadcrumb -->
            <nav class="mb-8">
                <ol class="flex items-center space-x-2 text-sm">
                    <li><a href="{{ route('welcome') }}" class="text-gray-500 hover:text-red-500 transition-colors">Home</a></li>
                    <li><span class="text-gray-500">/</span></li>
                    <li><span class="text-gray-900 font-medium">Terms of Service</span></li>
                </ol>
            </nav>
            
            <!-- Content -->
            <div class="bg-white rounded-2xl shadow-xl p-8 md:p-12">
                <h1 class="text-4xl font-bold text-gray-900 mb-8">Terms of Service</h1>
                <p class="text-gray-600 mb-8">Effective Date: {{ date('F d, Y') }}</p>
                
                <!-- Introduction -->
                <section class="mb-8">
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Welcome to {{ $websiteSettings->site_title }}. These Terms of Service ("Terms") govern your use of our IELTS preparation platform, including our website, mobile applications, and all related services.
                    </p>
                    <p class="text-gray-700 leading-relaxed">
                        By accessing or using our services, you agree to be bound by these Terms. If you disagree with any part of these terms, then you may not access our services.
                    </p>
                </section>
                
                <!-- Account Registration -->
                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">1. Account Registration</h2>
                    <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                        <li>You must be at least 13 years old to use our services</li>
                        <li>You must provide accurate and complete registration information</li>
                        <li>You are responsible for maintaining the security of your account</li>
                        <li>You are responsible for all activities under your account</li>
                        <li>You must notify us immediately of any unauthorized use</li>
                    </ul>
                </section>
                
                <!-- Use of Services -->
                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">2. Use of Services</h2>
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">You agree to:</h3>
                    <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4 mb-4">
                        <li>Use our services only for lawful purposes</li>
                        <li>Not share your account with others</li>
                        <li>Not attempt to copy or redistribute our content</li>
                        <li>Not interfere with the proper working of our platform</li>
                        <li>Not use automated systems to access our services</li>
                    </ul>
                    
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">You agree NOT to:</h3>
                    <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                        <li>Violate any laws or regulations</li>
                        <li>Infringe on intellectual property rights</li>
                        <li>Transmit malware or harmful code</li>
                        <li>Harass or harm other users</li>
                        <li>Create multiple accounts for fraudulent purposes</li>
                    </ul>
                </section>
                
                <!-- Subscription and Payment -->
                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">3. Subscription and Payment</h2>
                    <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                        <li>Some services require paid subscription</li>
                        <li>Subscription fees are billed in advance</li>
                        <li>All payments are non-refundable unless stated otherwise</li>
                        <li>You authorize us to charge your payment method</li>
                        <li>Prices may change with 30 days notice</li>
                        <li>You can cancel your subscription at any time</li>
                    </ul>
                </section>
                
                <!-- Intellectual Property -->
                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">4. Intellectual Property</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        All content on {{ $websiteSettings->site_title }}, including text, graphics, logos, images, audio clips, and software, is the property of {{ $websiteSettings->site_title }} or its content suppliers and is protected by international copyright laws.
                    </p>
                    <p class="text-gray-700 leading-relaxed">
                        You may not reproduce, distribute, modify, or create derivative works without our express written permission.
                    </p>
                </section>
                
                <!-- User Content -->
                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">5. User Content</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        By submitting content to our platform (including test responses, forum posts, and feedback), you grant us a non-exclusive, worldwide, royalty-free license to use, reproduce, and modify such content for the purpose of providing and improving our services.
                    </p>
                    <p class="text-gray-700 leading-relaxed">
                        You represent that you own or have the necessary rights to the content you submit.
                    </p>
                </section>
                
                <!-- Disclaimer -->
                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">6. Disclaimer</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Our services are provided "as is" without warranties of any kind, either express or implied. We do not guarantee:
                    </p>
                    <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                        <li>Specific test scores or results</li>
                        <li>Uninterrupted or error-free service</li>
                        <li>Accuracy of AI evaluations</li>
                        <li>Compatibility with all devices</li>
                    </ul>
                </section>
                
                <!-- Limitation of Liability -->
                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">7. Limitation of Liability</h2>
                    <p class="text-gray-700 leading-relaxed">
                        {{ $websiteSettings->site_title }} shall not be liable for any indirect, incidental, special, consequential, or punitive damages resulting from your use or inability to use our services, even if we have been advised of the possibility of such damages.
                    </p>
                </section>
                
                <!-- Termination -->
                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">8. Termination</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        We may terminate or suspend your account immediately, without prior notice, for any reason, including:
                    </p>
                    <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                        <li>Breach of these Terms</li>
                        <li>Fraudulent activities</li>
                        <li>Request by law enforcement</li>
                        <li>Extended period of inactivity</li>
                        <li>Non-payment of fees</li>
                    </ul>
                </section>
                
                <!-- Governing Law -->
                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">9. Governing Law</h2>
                    <p class="text-gray-700 leading-relaxed">
                        These Terms shall be governed by and construed in accordance with the laws of Bangladesh, without regard to its conflict of law provisions. Any legal action or proceeding shall be brought exclusively in the courts of Dhaka, Bangladesh.
                    </p>
                </section>
                
                <!-- Changes to Terms -->
                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">10. Changes to Terms</h2>
                    <p class="text-gray-700 leading-relaxed">
                        We reserve the right to modify these Terms at any time. If we make material changes, we will notify you by email or by posting a notice on our website. Your continued use of our services after changes constitutes acceptance of the modified Terms.
                    </p>
                </section>
                
                <!-- Contact -->
                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">11. Contact Information</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        If you have any questions about these Terms, please contact us:
                    </p>
                    <ul class="text-gray-700 space-y-2">
                        @if($websiteSettings->contact_email)
                        <li><strong>Email:</strong> {{ $websiteSettings->contact_email }}</li>
                        @endif
                        @if($websiteSettings->contact_phone)
                        <li><strong>Phone:</strong> {{ $websiteSettings->contact_phone }}</li>
                        @endif
                        @if($websiteSettings->address)
                        <li><strong>Address:</strong> {{ $websiteSettings->address }}</li>
                        @endif
                    </ul>
                </section>
                
                <!-- Agreement Section -->
                <section class="mb-8 bg-gray-50 p-6 rounded-xl">
                    <h2 class="text-lg font-bold text-gray-900 mb-3">By using our services, you acknowledge that:</h2>
                    <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                        <li>You have read and understood these Terms of Service</li>
                        <li>You agree to be bound by these Terms</li>
                        <li>You are at least 13 years of age</li>
                        <li>You have the legal capacity to enter into this agreement</li>
                    </ul>
                </section>
                
                <!-- Back to Home Button -->
                <div class="mt-12 text-center">
                    <a href="{{ route('welcome') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg hover:shadow-lg transform hover:-translate-y-0.5 transition-all font-medium">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        /* Custom scrollbar for content */
        .prose::-webkit-scrollbar {
            width: 6px;
        }
        .prose::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        .prose::-webkit-scrollbar-thumb {
            background: #ef4444;
            border-radius: 3px;
        }
    </style>
</x-guest-layout>
