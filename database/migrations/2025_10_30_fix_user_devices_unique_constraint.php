<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_devices', function (Blueprint $table) {
            // Drop the old unique constraint on device_fingerprint
            $table->dropUnique(['device_fingerprint']);
            
            // Add composite unique constraint for user_id and device_fingerprint
            $table->unique(['user_id', 'device_fingerprint'], 'user_devices_user_fingerprint_unique');
        });
    }

    public function down(): void
    {
        Schema::table('user_devices', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('user_devices_user_fingerprint_unique');
            
            // Re-add the old unique constraint on device_fingerprint
            $table->unique('device_fingerprint');
        });
    }
};
