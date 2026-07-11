<div class="bg-gray-100 min-h-screen">
    <div class="bg-white p-4 border-b flex justify-between items-center">
        <div class="flex items-center">
            <div class="bg-red-600 rounded-full p-2 mr-2">
                <i class="fas fa-desktop text-white"></i>
            </div>
            <span class="text-red-600 text-xl font-bold">Computer-delivered IELTS</span>
        </div>
        <div class="flex items-center">
            <img src="{{ asset('images/idp-logo.png') }}" alt="IDP" class="h-8 mr-2">
            <span class="text-red-600 font-bold text-xl">IELTS</span>
        </div>
    </div>
    
    <div class="bg-gray-800 p-2 flex justify-between items-center">
        <div class="text-white flex items-center">
            <i class="fas fa-user-circle mr-2"></i>
            <span>{{ auth()->user()->name }} - XX {{ auth()->id() }}</span>
        </div>
        <div class="flex items-center">
            <button class="bg-gray-300 text-gray-800 px-3 py-1 rounded-md text-sm mr-2">Help ?</button>
            <button class="bg-gray-300 text-gray-800 px-3 py-1 rounded-md text-sm mr-2">Hide</button>
            <button class="text-white mr-2">
                <i class="fas fa-volume-up"></i>
            </button>
            <input type="range" min="0" max="100" value="75" class="w-24">
        </div>
    </div>
    
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-1">Sample Listening <em>Multiple Choice (one answer)</em></h1>
        
        <div class="bg-white rounded-md shadow-sm p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Questions 1 - 3</h2>
            <p class="mb-6">Choose the correct answer.</p>
            
            <div class="grid grid-cols-2 gap-8">
                <div>
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold">1</h3>
                        <p class="mb-2">Why did Judy choose to study the East End of London?</p>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <input type="radio" id="q1-a" name="q1" class="mr-2">
                                <label for="q1-a">She wanted to understand her own background.</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="q1-b" name="q1" class="mr-2">
                                <label for="q1-b">She was interested in place names.</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="q1-c" name="q1" class="mr-2">
                                <label for="q1-c">She had read several books about it.</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div>
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold">2</h3>
                        <p class="mb-2">What was Judy's main source of material?</p>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <input type="radio" id="q2-a" name="q2" class="mr-2">
                                <label for="q2-a">books</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="q2-b" name="q2" class="mr-2">
                                <label for="q2-b">newspapers</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="q2-c" name="q2" class="mr-2">
                                <label for="q2-c">interviews</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="fixed bottom-0 left-0 right-0 bg-white p-3 border-t flex items-center justify-between">
            <div class="flex items-center">
                <input type="checkbox" id="review" class="mr-2">
                <label for="review">Review</label>
                
                <div class="ml-4 flex items-center">
                    <span class="mr-2">Part 1</span>
                    
                    <div class="flex space-x-1">
                        @for ($i = 1; $i <= 24; $i++)
                            <button class="w-6 h-6 flex items-center justify-center text-xs rounded-sm {{ $i == 1 ? 'bg-blue-500 text-white' : 'bg-gray-300' }}">
                                {{ $i }}
                            </button>
                        @endfor
                    </div>
                </div>
            </div>
            
            <div class="flex items-center">
                <button class="bg-amber-400 p-2 rounded-full mr-4">
                    <i class="fas fa-pencil-alt"></i>
                </button>
                <button class="bg-gray-300 p-2 rounded-full">
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>