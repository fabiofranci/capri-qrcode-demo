<?php

namespace App\Filament\Resources\PermitHolders\RelationManagers;

use App\Models\Vehicle;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;

class VehiclesRelationManager extends RelationManager
{
    protected static string $relationship = 'vehicles';

    protected static ?string $recordTitleAttribute = 'targa';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('targa')
                ->label('Targa')
                ->required()
                ->extraInputAttributes([
                    'style' => 'text-transform: uppercase',
                ])
                ->dehydrateStateUsing(fn (?string $state) => strtoupper($state))
                ->unique(table: Vehicle::class),

            TextInput::make('marca')
                ->label('Marca')
                ->nullable(),

            TextInput::make('modello')
                ->label('Modello')
                ->nullable(),

            TextInput::make('colore')
                ->label('Colore')
                ->nullable(),

            Textarea::make('note')
                ->label('Note')
                ->rows(4)
                ->nullable(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('targa')
                    ->label('Targa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('marca')
                    ->label('Marca')
                    ->sortable(),

                TextColumn::make('modello')
                    ->label('Modello')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
