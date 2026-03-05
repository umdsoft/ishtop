<?php

namespace App\Filament\Admin\Resources\EmployerProfileResource\Pages;

use App\Filament\Admin\Resources\EmployerProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployerProfiles extends ListRecords
{
    protected static string $resource = EmployerProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
