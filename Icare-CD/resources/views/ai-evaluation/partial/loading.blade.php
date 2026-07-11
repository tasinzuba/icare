@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="text-center">
        {{-- AI Animation --}}
        <div class="relative mb-8">
            <div class="w-32 h-32 mx-auto">
                <div class="absolute inset-0 bg-gradient-to-r from-purple-600 to-blue-600 rounded-full animate-pulse"></div>
                <div class="absolute inset-2 bg-white rounded-full flex items-center justify-center">
                    <i class="fas fa-robot text-4xl text-purple-600 animate-bounce"></i>
                </div>
            </div>
        </div>

        {{-- Loading Text --}}
        <h2 class="text-2xl font-bold text-gray-900 mb-4">AI is Evaluating Your {{ ucfirst($type) }}...</h2>
        <p class="text-gray-600 mb-8">This may take 30-60 seconds. Please don't close this window.</p>

        {{-- Progress Steps --}}
        <div class="max-w-md mx-auto space-y-4 text-left">
            <div class="flex items-center space-x-3" id="step-1">
                <div class="w-8 h-8 rounded-full bg-purple-600 text-white flex items-center justify-center">
                    <i class="fas fa-check text-sm hidden"></i>
                    <div class="spinner"></div>
                </div>
                <span class="text-gray-700">Analyzing your response...</span>
            </div>
            
            <div class="flex items-center space-x-3 opacity-50" id="step-2">
                <div class="w-8 h-8 rounded-full bg-gray-300 text-white flex items-center justify-center">
                    <i class="fas fa-check text-sm hidden"></i>
                    <div class="spinner hidden"></div>
                </div>
                <span class="text-gray-700">Evaluating against IELTS criteria...</span>
            </div>
            
            <div class="flex items-center space-x-3 opacity-50" id="step-3">
                <div class="w-8 h-8 rounded-full bg-gray-300 text-white flex items-center justify-center">
                    <i class="fas fa-check text-sm hidden"></i>
                    <div class="spinner hidden"></div>
                </div>
                <span class="text-gray-700">Generating detailed feedback...</span>
            </div>
            
            <div class="flex items-center space-x-3 opacity-50" id="step-4">
                <div class="w-8 h-8 rounded-full bg-gray-300 text-white flex items-center justify-center">
                    <i class="fas fa-check text-sm hidden"></i>
                    <div class="spinner hidden"></div>
                </div>
                <span class="text-gray-700">Calculating band score...</span>
            </div>
        </div>

        {{-- Tip --}}
        <div class="mt-12 p-4 bg-blue-50 rounded-lg max-w-md mx-auto">
            <p class="text-sm text-blue-800">
                <i class="fas fa-lightbulb mr-2"></i>
                <span class="font-semibold">Did you know?</span> Our AI evaluator is trained on thousands of real IELTS responses to provide accurate band score predictions.
            </p>
        </div>
    </div>
</div>

@push('styles')
<style>
.spinner {
    width: 16px;
    height: 16px;
    border: 2px solid #f3f4f6;
    border-top: 2px solid #9333ea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endpush

@push('scripts')
<script>
// Simulate progress
let currentStep = 1;
const totalSteps = 4;

function updateStep() {
    if (currentStep > totalSteps) return;
    
    // Complete current step
    const currentStepEl = document.getElementById(`step-${currentStep}`);
    currentStepEl.querySelector('.spinner').classList.add('hidden');
    currentStepEl.querySelector('.fa-check').classList.remove('hidden');
    
    // Start next step
    currentStep++;
    if (currentStep <= totalSteps) {
        const nextStepEl = document.getElementById(`step-${currentStep}`);
        nextStepEl.classList.remove('opacity-50');
        nextStepEl.querySelector('.bg-gray-300').classList.remove('bg-gray-300');
        nextStepEl.querySelector('.bg-gray-300')?.classList.add('bg-purple-600');
        nextStepEl.querySelector('.spinner').classList.remove('hidden');
        
        setTimeout(updateStep, 15000 / totalSteps);
    }
}

// Start the evaluation process
setTimeout(updateStep, 3000);

// Check evaluation status
let checkInterval = setInterval(function() {
    fetch('{{ route("ai.evaluation.status", $attemptId) }}')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'completed') {
                clearInterval(checkInterval);
                window.location.href = data.redirect_url;
            } else if (data.status === 'failed') {
                clearInterval(checkInterval);
                alert('Evaluation failed. Please try again.');
                window.history.back();
            }
        });
}, 5000);
</script>
@endpush
@endsection