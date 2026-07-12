<?php

use Illuminate\Database\Migrations\Migration;

/*
 * NOTE: The subscription system (SubscriptionPlan / SubscriptionFeature models)
 * was removed when the platform moved to the offline branch-enrollment model.
 * This data-fix migration is now obsolete and kept only so the migration
 * history stays intact. Its body is intentionally a no-op.
 */
return new class extends Migration
{
    public function up(): void
    {
        // no-op — subscription plans/features feature removed
    }

    public function down(): void
    {
        // no-op
    }
};
