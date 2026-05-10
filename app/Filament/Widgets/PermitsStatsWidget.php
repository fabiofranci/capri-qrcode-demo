<?php

namespace App\Filament\Widgets;

use App\Models\Permit;
use App\Models\PermitHolder;
use App\Models\Vehicle;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PermitsStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $now = now();

        $totaleTitolari = PermitHolder::count();
        $totaleVeicoli = Vehicle::count();

        $attivi = Permit::query()
            ->where('valid_to', '>', $now->copy()->addDays(30))
            ->count();

        $inScadenza = Permit::query()
            ->whereBetween('valid_to', [
                $now,
                $now->copy()->addDays(30),
            ])
            ->count();

        $scaduti = Permit::query()
            ->where('valid_to', '<', $now)
            ->count();

        return [
            Stat::make('Titolari', $totaleTitolari)
                ->color('primary'),

            Stat::make('Veicoli', $totaleVeicoli)
                ->color('primary'),

            Stat::make('Permessi attivi', $attivi)
                ->color('success')
                ->url(route('filament.admin.resources.permits.index', [
                    'filter' => 'attivi',
                ])),

            Stat::make('In scadenza (30gg)', $inScadenza)
                ->color('warning')
                ->url(route('filament.admin.resources.permits.index', [
                    'filter' => 'in_scadenza',
                ])),

            Stat::make('Scaduti', $scaduti)
                ->color('danger')
                ->url(route('filament.admin.resources.permits.index', [
                    'filter' => 'scaduti',
                ])),
        ];
    }
}