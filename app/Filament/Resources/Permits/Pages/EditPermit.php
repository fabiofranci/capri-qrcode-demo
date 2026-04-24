<?php

namespace App\Filament\Resources\Permits\Pages;

use App\Filament\Resources\Permits\PermitResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPermit extends EditRecord
{
    protected static string $resource = PermitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
