{{-- resources/views/student/test/onboarding/instructions.blade.php --}}
{{-- Centralized instructions page - EXACT same design as before --}}
<x-test-layout>
    <x-slot:title>Test Instructions - {{ $config['title'] }}</x-slot>

    <div class="min-h-screen bg-blue-50">

        <!-- Dark navbar with User Info -->
        <div class="bg-gray-800 py-2">
            <div class="max-w-7xl mx-auto px-4 flex items-center">
                <!-- User Info -->
                <div class="text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>{{ auth()->user()->name }} - BI {{ str_pad(auth()->id(), 6, '0', STR_PAD_LEFT) }}</span>
                </div>
            </div>
        </div>

        <div class="max-w-4xl mx-auto my-8">
            <div class="bg-white shadow-md rounded-md overflow-hidden">
                <div class="bg-black p-4 text-white">
                    <h2 class="text-xl font-medium">Test Instructions</h2>
                </div>

                <!-- No scroll container -->
                <div class="bg-gray-100">
                    <div class="p-8">
                        <h1 class="text-2xl font-bold mb-2">{{ $config['title'] }}</h1>
                        <p class="mb-6">Time: {{ $testSet->section->time_limit }} minutes</p>

                        <h2 class="text-xl font-bold mb-4">INSTRUCTIONS TO CANDIDATES</h2>
                        <ul class="list-disc pl-8 mb-6 space-y-2">
                            @foreach($config['instructions']['candidates'] as $instruction)
                                <li>{!! $instruction !!}</li>
                            @endforeach
                        </ul>

                        <h2 class="text-xl font-bold mb-4">INFORMATION FOR CANDIDATES</h2>
                        <ul class="list-disc pl-8 mb-6 space-y-2">
                            @foreach($config['instructions']['information'] as $info)
                                <li>{!! $info !!}</li>
                            @endforeach
                        </ul>

                        <div class="flex items-center justify-center text-blue-600 mb-8">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <p>When you are ready to begin, click 'Start test'.</p>
                        </div>

                        <div class="flex justify-center">
                            <button id="start-test-button"
                                    data-testset="{{ $testSet->id }}"
                                    data-section="{{ $section }}"
                                    class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-6 rounded-md border shadow-sm">
                                Start test
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const startButton = document.getElementById('start-test-button');

            startButton.addEventListener('click', function() {
                // Go to start test route
                window.location.href = "{{ route($config['routes']['start'], $testSet) }}";
            });
        });
    </script>
    @endpush
</x-test-layout>
