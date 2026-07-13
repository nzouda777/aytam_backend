<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->text('title');    // traduit ({"fr":..,"en":..})
            $table->text('excerpt');  // traduit
            $table->longText('content'); // traduit, HTML
            $table->string('icon')->nullable(); // nom d'icône lucide (Heart, GraduationCap...)
            $table->string('image')->nullable();
            // Catégorie de campagnes liée : la page du programme affiche ses campagnes
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            // Action mise en avant sur la page du programme
            $table->enum('cta_type', ['donate', 'sponsor'])->default('donate');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
