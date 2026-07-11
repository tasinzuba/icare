<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $fullTest->title }} - Test Instructions</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-2xl">
        <!-- Logo -->
        <div class="text-center mb-8">
            @php
                $websiteSetting = \App\Models\WebsiteSetting::first();
                $logoUrl = $websiteSetting && $websiteSetting->logo_url ? $websiteSetting->logo_url : null;
            @endphp
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo" class="h-8 mx-auto">
            @else
                <span class="text-xl font-bold text-[#C8102E]">CD IELTS</span>
            @endif
        </div>

        <!-- Main Card -->
        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-5 border-b border-gray-100 flex items-start justify-between">
                <div>
                    <h1 class="text-lg font-semibold text-gray-900">Test Instructions</h1>
                    <p class="text-sm text-gray-500 mt-1">Please read carefully before starting</p>
                </div>
                <a href="{{ route('student.full-test.index') }}"
                   class="px-4 py-2 text-sm font-medium text-[#C8102E] border border-[#C8102E] rounded-lg hover:bg-[#C8102E] hover:text-white transition-colors">
                    Exit
                </a>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-6">

                <!-- System Requirements -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">System Requirements</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Ensure you have a stable internet connection
                        @if(in_array('listening', $fullTest->getAvailableSections()))
                            and working headphones
                        @endif
                        @if(in_array('speaking', $fullTest->getAvailableSections()))
                            @if(in_array('listening', $fullTest->getAvailableSections())), @else and @endif
                            a working microphone
                        @endif
                        . Use a desktop or laptop for the best experience.
                    </p>
                </div>

                <!-- Test Duration -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Test Duration</h3>

                    @php
                        $sections = $fullTest->getAvailableSections();
                        $totalMinutes = 0;
                        $sectionTimes = [
                            'listening' => ['time' => 32, 'label' => 'Listening', 'duration' => '32 min'],
                            'reading' => ['time' => 60, 'label' => 'Reading', 'duration' => '60 min'],
                            'writing' => ['time' => 60, 'label' => 'Writing', 'duration' => '60 min'],
                            'speaking' => ['time' => 13, 'label' => 'Speaking', 'duration' => '13-15 min']
                        ];
                        foreach($sections as $section) {
                            if(isset($sectionTimes[$section])) {
                                $totalMinutes += $sectionTimes[$section]['time'];
                            }
                        }
                        $hours = floor($totalMinutes / 60);
                        $mins = $totalMinutes % 60;
                    @endphp

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3">
                        @foreach($sections as $section)
                            @if(isset($sectionTimes[$section]))
                                @php $info = $sectionTimes[$section]; @endphp
                                <div class="bg-gray-50 rounded-lg p-3 text-center">
                                    <p class="text-xs text-gray-500 mb-1">{{ $info['label'] }}</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ $info['duration'] }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <p class="text-sm text-gray-600">
                        Total time: <span class="font-semibold text-gray-900">{{ $hours > 0 ? $hours . ' hr ' : '' }}{{ $mins }} min</span>
                    </p>
                </div>

                <!-- Rules -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Rules</h3>
                    <ul class="text-sm text-gray-600 space-y-1.5">
                        <li class="flex items-start gap-2">
                            <span class="text-gray-400 mt-1">•</span>
                            <span>Sections must be completed in order, starting with {{ ucfirst($sections[0] ?? 'Listening') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-gray-400 mt-1">•</span>
                            <span>Timer cannot be paused once started</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-gray-400 mt-1">•</span>
                            <span>Find a quiet environment without interruptions</span>
                        </li>
                    </ul>
                </div>

                <!-- Important Note -->
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <p class="text-sm text-amber-800">
                        <span class="font-semibold">Important:</span> Make sure you have enough uninterrupted time before starting.
                    </p>
                </div>

                {{-- 24 Hour Time Limit Warning for Offline Students --}}
                @if(auth()->user()->isOfflineStudent())
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-stopwatch text-red-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-red-800">24 Hour Time Limit</p>
                                <p class="text-xs text-red-700 mt-1">
                                    Once you start this test, you must complete it within <strong>24 hours</strong>.
                                    If you don't finish in time, the test will expire and count as one of your attempts.
                                    Your progress will be saved if you need to take a break, but make sure to complete within the time limit.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                <form id="startTestForm" action="{{ route('student.full-test.start', $fullTest) }}" method="POST">
                    @csrf
                    <button type="button" id="startTestBtn" onclick="handleStartTest()"
                            class="w-full py-3 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-800 transition-colors flex items-center justify-center gap-2">
                        <span id="btnText">I Understand, Start Test</span>
                        <i id="btnLoader" class="fas fa-spinner fa-spin hidden"></i>
                    </button>
                </form>
            </div>

            <script>
                function handleStartTest() {
                    const btn = document.getElementById('startTestBtn');
                    const btnText = document.getElementById('btnText');
                    const btnLoader = document.getElementById('btnLoader');

                    // Disable button
                    btn.disabled = true;
                    btn.classList.add('opacity-75', 'cursor-not-allowed');

                    // Show loading
                    btnText.textContent = 'Starting...';
                    btnLoader.classList.remove('hidden');

                    // Submit after 1 second
                    setTimeout(function() {
                        document.getElementById('startTestForm').submit();
                    }, 1000);
                }
            </script>
        </div>

    </div>

</body>
</html>
