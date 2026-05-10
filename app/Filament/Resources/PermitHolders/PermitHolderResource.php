<?php

namespace App\Filament\Resources\PermitHolders;

use App\Filament\Resources\PermitHolders\Pages\CreatePermitHolder;
use App\Filament\Resources\PermitHolders\Pages\EditPermitHolder;
use App\Filament\Resources\PermitHolders\Pages\ListPermitHolders;
use App\Filament\Resources\PermitHolders\RelationManagers\VehiclesRelationManager;
use App\Models\PermitHolder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;

class PermitHolderResource extends Resource
{
    protected static ?string $model = PermitHolder::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Titolari';
    protected static ?string $modelLabel = 'Titolare';
    protected static ?string $pluralModelLabel = 'Titolari';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Dati')
                ->schema([
                    TextInput::make('nome')
                        ->label('Nome')
                        ->required(),

                    TextInput::make('cognome')
                        ->label('Cognome')
                        ->required(),

                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->nullable(),

                    TextInput::make('telefono')
                        ->label('Telefono')
                        ->tel()
                        ->nullable(),

                    Textarea::make('note')
                        ->label('Note')
                        ->rows(4)
                        ->nullable(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cognome')
                    ->label('Cognome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('telefono')
                    ->label('Telefono')
                    ->toggleable(),

                TextColumn::make('vehicles_count')
                    ->label('Numero veicoli')
                    ->counts('vehicles')
                    ->sortable(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            VehiclesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPermitHolders::route('/'),
            'create' => CreatePermitHolder::route('/create'),
            'edit' => EditPermitHolder::route('/{record}/edit'),
        ];
    }
}
