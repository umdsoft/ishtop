<?php

namespace App\Filament\Admin\Resources\EmployerProfileResource\Pages;

use App\Filament\Admin\Resources\EmployerProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployerProfile extends ViewRecord
{
    protected static string $resource = EmployerProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
