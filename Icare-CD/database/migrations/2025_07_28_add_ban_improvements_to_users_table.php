<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add ban duration fields if not exists
            if (!Schema::hasColumn('users', 'ban_expires_at')) {
                $table->timestamp('ban_expires_at')->nullable();
            }
            
            if (!Schema::hasColumn('users', 'ban_type')) {
                $table->enum('ban_type', ['temporary', 'permanent'])->default('temporary');
            }
            
            if (!Schema::hasColumn('users', 'banned_by')) {
                $table->unsignedBigInteger('banned_by')->nullable();
                $table->foreign('banned_by')->references('id')->on('users')->onDelete('set null');
            }
        });
        
        // Create ban appeals table
        if (Schema::hasTable('ban_appeals')) return;
        Schema::create('ban_appeals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('appeal_reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_response')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ban_appeals');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['banned_by']);
            $table->dropColumn(['ban_expires_at', 'ban_type', 'banned_by']);
        });
    }
};
