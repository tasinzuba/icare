<x-guest-layout>
    <x-slot name="title">Contact Us - Get in Touch</x-slot>
    
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-b from-white to-gray-50 py-16 md:py-20">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-5">
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23dc2626" fill-opacity="0.4"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        </div>
        
        <div class="container mx-auto px-6 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
               
                
                <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">
                    Get in
                    <span class="relative inline-block">
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-red-600">Touch</span>
                        <svg class="absolute -bottom-2 left-0 w-full" height="10" viewBox="0 0 200 10">
                            <path d="M0,8 Q50,0 100,8 T200,8" stroke="#ef4444" stroke-width="3" fill="none" opacity="0.6"/>
                        </svg>
                    </span>
                </h1>
                
                <p class="text-lg md:text-xl text-gray-700 mb-4 font-medium">
                    Have questions? We're here to help! Our support team typically responds within 24 hours.
                </p>
            </div>
        </div>
        
        <!-- Bottom Wave -->
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0,96L48,80C96,64,192,32,288,26.7C384,21,480,43,576,48C672,53,768,43,864,42.7C960,43,1056,53,1152,58.7C1248,64,1344,64,1392,64L1440,64L1440,120L1392,120C1344,120,1248,120,1152,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z" fill="#f9fafb"/>
            </svg>
        </div>
    </section>

    <!-- Contact Methods -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="max-w-6xl mx-auto">
                <div class="grid md:grid-cols-3 gap-6 mb-16">
                    <!-- Email -->
                    <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 text-center group">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mb-4 mx-auto group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Email Us</h3>
                        <p class="text-gray-600 mb-4">We'll respond within 24 hours</p>
                        <a href="mailto:support@cdielts.org" class="text-blue-600 font-semibold hover:text-blue-700">
                            support@cdielts.org
                        </a>
                    </div>
                    
                    <!-- Live Chat -->
                    <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 text-center group">
                        <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center mb-4 mx-auto group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Live Chat</h3>
                        <p class="text-gray-600 mb-4">Chat with our support team</p>
                        <button onclick="startChat()" class="text-green-600 font-semibold hover:text-green-700">
                            Start Chat Now
                        </button>
                    </div>
                    
                    <!-- Community -->
                    <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 text-center group">
                        <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mb-4 mx-auto group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Community</h3>
                        <p class="text-gray-600 mb-4">Join our learning community</p>
                        <button onclick="joinCommunity()" class="text-purple-600 font-semibold hover:text-purple-700">
                            Join Now
                        </button>
                    </div>
                </div>

                <!-- Contact Form & Info -->
                <div class="grid lg:grid-cols-2 gap-8">
                    <!-- Contact Form -->
                    <div class="bg-white rounded-2xl p-8 shadow-lg">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Send Us a Message</h2>
                        <form id="contactForm" class="space-y-6">
                            <!-- Name -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" required
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-red-500 focus:outline-none transition-colors"
                                    placeholder="Enter your full name">
                            </div>
                            
                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input type="email" required
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-red-500 focus:outline-none transition-colors"
                                    placeholder="your.email@example.com">
                            </div>
                            
                            <!-- Subject -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Subject <span class="text-red-500">*</span>
                                </label>
                                <select required
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-red-500 focus:outline-none transition-colors">
                                    <option value="">Choose a subject</option>
                                    <option>General Inquiry</option>
                                    <option>Technical Support</option>
                                    <option>Test Feedback</option>
                                    <option>Account Issue</option>
                                    <option>Feature Request</option>
                                    <option>Partnership</option>
                                </select>
                            </div>
                            
                            <!-- Message -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Message <span class="text-red-500">*</span>
                                </label>
                                <textarea required rows="5"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-red-500 focus:outline-none transition-colors resize-none"
                                    placeholder="Tell us how we can help you..."></textarea>
                            </div>
                            
                            <!-- Submit Button -->
                            <button type="submit"
                                class="w-full bg-gradient-to-r from-red-500 to-red-600 text-white font-bold py-4 rounded-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-200">
                                Send Message
                                <i class="fas fa-paper-plane ml-2"></i>
                            </button>
                        </form>
                    </div>
                    
                    <!-- Contact Info -->
                    <div class="space-y-6">
                        <!-- Contact Info Box -->
                        <div class="bg-white rounded-2xl p-8 shadow-lg">
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">Get In Touch</h2>
                            
                            <div class="space-y-4">
                                <!-- Email -->
                                <div class="flex items-start space-x-4">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-900 mb-1">Email</h4>
                                        <p class="text-gray-600">support@cdielts.org</p>
                                    </div>
                                </div>
                                
                                <!-- Response Time -->
                                <div class="flex items-start space-x-4">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-900 mb-1">Response Time</h4>
                                        <p class="text-gray-600">We typically respond within 24 hours</p>
                                    </div>
                                </div>
                                
                                <!-- Support -->
                                <div class="flex items-start space-x-4">
                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-900 mb-1">Support Available</h4>
                                        <p class="text-gray-600">7 days a week for all your queries</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-6">
            <div class="max-w-4xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">Frequently Asked Questions</h2>
                    <p class="text-lg text-gray-600">Quick answers to common questions</p>
                </div>
                
                <div class="space-y-4">
                    <!-- FAQ Item 1 -->
                    <div class="bg-gray-50 rounded-2xl p-6 hover:shadow-lg transition-all">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">How quickly will I receive a response?</h3>
                        <p class="text-gray-600">We aim to respond to all inquiries within 24 hours during business days. For urgent matters, please use our live chat feature.</p>
                    </div>
                    
                    <!-- FAQ Item 2 -->
                    <div class="bg-gray-50 rounded-2xl p-6 hover:shadow-lg transition-all">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Can I schedule a demo or consultation?</h3>
                        <p class="text-gray-600">Yes! Contact us through the form above and select "General Inquiry" to schedule a personalized demo or consultation session.</p>
                    </div>
                    
                    <!-- FAQ Item 3 -->
                    <div class="bg-gray-50 rounded-2xl p-6 hover:shadow-lg transition-all">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Do you offer institutional partnerships?</h3>
                        <p class="text-gray-600">Absolutely! We work with educational institutions worldwide. Select "Partnership" in the contact form to discuss custom solutions.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <x-slot name="scripts">
        <script>
            // Contact Form Submission
            document.getElementById('contactForm').addEventListener('submit', function(e) {
                e.preventDefault();
                alert('Thank you for your message! We will get back to you within 24 hours.');
                this.reset();
            });
            
            // Start Chat Function
            function startChat() {
                alert('Live chat feature coming soon! For immediate assistance, please email us at support@cdielts.org');
            }
            
            // Join Community Function
            function joinCommunity() {
                alert('Community feature coming soon! Stay tuned for updates.');
            }
        </script>
    </x-slot>
</x-guest-layout>
