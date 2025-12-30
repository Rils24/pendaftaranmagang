<?php

namespace App\Filament\Admin\Resources\PendaftaranMagangResource\Pages;

use App\Filament\Admin\Resources\PendaftaranMagangResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

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

    /**
     * Eager load relationships to prevent N+1 queries
     * This significantly improves performance on the list page
     */
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->with(['user', 'anggota'])
            ->withCount('anggota');
    }
}
