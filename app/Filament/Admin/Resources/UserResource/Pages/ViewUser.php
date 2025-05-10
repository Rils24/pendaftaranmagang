<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            
            // Tombol Verifikasi Pengguna
            Actions\Action::make('verifyUser')
                ->label('Verifikasi Pengguna')
                ->icon('heroicon-o-check')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => !$this->record->is_verified)
                ->modalHeading('Verifikasi Pengguna')
                ->modalDescription('Apakah Anda yakin ingin memverifikasi pengguna ini?')
                ->modalSubmitActionLabel('Ya, Verifikasi')
                ->action(function () {
                    $this->record->update(['is_verified' => true]);
                    $this->notify('success', 'Pengguna telah diverifikasi');
                }),
        ];
    }
}