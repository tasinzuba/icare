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
        Schema::create('announcement_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('action_type', ['viewed', 'clicked', 'dismissed'])->default('viewed');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['announcement_id', 'user_id', 'action_type']);
        });

        // Add analytics columns to announcements table
        Schema::table('announcements', function (Blueprint $table) {
            $table->integer('total_views')->default(0);
            $table->integer('total_clicks')->default(0);
            $table->integer('total_dismissals')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['total_views', 'total_clicks', 'total_dismissals']);
        });
        
        Schema::dropIfExists('announcement_analytics');
    }
};
