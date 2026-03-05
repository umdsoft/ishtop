<?php

namespace App\Filament\Recruiter\Resources\MyVacancyResource\Pages;

use App\Filament\Recruiter\Resources\MyVacancyResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMyVacancy extends ViewRecord
{
    protected static string $resource = MyVacancyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
