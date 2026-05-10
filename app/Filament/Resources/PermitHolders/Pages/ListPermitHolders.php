<?php

namespace App\Filament\Resources\PermitHolders\Pages;

use App\Filament\Resources\PermitHolders\PermitHolderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPermitHolders extends ListRecords
{
    protected static string $resource = PermitHolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
