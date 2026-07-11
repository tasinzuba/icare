<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('test_categories')) {
            Schema::create('test_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('icon')->nullable();
                $table->string('color')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('test_category_test_set')) {
            Schema::create('test_category_test_set', function (Blueprint $table) {
                $table->id();
                $table->foreignId('test_category_id')
                    ->constrained('test_categories')
                    ->cascadeOnDelete();
                $table->foreignId('test_set_id')
                    ->constrained('test_sets')
                    ->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['test_category_id', 'test_set_id'], 'test_category_test_set_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('test_category_test_set');
        Schema::dropIfExists('test_categories');
    }
};
