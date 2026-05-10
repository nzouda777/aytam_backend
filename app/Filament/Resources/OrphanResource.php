<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrphanResource\Pages;
use App\Models\Orphan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrphanResource extends Resource
{
    protected static ?string $model = Orphan::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Gestion des Bénéficiaires';

    protected static ?string $navigationLabel = 'Orphelins';

    protected static ?string $modelLabel = 'Orphelin';

    protected static ?string $pluralModelLabel = 'Orphelins';

    protected static ?int $navigationSort = 1;

    public static function getGloballySearchableAttributes(): array
    {
        return ['first_name', 'last_name', 'family.widow_name'];
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string|\Illuminate\Contracts\Support\Htmlable
    {
        return $record->full_name;
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Famille' => $record->family->widow_name,
            'Sexe' => $record->gender === 'male' ? 'Masculin' : 'Féminin',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations Personnelles')
                    ->schema([
                        Forms\Components\Select::make('family_id')
                            ->label('Famille')
                            ->relationship('family', 'widow_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('first_name')
                            ->label('Prénom')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->label('Date de naissance')
                            ->required()
                            ->maxDate(now()),
                        Forms\Components\Select::make('gender')
                            ->label('Sexe')
                            ->options([
                                'male' => 'Masculin',
                                'female' => 'Féminin',
                            ])
                            ->required(),
                        Forms\Components\Toggle::make('is_sponsored')
                            ->label('Parrainé')
                            ->default(false),
                    ])->columns(2),

                Forms\Components\Section::make('Scolarité & Santé')
                    ->schema([
                        Forms\Components\TextInput::make('school_name')
                            ->label('Nom de l\'école')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('school_level')
                            ->label('Niveau scolaire')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('health_status')
                            ->label('État de santé')
                            ->rows(3),
                        Forms\Components\Textarea::make('special_needs')
                            ->label('Besoins spéciaux')
                            ->rows(3),
                    ])->columns(2),

                Forms\Components\Section::make('Photo')
                    ->schema([
                        Forms\Components\FileUpload::make('photo')
                            ->label('Photo de l\'orphelin')
                            ->image()
                            ->imageEditor()
                            ->directory('orphans')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Photo')
                    ->circular()
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=O&background=10b981&color=fff'),
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('family.widow_name')
                    ->label('Famille (Veuve)')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->label('Date de naissance')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('gender')
                    ->label('Sexe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'male' ? 'Masculin' : 'Féminin')
                    ->color(fn (string $state): string => $state === 'male' ? 'info' : 'danger'),
                Tables\Columns\TextColumn::make('school_level')
                    ->label('Niveau scolaire')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_sponsored')
                    ->label('Parrainé')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gender')
                    ->label('Sexe')
                    ->options([
                        'male' => 'Masculin',
                        'female' => 'Féminin',
                    ]),
                Tables\Filters\TernaryFilter::make('is_sponsored')
                    ->label('Parrainé'),
                Tables\Filters\SelectFilter::make('family_id')
                    ->label('Famille')
                    ->relationship('family', 'widow_name')
                    ->searchable()
                    ->preload(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrphans::route('/'),
            'create' => Pages\CreateOrphan::route('/create'),
            'edit' => Pages\EditOrphan::route('/{record}/edit'),
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
