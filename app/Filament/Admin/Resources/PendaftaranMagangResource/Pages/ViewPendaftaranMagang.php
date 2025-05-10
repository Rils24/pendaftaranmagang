<?php

namespace App\Filament\Admin\Resources\PendaftaranMagangResource\Pages;

use App\Filament\Admin\Resources\PendaftaranMagangResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewPendaftaranMagang extends ViewRecord
{
    protected static string $resource = PendaftaranMagangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            
            // Tombol Terima
            Actions\Action::make('terima')
                ->label('Terima Pendaftaran')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn ($record) => $record->status === 'pending')
                ->requiresConfirmation()
                ->modalHeading('Terima Pendaftaran Magang')
                ->modalDescription('Apakah Anda yakin ingin menerima pendaftaran magang ini?')
                ->modalSubmitActionLabel('Ya, Terima')
                ->action(function () {
                    $this->record->update(['status' => 'diterima']);
                    
                    // Kirim email ke user (asumsikan fungsi ini ada di model PendaftaranMagang)
                    $this->record->sendStatusNotification('diterima');
                    
                    $this->notify('success', 'Pendaftaran telah diterima');
                }),
            
            // Tombol Tolak
            Actions\Action::make('tolak')
                ->label('Tolak Pendaftaran')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->visible(fn ($record) => $record->status === 'pending')
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\Textarea::make('alasan_penolakan')
                        ->label('Alasan Penolakan')
                        ->required(),
                ])
                ->modalHeading('Tolak Pendaftaran Magang')
                ->modalDescription('Harap berikan alasan penolakan pendaftaran ini')
                ->modalSubmitActionLabel('Tolak Pendaftaran')
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'ditolak',
                        'alasan_penolakan' => $data['alasan_penolakan'],
                    ]);
                    
                    // Kirim email ke user dengan alasan penolakan
                    $this->record->sendStatusNotification('ditolak', $data['alasan_penolakan']);
                    
                    $this->notify('success', 'Pendaftaran telah ditolak');
                }),
                
            // Tombol Download Surat Pengantar
            Actions\Action::make('downloadSurat')
                ->label('Download Surat Pengantar')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->url(fn ($record) => $record->surat_pengantar ? Storage::url($record->surat_pengantar) : '#')
                ->visible(fn ($record) => $record->surat_pengantar),
                
            // Tombol Print Detail
            Actions\Action::make('printDetail')
                ->label('Print Detail')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->openUrlInNewTab()
                ->url(fn ($record) => route('pendaftaran.print', $record)),
        ];
    }
}