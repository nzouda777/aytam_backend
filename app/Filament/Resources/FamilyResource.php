<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FamilyResource\Pages;
use App\Filament\Resources\FamilyResource\RelationManagers;
use App\Models\Family;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FamilyResource extends Resource
{
    protected static ?string $model = Family::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationGroup = 'Gestion des Bénéficiaires';

    protected static ?string $navigationLabel = 'Familles (Veuves)';

    protected static ?string $modelLabel = 'Famille';

    protected static ?string $pluralModelLabel = 'Familles';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de la Veuve')
                    ->schema([
                        Forms\Components\TextInput::make('widow_name')
                            ->label('Nom de la veuve')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('widow_phone')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('widow_email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('widow_date_of_birth')
                            ->label('Date de naissance')
                            ->maxDate(now()),
                        Forms\Components\FileUpload::make('widow_photo')
                            ->label('Photo de la veuve')
                            ->image()
                            ->imageEditor()
                            ->directory('widows')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Adresse')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->label('Adresse')
                            ->required()
                            ->rows(2),
                        Forms\Components\TextInput::make('city')
                            ->label('Ville')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('region')
                            ->label('Région')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Détails')
                    ->schema([
                        Forms\Components\TextInput::make('family_code')
                            ->label('Code famille')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Généré automatiquement'),
                        Forms\Components\TextInput::make('orphans_count')
                            ->label('Nombre d\'orphelins')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'pending' => 'En attente',
                            ])
                            ->default('pending')
                            ->required(),
                        Forms\Components\DatePicker::make('registration_date')
                            ->label('Date d\'inscription')
                            ->default(now())
                            ->required(),
                        Forms\Components\TextInput::make('needs')
                            ->label('Besoins')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('total_needs')
                            ->label('Total des besoins (FCFA)')
                            ->numeric()
                            ->prefix('FCFA')
                            ->default(0),
                        Forms\Components\TextInput::make('total_received')
                            ->label('Total reçu (FCFA)')
                            ->numeric()
                            ->prefix('FCFA')
                            ->default(0),
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
                Tables\Columns\ImageColumn::make('widow_photo')
                    ->label('Photo')
                    ->circular()
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=V&background=f43f5e&color=fff'),
                Tables\Columns\TextColumn::make('family_code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('widow_name')
                    ->label('Nom de la veuve')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('city')
                    ->label('Ville')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('orphans_count')
                    ->label('Orphelins')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'pending' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('total_needs')
                    ->label('Besoins')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' FCFA')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_received')
                    ->label('Reçu')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' FCFA')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('registration_date')
                    ->label('Inscrite le')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'pending' => 'En attente',
                    ]),
                Tables\Filters\SelectFilter::make('city')
                    ->label('Ville'),
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
        return [
            RelationManagers\OrphansRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFamilies::route('/'),
            'create' => Pages\CreateFamily::route('/create'),
            'edit' => Pages\EditFamily::route('/{record}/edit'),
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
        return static::getModel()::count();
    }
}
