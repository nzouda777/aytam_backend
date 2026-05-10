<?php

namespace App\Filament\Widgets;

use App\Models\Donation;
use App\Models\Sponsorship;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestDonations extends BaseWidget
{
    protected static ?string $heading = 'Dernières Transactions';

    protected static ?int $sort = 9;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Donation::query()->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('Transaction')
                    ->searchable()
                    ->copyable()
                    ->weight('bold')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('donor_name')
                    ->label('Donateur')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->is_anonymous) return '🔒 Anonyme';
                        return $state ?? 'N/A';
                    }),
                Tables\Columns\TextColumn::make('campaign.title')
                    ->label('Campagne')
                    ->limit(25),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Montant')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' FCFA')
                    ->color('success')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Méthode')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'mobile_money' => 'success',
                        'card' => 'info',
                        'bank_transfer' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'mobile_money' => '📱 Mobile Money',
                        'card' => '💳 Carte',
                        'bank_transfer' => '🏦 Virement',
                        'cash' => '💵 Espèces',
                        default => $state ?? 'N/A',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'refunded' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completed' => '✅ Complété',
                        'pending' => '⏳ En attente',
                        'failed' => '❌ Échoué',
                        'refunded' => '↩️ Remboursé',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }
}
