<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Filament\Resources\CampaignResource\RelationManagers;
use App\Models\Campaign;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Campagnes';

    protected static ?string $navigationLabel = 'Campagnes';

    protected static ?string $modelLabel = 'Campagne';

    protected static ?string $pluralModelLabel = 'Campagnes';

    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations Générales')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', \Illuminate\Support\Str::slug($state))),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Select::make('category_id')
                            ->label('Catégorie')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nom')
                                    ->required(),
                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->required(),
                                Forms\Components\ColorPicker::make('color')
                                    ->label('Couleur'),
                            ]),
                        Forms\Components\RichEditor::make('description')
                            ->label('Description')
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Image & Visibilité')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label('Image de la campagne')
                            ->image()
                            ->imageEditor()
                            ->directory('campaigns')
                            ->visibility('public')
                            ->maxSize(2048),
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Mise en avant')
                            ->helperText('Afficher sur la page d\'accueil'),
                        Forms\Components\Select::make('urgency')
                            ->label('Urgence')
                            ->options([
                                'normal' => 'Normal',
                                'urgent' => 'Urgent',
                            ])
                            ->default('normal')
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Objectif & Dates')
                    ->schema([
                        Forms\Components\TextInput::make('goal_amount')
                            ->label('Objectif (FCFA)')
                            ->numeric()
                            ->required()
                            ->prefix('FCFA')
                            ->minValue(0),
                        Forms\Components\TextInput::make('current_amount')
                            ->label('Montant collecté (FCFA)')
                            ->numeric()
                            ->default(0)
                            ->prefix('FCFA')
                            ->disabled()
                            ->dehydrated(true),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Date de début')
                            ->required()
                            ->default(now()),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Date de fin')
                            ->required()
                            ->after('start_date'),
                        Forms\Components\TextInput::make('beneficiaries_count')
                            ->label('Nombre de bénéficiaires')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'draft' => 'Brouillon',
                                'active' => 'Active',
                                'completed' => 'Terminée',
                                'cancelled' => 'Annulée',
                            ])
                            ->default('draft')
                            ->required(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=C&background=10b981&color=fff'),
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->limit(35)
                    ->tooltip(fn ($record) => $record->title),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('goal_amount')
                    ->label('Objectif')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' FCFA')
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_amount')
                    ->label('Collecté')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' FCFA')
                    ->color('success')
                    ->sortable(),
                Tables\Columns\ViewColumn::make('progress')
                    ->label('Progression')
                    ->view('filament.tables.columns.progress-bar'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'draft' => 'gray',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Active',
                        'draft' => 'Brouillon',
                        'completed' => 'Terminée',
                        'cancelled' => 'Annulée',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('urgency')
                    ->label('Urgent')
                    ->icon(fn (string $state): string => match ($state) {
                        'urgent' => 'heroicon-o-fire',
                        default => 'heroicon-o-minus',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'urgent' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('⭐')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('donations_count')
                    ->label('Dons')
                    ->counts('donations')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => $record->end_date < now() ? 'danger' : 'gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'draft' => 'Brouillon',
                        'active' => 'Active',
                        'completed' => 'Terminée',
                        'cancelled' => 'Annulée',
                    ]),
                Tables\Filters\SelectFilter::make('urgency')
                    ->label('Urgence')
                    ->options([
                        'normal' => 'Normal',
                        'urgent' => 'Urgent',
                    ]),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Catégorie')
                    ->relationship('category', 'name'),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Mise en avant'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('toggleFeatured')
                        ->label(fn ($record) => $record->is_featured ? 'Retirer vedette' : 'Mettre en vedette')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(fn ($record) => $record->update(['is_featured' => !$record->is_featured]))
                        ->requiresConfirmation(),
                    Tables\Actions\Action::make('activate')
                        ->label('Activer')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->visible(fn ($record) => $record->status === 'draft')
                        ->action(fn ($record) => $record->update(['status' => 'active']))
                        ->requiresConfirmation(),
                    Tables\Actions\DeleteAction::make(),
                ]),
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
        return [
            RelationManagers\DonationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'view' => Pages\ViewCampaign::route('/{record}'),
            'edit' => Pages\EditCampaign::route('/{record}/edit'),
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
