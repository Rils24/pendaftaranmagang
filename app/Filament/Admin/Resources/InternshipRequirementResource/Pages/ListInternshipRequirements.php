<?php

namespace App\Filament\Admin\Resources\InternshipRequirementResource\Pages;

use App\Filament\Admin\Resources\InternshipRequirementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInternshipRequirements extends ListRecords
{
    protected static string $resource = InternshipRequirementResource::class;

    protected static ?string $title = 'Persyaratan Magang'; // Tambahkan ini

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}