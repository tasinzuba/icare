<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_number')->nullable()->unique();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('google_id')->nullable()->unique();
            $table->string('facebook_id')->nullable()->unique();
            $table->string('avatar_url')->nullable();
            $table->enum('login_method', ['email', 'google', 'facebook'])->default('email');
            $table->string('country_code', 2)->nullable();
            $table->string('country_name')->nullable();
            $table->string('city')->nullable();
            $table->string('timezone')->nullable();
            $table->string('currency', 3)->nullable();
            $table->boolean('is_social_signup')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone_number', 'phone_verified_at', 'google_id', 'facebook_id',
                'avatar_url', 'login_method', 'country_code', 'country_name',
                'city', 'timezone', 'currency', 'is_social_signup'
            ]);
        });
    }
};