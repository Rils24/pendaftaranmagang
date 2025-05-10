<?php

namespace App\Filament\Admin\Resources\PendaftaranMagangResource\Pages;

use App\Filament\Admin\Resources\PendaftaranMagangResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPendaftaranMagang extends EditRecord
{
    protected static string $resource = PendaftaranMagangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
