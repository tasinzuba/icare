<x-dashboard-layout>
    <x-slot:title>Select Teacher for Evaluation</x-slot>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Hero Header Card -->
        <div class="relative bg-white rounded-3xl border border-gray-200 overflow-hidden mb-6 shadow-xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-24 -right-24 w-64 h-64 bg-gradient-to-br from-[#C8102E] to-[#A00E27] opacity-5 rounded-full"></div>
                <div class="absolute -bottom-16 -left-16 w-48 h-48 bg-gradient-to-br from-gray-400 to-gray-500 opacity-5 rounded-full"></div>
            </div>

            <div class="relative p-6 lg:p-8">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <!-- Test Info -->
                    <div class="flex items-start gap-4">
                        <div class="relative">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#C8102E] to-[#A00E27] flex items-center justify-center shadow-lg">
                                <i class="fas fa-user-tie text-white text-2xl"></i>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs text-[#C8102E] font-semibold uppercase tracking-wider mb-1">Expert Evaluation</p>
                            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-2">Choose Your IELTS Expert</h1>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm mt-3">
                                <span class="inline-flex items-center gap-2 text-gray-600">
                                    <i class="fas fa-file-alt text-gray-400"></i>
                                    <span class="font-medium">{{ $attempt->testSet->title }}</span>
                                </span>
                                <span class="text-gray-300">|</span>
                                <span class="inline-flex items-center gap-2 text-gray-600">
                                    <i class="fas fa-layer-group text-gray-400"></i>
                                    <span class="font-medium capitalize">{{ $section }}</span>
                                </span>
                                <span class="text-gray-300">|</span>
                                <span class="inline-flex items-center gap-2 text-gray-600">
                                    <i class="fas fa-calendar-alt text-gray-400"></i>
                                    <span class="font-medium">{{ $attempt->created_at->format('M d, Y') }}</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Token Balance Card -->
                    <div class="bg-gray-50 rounded-2xl p-5 border border-gray-200">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-xl bg-gray-900 flex items-center justify-center shadow-lg">
                                <i class="fas fa-coins text-amber-400 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium">Your Balance</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $tokenBalance->available_tokens }} <span class="text-sm font-medium text-gray-500">tokens</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($teachers->isEmpty())
            <!-- No Teachers Available -->
            <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
                <div class="max-w-md mx-auto">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-user-slash text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">No Teachers Available</h3>
                    <p class="text-gray-500 mb-8">
                        We're sorry, but there are no teachers available for {{ $section }} evaluation at the moment.
                        Please check back later or contact support.
                    </p>
                    <a href="{{ route('student.results.show', $attempt) }}"
                       class="inline-flex items-center px-6 py-3 rounded-xl bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 transition-all">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Results
                    </a>
                </div>
            </div>
        @else
            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - Teachers List -->
                <div class="lg:col-span-2 space-y-4">
                    <!-- Section Header -->
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="text-lg font-bold text-gray-900">Available Teachers</h2>
                        <span class="text-sm text-gray-500">{{ $teachers->count() }} teachers</span>
                    </div>

                    <!-- Teachers List -->
                    @foreach($teachers as $teacher)
                    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden hover:shadow-md hover:border-gray-300 transition-all">
                        <div class="p-5">
                            <div class="flex flex-col sm:flex-row gap-4">
                                <!-- Avatar & Basic Info -->
                                <div class="flex items-start gap-4 flex-1">
                                    @if($teacher->user->avatar_url)
                                        <img src="{{ $teacher->user->avatar_url }}"
                                             alt="{{ $teacher->user->name }}"
                                             class="w-14 h-14 rounded-xl object-cover border border-gray-100">
                                    @else
                                        <div class="w-14 h-14 rounded-xl bg-gray-800 flex items-center justify-center text-white font-bold text-lg">
                                            {{ substr($teacher->user->name, 0, 1) }}
                                        </div>
                                    @endif

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h3 class="font-bold text-gray-900 truncate">{{ $teacher->user->name }}</h3>
                                            @if($teacher->rating >= 4.8)
                                                <span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-xs rounded-full font-medium">
                                                    <i class="fas fa-crown mr-1" style="font-size: 9px;"></i>Top
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Rating -->
                                        <div class="flex items-center gap-2 mb-2">
                                            <div class="flex items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fas fa-star {{ $i <= $teacher->rating ? 'text-amber-400' : 'text-gray-200' }} text-xs"></i>
                                                @endfor
                                            </div>
                                            <span class="text-gray-900 font-semibold text-sm">{{ number_format($teacher->rating, 1) }}</span>
                                            <span class="text-gray-400 text-xs">({{ $teacher->total_evaluations_done }} reviews)</span>
                                        </div>

                                        <!-- Stats Row -->
                                        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500">
                                            <span class="inline-flex items-center gap-1">
                                                <i class="fas fa-briefcase text-gray-400"></i>
                                                {{ $teacher->experience_years }} years exp
                                            </span>
                                            <span class="inline-flex items-center gap-1">
                                                <i class="fas fa-clock text-gray-400"></i>
                                                ~{{ $teacher->average_turnaround_hours }}h delivery
                                            </span>
                                            @if($teacher->languages && count($teacher->languages) > 0)
                                            <span class="inline-flex items-center gap-1">
                                                <i class="fas fa-language text-gray-400"></i>
                                                {{ implode(', ', array_slice($teacher->languages, 0, 2)) }}
                                            </span>
                                            @endif
                                        </div>

                                        <!-- Specializations -->
                                        @if($teacher->specialization && count($teacher->specialization) > 0)
                                        <div class="flex flex-wrap gap-1.5 mt-3">
                                            @foreach(array_slice($teacher->specialization, 0, 3) as $spec)
                                                <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600 font-medium">
                                                    {{ ucfirst($spec) }}
                                                </span>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Pricing & Action -->
                                <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-3 sm:gap-2 sm:min-w-[140px] pt-3 sm:pt-0 border-t sm:border-t-0 sm:border-l border-gray-100 sm:pl-4">
                                    <div class="text-center sm:text-right">
                                        <p class="text-xs text-gray-400 mb-0.5">From</p>
                                        <p class="text-2xl font-bold text-gray-900">{{ $teacher->token_price }}</p>
                                        <p class="text-xs text-gray-500">tokens</p>
                                    </div>

                                    @if($tokenBalance->available_tokens >= $teacher->token_price)
                                        <button onclick="selectTeacher({{ $teacher->id }}, '{{ $teacher->user->name }}', {{ $teacher->token_price }}, {{ $teacher->urgent_price }})"
                                                class="px-5 py-2.5 rounded-xl bg-gray-900 text-white text-sm font-semibold hover:bg-gray-800 transition-all">
                                            Select
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Right Column - Info & Help -->
                <div class="space-y-6">
                    <!-- How it Works -->
                    <div class="bg-white rounded-2xl border border-gray-200 p-5">
                        <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-info-circle text-gray-400"></i>
                            How it Works
                        </h3>
                        <div class="space-y-4">
                            <div class="flex gap-3">
                                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 font-bold text-sm flex-shrink-0">1</div>
                                <div>
                                    <p class="font-medium text-gray-900 text-sm">Choose a Teacher</p>
                                    <p class="text-xs text-gray-500">Select based on rating, experience & price</p>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 font-bold text-sm flex-shrink-0">2</div>
                                <div>
                                    <p class="font-medium text-gray-900 text-sm">Submit Your Work</p>
                                    <p class="text-xs text-gray-500">Your test will be sent for evaluation</p>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 font-bold text-sm flex-shrink-0">3</div>
                                <div>
                                    <p class="font-medium text-gray-900 text-sm">Get Feedback</p>
                                    <p class="text-xs text-gray-500">Receive detailed evaluation within 48h</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Why Expert Evaluation -->
                    <div class="bg-white rounded-2xl border border-gray-200 p-5">
                        <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-star text-gray-400"></i>
                            Why Expert Evaluation?
                        </h3>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-2 text-sm">
                                <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
                                <span class="text-gray-600">Certified IELTS examiners with real test experience</span>
                            </li>
                            <li class="flex items-start gap-2 text-sm">
                                <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
                                <span class="text-gray-600">Detailed feedback on all four criteria</span>
                            </li>
                            <li class="flex items-start gap-2 text-sm">
                                <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
                                <span class="text-gray-600">Personalized tips for improvement</span>
                            </li>
                            <li class="flex items-start gap-2 text-sm">
                                <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
                                <span class="text-gray-600">Accurate band score prediction</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Pricing Guide -->
                    <div class="bg-gray-50 rounded-2xl border border-gray-200 p-5">
                        <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                            <i class="fas fa-tags text-gray-400"></i>
                            Pricing Options
                        </h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-white rounded-xl border border-gray-200">
                                <div>
                                    <p class="font-medium text-gray-900 text-sm">Normal</p>
                                    <p class="text-xs text-gray-500">Within 48 hours</p>
                                </div>
                                <span class="text-gray-600 font-semibold text-sm">Standard price</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-white rounded-xl border border-amber-200">
                                <div>
                                    <p class="font-medium text-gray-900 text-sm flex items-center gap-1">
                                        <i class="fas fa-bolt text-amber-500 text-xs"></i>
                                        Urgent
                                    </p>
                                    <p class="text-xs text-gray-500">Within 12 hours</p>
                                </div>
                                <span class="text-amber-600 font-semibold text-sm">+50% price</span>
                            </div>
                        </div>
                    </div>

                    <!-- Back Button -->
                    <a href="{{ route('student.results.show', $attempt) }}"
                       class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-white border border-gray-200 text-gray-600 rounded-xl font-medium hover:bg-gray-50 transition-all">
                        <i class="fas fa-arrow-left"></i>
                        Back to Results
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Teacher Selection Modal -->
    <div id="teacherModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div onclick="closeTeacherModal()" class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>

            <div class="relative bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl transform transition-all">
                <button onclick="closeTeacherModal()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 transition-all">
                    <i class="fas fa-times"></i>
                </button>

                <div class="text-center mb-6">
                    <div class="w-16 h-16 rounded-full bg-gray-900 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user-tie text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-1">Confirm Request</h3>
                    <p class="text-gray-500">Teacher: <span id="selectedTeacherName" class="text-gray-900 font-semibold"></span></p>
                </div>

                <form action="{{ route('student.evaluation.request', $attempt) }}" method="POST">
                    @csrf
                    <input type="hidden" name="teacher_id" id="selectedTeacherId">

                    <!-- Priority Selection -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Select Delivery Speed</label>

                        <div class="space-y-3">
                            <label class="flex items-center p-4 rounded-xl bg-gray-50 border-2 border-gray-200 cursor-pointer hover:border-gray-400 transition-all priority-option" data-priority="normal">
                                <input type="radio" name="priority" value="normal" checked class="sr-only" onchange="updatePrice('normal')">
                                <div class="flex items-center justify-between w-full">
                                    <div>
                                        <p class="font-semibold text-gray-900">Normal Delivery</p>
                                        <p class="text-xs text-gray-500">Within 48 hours</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-bold text-gray-900">
                                            <span id="normalPrice">0</span>
                                            <span class="text-xs font-normal text-gray-500">tokens</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="ml-3 priority-check">
                                    <i class="fas fa-check-circle text-[#C8102E] text-lg"></i>
                                </div>
                            </label>

                            <label class="flex items-center p-4 rounded-xl bg-amber-50 border-2 border-amber-200 cursor-pointer hover:border-amber-400 transition-all priority-option" data-priority="urgent">
                                <input type="radio" name="priority" value="urgent" class="sr-only" onchange="updatePrice('urgent')">
                                <div class="flex items-center justify-between w-full">
                                    <div>
                                        <p class="font-semibold text-gray-900 flex items-center gap-1">
                                            <i class="fas fa-bolt text-amber-500"></i>
                                            Urgent Delivery
                                        </p>
                                        <p class="text-xs text-gray-500">Within 12 hours</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-bold text-gray-900">
                                            <span id="urgentPrice">0</span>
                                            <span class="text-xs font-normal text-gray-500">tokens</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="ml-3 priority-check hidden">
                                    <i class="fas fa-check-circle text-amber-500 text-lg"></i>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Token Summary -->
                    <div class="bg-gray-50 rounded-xl p-4 mb-6 border border-gray-200">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Cost</span>
                            <span class="text-xl font-bold text-gray-900" id="totalTokens">0</span>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-gray-500">Your Balance</span>
                            <span class="text-gray-900 font-medium">{{ $tokenBalance->available_tokens }}</span>
                        </div>
                        <hr class="my-3 border-gray-200">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">After Payment</span>
                            <span class="text-gray-900 font-bold" id="remainingTokens">0</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3">
                        <button type="button" onclick="closeTeacherModal()"
                                class="flex-1 py-3 rounded-xl bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 transition-all">
                            Cancel
                        </button>
                        <button type="submit"
                                class="flex-1 py-3 rounded-xl font-semibold bg-gray-900 hover:bg-gray-800 text-white transition-all">
                            <i class="fas fa-check mr-2"></i>
                            Confirm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let selectedNormalPrice = 0;
        let selectedUrgentPrice = 0;
        let userBalance = {{ $tokenBalance->available_tokens }};

        function selectTeacher(teacherId, teacherName, normalPrice, urgentPrice) {
            document.getElementById('selectedTeacherId').value = teacherId;
            document.getElementById('selectedTeacherName').textContent = teacherName;
            document.getElementById('normalPrice').textContent = normalPrice;
            document.getElementById('urgentPrice').textContent = urgentPrice;

            selectedNormalPrice = normalPrice;
            selectedUrgentPrice = urgentPrice;

            // Reset to normal priority
            document.querySelector('input[value="normal"]').checked = true;
            updatePrice('normal');

            // Show modal
            document.getElementById('teacherModal').classList.remove('hidden');
        }

        function closeTeacherModal() {
            document.getElementById('teacherModal').classList.add('hidden');
        }

        function updatePrice(priority) {
            const price = priority === 'urgent' ? selectedUrgentPrice : selectedNormalPrice;
            document.getElementById('totalTokens').textContent = price;
            document.getElementById('remainingTokens').textContent = userBalance - price;

            // Update check marks
            document.querySelectorAll('.priority-check').forEach(el => el.classList.add('hidden'));
            document.querySelector(`input[value="${priority}"]`).parentElement.querySelector('.priority-check').classList.remove('hidden');

            // Update priority option styles
            document.querySelectorAll('.priority-option').forEach(el => {
                el.classList.remove('border-gray-900', 'border-amber-500');
            });
            const selectedOption = document.querySelector(`input[value="${priority}"]`).parentElement;
            selectedOption.classList.add(priority === 'urgent' ? 'border-amber-500' : 'border-gray-900');

            // Update remaining tokens color
            const remaining = userBalance - price;
            const remainingEl = document.getElementById('remainingTokens');
            if (remaining < 0) {
                remainingEl.classList.add('text-red-500');
                remainingEl.classList.remove('text-gray-900');
            } else {
                remainingEl.classList.remove('text-red-500');
                remainingEl.classList.add('text-gray-900');
            }
        }

        // ESC key to close modal
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeTeacherModal();
            }
        });
    </script>
    @endpush
</x-dashboard-layout>
