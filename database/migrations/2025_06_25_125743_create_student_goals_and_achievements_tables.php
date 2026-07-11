<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // User Goals Table
        Schema::create('user_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('target_band_score', 2, 1)->nullable();
            $table->date('target_date')->nullable();
            $table->enum('exam_type', ['academic', 'general'])->default('academic');
            $table->string('study_reason')->nullable(); // study abroad, immigration, work, etc.
            $table->integer('weekly_study_hours')->default(10);
            $table->json('section_targets')->nullable(); // {"listening": 7.5, "reading": 7.0, etc}
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Achievement Badges Table
        Schema::create('achievement_badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description');
            $table->string('icon'); // icon class or image path
            $table->string('color')->default('blue'); // badge color theme
            $table->enum('category', ['milestone', 'streak', 'performance', 'special']);
            $table->json('criteria'); // conditions to earn badge
            $table->integer('points')->default(10); // gamification points
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // User Achievements Table
        Schema::create('user_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('badge_id')->constrained('achievement_badges')->onDelete('cascade');
            $table->timestamp('earned_at');
            $table->json('metadata')->nullable(); // additional info about achievement
            $table->boolean('is_seen')->default(false);
            $table->timestamps();
            
            $table->unique(['user_id', 'badge_id']);
        });

        // Leaderboard Cache Table
        Schema::create('leaderboard_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('period', ['daily', 'weekly', 'monthly', 'all_time']);
            $table->enum('category', ['overall', 'listening', 'reading', 'writing', 'speaking']);
            $table->decimal('average_score', 3, 1)->default(0);
            $table->integer('tests_taken')->default(0);
            $table->integer('total_points')->default(0); // from achievements
            $table->integer('rank')->default(0);
            $table->date('period_start');
            $table->date('period_end')->nullable();
            $table->timestamps();
            
            $table->index(['period', 'category', 'rank']);
            $table->unique(['user_id', 'period', 'category', 'period_start']);
        });

        // Add columns to users table
        Schema::table('users', function (Blueprint $table) {
            $table->integer('achievement_points')->default(0);
            $table->integer('study_streak_days')->default(0);
            $table->date('last_study_date')->nullable();
            $table->boolean('show_on_leaderboard')->default(true);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['achievement_points', 'study_streak_days', 'last_study_date', 'show_on_leaderboard']);
        });
        
        Schema::dropIfExists('leaderboard_entries');
        Schema::dropIfExists('user_achievements');
        Schema::dropIfExists('achievement_badges');
        Schema::dropIfExists('user_goals');
    }
};