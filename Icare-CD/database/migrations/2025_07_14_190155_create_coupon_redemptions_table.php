<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('coupon_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('coupon_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained('user_subscriptions')->onDelete('set null');
            $table->decimal('original_price', 10, 2);
            $table->decimal('discount_amount', 10, 2);
            $table->decimal('final_price', 10, 2);
            $table->datetime('redeemed_at');
            $table->datetime('expires_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'coupon_id']);
            $table->index('redeemed_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('coupon_redemptions');
    }
};