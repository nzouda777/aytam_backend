<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramResource\Pages;
use App\Models\Program;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProgramResource extends Resource
{
    use Translatable;

    protected static ?string $model = Program::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Programmes';

    protected static ?string $modelLabel = 'Programme';

    protected static ?string $pluralModelLabel = 'Programmes';

    protected static ?string $navigationGroup = 'Contenu';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Programme')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Program::class, 'slug', ignoreRecord: true)
                            ->helperText('Identifiant d\'URL : /programs/{slug}'),

                        Forms\Components\Textarea::make('excerpt')
                            ->label('Extrait')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('content')
                            ->label('Contenu')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Présentation & liens')
                    ->schema([
                        Forms\Components\Select::make('icon')
                            ->label('Icône')
                            ->options([
                                'Heart' => 'Cœur',
                                'HandHeart' => 'Main et cœur',
                                'Home' => 'Maison',
                                'GraduationCap' => 'Éducation',
                                'Siren' => 'Urgence',
                                'Coins' => 'Zakat / dons',
                                'Users' => 'Communauté',
                                'Shield' => 'Protection',
                            ])
                            ->default('Heart'),

                        Forms\Components\FileUpload::make('image')
                            ->label('Image de couverture')
                            ->image()
                            ->directory('programs'),

                        Forms\Components\Select::make('category_id')
                            ->label('Catégorie de campagnes liée')
                            ->relationship('category', 'name')
                            // La colonne name est du JSON traduit : on affiche la locale courante
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                            ->helperText('Les campagnes de cette catégorie s\'affichent sur la page du programme')
                            ->nullable(),

                        Forms\Components\Select::make('cta_type')
                            ->label('Action mise en avant')
                            ->options([
                                'donate' => 'Faire un don',
                                'sponsor' => 'Parrainer',
                            ])
                            ->default('donate')
                            ->required(),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Ordre d\'affichage')
                            ->numeric()
                            ->default(0),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug'),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Catégorie liée'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrograms::route('/'),
            'create' => Pages\CreateProgram::route('/create'),
            'edit' => Pages\EditProgram::route('/{record}/edit'),
        ];
    }
}
