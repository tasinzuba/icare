<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SubscriptionFeature;
use App\Models\SubscriptionPlan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add new subscription feature for evaluation tokens
        $tokenFeature = SubscriptionFeature::create([
            'key' => 'evaluation_tokens_per_month',
            'name' => 'Human Evaluation Tokens',
            'description' => 'Number of tokens for human evaluation per month',
            'icon' => 'fas fa-coins'
        ]);

        // Attach tokens to subscription plans
        $freePlan = SubscriptionPlan::where('slug', 'free')->first();
        if ($freePlan) {
            $freePlan->features()->attach($tokenFeature->id, ['value' => '0']);
        }

        $premiumPlan = SubscriptionPlan::where('slug', 'premium')->first();
        if ($premiumPlan) {
            $premiumPlan->features()->attach($tokenFeature->id, ['value' => '10']);
        }

        $proPlan = SubscriptionPlan::where('slug', 'pro')->first();
        if ($proPlan) {
            $proPlan->features()->attach($tokenFeature->id, ['value' => '25']);
        }

        // Add column to track monthly token grants
        Schema::table('user_evaluation_tokens', function (Blueprint $table) {
            if (!Schema::hasColumn('user_evaluation_tokens', 'tokens_granted_this_month')) {
                $table->integer('tokens_granted_this_month')->default(0);
            }
            if (!Schema::hasColumn('user_evaluation_tokens', 'last_monthly_grant_at')) {
                $table->timestamp('last_monthly_grant_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove token feature
        $tokenFeature = SubscriptionFeature::where('key', 'evaluation_tokens_per_month')->first();
        if ($tokenFeature) {
            $tokenFeature->plans()->detach();
            $tokenFeature->delete();
        }

        // Remove columns
        Schema::table('user_evaluation_tokens', function (Blueprint $table) {
            $table->dropColumn(['tokens_granted_this_month', 'last_monthly_grant_at']);
        });
    }
};