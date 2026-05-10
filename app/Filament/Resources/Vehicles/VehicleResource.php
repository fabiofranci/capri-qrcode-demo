<?php

namespace App\Filament\Resources\Vehicles;

use App\Filament\Resources\Vehicles\Pages\CreateVehicle;
use App\Filament\Resources\Vehicles\Pages\EditVehicle;
use App\Filament\Resources\Vehicles\Pages\ListVehicles;
use App\Models\Vehicle;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Veicoli';
    protected static ?string $modelLabel = 'Veicolo';
    protected static ?string $pluralModelLabel = 'Veicoli';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Dati')
                ->schema([
                    Select::make('permit_holder_id')
                        ->label('Titolare')
                        ->relationship('permitHolder', 'nome')
                        ->searchable()
                        ->required(),

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
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('targa')
                    ->label('Targa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('permitHolder.nome')
                    ->label('Intestatario')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('marca')
                    ->label('Marca')
                    ->sortable(),

                TextColumn::make('modello')
                    ->label('Modello')
                    ->sortable(),

                TextColumn::make('permesso_attivo')
                    ->label('Permesso attivo')

                    ->state(function ($record) {

                        return $record->activePermit?->type ?? 'NO';
                    })

                    ->badge()

                    ->color(function ($record) {

                        return $record->activePermit
                            ? 'success'
                            : 'danger';
                    }),

                    ])


            ->actions([

                \Filament\Actions\Action::make('permit')

                    ->label(function ($record) {

                        return $record->activePermit
                            ? 'Apri'
                            : 'Nuovo';
                    })

                    ->icon(function ($record) {

                        return $record->activePermit
                            ? 'heroicon-o-eye'
                            : 'heroicon-o-qr-code';
                    })

                    ->color(function ($record) {

                        return $record->activePermit
                            ? 'success'
                            : 'primary';
                    })

                    ->url(function ($record) {

                        // esiste già un permesso valido
                        if ($record->activePermit) {

                            return \App\Filament\Resources\Permits\Pages\EditPermit::getUrl([
                                'record' => $record->activePermit,
                            ]);
                        }

                        // crea nuovo permesso
                        return \App\Filament\Resources\Permits\Pages\CreatePermit::getUrl([
                            'vehicle_id' => $record->id,
                            'permit_holder_id' => $record->permit_holder_id,
                        ]);
                    }),

            ]);            

    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PermitsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVehicles::route('/'),
            'create' => CreateVehicle::route('/create'),
            'edit' => EditVehicle::route('/{record}/edit'),
        ];
    }
}
