<?php

namespace App\Filament\Admin\Resources\SettingsResource\Pages;

use App\Filament\Admin\Resources\SettingsResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use App\Models\Setting;

class ManageSettings extends ManageRecords
{
    protected static string $resource = SettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('edit')
                ->label('Edit Pengaturan')
                ->url(fn () => static::getResource()::getUrl('edit', ['record' => Setting::first() ?? 1]))
                ->icon('heroicon-o-pencil-square')
                ->color('primary'),
                
            Actions\Action::make('toggleRegistration')
                ->label(fn () => Setting::first()?->status_pendaftaran ? 'Tutup Pendaftaran' : 'Buka Pendaftaran')
                ->icon(fn () => Setting::first()?->status_pendaftaran ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
                ->color(fn () => Setting::first()?->status_pendaftaran ? 'danger' : 'success')
                ->requiresConfirmation()
                ->modalHeading(fn () => Setting::first()?->status_pendaftaran ? 'Tutup Pendaftaran' : 'Buka Pendaftaran')
                ->modalDescription(fn () => Setting::first()?->status_pendaftaran 
                    ? 'Apakah Anda yakin ingin menutup pendaftaran magang?' 
                    : 'Apakah Anda yakin ingin membuka pendaftaran magang?')
                ->action(function () {
                    $setting = Setting::first();
                    if (!$setting) {
                        $setting = Setting::create(['status_pendaftaran' => 1]);
                    }
                    $setting->update(['status_pendaftaran' => !$setting->status_pendaftaran]);
                    
                    $status = $setting->status_pendaftaran ? 'dibuka' : 'ditutup';
                    $this->notify('success', "Pendaftaran magang telah $status");
                }),
        ];
    }
}