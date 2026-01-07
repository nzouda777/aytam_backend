<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            // Add payment type enum
            $table->enum('payment_type', ['campaign_donation', 'family_sponsorship', 'orphan_sponsorship'])
                ->default('campaign_donation')
                ->after('campaign_id');
            
            // Add polymorphic relationship fields
            $table->string('payable_type')->nullable()->after('payment_type');
            $table->unsignedBigInteger('payable_id')->nullable()->after('payable_type');
            
            // Add index for polymorphic relationship
            $table->index(['payable_type', 'payable_id']);
        });
    }

    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropIndex(['payable_type', 'payable_id']);
            $table->dropColumn(['payment_type', 'payable_type', 'payable_id']);
        });
    }
};
