<x-guest-layout>
    <x-slot name="title">Pricing - IELTS Preparation Plans</x-slot>

    <x-slot name="head">
        <meta name="description" content="Affordable IELTS preparation plans. Start free, upgrade when ready.">
        <style>
            .plan-card {
                transition: all 0.2s ease;
            }
            .plan-card:hover {
                transform: translateY(-4px);
            }
        </style>
    </x-slot>

    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-b from-white to-gray-50 pt-24 pb-6">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-5">
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23dc2626&quot; fill-opacity=&quot;0.4&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        </div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-gray-900 mb-6 tracking-tight leading-tight">
                    Invest in Your
                    <span class="relative inline-block">
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-red-600">IELTS Success</span>
                        <svg class="absolute -bottom-2 left-0 w-full" height="10" viewBox="0 0 200 10">
                            <path d="M0,8 Q50,0 100,8 T200,8" stroke="#ef4444" stroke-width="3" fill="none" opacity="0.6"/>
                        </svg>
                    </span>
                </h1>

                <p class="text-lg md:text-xl text-gray-600 font-medium max-w-2xl mx-auto">
                    Choose the plan that fits your preparation journey. Every plan includes world-class preparation materials.
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

    <!-- Pricing Cards -->
    <section class="py-6 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="max-w-5xl mx-auto">
                @if($plans->isNotEmpty())
                    <div class="flex flex-col md:flex-row justify-center gap-6">
                        @foreach($plans as $index => $plan)
                            @php
                                $isFeatured = $plan->is_featured;
                            @endphp

                            <div class="plan-card bg-white rounded-2xl p-6 shadow-sm border {{ $isFeatured ? 'border-red-200' : 'border-gray-100' }} relative w-full md:w-72">
                                @if($isFeatured)
                                    <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                                        <span class="inline-block px-3 py-1 bg-[#C8102E] text-white text-xs font-bold rounded-full">
                                            Most Popular
                                        </span>
                                    </div>
                                @endif

                                <!-- Plan Name -->
                                <h3 class="text-xl font-bold text-gray-900 mb-1 text-center {{ $isFeatured ? 'mt-2' : '' }}">{{ $plan->name }}</h3>
                                <p class="text-xs text-gray-500 text-center mb-5">{{ $plan->description }}</p>

                                <!-- Price -->
                                <div class="text-center mb-5">
                                    <div class="flex items-start justify-center">
                                        <span class="text-xl font-bold text-gray-400 mt-1">৳</span>
                                        <span class="text-4xl font-extrabold text-gray-900">{{ $plan->is_free ? '0' : number_format($plan->current_price, 0) }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        @if($plan->is_free)
                                            Forever free
                                        @else
                                            {{ $plan->duration_days == 30 ? 'per month' : ($plan->duration_days == 365 ? 'per year' : 'for '.$plan->duration_days.' days') }}
                                        @endif
                                    </p>
                                </div>

                                <!-- CTA Button -->
                                <a href="{{ route('register') }}"
                                   class="block w-full py-3 text-center font-semibold rounded-lg transition-all mb-5 {{ $isFeatured ? 'bg-[#C8102E] text-white hover:bg-[#a50d26]' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }}">
                                    {{ $plan->is_free ? 'Start Free' : 'Get Started' }}
                                </a>

                                <!-- Features -->
                                @if($plan->relationLoaded('features') && $plan->features->count() > 0)
                                    <div class="space-y-2 pt-5 border-t border-gray-100">
                                        @foreach($plan->features->take(5) as $feature)
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span class="text-sm text-gray-600">
                                                    {{ $feature->name }}
                                                    @if($feature->pivot && $feature->pivot->value && !in_array($feature->pivot->value, ['true', '1']))
                                                        <span class="font-medium text-gray-900">({{ $feature->pivot->value }})</span>
                                                    @endif
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-20">
                        <p class="text-gray-400">No plans available</p>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- Why Upgrade Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">Why Upgrade to Premium?</h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">Unlock powerful features that accelerate your IELTS preparation</p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Feature 1 -->
                    <div class="group text-center">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mb-6 mx-auto group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Unlimited Mock Tests</h3>
                        <p class="text-gray-600">Practice without limits. Access all 100+ tests anytime, anywhere.</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="group text-center">
                        <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mb-6 mx-auto group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">AI + Human Review</h3>
                        <p class="text-gray-600">Get instant AI feedback plus expert human evaluation for Writing & Speaking.</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="group text-center">
                        <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center mb-6 mx-auto group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Detailed Analytics</h3>
                        <p class="text-gray-600">Track your progress with comprehensive performance insights.</p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="group text-center">
                        <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl flex items-center justify-center mb-6 mx-auto group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Priority Support</h3>
                        <p class="text-gray-600">Get faster responses and dedicated assistance when you need help.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="py-20 bg-gradient-to-br from-red-500 to-red-600 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.4&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        </div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-4xl md:text-5xl font-extrabold text-white mb-6">Start Your IELTS Journey Today</h2>
                <p class="text-xl text-red-100 mb-10 leading-relaxed">Join thousands of successful students. No credit card required to start.</p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}"
                        class="inline-flex items-center justify-center px-8 py-4 bg-white text-red-600 font-bold text-lg rounded-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Start Free Now
                    </a>

                    <a href="{{ route('welcome') }}"
                        class="inline-flex items-center justify-center px-8 py-4 bg-white/10 backdrop-blur border-2 border-white/30 text-white font-bold text-lg rounded-xl hover:bg-white/20 transition-all">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Learn More
                    </a>
                </div>
            </div>
        </div>
    </section>

</x-guest-layout>
