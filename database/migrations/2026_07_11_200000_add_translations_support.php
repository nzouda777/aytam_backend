<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Colonnes converties au format JSON de spatie/laravel-translatable
     * ({"fr": "...", "en": "..."}). Les valeurs existantes sont enveloppées
     * dans la locale "fr" pour ne rien perdre.
     */
    private array $translatable = [
        'campaigns' => ['title', 'description'],
        'categories' => ['name', 'description'],
        'posts' => ['title', 'content'],
    ];

    public function up(): void
    {
        // Champs manquants pour le blog public
        Schema::table('posts', function (Blueprint $table) {
            $table->text('excerpt')->nullable()->after('slug');
            $table->string('author')->nullable()->after('content');
            $table->text('category')->nullable()->after('author');
            $table->string('read_time')->nullable()->after('category');
        });

        // Les varchar(255) sont trop courts pour du JSON bilingue
        Schema::table('campaigns', function (Blueprint $table) {
            $table->text('title')->change();
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->text('name')->change();
        });
        Schema::table('posts', function (Blueprint $table) {
            $table->text('title')->change();
        });

        foreach ($this->translatable as $tableName => $columns) {
            foreach (DB::table($tableName)->get() as $row) {
                $updates = [];
                foreach ($columns as $column) {
                    $value = $row->{$column};
                    if ($value === null || $value === '') {
                        continue;
                    }
                    $decoded = json_decode($value, true);
                    if (is_array($decoded)) {
                        continue; // déjà au format traductions
                    }
                    $updates[$column] = json_encode(['fr' => $value], JSON_UNESCAPED_UNICODE);
                }
                if ($updates) {
                    DB::table($tableName)->where('id', $row->id)->update($updates);
                }
            }
        }
    }

    public function down(): void
    {
        foreach ($this->translatable as $tableName => $columns) {
            foreach (DB::table($tableName)->get() as $row) {
                $updates = [];
                foreach ($columns as $column) {
                    $decoded = json_decode($row->{$column} ?? '', true);
                    if (is_array($decoded)) {
                        $updates[$column] = $decoded['fr'] ?? reset($decoded);
                    }
                }
                if ($updates) {
                    DB::table($tableName)->where('id', $row->id)->update($updates);
                }
            }
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['excerpt', 'author', 'category', 'read_time']);
        });
    }
};
