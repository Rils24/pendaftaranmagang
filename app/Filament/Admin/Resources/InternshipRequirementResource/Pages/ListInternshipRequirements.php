<?php

namespace App\Filament\Admin\Resources\InternshipRequirementResource\Pages;

use App\Filament\Admin\Resources\InternshipRequirementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInternshipRequirements extends ListRecords
{
    protected static string $resource = InternshipRequirementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
