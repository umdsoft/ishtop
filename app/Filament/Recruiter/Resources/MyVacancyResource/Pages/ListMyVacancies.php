<?php

namespace App\Filament\Recruiter\Resources\MyVacancyResource\Pages;

use App\Filament\Recruiter\Resources\MyVacancyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMyVacancies extends ListRecords
{
    protected static string $resource = MyVacancyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
