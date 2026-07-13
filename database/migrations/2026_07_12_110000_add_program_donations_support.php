<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->foreignId('program_id')
                ->nullable()
                ->after('campaign_id')
                ->constrained()
                ->nullOnDelete();
        });

        // Nouveau type de paiement pour les dons faits directement à un programme
        DB::statement("ALTER TABLE donations MODIFY COLUMN payment_type ENUM('campaign_donation', 'family_sponsorship', 'orphan_sponsorship', 'program_donation') NOT NULL DEFAULT 'campaign_donation'");
    }

    public function down(): void
    {
        DB::statement("UPDATE donations SET payment_type = 'campaign_donation' WHERE payment_type = 'program_donation'");
        DB::statement("ALTER TABLE donations MODIFY COLUMN payment_type ENUM('campaign_donation', 'family_sponsorship', 'orphan_sponsorship') NOT NULL DEFAULT 'campaign_donation'");

        Schema::table('donations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('program_id');
        });
    }
};
