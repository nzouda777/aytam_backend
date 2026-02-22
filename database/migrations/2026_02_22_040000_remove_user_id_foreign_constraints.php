<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove foreign key constraints on user_id to allow storing beneficiary IDs (orphans/families)
        // that may not exist in the users table.
        
        Schema::table('sponsorships', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('donations', function (Blueprint $table) {
            if (Schema::hasColumn('donations', 'user_id')) {
                // Determine if there's a foreign key to drop
                // Note: We use raw SQL or try-catch if unsure, but since I created it, I know it's there.
                try {
                    $table->dropForeign(['user_id']);
                } catch (\Exception $e) {
                    // Ignore if it doesn't exist
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('sponsorships', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('donations', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }
};
