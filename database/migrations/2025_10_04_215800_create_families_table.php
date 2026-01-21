<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->string('family_code')->unique(); // Code unique pour chaque famille
            $table->string('widow_name');
            $table->string('widow_phone')->nullable();
            $table->string('widow_email')->nullable();
            $table->date('widow_date_of_birth')->nullable();
            $table->text('address');
            $table->string('city');
            $table->string('region')->nullable();
            $table->integer('orphans_count')->default(0)->nullable();
            $table->string('needs')->nullable();
            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');
            $table->date('registration_date');
            $table->text('notes')->nullable();
            $table->decimal('total_needs', 12, 2)->default(0)->nullable();
            $table->decimal('total_received', 12, 2)->default(0)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('families');
    }
};