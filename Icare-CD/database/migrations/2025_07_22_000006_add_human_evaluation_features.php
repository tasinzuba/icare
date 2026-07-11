<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add new features for human evaluation
        DB::table('subscription_features')->insert([
            [
                'key' => 'human_evaluation_tokens',
                'name' => 'Monthly Evaluation Tokens',
                'description' => 'Monthly tokens for human evaluation',
                'icon' => 'fas fa-coins',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'human_evaluation_discount',
                'name' => 'Token Purchase Discount',
                'description' => 'Discount percentage on token purchases',
                'icon' => 'fas fa-percentage',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
        
        // Update existing subscription plans to include these features
        $plans = DB::table('subscription_plans')->get();
        
        foreach ($plans as $plan) {
            // Add human evaluation tokens based on plan
            $tokens = 0;
            $discount = 0;
            
            if ($plan->slug === 'premium') {
                $tokens = 20;  // 20 tokens per month
                $discount = 10; // 10% discount
            } elseif ($plan->slug === 'pro') {
                $tokens = 50;  // 50 tokens per month
                $discount = 20; // 20% discount
            }
            
            // Insert or update plan features
            if ($tokens > 0) {
                DB::table('plan_feature')->updateOrInsert(
                    [
                        'plan_id' => $plan->id,
                        'feature_id' => DB::table('subscription_features')
                            ->where('key', 'human_evaluation_tokens')->first()->id
                    ],
                    [
                        'value' => (string)$tokens,
                        'limit' => $tokens,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }
            
            if ($discount > 0) {
                DB::table('plan_feature')->updateOrInsert(
                    [
                        'plan_id' => $plan->id,
                        'feature_id' => DB::table('subscription_features')
                            ->where('key', 'human_evaluation_discount')->first()->id
                    ],
                    [
                        'value' => $discount . '%',
                        'limit' => $discount,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        // Remove the features
        DB::table('subscription_features')
            ->whereIn('name', ['human_evaluation_tokens', 'human_evaluation_discount'])
            ->delete();
    }
};
