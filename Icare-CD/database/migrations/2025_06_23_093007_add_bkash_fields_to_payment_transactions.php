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
        Schema::table('payment_transactions', function (Blueprint $table) {
            // Add bKash specific fields if they don't exist
            if (!Schema::hasColumn('payment_transactions', 'bkash_payment_id')) {
                $table->string('bkash_payment_id')->nullable();
            }
            
            if (!Schema::hasColumn('payment_transactions', 'bkash_trx_id')) {
                $table->string('bkash_trx_id')->nullable();
            }
            
            if (!Schema::hasColumn('payment_transactions', 'invoice_number')) {
                $table->string('invoice_number')->nullable();
            }
            
            if (!Schema::hasColumn('payment_transactions', 'customer_msisdn')) {
                $table->string('customer_msisdn')->nullable();
            }
            
            // Add indexes only if not already present
            $indexes = collect(\DB::select("SHOW INDEX FROM payment_transactions"))->pluck('Key_name');
            if (!$indexes->contains('payment_transactions_bkash_payment_id_index')) {
                $table->index('bkash_payment_id');
            }
            if (!$indexes->contains('payment_transactions_bkash_trx_id_index')) {
                $table->index('bkash_trx_id');
            }
            if (!$indexes->contains('payment_transactions_invoice_number_index')) {
                $table->index('invoice_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['bkash_payment_id']);
            $table->dropIndex(['bkash_trx_id']);
            $table->dropIndex(['invoice_number']);
            
            // Drop columns
            $table->dropColumn([
                'bkash_payment_id',
                'bkash_trx_id',
                'invoice_number',
                'customer_msisdn'
            ]);
        });
    }
};
