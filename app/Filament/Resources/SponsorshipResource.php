<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SponsorshipResource\Pages;
use App\Models\Sponsorship;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SponsorshipResource extends Resource
{
    protected static ?string $model = Sponsorship::class;

    protected static ?string $navigationIcon = 'heroicon-o-hand-raised';

    protected static ?string $navigationGroup = 'Gestion Financière';

    protected static ?string $navigationLabel = 'Parrainages';

    protected static ?string $modelLabel = 'Parrainage';

    protected static ?string $pluralModelLabel = 'Parrainages';

    protected static ?int $navigationSort = 2;

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'family.widow_name', 'orphan.first_name'];
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string|\Illuminate\Contracts\Support\Htmlable
    {
        return "Parrainage de " . ($record->name ?? $record->user?->name ?? 'N/A');
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        $details = [
            'Type' => $record->sponsorship_type === 'orphan' ? 'Orphelin' : 'Famille',
        ];

        if ($record->sponsorship_type === 'orphan' && $record->orphan) {
            $details['Bénéficiaire'] = $record->orphan->full_name;
        } elseif ($record->sponsorship_type === 'family' && $record->family) {
            $details['Bénéficiaire'] = $record->family->widow_name;
        }

        return $details;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du Parrainage')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Parrain / Donateur')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\TextInput::make('name')
                            ->label('Nom du parrain')
                            ->maxLength(255),
                        Forms\Components\Select::make('sponsorship_type')
                            ->label('Type de parrainage')
                            ->options([
                                'orphan' => 'Orphelin',
                                'family' => 'Famille / Veuve',
                            ])
                            ->reactive()
                            ->required(),
                        Forms\Components\Select::make('family_id')
                            ->label('Famille')
                            ->relationship('family', 'widow_name')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Forms\Get $get) => $get('sponsorship_type') === 'family')
                            ->nullable(),
                        Forms\Components\Select::make('orphan_id')
                            ->label('Orphelin')
                            ->relationship('orphan', 'first_name')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Forms\Get $get) => $get('sponsorship_type') === 'orphan')
                            ->nullable(),
                    ])->columns(2),

                Forms\Components\Section::make('Détails Financiers')
                    ->schema([
                        Forms\Components\TextInput::make('monthly_amount')
                            ->label('Montant mensuel (FCFA)')
                            ->numeric()
                            ->required()
                            ->prefix('FCFA'),
                        Forms\Components\Select::make('payment_frequency')
                            ->label('Fréquence de paiement')
                            ->options([
                                'monthly' => 'Mensuel',
                                'quarterly' => 'Trimestriel',
                                'yearly' => 'Annuel',
                            ])
                            ->default('monthly'),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Date de début')
                            ->required()
                            ->default(now()),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Date de fin')
                            ->nullable(),
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'active' => 'Actif',
                                'paused' => 'En pause',
                                'completed' => 'Terminé',
                                'cancelled' => 'Annulé',
                            ])
                            ->default('active')
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Parrain')
                    ->searchable()
                    ->sortable()
                    ->default(fn ($record) => $record->user?->name ?? 'N/A'),
                Tables\Columns\TextColumn::make('sponsorship_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'orphan' => 'Orphelin',
                        'family' => 'Famille',
                        default => $state ?? 'N/A',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'orphan' => 'info',
                        'family' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('family.widow_name')
                    ->label('Famille / Veuve')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('orphan.first_name')
                    ->label('Orphelin')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('monthly_amount')
                    ->label('Montant mensuel')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' FCFA')
                    ->sortable()
                    ->color('success'),
                Tables\Columns\TextColumn::make('payment_frequency')
                    ->label('Fréquence')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'monthly' => 'Mensuel',
                        'quarterly' => 'Trimestriel',
                        'yearly' => 'Annuel',
                        default => $state ?? 'N/A',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'paused' => 'warning',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Actif',
                        'paused' => 'En pause',
                        'completed' => 'Terminé',
                        'cancelled' => 'Annulé',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Début')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'active' => 'Actif',
                        'paused' => 'En pause',
                        'completed' => 'Terminé',
                        'cancelled' => 'Annulé',
                    ]),
                Tables\Filters\SelectFilter::make('sponsorship_type')
                    ->label('Type')
                    ->options([
                        'orphan' => 'Orphelin',
                        'family' => 'Famille',
                    ]),
                Tables\Filters\SelectFilter::make('payment_frequency')
                    ->label('Fréquence')
                    ->options([
                        'monthly' => 'Mensuel',
                        'quarterly' => 'Trimestriel',
                        'yearly' => 'Annuel',
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSponsorships::route('/'),
            'create' => Pages\CreateSponsorship::route('/create'),
            'edit' => Pages\EditSponsorship::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'active')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
