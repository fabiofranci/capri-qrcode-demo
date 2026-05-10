<?php

namespace App\Filament\Resources\Vehicles\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\Action;

class PermitsRelationManager extends RelationManager
{
    protected static string $relationship = 'permits';

    protected static ?string $title = 'Storico permessi';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('valid_to', 'desc')

            ->columns([

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge(),

Tables\Columns\TextColumn::make('real_status')
    ->label('Stato')

    ->state(function ($record) {

        $result = $record->getValidationResult();

        return match ($result['reason']) {

            'expired' => 'SCADUTO',

            'revoked' => 'REVOCATO',

            'not_started' => 'NON ATTIVO',

            default => 'ATTIVO',
        };
    })

    ->badge()

    ->color(function ($record) {

        $result = $record->getValidationResult();

        return match ($result['reason']) {

            'expired' => 'danger',

            'revoked' => 'danger',

            'not_started' => 'warning',

            default => 'success',
        };
    }),
    
                Tables\Columns\TextColumn::make('valid_from')
                    ->label('Dal')
                    ->date(),

                Tables\Columns\TextColumn::make('valid_to')
                    ->label('Al')
                    ->date(),

                Tables\Columns\TextColumn::make('qr_token')
                    ->label('QR')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])

            ->actions([

                EditAction::make(),

                Action::make('verifica')
                    ->label('Verifica')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) =>
                        url("/verify/{$record->qr_token}")
                    )
                    ->openUrlInNewTab(),

                Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document')
                    ->url(fn ($record) =>
                        route('permits.pdf', [
                            'permit' => $record->id,
                        ])
                    )
                    ->openUrlInNewTab(),
            ]);
    }
}