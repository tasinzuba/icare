{{-- AI Evaluation Button Component --}}
@if(!$attempt->ai_evaluated_at)
    <button onclick="startAIEvaluation({{ $attempt->id }}, '{{ $type }}')"
            class="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-6 py-3 rounded-lg hover:from-purple-700 hover:to-blue-700 transition-all transform hover:scale-105 shadow-lg">
        <i class="fas fa-robot mr-2"></i>
        Get AI Evaluation
    </button>
@else
    <a href="{{ route('ai.evaluation.get', $attempt->id) }}"
       class="bg-gradient-to-r from-green-600 to-teal-600 text-white px-6 py-3 rounded-lg hover:from-green-700 hover:to-teal-700 transition-all inline-block">
        <i class="fas fa-chart-line mr-2"></i>
        View AI Evaluation
    </a>
@endif