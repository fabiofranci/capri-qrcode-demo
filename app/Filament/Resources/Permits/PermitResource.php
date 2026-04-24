<?php

namespace App\Filament\Resources\Permits;

use App\Filament\Resources\Permits\Pages;
use App\Models\Permit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

// Forms (v4: componenti sotto Forms\Components)
use Filament\Schemas\Components\Section;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;

// Tables
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;

use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;

use Barryvdh\DomPDF\Facade\Pdf;


class PermitResource extends Resource
{
    protected static ?string $model = Permit::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';
    protected static ?string $navigationLabel = 'Permessi';
    protected static ?string $modelLabel = 'Permesso';
    protected static ?string $pluralModelLabel = 'Permessi';

    public function downloadPdf($id)
    {
        $permit = Permit::findOrFail($id);

        $pdf = Pdf::loadView('pdf.permit_badge', compact('permit'));

        return $pdf->download('permesso_'.$permit->plate.'.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | FORM (Filament v4)
    |--------------------------------------------------------------------------
    */

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Dati')
                ->schema([
                    TextInput::make('plate')
                        ->label('Targa')
                        ->required(),

                    TextInput::make('holder')
                        ->label('Intestatario')
                        ->required(),

                    Select::make('type')
                        ->options([
                            'NCC' => 'NCC',
                            'navetta' => 'Navetta',
                        ])
                        ->required(),

                    Select::make('status')
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

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('valid_to', 'desc')
            ->columns([
                TextColumn::make('plate')
                    ->label('Targa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('holder')
                    ->label('Intestatario')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge(),

                // Stato reale
                BadgeColumn::make('status_real')
                    ->label('Stato')
                    ->getStateUsing(fn (Permit $record) => $record->getValidationResult()['status'])
                    ->colors([
                        'success' => 'valid',
                        'danger' => 'invalid',
                    ]),

                // Motivo
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
                        'Revocato' => 'danger',
                        'Scaduto' => 'danger',
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
                    ->url(fn ($record) => route('permits.pdf', $record->id))
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

    /*
    |--------------------------------------------------------------------------
    | PAGES
    |--------------------------------------------------------------------------
    */

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermits::route('/'),
            'create' => Pages\CreatePermit::route('/create'),
            'edit' => Pages\EditPermit::route('/{record}/edit'),
        ];
    }
}