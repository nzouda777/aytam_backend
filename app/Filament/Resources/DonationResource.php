<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DonationResource\Pages;
use App\Models\Donation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DonationResource extends Resource
{
    protected static ?string $model = Donation::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Gestion Financière';

    protected static ?string $navigationLabel = 'Dons';

    protected static ?string $modelLabel = 'Don';

    protected static ?string $pluralModelLabel = 'Dons';

    protected static ?int $navigationSort = 1;

    public static function getGloballySearchableAttributes(): array
    {
        return ['transaction_id', 'donor_name', 'donor_email'];
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string|\Illuminate\Contracts\Support\Htmlable
    {
        return "Don de " . ($record->is_anonymous ? "Anonyme" : ($record->donor_name ?? $record->user?->name ?? 'N/A'));
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Transaction' => $record->transaction_id,
            'Montant' => number_format($record->amount, 0, ',', ' ') . ' FCFA',
            'Date' => $record->payment_date?->format('d/m/Y'),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du Donateur')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Utilisateur')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\TextInput::make('donor_name')
                            ->label('Nom du donateur')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('donor_email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('donor_phone')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_anonymous')
                            ->label('Don anonyme')
                            ->default(false)
                            ->inline(false),
                        Forms\Components\Toggle::make('is_recurring')
                            ->label('Don récurrent')
                            ->default(false)
                            ->inline(false),
                    ])->columns(3),

                Forms\Components\Section::make('Détails du Don')
                    ->schema([
                        Forms\Components\Select::make('campaign_id')
                            ->label('Campagne')
                            ->relationship('campaign', 'title')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Montant (FCFA)')
                            ->numeric()
                            ->required()
                            ->prefix('FCFA')
                            ->minValue(100),
                        Forms\Components\Select::make('payment_method')
                            ->label('Méthode de paiement')
                            ->options([
                                'mobile_money' => 'Mobile Money',
                                'card' => 'Carte bancaire',
                                'bank_transfer' => 'Virement bancaire',
                                'cash' => 'Espèces',
                            ])
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'pending' => 'En attente',
                                'completed' => 'Complété',
                                'failed' => 'Échoué',
                                'refunded' => 'Remboursé',
                            ])
                            ->default('pending')
                            ->required(),
                        Forms\Components\TextInput::make('transaction_id')
                            ->label('ID Transaction')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Généré automatiquement')
                            ->visibleOn('edit'),
                        Forms\Components\DateTimePicker::make('payment_date')
                            ->label('Date de paiement')
                            ->default(now()),
                        Forms\Components\Textarea::make('message')
                            ->label('Message du donateur')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('Transaction')
                    ->searchable()
                    ->copyable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('donor_name')
                    ->label('Donateur')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->is_anonymous) return '🔒 Anonyme';
                        return $state ?? $record->user?->name ?? 'N/A';
                    }),
                Tables\Columns\TextColumn::make('campaign.title')
                    ->label('Campagne')
                    ->searchable()
                    ->limit(25)
                    ->tooltip(fn ($record) => $record->campaign?->title),
                Tables\Columns\TextColumn::make('program.title')
                    ->label('Programme')
                    ->limit(25)
                    ->toggleable()
                    ->tooltip(fn ($record) => $record->program?->title),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Montant')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' FCFA')
                    ->sortable()
                    ->color('success')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Méthode')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'mobile_money' => 'success',
                        'card' => 'info',
                        'bank_transfer' => 'warning',
                        'cash' => 'gray',
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
                Tables\Columns\IconColumn::make('is_recurring')
                    ->label('🔄')
                    ->boolean()
                    ->tooltip('Don récurrent'),
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->groups([
                Tables\Grouping\Group::make('campaign.category_id')
                    ->label('Catégorie')
                    ->getTitleFromRecordUsing(fn ($record) => $record->campaign?->category?->name ?? 'Sans catégorie'),
                Tables\Grouping\Group::make('program_id')
                    ->label('Programme')
                    ->getTitleFromRecordUsing(fn ($record) => $record->program?->title ?? 'Hors programme'),
                Tables\Grouping\Group::make('payment_type')
                    ->label('Type de don')
                    ->getTitleFromRecordUsing(fn ($record) => match ($record->payment_type) {
                        'campaign_donation' => 'Don à une campagne',
                        'program_donation' => 'Don à un programme',
                        'family_sponsorship' => 'Parrainage de famille',
                        'orphan_sponsorship' => 'Parrainage d\'orphelin',
                        default => $record->payment_type,
                    }),
                Tables\Grouping\Group::make('created_at')
                    ->label('Mois')
                    ->date()
                    ->getTitleFromRecordUsing(fn ($record) => $record->created_at->translatedFormat('F Y'))
                    ->getKeyFromRecordUsing(fn ($record) => $record->created_at->format('Y-m')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'completed' => 'Complété',
                        'pending' => 'En attente',
                        'failed' => 'Échoué',
                        'refunded' => 'Remboursé',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Méthode')
                    ->options([
                        'mobile_money' => 'Mobile Money',
                        'card' => 'Carte bancaire',
                        'bank_transfer' => 'Virement',
                        'cash' => 'Espèces',
                    ]),
                Tables\Filters\SelectFilter::make('campaign_id')
                    ->label('Campagne')
                    ->relationship('campaign', 'title')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('program_id')
                    ->label('Programme')
                    ->options(fn () => \App\Models\Program::all()->pluck('title', 'id')),
                Tables\Filters\SelectFilter::make('category')
                    ->label('Catégorie')
                    ->options(fn () => \App\Models\Category::all()->pluck('name', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $q, $categoryId) => $q->whereHas('campaign', fn (Builder $c) => $c->where('category_id', $categoryId))
                        );
                    }),
                Tables\Filters\SelectFilter::make('payment_type')
                    ->label('Type de don')
                    ->options([
                        'campaign_donation' => 'Don à une campagne',
                        'program_donation' => 'Don à un programme',
                        'family_sponsorship' => 'Parrainage de famille',
                        'orphan_sponsorship' => 'Parrainage d\'orphelin',
                    ]),
                Tables\Filters\TernaryFilter::make('is_recurring')
                    ->label('Récurrent'),
                Tables\Filters\TernaryFilter::make('is_anonymous')
                    ->label('Anonyme'),
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Du'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Au'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) $indicators['from'] = 'Du ' . \Carbon\Carbon::parse($data['from'])->format('d/m/Y');
                        if ($data['until'] ?? null) $indicators['until'] = 'Au ' . \Carbon\Carbon::parse($data['until'])->format('d/m/Y');
                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('markCompleted')
                        ->label('Marquer complété')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn ($record) => $record->status === 'pending')
                        ->action(fn ($record) => $record->update(['status' => 'completed', 'payment_date' => now()]))
                        ->requiresConfirmation(),
                    Tables\Actions\Action::make('markFailed')
                        ->label('Marquer échoué')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn ($record) => $record->status === 'pending')
                        ->action(fn ($record) => $record->update(['status' => 'failed']))
                        ->requiresConfirmation(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->striped();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDonations::route('/'),
            'create' => Pages\CreateDonation::route('/create'),
            'edit' => Pages\EditDonation::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
