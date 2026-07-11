<x-guest-layout>
    <x-slot name="title">Help Center - Your Questions Answered</x-slot>
    
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-b from-white to-gray-50 py-20 md:py-28">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-5">
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23dc2626" fill-opacity="0.4"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        </div>
        
        <div class="container mx-auto px-6 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                
                
                <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">
                    How Can We
                    <span class="relative inline-block">
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-red-600">Help You?</span>
                        <svg class="absolute -bottom-2 left-0 w-full" height="10" viewBox="0 0 200 10">
                            <path d="M0,8 Q50,0 100,8 T200,8" stroke="#ef4444" stroke-width="3" fill="none" opacity="0.6"/>
                        </svg>
                    </span>
                </h1>
                
                <p class="text-lg md:text-xl text-gray-700 mb-8 font-medium">
                    Comprehensive answers to your most common questions about using our platform
                </p>
                
                <!-- Search Bar -->
                <div class="max-w-2xl mx-auto">
                    <div class="relative">
                        <input type="text" id="helpSearch"
                            class="w-full px-6 py-4 pr-14 rounded-2xl border-2 border-gray-200 focus:border-red-500 focus:outline-none text-lg shadow-lg"
                            placeholder="Search for answers...">
                        <button class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-red-500 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bottom Wave -->
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0,96L48,80C96,64,192,32,288,26.7C384,21,480,43,576,48C672,53,768,43,864,42.7C960,43,1056,53,1152,58.7C1248,64,1344,64,1392,64L1440,64L1440,120L1392,120C1344,120,1248,120,1152,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z" fill="#f9fafb"/>
            </svg>
        </div>
    </section>

    <!-- Quick Access Categories -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-12">
                  
                    <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">Browse by Category</h2>
                    <p class="text-lg text-gray-600">Select a topic to find relevant information</p>
                </div>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Category 1 -->
                    <a href="#platform-basics" class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 border-2 border-transparent hover:border-blue-200">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 mb-2 group-hover:text-red-600 transition-colors text-lg">Platform Basics</h3>
                                <p class="text-sm text-gray-600">Learn how to navigate and use essential features</p>
                            </div>
                        </div>
                    </a>
                    
                    <!-- Category 2 -->
                    <a href="#test-procedures" class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 border-2 border-transparent hover:border-green-200">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 mb-2 group-hover:text-red-600 transition-colors text-lg">Test Procedures</h3>
                                <p class="text-sm text-gray-600">Understanding how to complete mock examinations</p>
                            </div>
                        </div>
                    </a>
                    
                    <!-- Category 3 -->
                    <a href="#evaluation-system" class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 border-2 border-transparent hover:border-purple-200">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 mb-2 group-hover:text-red-600 transition-colors text-lg">Evaluation System</h3>
                                <p class="text-sm text-gray-600">How scoring and feedback mechanisms work</p>
                            </div>
                        </div>
                    </a>
                    
                    <!-- Category 4 -->
                    <a href="#technical-support" class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 border-2 border-transparent hover:border-orange-200">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 mb-2 group-hover:text-red-600 transition-colors text-lg">Technical Support</h3>
                                <p class="text-sm text-gray-600">Troubleshooting common technical issues</p>
                            </div>
                        </div>
                    </a>
                    
                    <!-- Category 5 -->
                    <a href="#account-management" class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 border-2 border-transparent hover:border-pink-200">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-pink-500 to-pink-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 mb-2 group-hover:text-red-600 transition-colors text-lg">Account Management</h3>
                                <p class="text-sm text-gray-600">Profile settings and preferences</p>
                            </div>
                        </div>
                    </a>
                    
                    <!-- Category 6 -->
                    <a href="#best-practices" class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 border-2 border-transparent hover:border-yellow-200">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 mb-2 group-hover:text-red-600 transition-colors text-lg">Best Practices</h3>
                                <p class="text-sm text-gray-600">Tips for maximizing your preparation</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Sections -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="max-w-5xl mx-auto">
                <!-- Platform Basics -->
                <div id="platform-basics" class="mb-16">
                    <div class="flex items-center mb-8">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900">Platform Basics</h2>
                    </div>
                    
                    <div class="space-y-4">
                        <details class="group bg-gray-50 rounded-xl p-6 hover:shadow-lg transition-all cursor-pointer">
                            <summary class="font-bold text-gray-900 flex justify-between items-center">
                                <span>How do I create a new account?</span>
                                <svg class="w-5 h-5 transform group-open:rotate-180 transition-transform text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <p class="text-gray-600 mt-4 leading-relaxed border-t border-gray-200 pt-4">
                                Navigate to the registration page by clicking the "Start Now" or "Register" button. Complete the required fields including your name, email address, and password. Verify your email address through the confirmation link sent to your inbox. Your account will be activated immediately upon verification.
                            </p>
                        </details>
                        
                        <details class="group bg-gray-50 rounded-xl p-6 hover:shadow-lg transition-all cursor-pointer">
                            <summary class="font-bold text-gray-900 flex justify-between items-center">
                                <span>Is the platform genuinely free?</span>
                                <svg class="w-5 h-5 transform group-open:rotate-180 transition-transform text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <p class="text-gray-600 mt-4 leading-relaxed border-t border-gray-200 pt-4">
                                Absolutely. All mock tests, AI evaluations, and core platform features remain completely free with no hidden charges. While we offer optional premium services such as human evaluations from certified teachers, the fundamental platform remains accessible to everyone at zero cost.
                            </p>
                        </details>
                        
                        <details class="group bg-gray-50 rounded-xl p-6 hover:shadow-lg transition-all cursor-pointer">
                            <summary class="font-bold text-gray-900 flex justify-between items-center">
                                <span>What test modules are available?</span>
                                <svg class="w-5 h-5 transform group-open:rotate-180 transition-transform text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <p class="text-gray-600 mt-4 leading-relaxed border-t border-gray-200 pt-4">
                                We provide comprehensive coverage of all four IELTS modules: Listening, Reading, Writing, and Speaking. Each module contains extensive practice materials following official IELTS formats for both Academic and General Training versions.
                            </p>
                        </details>
                    </div>
                </div>

                <!-- Test Procedures -->
                <div id="test-procedures" class="mb-16">
                    <div class="flex items-center mb-8">
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900">Test Procedures</h2>
                    </div>
                    
                    <div class="space-y-4">
                        <details class="group bg-gray-50 rounded-xl p-6 hover:shadow-lg transition-all cursor-pointer">
                            <summary class="font-bold text-gray-900 flex justify-between items-center">
                                <span>How do I initiate a practice test?</span>
                                <svg class="w-5 h-5 transform group-open:rotate-180 transition-transform text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <p class="text-gray-600 mt-4 leading-relaxed border-t border-gray-200 pt-4">
                                Access your dashboard, select your desired test module (Listening, Reading, Writing, or Speaking), choose a specific test from the available options, and click "Start Test". Follow the on-screen instructions and complete the test within the allocated time limit.
                            </p>
                        </details>
                        
                        <details class="group bg-gray-50 rounded-xl p-6 hover:shadow-lg transition-all cursor-pointer">
                            <summary class="font-bold text-gray-900 flex justify-between items-center">
                                <span>Can I pause and resume tests later?</span>
                                <svg class="w-5 h-5 transform group-open:rotate-180 transition-transform text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <p class="text-gray-600 mt-4 leading-relaxed border-t border-gray-200 pt-4">
                                To maintain authentic examination conditions, tests must be completed in a single session. However, you may retake any test multiple times to practice and improve your performance without restrictions.
                            </p>
                        </details>
                        
                        <details class="group bg-gray-50 rounded-xl p-6 hover:shadow-lg transition-all cursor-pointer">
                            <summary class="font-bold text-gray-900 flex justify-between items-center">
                                <span>What technical requirements exist for Speaking tests?</span>
                                <svg class="w-5 h-5 transform group-open:rotate-180 transition-transform text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <p class="text-gray-600 mt-4 leading-relaxed border-t border-gray-200 pt-4">
                                A functional microphone is essential for recording your responses. Most modern laptops and smartphones feature built-in microphones. Ensure you're in a quiet environment and grant your browser permission to access the microphone when prompted.
                            </p>
                        </details>
                    </div>
                </div>

                <!-- Evaluation System -->
                <div id="evaluation-system" class="mb-16">
                    <div class="flex items-center mb-8">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900">Evaluation System</h2>
                    </div>
                    
                    <div class="space-y-4">
                        <details class="group bg-gray-50 rounded-xl p-6 hover:shadow-lg transition-all cursor-pointer">
                            <summary class="font-bold text-gray-900 flex justify-between items-center">
                                <span>How are scores calculated?</span>
                                <svg class="w-5 h-5 transform group-open:rotate-180 transition-transform text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <p class="text-gray-600 mt-4 leading-relaxed border-t border-gray-200 pt-4">
                                Listening and Reading modules are scored automatically based on correct answers. Writing and Speaking utilize our advanced AI evaluation system, which analyzes grammar, vocabulary, coherence, fluency, and task achievement according to official IELTS criteria.
                            </p>
                        </details>
                        
                        <details class="group bg-gray-50 rounded-xl p-6 hover:shadow-lg transition-all cursor-pointer">
                            <summary class="font-bold text-gray-900 flex justify-between items-center">
                                <span>How reliable is the AI assessment?</span>
                                <svg class="w-5 h-5 transform group-open:rotate-180 transition-transform text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <p class="text-gray-600 mt-4 leading-relaxed border-t border-gray-200 pt-4">
                                Our AI has been trained on thousands of actual IELTS responses and typically provides scores within 0.5 band of human evaluators. For the highest accuracy, consider our premium human evaluation service from certified IELTS examiners.
                            </p>
                        </details>
                        
                        <details class="group bg-gray-50 rounded-xl p-6 hover:shadow-lg transition-all cursor-pointer">
                            <summary class="font-bold text-gray-900 flex justify-between items-center">
                                <span>Can I access detailed feedback?</span>
                                <svg class="w-5 h-5 transform group-open:rotate-180 transition-transform text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <p class="text-gray-600 mt-4 leading-relaxed border-t border-gray-200 pt-4">
                                Yes. Upon test completion, you receive comprehensive feedback including correct answers, detailed explanations, analysis of your strengths and weaknesses, and specific recommendations for improvement.
                            </p>
                        </details>
                    </div>
                </div>

                <!-- Technical Support -->
                <div id="technical-support" class="mb-16">
                    <div class="flex items-center mb-8">
                        <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900">Technical Support</h2>
                    </div>
                    
                    <div class="space-y-4">
                        <details class="group bg-gray-50 rounded-xl p-6 hover:shadow-lg transition-all cursor-pointer">
                            <summary class="font-bold text-gray-900 flex justify-between items-center">
                                <span>Audio not playing in Listening tests</span>
                                <svg class="w-5 h-5 transform group-open:rotate-180 transition-transform text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <p class="text-gray-600 mt-4 leading-relaxed border-t border-gray-200 pt-4">
                                Verify your device volume settings and ensure your browser has permission to play audio. Try refreshing the page or using an alternative browser (Chrome or Firefox recommended). Check your internet connection stability.
                            </p>
                        </details>
                        
                        <details class="group bg-gray-50 rounded-xl p-6 hover:shadow-lg transition-all cursor-pointer">
                            <summary class="font-bold text-gray-900 flex justify-between items-center">
                                <span>Microphone not functioning for Speaking tests</span>
                                <svg class="w-5 h-5 transform group-open:rotate-180 transition-transform text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <p class="text-gray-600 mt-4 leading-relaxed border-t border-gray-200 pt-4">
                                Confirm you've granted browser permission to access your microphone. Check system settings to verify the correct microphone is selected. Consider using headphones with an integrated microphone for improved reliability.
                            </p>
                        </details>
                        
                        <details class="group bg-gray-50 rounded-xl p-6 hover:shadow-lg transition-all cursor-pointer">
                            <summary class="font-bold text-gray-900 flex justify-between items-center">
                                <span>Platform loading slowly</span>
                                <svg class="w-5 h-5 transform group-open:rotate-180 transition-transform text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <p class="text-gray-600 mt-4 leading-relaxed border-t border-gray-200 pt-4">
                                Verify your internet connection speed. Clear your browser cache and cookies. If issues persist, try accessing during off-peak hours or contact our support team for assistance.
                            </p>
                        </details>
                    </div>
                </div>

                <!-- Account Management -->
                <div id="account-management" class="mb-16">
                    <div class="flex items-center mb-8">
                        <div class="w-12 h-12 bg-pink-100 rounded-xl flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900">Account Management</h2>
                    </div>
                    
                    <div class="space-y-4">
                        <details class="group bg-gray-50 rounded-xl p-6 hover:shadow-lg transition-all cursor-pointer">
                            <summary class="font-bold text-gray-900 flex justify-between items-center">
                                <span>How do I reset my password?</span>
                                <svg class="w-5 h-5 transform group-open:rotate-180 transition-transform text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <p class="text-gray-600 mt-4 leading-relaxed border-t border-gray-200 pt-4">
                                Click "Forgot Password?" on the login page, enter your registered email address, and follow the instructions sent to your inbox to create a new password securely.
                            </p>
                        </details>
                        
                        <details class="group bg-gray-50 rounded-xl p-6 hover:shadow-lg transition-all cursor-pointer">
                            <summary class="font-bold text-gray-900 flex justify-between items-center">
                                <span>Can I delete my account?</span>
                                <svg class="w-5 h-5 transform group-open:rotate-180 transition-transform text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <p class="text-gray-600 mt-4 leading-relaxed border-t border-gray-200 pt-4">
                                Yes. Navigate to Settings, then Account, and select Delete Account. Note that this action is permanent and will permanently remove all your test history and progress data.
                            </p>
                        </details>
                    </div>
                </div>

                <!-- Best Practices -->
                <div id="best-practices">
                    <div class="flex items-center mb-8">
                        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900">Best Practices</h2>
                    </div>
                    
                    <div class="space-y-4">
                        <details class="group bg-gray-50 rounded-xl p-6 hover:shadow-lg transition-all cursor-pointer">
                            <summary class="font-bold text-gray-900 flex justify-between items-center">
                                <span>How often should I practice?</span>
                                <svg class="w-5 h-5 transform group-open:rotate-180 transition-transform text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <p class="text-gray-600 mt-4 leading-relaxed border-t border-gray-200 pt-4">
                                Consistent daily practice yields optimal results. Aim for at least one complete test section daily, gradually increasing intensity as your exam date approaches. Focus on weaker areas while maintaining strength in proficient modules.
                            </p>
                        </details>
                        
                        <details class="group bg-gray-50 rounded-xl p-6 hover:shadow-lg transition-all cursor-pointer">
                            <summary class="font-bold text-gray-900 flex justify-between items-center">
                                <span>What's the most effective preparation strategy?</span>
                                <svg class="w-5 h-5 transform group-open:rotate-180 transition-transform text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <p class="text-gray-600 mt-4 leading-relaxed border-t border-gray-200 pt-4">
                                Begin with a diagnostic test to identify your baseline. Create a structured study plan targeting weak areas. Practice under timed conditions to build stamina. Carefully review all feedback and implement suggested improvements. Track your progress regularly and adjust your strategy accordingly.
                            </p>
                        </details>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Need More Help -->
    <section class="py-20 bg-gradient-to-br from-red-500 to-red-600">
        <div class="container mx-auto px-6">
            <div class="max-w-3xl mx-auto text-center text-white">
                <div class="w-16 h-16 bg-white/20 backdrop-blur rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                
                <h2 class="text-3xl md:text-4xl font-extrabold mb-4">Still Need Assistance?</h2>
                <p class="text-xl mb-8 text-red-50">Our support team is ready to help you succeed</p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('contact') }}" 
                        class="inline-flex items-center justify-center px-8 py-4 bg-white text-red-600 font-bold rounded-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Contact Support
                    </a>
                </div>
            </div>
        </div>
    </section>

    <x-slot name="scripts">
        <script>
            // Search functionality
            document.getElementById('helpSearch').addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                const allDetails = document.querySelectorAll('details');
                
                allDetails.forEach(detail => {
                    const content = detail.textContent.toLowerCase();
                    const parent = detail.closest('div[id]');
                    
                    if (content.includes(searchTerm) || searchTerm === '') {
                        detail.style.display = 'block';
                        if (parent) parent.style.display = 'block';
                    } else {
                        detail.style.display = 'none';
                    }
                });
                
                // Hide category sections with no visible details
                document.querySelectorAll('section > div > div > div[id]').forEach(section => {
                    const visibleDetails = section.querySelectorAll('details[style="display: block"]');
                    section.style.display = visibleDetails.length > 0 || searchTerm === '' ? 'block' : 'none';
                });
            });
            
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });
        </script>
    </x-slot>
</x-guest-layout>
