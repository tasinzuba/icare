<!-- Guest Footer Component -->
<footer class="bg-white/10 backdrop-blur-md border-t border-white/20 mt-20">
    <div class="container mx-auto px-4 py-16">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
            <!-- Company Info -->
            <div class="space-y-4">
                <a href="{{ route('welcome') }}" class="flex items-center">
                    @if($websiteSettings->site_logo)
                        <img src="{{ $websiteSettings->logo_url }}" alt="{{ $websiteSettings->site_title }}" class="h-12 w-auto">
                    @else
                        <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-red-600 rounded-lg flex items-center justify-center shadow-lg">
                            <span class="text-white font-bold text-xl">{{ substr($websiteSettings->site_title, 0, 1) }}</span>
                        </div>
                    @endif
                </a>
                <p class="text-gray-600 leading-relaxed">
                    Your trusted partner for IELTS preparation with comprehensive mock tests and expert guidance.
                </p>
                @if($websiteSettings->contact_email || $websiteSettings->contact_phone || $websiteSettings->address)
                <div class="space-y-2 mt-4">
                    @if($websiteSettings->contact_email)
                    <p class="text-gray-600 text-sm flex items-center">
                        <svg class="w-4 h-4 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        {{ $websiteSettings->contact_email }}
                    </p>
                    @endif
                    @if($websiteSettings->contact_phone)
                    <p class="text-gray-600 text-sm flex items-center">
                        <svg class="w-4 h-4 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        {{ $websiteSettings->contact_phone }}
                    </p>
                    @endif
                    @if($websiteSettings->address)
                    <p class="text-gray-600 text-sm flex items-start">
                        <svg class="w-4 h-4 mr-2 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        {{ $websiteSettings->address }}
                    </p>
                    @endif
                </div>
                @endif
                @if($websiteSettings->hasSocialLinks())
                <div class="flex space-x-4">
                    <!-- Social Icons -->
                    @if($websiteSettings->facebook_url)
                    <a href="{{ $websiteSettings->facebook_url }}" target="_blank" class="w-10 h-10 bg-white/50 backdrop-blur-sm rounded-lg flex items-center justify-center hover:bg-white/70 transition-all group">
                        <svg class="w-5 h-5 text-gray-600 group-hover:text-red-600 transition-colors" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </a>
                    @endif
                    @if($websiteSettings->twitter_url)
                    <a href="{{ $websiteSettings->twitter_url }}" target="_blank" class="w-10 h-10 bg-white/50 backdrop-blur-sm rounded-lg flex items-center justify-center hover:bg-white/70 transition-all group">
                        <svg class="w-5 h-5 text-gray-600 group-hover:text-red-600 transition-colors" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                        </svg>
                    </a>
                    @endif
                    @if($websiteSettings->linkedin_url)
                    <a href="{{ $websiteSettings->linkedin_url }}" target="_blank" class="w-10 h-10 bg-white/50 backdrop-blur-sm rounded-lg flex items-center justify-center hover:bg-white/70 transition-all group">
                        <svg class="w-5 h-5 text-gray-600 group-hover:text-red-600 transition-colors" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                    </a>
                    @endif
                    @if($websiteSettings->instagram_url)
                    <a href="{{ $websiteSettings->instagram_url }}" target="_blank" class="w-10 h-10 bg-white/50 backdrop-blur-sm rounded-lg flex items-center justify-center hover:bg-white/70 transition-all group">
                        <svg class="w-5 h-5 text-gray-600 group-hover:text-red-600 transition-colors" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zM5.838 12a6.162 6.162 0 1112.324 0 6.162 6.162 0 01-12.324 0zM12 16a4 4 0 110-8 4 4 0 010 8zm4.965-10.405a1.44 1.44 0 112.881.001 1.44 1.44 0 01-2.881-.001z"/>
                        </svg>
                    </a>
                    @endif
                    @if($websiteSettings->youtube_url)
                    <a href="{{ $websiteSettings->youtube_url }}" target="_blank" class="w-10 h-10 bg-white/50 backdrop-blur-sm rounded-lg flex items-center justify-center hover:bg-white/70 transition-all group">
                        <svg class="w-5 h-5 text-gray-600 group-hover:text-red-600 transition-colors" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                        </svg>
                    </a>
                    @endif
                </div>
                @endif
            </div>
            
            <!-- Quick Links -->
            <div>
                <h3 class="text-gray-800 font-semibold text-lg mb-4">Quick Links</h3>
                <ul class="space-y-3">
                    <li><a href="{{ route('about') }}" class="text-gray-600 hover:text-red-600 transition-colors">About Us</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-red-600 transition-colors">Mock Tests</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-red-600 transition-colors">Study Materials</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-red-600 transition-colors">Success Stories</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-red-600 transition-colors">Blog</a></li>
                </ul>
            </div>
            
            <!-- Support -->
            <div>
                <h3 class="text-gray-800 font-semibold text-lg mb-4">Support</h3>
                <ul class="space-y-3">
                    <li><a href="{{ route('help-center') }}" class="text-gray-600 hover:text-red-600 transition-colors">Help Center</a></li>
                    <li><a href="{{ route('contact') }}" class="text-gray-600 hover:text-red-600 transition-colors">Contact Us</a></li>
                    <li><a href="{{ route('help-center') }}" class="text-gray-600 hover:text-red-600 transition-colors">FAQ</a></li>
                    <li><a href="{{ route('privacy-policy') }}" class="text-gray-600 hover:text-red-600 transition-colors">Privacy Policy</a></li>
                    <li><a href="{{ route('terms-of-service') }}" class="text-gray-600 hover:text-red-600 transition-colors">Terms of Service</a></li>
                </ul>
            </div>
            
            <!-- Newsletter -->
            <div>
                <h3 class="text-gray-800 font-semibold text-lg mb-4">Stay Updated</h3>
                <p class="text-gray-600 mb-4">Get the latest tips and updates for IELTS preparation.</p>
                <form class="space-y-3">
                    <input 
                        type="email" 
                        placeholder="Enter your email" 
                        class="w-full px-4 py-3 bg-white/50 backdrop-blur-sm border border-gray-200 rounded-lg text-gray-700 placeholder-gray-400 focus:outline-none focus:border-red-500 focus:bg-white/70 transition-all"
                    >
                    <button 
                        type="submit"
                        class="w-full px-4 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg hover:shadow-lg transform hover:-translate-y-0.5 transition-all font-medium"
                    >
                        Subscribe
                    </button>
                </form>
                <p class="text-gray-500 text-sm mt-3">We respect your privacy. Unsubscribe anytime.</p>
            </div>
        </div>
        
        <!-- Bottom Bar -->
        <div class="pt-8 border-t border-gray-200">
            <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                <div class="text-center md:text-left">
                    <p class="text-gray-600">{{ $websiteSettings->copyright_text }}</p>
                    @if($websiteSettings->footer_text)
                        <p class="text-gray-500 text-sm mt-1">{{ $websiteSettings->footer_text }}</p>
                    @endif
                </div>
                <div class="flex flex-wrap justify-center gap-4 text-sm">
                    <a href="{{ route('privacy-policy') }}" class="text-gray-600 hover:text-red-600 transition-colors">Privacy</a>
                    <span class="text-gray-400">•</span>
                    <a href="{{ route('terms-of-service') }}" class="text-gray-600 hover:text-red-600 transition-colors">Terms</a>
                    <span class="text-gray-400">•</span>
                    <a href="#" class="text-gray-600 hover:text-red-600 transition-colors">Cookies</a>
                    <span class="text-gray-400">•</span>
                    <a href="#" class="text-gray-600 hover:text-red-600 transition-colors">Sitemap</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
    /* Additional footer styles */
    footer {
        box-shadow: 0 -1px 3px rgba(0, 0, 0, 0.05);
    }
</style>
