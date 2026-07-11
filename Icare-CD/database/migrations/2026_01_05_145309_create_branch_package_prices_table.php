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
        Schema::create('branch_package_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('offline_packages')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->decimal('custom_price', 10, 2);
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            // Unique constraint - one price override per package per branch
            $table->unique(['package_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_package_prices');
    }
};
