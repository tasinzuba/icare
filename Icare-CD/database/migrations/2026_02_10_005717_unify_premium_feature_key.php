<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Unify premium feature keys:
     * - 'premium_full_tests' was used for FullTest premium check
     * - 'premium_test_sets' was used for section test premium check (but never existed in DB!)
     * - Now unified to single key: 'premium_content' for all premium access
     */
    public function up(): void
    {
        // Step 1: Rename existing 'premium_full_tests' → 'premium_content'
        DB::table('subscription_features')
            ->where('key', 'premium_full_tests')
            ->update([
                'key' => 'premium_content',
                'name' => 'Premium Content Access',
                'description' => 'Access to all premium tests (full tests and section tests)',
                'updated_at' => now(),
            ]);

        // Step 2: Remove 'premium_test_sets' if it somehow exists (it shouldn't, but safety)
        $duplicateFeature = DB::table('subscription_features')
            ->where('key', 'premium_test_sets')
            ->first();

        if ($duplicateFeature) {
            // Move any plan_feature mappings to premium_content
            $premiumContentFeature = DB::table('subscription_features')
                ->where('key', 'premium_content')
                ->first();

            if ($premiumContentFeature) {
                // Delete duplicate mappings
                DB::table('plan_feature')
                    ->where('feature_id', $duplicateFeature->id)
                    ->delete();
            }

            DB::table('subscription_features')
                ->where('id', $duplicateFeature->id)
                ->delete();
        }

        // Step 3: Ensure Free plan has premium_content = 'false' (disabled)
        $premiumContentFeature = DB::table('subscription_features')
            ->where('key', 'premium_content')
            ->first();

        if ($premiumContentFeature) {
            $freePlan = DB::table('subscription_plans')->where('slug', 'free')->first();
            if ($freePlan) {
                DB::table('plan_feature')
                    ->updateOrInsert(
                        ['plan_id' => $freePlan->id, 'feature_id' => $premiumContentFeature->id],
                        ['value' => 'false', 'updated_at' => now()]
                    );
            }

            // Ensure Premium plan has premium_content = 'true'
            $premiumPlan = DB::table('subscription_plans')->where('slug', 'premium')->first();
            if ($premiumPlan) {
                DB::table('plan_feature')
                    ->updateOrInsert(
                        ['plan_id' => $premiumPlan->id, 'feature_id' => $premiumContentFeature->id],
                        ['value' => 'true', 'updated_at' => now()]
                    );
            }

            // Ensure Pro plan has premium_content = 'true'
            $proPlan = DB::table('subscription_plans')->where('slug', 'pro')->first();
            if ($proPlan) {
                DB::table('plan_feature')
                    ->updateOrInsert(
                        ['plan_id' => $proPlan->id, 'feature_id' => $premiumContentFeature->id],
                        ['value' => 'true', 'updated_at' => now()]
                    );
            }

            // Ensure Basic plan has premium_content = 'false'
            $basicPlan = DB::table('subscription_plans')->where('slug', 'basic')->first();
            if ($basicPlan) {
                DB::table('plan_feature')
                    ->updateOrInsert(
                        ['plan_id' => $basicPlan->id, 'feature_id' => $premiumContentFeature->id],
                        ['value' => 'false', 'updated_at' => now()]
                    );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert 'premium_content' back to 'premium_full_tests'
        DB::table('subscription_features')
            ->where('key', 'premium_content')
            ->update([
                'key' => 'premium_full_tests',
                'name' => 'Premium Full Tests',
                'description' => 'Access to premium full mock tests',
                'updated_at' => now(),
            ]);
    }
};
