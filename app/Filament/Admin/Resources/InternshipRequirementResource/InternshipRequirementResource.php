<?php
namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InternshipRequirementResource\Pages;
use App\Models\InternshipRequirement;
use App\Models\PendaftaranMagang;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Support\Colors\Color;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\HtmlString;

class InternshipRequirementResource extends Resource
{
    protected static ?string $model = InternshipRequirement::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Persyaratan Magang';
    protected static ?string $recordTitleAttribute = 'period';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Manajemen Magang';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::currentlyActive()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::currentlyActive()->count() > 0 ? 'success' : 'warning';
    }

    // Fungsi helper untuk menghitung jumlah anggota saja (tanpa pendaftar utama)
    private static function hitungTotalAnggota($periode)
    {
        // Menghitung jumlah pendaftar yang diterima pada periode tertentu
        $pendaftaranDiterima = PendaftaranMagang::where('status', 'diterima')
            ->when($periode->deadline, function($query, $deadline) {
                // Pendaftaran 3 bulan sebelum deadline
                $startDate = Carbon::parse($deadline)->subMonths(3);
                return $query->whereBetween('created_at', [$startDate, $deadline]);
            })
            ->get();
        
        // Hitung jumlah anggota tim saja (pendaftar utama tidak dihitung)
        $totalAnggota = 0;
        
        foreach ($pendaftaranDiterima as $pendaftaran) {
            // Hanya menghitung anggota tim
            $totalAnggota += $pendaftaran->anggota()->count();
        }
        
        return $totalAnggota;
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Section::make('Informasi Periode')
                            ->description('Informasi tentang periode magang')
                            ->icon('heroicon-o-calendar')
                            ->collapsible()
                            ->schema([
                                TextInput::make('period')
                                    ->label('Periode')
                                    ->placeholder('Contoh: Januari - Juni 2025')
                                    ->required()
                                    ->maxLength(100),

                                DatePicker::make('deadline')
                                    ->label('Batas Waktu Pendaftaran')
                                    ->required()
                                    ->helperText('Tanggal terakhir penerimaan pendaftaran')
                                    ->weekStartsOnSunday(),

                                TextInput::make('quota')
                                    ->label('Kuota Anggota')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required()
                                    ->helperText('Jumlah maksimal anggota tim yang diterima (tidak termasuk pendaftar utama)')
                                    ->hintIcon('heroicon-m-information-circle', tooltip: 'Kuota ini hanya menghitung anggota tim, bukan pendaftar utama. Pendaftar utama tidak dihitung dalam kuota.'),

                                Forms\Components\Placeholder::make('available_quota')
                                    ->label('Sisa Kuota')
                                    ->content(function (Model $record) {
                                        if (!$record->exists) {
                                            return '-';
                                        }
                                        
                                        // Hitung total anggota tim saja (tanpa pendaftar utama)
                                        $totalAnggota = self::hitungTotalAnggota($record);
                                        
                                        $sisaKuota = max(0, $record->quota - $totalAnggota);
                                        
                                        // Ambil jumlah pendaftar utama untuk informasi
                                        $jumlahPendaftar = PendaftaranMagang::where('status', 'diterima')
                                            ->when($record->deadline, function($query, $deadline) {
                                                $startDate = Carbon::parse($deadline)->subMonths(3);
                                                return $query->whereBetween('created_at', [$startDate, $deadline]);
                                            })
                                            ->count();
                                        
                                        // Tambahkan informasi detail
                                        $detailHTML = "<div class='mt-1 text-sm'>";
                                        $detailHTML .= "<span class='font-medium'>Pendaftar utama:</span> {$jumlahPendaftar} orang (tidak dihitung dalam kuota)<br>";
                                        $detailHTML .= "<span class='font-medium'>Total anggota:</span> {$totalAnggota} orang<br>";
                                        $detailHTML .= "<span class='font-medium'>Kuota anggota:</span> {$record->quota} orang<br>";
                                        $detailHTML .= "<span class='font-medium'>Sisa kuota:</span> {$sisaKuota} orang";
                                        $detailHTML .= "</div>";
                                        
                                        return new HtmlString("{$sisaKuota} orang {$detailHTML}");
                                    })
                                    ->visibleOn('edit'),
                            ])
                            ->columns(2),

                        Section::make('Lokasi dan Dokumen')
                            ->icon('heroicon-o-document-text')
                            ->collapsible()
                            ->schema([
                                TextInput::make('location')
                                    ->label('Lokasi')
                                    ->required()
                                    ->maxLength(255),

                                RichEditor::make('documents')
                                    ->label('Dokumen yang Diperlukan')
                                    ->required()
                                    ->helperText('Daftar dokumen yang perlu disiapkan oleh pendaftar')
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'bulletList',
                                        'orderedList',
                                        'redo',
                                        'undo',
                                    ]),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Section::make('Status')
                            ->icon('heroicon-o-check-circle')
                            ->collapsible()
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->helperText('Aktifkan periode magang ini')
                                    ->default(true)
                                    ->onIcon('heroicon-s-check')
                                    ->offIcon('heroicon-s-x-mark'),
                                
                                Forms\Components\Placeholder::make('is_expired')
                                    ->label('Status Deadline')
                                    ->content(function (Model $record) {
                                        if (!$record->exists) {
                                            return '-';
                                        }
                                        
                                        return $record->is_expired 
                                            ? 'Sudah Berakhir (Deadline: ' . $record->deadline->format('d M Y') . ')'
                                            : 'Masih Berlaku (Deadline: ' . $record->deadline->format('d M Y') . ')';
                                    })
                                    ->extraAttributes(['class' => 'font-medium'])
                                    ->visibleOn('edit'),
                                    
                                Forms\Components\Placeholder::make('status_aktual')
                                    ->label('Status Aktual')
                                    ->content(function (Model $record) {
                                        if (!$record->exists) {
                                            return '-';
                                        }
                                        
                                        return $record->isCurrentlyActive() 
                                            ? 'Aktif dan Ditampilkan ke Pengguna'
                                            : 'Tidak Aktif (Tidak Ditampilkan ke Pengguna)';
                                    })
                                    ->extraAttributes(['class' => 'font-medium'])
                                    ->visibleOn('edit'),
                                
                                Forms\Components\Placeholder::make('created_at')
                                    ->label('Dibuat Pada')
                                    ->content(fn (Model $record): ?string => $record->created_at?->diffForHumans())
                                    ->visibleOn('edit'),

                                Forms\Components\Placeholder::make('updated_at')
                                    ->label('Terakhir Diperbarui')
                                    ->content(fn (Model $record): ?string => $record->updated_at?->diffForHumans())
                                    ->visibleOn('edit'),
                            ]),

                        Section::make('Informasi Tambahan')
                            ->icon('heroicon-o-information-circle')
                            ->collapsed()
                            ->schema([
                                RichEditor::make('additional_info')
                                    ->label('Info Tambahan')
                                    ->nullable()
                                    ->helperText('Informasi tambahan yang perlu diketahui oleh pendaftar (opsional)')
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'bulletList',
                                        'orderedList',
                                        'redo',
                                        'undo',
                                        'link',
                                    ]),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('period')
                    ->label('Periode')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('deadline')
                    ->label('Deadline')
                    ->date('d M Y')
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => 
                        $record->is_expired 
                            ? 'danger' 
                            : ($record->deadline->diffInDays(now()) <= 7 ? 'warning' : 'success')
                    ),

                TextColumn::make('quota')
                    ->label('Kuota Anggota')
                    ->numeric()
                    ->sortable()
                    ->description('Hanya anggota'),

                TextColumn::make('terisi')
                    ->label('Anggota Terisi')
                    ->getStateUsing(function (Model $record) {
                        // Menghitung total anggota tim saja (tanpa pendaftar utama)
                        $totalAnggota = self::hitungTotalAnggota($record);
                        
                        // Menampilkan persentase terisi jika ada kuota
                        $persentase = $record->quota > 0 ? round(($totalAnggota / $record->quota) * 100) : 0;
                        
                        return "{$totalAnggota} anggota ({$persentase}%)";
                    })
                    ->sortable(false),
                    
                TextColumn::make('available_quota')
                    ->label('Sisa Kuota')
                    ->getStateUsing(function (Model $record) {
                        // Menghitung total anggota tim saja (tanpa pendaftar utama)
                        $totalAnggota = self::hitungTotalAnggota($record);
                        
                        // Hitung sisa kuota
                        return max(0, $record->quota - $totalAnggota);
                    })
                    ->badge()
                    ->color(function ($state) {
                        if ($state <= 0) return 'danger';
                        if ($state <= 5) return 'warning';
                        return 'success';
                    }),

                TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (Model $record): string {
                        return $record->location;
                    }),

                IconColumn::make('is_active')
                    ->label('Admin')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                    
                IconColumn::make('isCurrentlyActive')
                    ->label('Aktual')
                    ->getStateUsing(fn (Model $record): bool => 
                        $record->isCurrentlyActive()
                    )
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn (Model $record): string => 
                        $record->isCurrentlyActive()
                            ? 'Aktif dan ditampilkan kepada pengguna'
                            : 'Tidak aktif atau telah melewati deadline'
                    ),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('deadline', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status Admin')
                    ->options([
                        true => 'Aktif',
                        false => 'Tidak Aktif',
                    ]),
                
                Tables\Filters\Filter::make('currently_active')
                    ->label('Status Aktual')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('is_active', true)
                              ->whereDate('deadline', '>=', now())
                    )
                    ->toggle(),
                
                Tables\Filters\Filter::make('deadline')
                    ->form([
                        Forms\Components\DatePicker::make('deadline_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('deadline_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['deadline_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('deadline', '>=', $date),
                            )
                            ->when(
                                $data['deadline_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('deadline', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye'),
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil'),
                
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn (Model $record): string => $record->is_active ? 'Nonaktifkan' : 'Aktifkan')
                    ->icon(fn (Model $record): string => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (Model $record): string => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Model $record): string => 
                        $record->is_active 
                            ? 'Nonaktifkan Periode Magang "' . $record->period . '"?' 
                            : 'Aktifkan Periode Magang "' . $record->period . '"?'
                    )
                    ->modalDescription(fn (Model $record): string => 
                        $record->is_active 
                            ? 'Periode magang ini tidak akan ditampilkan kepada pengguna setelah dinonaktifkan.'
                            : 'Periode magang ini akan ditampilkan kepada pengguna jika masih dalam deadline.'
                    )
                    ->modalSubmitActionLabel(fn (Model $record): string => 
                        $record->is_active ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'
                    )
                    ->action(function (Model $record): void {
                        $record->update(['is_active' => !$record->is_active]);
                    }),
                    
                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Aktifkan')
                        ->color('success')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->modalHeading('Aktifkan periode magang yang dipilih?')
                        ->modalDescription('Periode magang yang dipilih akan diaktifkan dan dapat ditampilkan kepada pengguna jika masih dalam deadline.')
                        ->modalSubmitActionLabel('Ya, Aktifkan Semua')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true])),
                        
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Nonaktifkan')
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->requiresConfirmation()
                        ->modalHeading('Nonaktifkan periode magang yang dipilih?')
                        ->modalDescription('Periode magang yang dipilih akan dinonaktifkan dan tidak akan ditampilkan kepada pengguna.')
                        ->modalSubmitActionLabel('Ya, Nonaktifkan Semua')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false])),
                ]),
            ])
            ->emptyStateHeading('Belum ada periode magang')
            ->emptyStateDescription('Buat periode magang baru untuk ditampilkan di halaman pendaftaran.')
            ->emptyStateIcon('heroicon-o-academic-cap')
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('Buat Periode Magang')
                    ->url(route('filament.admin.resources.internship-requirements.create'))
                    ->icon('heroicon-o-plus')
                    ->button(),
            ]);
    }
    
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Periode')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        Infolists\Components\TextEntry::make('period')
                            ->label('Periode')
                            ->weight('bold')
                            ->size('text-xl'),
                            
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('deadline')
                                    ->label('Batas Waktu')
                                    ->date('d M Y')
                                    ->badge()
                                    ->color(fn ($record) => $record->deadline < now() ? 'danger' : 'success'),
                                    
                                Infolists\Components\TextEntry::make('quota')
                                    ->label('Kuota Anggota'),
                                    // ->description('Hanya menghitung anggota tim'),
                                    
                                Infolists\Components\TextEntry::make('sisa_quota')
                                    ->label('Sisa Kuota')
                                    ->getStateUsing(function (Model $record) {
                                        // Menghitung total anggota tim saja (tanpa pendaftar utama)
                                        $totalAnggota = self::hitungTotalAnggota($record);
                                        
                                        // Hitung sisa kuota
                                        $sisaKuota = max(0, $record->quota - $totalAnggota);
                                        
                                        // Menampilkan persentase terisi jika ada kuota
                                        $persentaseTerisi = $record->quota > 0 ? round(($totalAnggota / $record->quota) * 100) : 0;
                                        
                                        return new HtmlString(
                                            "{$sisaKuota} anggota <span class='text-sm text-gray-500'>({$persentaseTerisi}% terisi)</span><br>" .
                                            "<span class='text-sm text-gray-500'>{$totalAnggota} dari {$record->quota} anggota</span>"
                                        );
                                    })
                                    ->badge()
                                    ->color(function ($record) {
                                        // Menghitung total anggota tim saja (tanpa pendaftar utama)
                                        $totalAnggota = self::hitungTotalAnggota($record);
                                        
                                        // Hitung sisa kuota
                                        $sisaKuota = max(0, $record->quota - $totalAnggota);
                                        
                                        if ($sisaKuota <= 0) return 'danger';
                                        if ($sisaKuota <= 5) return 'warning';
                                        return 'success';
                                    }),
                            ]),
                            
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\IconEntry::make('is_active')
                                    ->label('Status Admin')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger'),
                                    
                                Infolists\Components\IconEntry::make('isCurrentlyActive')
                                    ->label('Status Aktual')
                                    ->getStateUsing(fn (Model $record): bool => 
                                        $record->isCurrentlyActive()
                                    )
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger'),
                            ]),
                    ]),
                    
                Infolists\Components\Section::make('Detail Kuota')
                    ->icon('heroicon-o-users')
                    ->schema([
                        Infolists\Components\TextEntry::make('detail_kuota')
                            ->label(false)
                            ->getStateUsing(function (Model $record) {
                                // Menghitung jumlah pendaftar yang diterima pada periode tertentu
                                $pendaftaranDiterima = PendaftaranMagang::where('status', 'diterima')
                                    ->when($record->deadline, function($query, $deadline) {
                                        // Pendaftaran 3 bulan sebelum deadline
                                        $startDate = Carbon::parse($deadline)->subMonths(3);
                                        return $query->whereBetween('created_at', [$startDate, $deadline]);
                                    })
                                    ->get();
                                
                                // Hitung rincian kuota
                                $jumlahPendaftar = $pendaftaranDiterima->count();
                                $jumlahAnggota = 0;
                                
                                foreach ($pendaftaranDiterima as $pendaftaran) {
                                    $jumlahAnggota += $pendaftaran->anggota()->count();
                                }
                                
                                $sisaKuota = max(0, $record->quota - $jumlahAnggota);
                                $persentaseTerisi = $record->quota > 0 ? round(($jumlahAnggota / $record->quota) * 100) : 0;
                                
                                $html = '<div class="space-y-3">';
                                $html .= '<div class="p-4 bg-blue-50 rounded-lg border border-blue-100 mb-4">';
                                $html .= '<p class="text-blue-700 font-medium mb-2">Informasi Kuota</p>';
                                $html .= '<p class="text-sm text-blue-600">Sistem menghitung kuota berdasarkan jumlah anggota tim saja. Pendaftar utama tidak dihitung dalam kuota.</p>';
                                $html .= '</div>';
                                
                                $html .= '<div class="grid grid-cols-2 gap-4">';
                                $html .= '<div><span class="font-medium">Pendaftar Utama:</span> ' . $jumlahPendaftar . ' orang <span class="text-gray-500 text-sm">(tidak dihitung dalam kuota)</span></div>';
                                $html .= '<div><span class="font-medium">Anggota Tim:</span> ' . $jumlahAnggota . ' anggota <span class="text-gray-500 text-sm">(dihitung dalam kuota)</span></div>';
                                $html .= '<div><span class="font-medium">Kuota Anggota:</span> ' . $record->quota . ' anggota</div>';
                                $html .= '<div><span class="font-medium">Sisa Kuota:</span> ' . $sisaKuota . ' anggota</div>';
                                $html .= '<div><span class="font-medium">Persentase Terisi:</span> ' . $persentaseTerisi . '%</div>';
                                $html .= '</div>';
                                
                                // Progress bar
                                $barClass = "mt-3 w-full h-4 bg-gray-200 rounded-full overflow-hidden";
                                $fillClass = "h-full rounded-full";
                                
                                if ($persentaseTerisi <= 50) {
                                    $fillClass .= " bg-green-500";
                                } elseif ($persentaseTerisi <= 80) {
                                    $fillClass .= " bg-yellow-500";
                                } else {
                                    $fillClass .= " bg-red-500";
                                }
                                
                                $html .= '<div class="' . $barClass . '">';
                                $html .= '<div class="' . $fillClass . '" style="width: ' . min(100, $persentaseTerisi) . '%"></div>';
                                $html .= '</div>';
                                
                                $html .= '<div class="text-sm text-gray-500 mt-1">Terisi ' . $persentaseTerisi . '% dari total kuota anggota</div>';
                                
                                // Rincian pendaftar dan anggota
                                if (count($pendaftaranDiterima) > 0) {
                                    $html .= '<div class="mt-6">';
                                    $html .= '<h3 class="text-lg font-medium mb-2">Rincian Pendaftar</h3>';
                                    $html .= '<div class="overflow-x-auto">';
                                    $html .= '<table class="min-w-full divide-y divide-gray-200">';
                                    $html .= '<thead class="bg-gray-50">';
                                    $html .= '<tr>';
                                    $html .= '<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>';
                                    $html .= '<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pendaftar</th>';
                                    $html .= '<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asal Kampus</th>';
                                    $html .= '<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Anggota</th>';
                                    $html .= '</tr>';
                                    $html .= '</thead>';
                                    $html .= '<tbody class="bg-white divide-y divide-gray-200">';
                                    
                                    $no = 1;
                                    foreach ($pendaftaranDiterima as $pendaftaran) {
                                        $html .= '<tr>';
                                        $html .= '<td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">' . $no++ . '</td>';
                                        $html .= '<td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900">' . $pendaftaran->user->name . '</td>';
                                        $html .= '<td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">' . $pendaftaran->asal_kampus . '</td>';
                                        $html .= '<td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">' . $pendaftaran->anggota()->count() . ' anggota</td>';
                                        $html .= '</tr>';
                                    }
                                    
                                    $html .= '</tbody>';
                                    $html .= '</table>';
                                    $html .= '</div>';
                                    $html .= '</div>';
                                }
                                
                                $html .= '</div>';
                                
                                return new HtmlString($html);
                            }),
                    ]),
                    
                Infolists\Components\Section::make('Lokasi')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Infolists\Components\TextEntry::make('location')
                            ->label('Lokasi')
                            ->columnSpanFull(),
                    ]),
                    
                Infolists\Components\Section::make('Dokumen yang Diperlukan')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Infolists\Components\TextEntry::make('documents')
                            ->label(false)
                            ->html()
                            ->columnSpanFull(),
                    ]),
                    
                Infolists\Components\Section::make('Informasi Tambahan')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Infolists\Components\TextEntry::make('additional_info')
                            ->label(false)
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => !empty($record->additional_info)),
                
                Infolists\Components\Section::make('Metadata')
                    ->icon('heroicon-o-clock')
                    ->collapsed()
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime('d M Y, H:i'),
                            
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Terakhir Diperbarui')
                            ->dateTime('d M Y, H:i'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Tambahkan relasi ke PendaftaranMagang jika diperlukan
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInternshipRequirements::route('/'),
            'create' => Pages\CreateInternshipRequirement::route('/create'),
            'edit' => Pages\EditInternshipRequirement::route('/{record}/edit'),
            'view' => Pages\ViewInternshipRequirement::route('/{record}'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}