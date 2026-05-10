<?php

namespace App\Filament\Widgets;

use App\Models\Permit;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class PermitsExpiringWidget extends BaseWidget
{
    protected static ?string $heading = 'Permessi in scadenza (30 giorni)';

    protected function getTableQuery(): Builder
    {
        return Permit::query()
            ->whereNotNull('valid_to')
            ->whereBetween('valid_to', [
                now()->startOfDay(),
                now()->addDays(30)->endOfDay(),
            ])
            ->with(['permitHolder', 'vehicle']);
    }

    protected function getTableColumns(): array
    {
        return [

            Tables\Columns\TextColumn::make('intestatario')
                ->label('Intestatario')
                ->getStateUsing(fn ($record) =>
                    trim(
                        ($record->permitHolder->nome ?? '') . ' ' .
                        ($record->permitHolder->cognome ?? '')
                    )
                ),

            Tables\Columns\TextColumn::make('vehicle.targa')
                ->label('Targa'),

            Tables\Columns\TextColumn::make('valid_to')
                ->label('Scadenza')
                ->date('d/m/Y'),

            Tables\Columns\BadgeColumn::make('stato')
                ->label('Stato')
                ->getStateUsing(function ($record) {

                    $days = now()->diffInDays($record->valid_to, false);

                    if ($days < 0) {
                        return 'Scaduto';
                    }

                    if ($days <= 7) {
                        return 'Urgente';
                    }

                    return 'In scadenza';
                })
                ->colors([
                    'danger' => 'Scaduto',
                    'warning' => 'Urgente',
                    'info' => 'In scadenza',
                ]),

            Tables\Columns\TextColumn::make('pdf')
                ->label('QR PDF')
                ->state('Apri PDF')
                ->url(fn ($record) => route('permits.pdf', [
                    'permit' => $record->id,
                ]))
                ->openUrlInNewTab()
                ->color('primary'),

            Tables\Columns\TextColumn::make('verify')
                ->label('Verifica')
                ->state('Verifica')
                ->url(fn ($record) => route('verify.show', [
                    'token' => $record->qr_token,
                ]))
                ->openUrlInNewTab()
                ->color('success'),

                ];
    }
}