<?php

namespace App\Filament\Recruiter\Resources\MyVacancyResource\Pages;

use App\Filament\Recruiter\Resources\MyVacancyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMyVacancy extends EditRecord
{
    protected static string $resource = MyVacancyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
