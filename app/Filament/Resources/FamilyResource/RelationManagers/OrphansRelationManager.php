<?php

namespace App\Filament\Resources\FamilyResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrphansRelationManager extends RelationManager
{
    protected static string $relationship = 'orphans';

    protected static ?string $title = 'Orphelins';

    protected static ?string $modelLabel = 'Orphelin';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
                    ->required(),
                Forms\Components\Select::make('gender')
                    ->label('Sexe')
                    ->options([
                        'male' => 'Masculin',
                        'female' => 'Féminin',
                    ])
                    ->required(),
                Forms\Components\FileUpload::make('photo')
                    ->label('Photo')
                    ->image()
                    ->directory('orphans')
                    ->visibility('public')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('school_name')
                    ->label('École'),
                Forms\Components\TextInput::make('school_level')
                    ->label('Niveau scolaire'),
                Forms\Components\Toggle::make('is_sponsored')
                    ->label('Parrainé'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('first_name')
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Photo')
                    ->circular(),
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Prénom'),
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Nom'),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->label('Naissance')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('gender')
                    ->label('Sexe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'male' ? 'M' : 'F'),
                Tables\Columns\IconColumn::make('is_sponsored')
                    ->label('Parrainé')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
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
}
