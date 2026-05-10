<?php

namespace App\Filament\Resources\Permits;

use App\Filament\Resources\Permits\Pages;
use App\Models\Permit;
use App\Models\PermitHolder;
use App\Models\Vehicle;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PermitResource extends Resource
{
    protected static ?string $model = Permit::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';
    protected static ?string $navigationLabel = 'Permessi';
    protected static ?string $modelLabel = 'Permesso';
    protected static ?string $pluralModelLabel = 'Permessi';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        return match (request()->get('filter')) {
            'scaduti' => $query->where('valid_to', '<', now()),
            'in_scadenza' => $query->whereBetween('valid_to', [
                now(),
                now()->addDays(30),
            ]),
            'attivi' => $query->where('valid_to', '>', now()->addDays(30)),
            default => $query,
        };
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Dati')
                ->schema([
                    Select::make('vehicle_id')
                        ->default(request()->get('vehicle_id'))
                        ->label('Veicolo')
                        ->relationship('vehicle', 'targa')
                        ->getOptionLabelFromRecordUsing(fn (Vehicle $record): string =>
                            trim($record->targa . ' - ' . ($record->marca ?? '') . ' ' . ($record->modello ?? ''))
                        )
                        ->searchable()
                        ->preload()
                        ->live()
                        ->required()
                        ->createOptionForm([
                            Select::make('permit_holder_id')
                                ->label('Intestatario')
                                ->relationship('permitHolder', 'cognome')
                                ->getOptionLabelFromRecordUsing(fn (PermitHolder $record): string =>
                                    trim($record->cognome . ' ' . $record->nome)
                                )
                                ->searchable()
                                ->preload()
                                ->required()
                                ->createOptionForm([
                                    TextInput::make('nome')
                                        ->required(),

                                    TextInput::make('cognome')
                                        ->required(),

                                    TextInput::make('email')
                                        ->email(),

                                    TextInput::make('telefono'),

                                    TextInput::make('codice_fiscale')
                                        ->extraInputAttributes([
                                            'style' => 'text-transform: uppercase',
                                        ])
                                        ->dehydrateStateUsing(fn (?string $state): ?string =>
                                            $state ? strtoupper($state) : null
                                        ),
                                ])
                                ->createOptionUsing(function (array $data): int {
                                    return PermitHolder::create($data)->id;
                                })
                                ->createOptionAction(fn ($action) =>
                                    $action
                                        ->modalHeading('Nuovo intestatario')
                                        ->modalSubmitActionLabel('Crea')
                                ),

                            TextInput::make('targa')
                                ->label('Targa')
                                ->required()
                                ->extraInputAttributes([
                                    'style' => 'text-transform: uppercase',
                                ])
                                ->dehydrateStateUsing(fn (?string $state): ?string =>
                                    $state ? strtoupper($state) : null
                                ),

                            TextInput::make('marca')
                                ->label('Marca'),

                            TextInput::make('modello')
                                ->label('Modello'),
                        ])
                        ->createOptionUsing(function (array $data): int {
                            $data['targa'] = strtoupper($data['targa']);

                            return Vehicle::create($data)->id;
                        })
                        ->createOptionAction(fn ($action) =>
                            $action
                                ->modalHeading('Nuovo veicolo')
                                ->modalSubmitActionLabel('Crea')
                        ),

                    Placeholder::make('holder')
                        ->label('Intestatario')
                        ->content(function (Get $get): string {
                            $vehicle = Vehicle::with('permitHolder')
                                ->find($get('vehicle_id'));

                            return trim(
                                ($vehicle?->permitHolder?->nome ?? '') . ' ' .
                                ($vehicle?->permitHolder?->cognome ?? '')
                            ) ?: '-';
                        }),

                    Select::make('type')
                        ->label('Tipo')
                        ->options([
                            'NCC' => 'NCC',
                            'navetta' => 'Navetta',
                        ])
                        ->required(),

                    Select::make('status')
                        ->label('Stato')
                        ->options([
                            'active' => 'Attivo',
                            'revoked' => 'Revocato',
                        ])
                        ->default('active')
                        ->required(),
                ])
                ->columns(2),

            Section::make('Validità')
                ->schema([
                    DatePicker::make('valid_from')
                        ->label('Valido dal')
                        ->required(),

                    DatePicker::make('valid_to')
                        ->label('Valido al')
                        ->required(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('valid_to', 'desc')
            ->columns([
                TextColumn::make('plate')
                    ->label('Targa')
                    ->searchable(),

                TextColumn::make('holder')
                    ->label('Intestatario')
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge(),

                BadgeColumn::make('status_real')
                    ->label('Stato')
                    ->getStateUsing(fn (Permit $record) => $record->getValidationResult()['status'])
                    ->colors([
                        'success' => 'valid',
                        'danger' => 'invalid',
                    ]),

                TextColumn::make('reason')
                    ->label('Motivo')
                    ->getStateUsing(function (Permit $record) {
                        return match ($record->getValidationResult()['reason']) {
                            'revoked' => 'Revocato',
                            'expired' => 'Scaduto',
                            'not_started' => 'Non ancora valido',
                            default => '-',
                        };
                    })
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Revocato', 'Scaduto' => 'danger',
                        'Non ancora valido' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('valid_from')
                    ->label('Dal')
                    ->date()
                    ->toggleable(),

                TextColumn::make('valid_to')
                    ->label('Al')
                    ->date()
                    ->sortable(),

                TextColumn::make('qr_token')
                    ->label('QR Token')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Stato manuale')
                    ->options([
                        'active' => 'Attivo',
                        'revoked' => 'Revocato',
                    ]),

                Filter::make('expired')
                    ->label('Scaduti')
                    ->query(fn ($query) =>
                        $query->whereDate('valid_to', '<', now())
                    ),

                Filter::make('not_started')
                    ->label('Non ancora validi')
                    ->query(fn ($query) =>
                        $query->whereDate('valid_from', '>', now())
                    ),
            ])
            ->actions([
                EditAction::make(),

                Action::make('verifica')
                    ->label('Verifica')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Permit $record) => url("/verify/{$record->qr_token}"))
                    ->openUrlInNewTab(),

                Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document')
                    ->url(fn (Permit $record) => route('permits.pdf', [
                        'permit' => $record->id,
                    ]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                BulkAction::make('revoke')
                    ->label('Revoca')
                    ->requiresConfirmation()
                    ->action(fn ($records) =>
                        $records->each->update(['status' => 'revoked'])
                    ),

                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermits::route('/'),
            'create' => Pages\CreatePermit::route('/create'),
            'edit' => Pages\EditPermit::route('/{record}/edit'),
        ];
    }
}