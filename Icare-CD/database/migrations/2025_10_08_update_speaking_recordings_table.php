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
        Schema::table('speaking_recordings', function (Blueprint $table) {
            // Add missing columns for R2/CDN support
            if (!Schema::hasColumn('speaking_recordings', 'file_url')) {
                $table->string('file_url')->nullable();
            }
            
            if (!Schema::hasColumn('speaking_recordings', 'storage_disk')) {
                $table->string('storage_disk')->default('public');
            }
            
            if (!Schema::hasColumn('speaking_recordings', 'file_size')) {
                $table->bigInteger('file_size')->nullable();
            }
            
            if (!Schema::hasColumn('speaking_recordings', 'mime_type')) {
                $table->string('mime_type')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('speaking_recordings', function (Blueprint $table) {
            $table->dropColumn(['file_url', 'storage_disk', 'file_size', 'mime_type']);
        });
    }
};
