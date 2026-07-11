<div class="bg-gray-100 min-h-screen">
    <div class="bg-white p-4 border-b flex justify-between items-center">
        <div>
            <img src="{{ asset('images/ielts-logo.png') }}" alt="IELTS Logo" class="h-8">
        </div>
        <div class="flex space-x-4">
            <img src="{{ asset('images/british-council-logo.png') }}" alt="British Council" class="h-8">
            <img src="{{ asset('images/idp-logo.png') }}" alt="IDP" class="h-8">
            <img src="{{ asset('images/cambridge-logo.png') }}" alt="Cambridge Assessment" class="h-8">
        </div>
    </div>
    
    <div class="bg-gray-800 p-2 flex justify-between items-center">
        <div class="text-white flex items-center">
            <i class="fas fa-user-circle mr-2"></i>
            <span>{{ auth()->user()->name }} - XX {{ auth()->id() }}</span>
        </div>
        <div class="flex items-center">
            <button class="text-white mr-2">
                <i class="fas fa-volume-up"></i>
            </button>
            <input type="range" min="0" max="100" value="75" class="w-24">
        </div>
    </div>
    
    <div class="max-w-2xl mx-auto my-24 bg-white rounded-md shadow-sm overflow-hidden">
        <div class="bg-black text-white p-3 text-center">
            <span class="text-lg font-medium">Please wait...</span>
        </div>
        
        <div class="bg-gray-100 p-16 text-center">
            <p class="text-lg mb-8">The next phase of the test is about to begin.</p>
            
            <div class="flex justify-center">
                <div class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-12 w-12"></div>
            </div>
        </div>
    </div>
</div>

<style>
    .loader {
        border-top-color: #3498db;
        -webkit-animation: spinner 1.5s linear infinite;
        animation: spinner 1.5s linear infinite;
    }

    @-webkit-keyframes spinner {
        0% { -webkit-transform: rotate(0deg); }
        100% { -webkit-transform: rotate(360deg); }
    }

    @keyframes spinner {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>