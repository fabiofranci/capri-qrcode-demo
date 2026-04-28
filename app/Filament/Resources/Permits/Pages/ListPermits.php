<?php

namespace App\Filament\Resources\Permits\Pages;

use App\Filament\Resources\Permits\PermitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPermits extends ListRecords
{
    protected static string $resource = PermitResource::class;

    public function getTitle(): string
    {
        return match (request()->get('filter')) {
            'scaduti' => 'Permessi scaduti',
            'in_scadenza' => 'Permessi in scadenza',
            'attivi' => 'Permessi attivi',
            default => 'Permessi',
        };
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
