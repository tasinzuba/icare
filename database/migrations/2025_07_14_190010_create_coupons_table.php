<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('description')->nullable();
            $table->enum('discount_type', ['percentage', 'fixed', 'full_access', 'trial']);
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->foreignId('plan_id')->constrained('subscription_plans')->onDelete('cascade');
            $table->integer('duration_days')->nullable()->comment('For trial coupons');
            $table->integer('usage_limit')->nullable()->comment('NULL means unlimited');
            $table->integer('used_count')->default(0);
            $table->datetime('valid_from')->nullable();
            $table->datetime('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->json('metadata')->nullable()->comment('Extra data like minimum purchase, etc');
            $table->timestamps();
            
            $table->index(['code', 'is_active']);
            $table->index('valid_from');
            $table->index('valid_until');
        });
    }

    public function down()
    {
        Schema::dropIfExists('coupons');
    }
};