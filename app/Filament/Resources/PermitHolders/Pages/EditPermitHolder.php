<?php

namespace App\Filament\Resources\PermitHolders\Pages;

use App\Filament\Resources\PermitHolders\PermitHolderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPermitHolder extends EditRecord
{
    protected static string $resource = PermitHolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
