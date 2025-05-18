<?php

namespace App\Filament\Admin\Resources\PendaftaranMagangResource\Pages;

use App\Filament\Admin\Resources\PendaftaranMagangResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPendaftaranMagangs extends ListRecords
{
    protected static string $resource = PendaftaranMagangResource::class;
     protected static ?string $title = 'Pendaftaran Magang';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
