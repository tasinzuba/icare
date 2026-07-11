<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if feature already exists
        $existingFeature = DB::table('subscription_features')
            ->where('key', 'premium_full_tests')
            ->first();
            
        if ($existingFeature) {
            $featureId = $existingFeature->id;
        } else {
            // Add premium_full_tests feature
            $featureId = DB::table('subscription_features')->insertGetId([
                'key' => 'premium_full_tests',
                'name' => 'Premium Full Tests',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Get subscription plans
        $freePlan = DB::table('subscription_plans')->where('slug', 'free')->first();
        $premiumPlan = DB::table('subscription_plans')->where('slug', 'premium')->first();
        $proPlan = DB::table('subscription_plans')->where('slug', 'pro')->first();

        // Assign feature to plans (check if not already assigned)
        if ($freePlan) {
            $existing = DB::table('plan_feature')
                ->where('plan_id', $freePlan->id)
                ->where('feature_id', $featureId)
                ->first();
                
            if (!$existing) {
                DB::table('plan_feature')->insert([
                    'plan_id' => $freePlan->id,
                    'feature_id' => $featureId,
                    'value' => 'false',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        if ($premiumPlan) {
            $existing = DB::table('plan_feature')
                ->where('plan_id', $premiumPlan->id)
                ->where('feature_id', $featureId)
                ->first();
                
            if (!$existing) {
                DB::table('plan_feature')->insert([
                    'plan_id' => $premiumPlan->id,
                    'feature_id' => $featureId,
                    'value' => 'true',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        if ($proPlan) {
            $existing = DB::table('plan_feature')
                ->where('plan_id', $proPlan->id)
                ->where('feature_id', $featureId)
                ->first();
                
            if (!$existing) {
                DB::table('plan_feature')->insert([
                    'plan_id' => $proPlan->id,
                    'feature_id' => $featureId,
                    'value' => 'true',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $feature = DB::table('subscription_features')->where('key', 'premium_full_tests')->first();
        
        if ($feature) {
            DB::table('plan_feature')->where('feature_id', $feature->id)->delete();
            DB::table('subscription_features')->where('id', $feature->id)->delete();
        }
    }
};
