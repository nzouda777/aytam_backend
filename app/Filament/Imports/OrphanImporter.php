<?php

namespace App\Filament\Imports;

use App\Models\Orphan;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Validation\Rule;

class OrphanImporter extends Importer
{
    protected static ?string $model = Orphan::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('first_name')
                ->label('Prénom')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('last_name')
                ->label('Nom')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('date_of_birth')
                ->label('Date de naissance')
                ->rules(['nullable', 'date']),
            ImportColumn::make('gender')
                ->label('Sexe')
                ->rules(['required', Rule::in(['male', 'female'])]),
            ImportColumn::make('family')
                ->label('Famille (Veuve)')
                ->relationship(lookupColumn: 'widow_name')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('school_name')
                ->label('Nom de l\'école')
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('school_level')
                ->label('Niveau scolaire')
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('health_status')
                ->label('État de santé')
                ->rules(['nullable', 'string']),
            ImportColumn::make('special_needs')
                ->label('Besoins spéciaux')
                ->rules(['nullable', 'string']),
            ImportColumn::make('is_sponsored')
                ->label('Parrainé')
                ->boolean()
                ->rules(['required', 'boolean']),
        ];
    }

    public function resolveRecord(): ?Orphan
    {
        return Orphan::firstOrNew([
            'first_name' => $this->data['first_name'],
            'last_name' => $this->data['last_name'],
            'date_of_birth' => $this->data['date_of_birth'] ?? null,
        ]);
    }

    public function getValidationMessages(): array
    {
        return [
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'gender.required' => 'Le sexe est obligatoire (male/female).',
            'gender.in' => 'Le sexe doit être "male" ou "female".',
            'family.required' => 'Le lien avec une famille (veuve) est obligatoire.',
            'is_sponsored.required' => 'L\'état de parrainage (0 ou 1) est requis.',
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Votre importation d\'orphelins est terminée et ' . number_format($import->successful_rows) . ' ' . str('ligne')->plural($import->successful_rows) . ' ont été importées.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('ligne')->plural($failedRowsCount) . ' n\'ont pas pu être importées.';
        }

        return $body;
    }
}
