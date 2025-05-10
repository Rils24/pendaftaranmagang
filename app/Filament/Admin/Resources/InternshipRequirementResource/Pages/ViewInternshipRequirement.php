<?php

namespace App\Filament\Admin\Resources\InternshipRequirementResource\Pages;

use App\Filament\Admin\Resources\InternshipRequirementResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewInternshipRequirement extends ViewRecord
{
    protected static string $resource = InternshipRequirementResource::class;

    // Tambahkan metode notify
    public function notify(string $type, string $message): void
    {
        Notification::make()
            ->title($message)
            ->{$type}() // Gunakan metode status dinamis (success(), error(), warning())
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            
            // Tombol untuk toggle status aktif
            Actions\Action::make('toggleActive')
                ->label(fn () => $this->record->is_active ? 'Nonaktifkan' : 'Aktifkan')
                ->icon(fn () => $this->record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->color(fn () => $this->record->is_active ? 'danger' : 'success')
                ->requiresConfirmation()
                ->modalHeading(fn () => $this->record->is_active ? 'Nonaktifkan Periode Magang' : 'Aktifkan Periode Magang')
                ->modalDescription(fn () => $this->record->is_active 
                    ? 'Apakah Anda yakin ingin menonaktifkan periode magang ini?' 
                    : 'Apakah Anda yakin ingin mengaktifkan periode magang ini?')
                ->action(function () {
                    $this->record->update(['is_active' => !$this->record->is_active]);
                    
                    $status = $this->record->is_active ? 'diaktifkan' : 'dinonaktifkan';
                    $this->notify('success', "Periode magang telah $status");
                }),
        ];
    }
}