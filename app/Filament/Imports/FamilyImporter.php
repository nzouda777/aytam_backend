<?php

namespace App\Filament\Imports;

use App\Models\Family;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Validation\Rule;

class FamilyImporter extends Importer
{
    protected static ?string $model = Family::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('widow_name')
                ->label('Nom de la veuve')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('widow_phone')
                ->label('Téléphone')
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('widow_email')
                ->label('Email')
                ->rules(['nullable', 'email', 'max:255']),
            ImportColumn::make('widow_date_of_birth')
                ->label('Date de naissance')
                ->rules(['nullable', 'date']),
            ImportColumn::make('address')
                ->label('Adresse')
                ->rules(['required', 'string']),
            ImportColumn::make('city')
                ->label('Ville')
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('region')
                ->label('Région')
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('status')
                ->label('Statut')
                ->rules(['required', Rule::in(['active', 'inactive', 'pending'])]),
            ImportColumn::make('registration_date')
                ->label('Date d\'inscription')
                ->rules(['nullable', 'date']),
        ];
    }

    public function resolveRecord(): ?Family
    {
        return Family::firstOrNew([
            'widow_name' => $this->data['widow_name'],
            'widow_phone' => $this->data['widow_phone'] ?? null,
        ]);
    }

    public function getValidationMessages(): array
    {
        return [
            'widow_name.required' => 'Le nom de la veuve est obligatoire.',
            'address.required' => 'L\'adresse est obligatoire pour la distribution.',
            'city.required' => 'La ville est requise.',
            'status.required' => 'Le statut doit être spécifié (active, inactive, pending).',
            'status.in' => 'Le statut choisi est invalide.',
            'widow_email.email' => 'L\'adresse email n\'est pas valide.',
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Votre importation de familles est terminée et ' . number_format($import->successful_rows) . ' ' . str('ligne')->plural($import->successful_rows) . ' ont été importées.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('ligne')->plural($failedRowsCount) . ' n\'ont pas pu être importées.';
        }

        return $body;
    }
}
