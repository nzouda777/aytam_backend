<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add sponsorship_type to sponsorships table
        Schema::table('sponsorships', function (Blueprint $table) {
            $table->enum('sponsorship_type', ['orphan', 'widow', 'family'])
                ->default('family')
                ->after('user_id');
        });

        // 2. Make family_id and orphan_id nullable
        Schema::table('sponsorships', function (Blueprint $table) {
            $table->unsignedBigInteger('family_id')->nullable()->change();
            $table->unsignedBigInteger('orphan_id')->nullable()->change();
        });

        // 3. Add 'pending' to sponsorships status enum
        DB::statement("ALTER TABLE sponsorships MODIFY COLUMN status ENUM('active', 'paused', 'completed', 'cancelled', 'pending') DEFAULT 'pending'");

        // 4. Add 'widow_sponsorship' to donations payment_type enum
        DB::statement("ALTER TABLE donations MODIFY COLUMN payment_type ENUM('campaign_donation', 'family_sponsorship', 'orphan_sponsorship', 'widow_sponsorship') DEFAULT 'campaign_donation'");
    }

    public function down(): void
    {
        Schema::table('sponsorships', function (Blueprint $table) {
            $table->dropColumn('sponsorship_type');
        });

        DB::statement("ALTER TABLE sponsorships MODIFY COLUMN status ENUM('active', 'paused', 'completed', 'cancelled') DEFAULT 'active'");

        DB::statement("ALTER TABLE donations MODIFY COLUMN payment_type ENUM('campaign_donation', 'family_sponsorship', 'orphan_sponsorship') DEFAULT 'campaign_donation'");
    }
};
