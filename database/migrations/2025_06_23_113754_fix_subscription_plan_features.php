<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionFeature;

return new class extends Migration
{
    public function up(): void
    {
        // Get all features
        $features = SubscriptionFeature::all()->keyBy('key');
        
        // Fix Free Plan
        $freePlan = SubscriptionPlan::where('slug', 'free')->first();
        if ($freePlan && $freePlan->features()->count() == 0) {
            $freePlan->features()->attach([
                $features['mock_tests_per_month']->id => ['value' => '3'],
            ]);
        }
        
        // Fix Premium Plan
        $premiumPlan = SubscriptionPlan::where('slug', 'premium')->first();
        if ($premiumPlan && $premiumPlan->features()->count() == 0) {
            $premiumPlan->features()->attach([
                $features['mock_tests_per_month']->id => ['value' => 'unlimited'],
                $features['ai_writing_evaluation']->id => ['value' => 'true'],
                $features['ai_speaking_evaluation']->id => ['value' => 'true'],
                $features['detailed_analytics']->id => ['value' => 'true'],
                $features['priority_support']->id => ['value' => 'true'],
            ]);
        }
        
        // Fix Pro Plan
        $proPlan = SubscriptionPlan::where('slug', 'pro')->first();
        if ($proPlan && $proPlan->features()->count() == 0) {
            $proPlan->features()->attach([
                $features['mock_tests_per_month']->id => ['value' => 'unlimited'],
                $features['ai_writing_evaluation']->id => ['value' => 'true'],
                $features['ai_speaking_evaluation']->id => ['value' => 'true'],
                $features['detailed_analytics']->id => ['value' => 'true'],
                $features['priority_support']->id => ['value' => 'true'],
            ]);
            
            // Add Pro-only features if they exist
            if (isset($features['tutor_sessions'])) {
                $proPlan->features()->attach($features['tutor_sessions']->id, ['value' => '2']);
            }
            if (isset($features['certificate_generation'])) {
                $proPlan->features()->attach($features['certificate_generation']->id, ['value' => 'true']);
            }
        }
    }

    public function down(): void
    {
        // Detach all features
        $plans = SubscriptionPlan::all();
        foreach ($plans as $plan) {
            $plan->features()->detach();
        }
    }
};