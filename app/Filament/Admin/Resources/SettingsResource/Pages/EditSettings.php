<?php

namespace App\Filament\Admin\Resources\SettingsResource\Pages;

use App\Filament\Admin\Resources\SettingsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSettings extends EditRecord
{
    protected static string $resource = SettingsResource::class;
    
    // Tambahkan property ini untuk mendefinisikan slug halaman
    protected static ?string $slug = 'settings';
    
    // Hapus override method getUrl() yang tidak kompatibel
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}