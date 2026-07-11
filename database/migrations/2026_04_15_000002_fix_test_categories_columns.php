<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('test_categories')) {
            return;
        }

        Schema::table('test_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('test_categories', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }
            if (!Schema::hasColumn('test_categories', 'description')) {
                $table->text('description')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('test_categories', 'icon')) {
                $table->string('icon')->nullable()->after('description');
            }
            if (!Schema::hasColumn('test_categories', 'color')) {
                $table->string('color')->nullable()->after('icon');
            }
            if (!Schema::hasColumn('test_categories', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('color');
            }
            if (!Schema::hasColumn('test_categories', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('sort_order');
            }
        });

        // If legacy 'active' column exists, copy its value to 'is_active'
        if (Schema::hasColumn('test_categories', 'active') && Schema::hasColumn('test_categories', 'is_active')) {
            \DB::statement('UPDATE test_categories SET is_active = active');
        }

        // Backfill empty slugs from name
        if (Schema::hasColumn('test_categories', 'slug')) {
            $rows = \DB::table('test_categories')->whereNull('slug')->orWhere('slug', '')->get();
            foreach ($rows as $row) {
                \DB::table('test_categories')
                    ->where('id', $row->id)
                    ->update(['slug' => \Illuminate\Support\Str::slug($row->name) . '-' . $row->id]);
            }
        }
    }

    public function down(): void
    {
        // non-destructive
    }
};
