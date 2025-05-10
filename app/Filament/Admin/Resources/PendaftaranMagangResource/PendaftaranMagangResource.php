<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PendaftaranMagangResource\Pages;
use App\Models\PendaftaranMagang;
use App\Models\InternshipRequirement;
use App\Models\Setting;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use App\Mail\PendaftaranMagangMail;
use Illuminate\Support\Facades\Mail;
use Filament\Tables\Actions\ToggleAction;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\Collection;
use Filament\Support\Colors\Color;
use Carbon\Carbon;
use Filament\Forms\Components\Tabs;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Support\HtmlString;
use Filament\Support\Enums\IconPosition;
use App\Models\User;
use Filament\Tables\Grouping\Group;

class PendaftaranMagangResource extends Resource
{
    protected static ?string $model = PendaftaranMagang::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Pendaftaran Magang';
    protected static ?string $recordTitleAttribute = 'user.name';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Manajemen Magang';
    protected static ?string $activeNavigationIcon = 'heroicon-s-clipboard-document-list';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $pendingCount = static::getModel()::where('status', 'pending')->count();
        
        if ($pendingCount > 10) {
            return 'danger';
        } elseif ($pendingCount > 0) {
            return 'warning';
        }
        
        return 'success';
    }
    
    public static function getGloballySearchableAttributes(): array
    {
        return ['user.name', 'asal_kampus', 'jurusan', 'user.email'];
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Tabs::make('Pendaftaran Magang')
                    ->tabs([
                        Tabs\Tab::make('Informasi Dasar')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\Section::make('Informasi Pendaftar')
                                            ->icon('heroicon-o-user')
                                            ->description('Detail pendaftar magang')
                                            ->collapsible()
                                            ->schema([
                                                Forms\Components\TextInput::make('user.name')
                                                    ->label('Nama Pengguna')
                                                    ->disabled()
                                                    ->helperText('Nama pendaftar')
                                                    ->suffixIcon('heroicon-m-user'),
                                                
                                                Forms\Components\TextInput::make('user.email')
                                                    ->label('Email Pengguna')
                                                    ->disabled()
                                                    ->email()
                                                    ->suffixIcon('heroicon-m-envelope'),
                                                
                                                Forms\Components\TextInput::make('asal_kampus')
                                                    ->label('Asal Kampus')
                                                    ->disabled()
                                                    ->suffixIcon('heroicon-m-academic-cap'),
                                                
                                                Forms\Components\TextInput::make('jurusan')
                                                    ->label('Jurusan')
                                                    ->disabled()
                                                    ->suffixIcon('heroicon-m-book-open'),
                                                
                                                Forms\Components\Grid::make()
                                                    ->schema([
                                                        Forms\Components\DatePicker::make('tanggal_mulai')
                                                            ->label('Tanggal Mulai')
                                                            ->disabled()
                                                            ->displayFormat('d M Y')
                                                            ->suffixIcon('heroicon-m-calendar-days'),
                                                            
                                                        Forms\Components\DatePicker::make('tanggal_selesai')
                                                            ->label('Tanggal Selesai')
                                                            ->disabled()
                                                            ->displayFormat('d M Y')
                                                            ->suffixIcon('heroicon-m-calendar-days'),
                                                    ])
                                                    ->columns(2),
                                                    
                                                Forms\Components\Placeholder::make('durasi_magang')
                                                    ->label('Durasi Magang')
                                                    ->content(function (PendaftaranMagang $record): string {
                                                        if (!$record->tanggal_mulai || !$record->tanggal_selesai) {
                                                            return '-';
                                                        }
                                                        
                                                        $start = Carbon::parse($record->tanggal_mulai);
                                                        $end = Carbon::parse($record->tanggal_selesai);
                                                        $diffInDays = $end->diffInDays($start) + 1;
                                                        $diffInWeeks = ceil($diffInDays / 7);
                                                        $diffInMonths = ceil($diffInDays / 30);
                                                        
                                                        $result = "<span class='text-primary-500 font-medium'>{$diffInDays} hari</span> ";
                                                        $result .= "(<span class='text-primary-500 font-medium'>{$diffInWeeks} minggu</span>";
                                                        
                                                        if ($diffInMonths > 0) {
                                                            $result .= " / <span class='text-primary-500 font-medium'>{$diffInMonths} bulan</span>";
                                                        }
                                                        
                                                        $result .= ")";
                                                        
                                                        return new HtmlString($result);
                                                    })
                                                    ->hidden(function (PendaftaranMagang $record): bool {
                                                        return !$record->exists || !$record->tanggal_mulai || !$record->tanggal_selesai;
                                                    }),
                                                    
                                                Forms\Components\TextInput::make('created_at')
                                                    ->label('Tanggal Pendaftaran')
                                                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('d M Y H:i') : '-')
                                                    ->disabled()
                                                    ->suffixIcon('heroicon-m-clock'),
                                            ])
                                            ->columns(2),

                                        Forms\Components\Section::make('Periode Magang')
                                            ->icon('heroicon-o-calendar')
                                            ->description('Informasi periode magang terkait')
                                            ->collapsible()
                                            ->schema([
                                                Forms\Components\Select::make('periode_magang')
                                                    ->label('Periode Magang')
                                                    ->options(function () {
                                                        return InternshipRequirement::orderBy('deadline', 'desc')
                                                            ->get()
                                                            ->mapWithKeys(function ($item) {
                                                                $statusIcon = $item->isCurrentlyActive() ? '🟢 ' : '🔴 ';
                                                                $kuotaInfo = "({$item->quota} kuota)";
                                                                return [$item->id => $statusIcon . $item->period . ' ' . $kuotaInfo . ' - ' . $item->deadline->format('d M Y')];
                                                            });
                                                    })
                                                    ->helperText('Periode magang yang terkait dengan pendaftaran ini')
                                                    ->disabled()
                                                    ->dehydrated(false)
                                                    ->searchable(),
                                                    
                                                Forms\Components\Placeholder::make('info_periode')
                                                    ->label('Keterangan Periode')
                                                    ->content(function (PendaftaranMagang $record) {
                                                        // Mencari periode yang sesuai berdasarkan tanggal pendaftaran
                                                        $periode = InternshipRequirement::where('deadline', '>=', $record->created_at)
                                                            ->where('created_at', '<=', $record->created_at)
                                                            ->orderBy('deadline', 'asc')
                                                            ->first();
                                                            
                                                        if (!$periode) {
                                                            return new HtmlString('<span class="text-danger-500">Tidak dapat menentukan periode magang yang sesuai</span>');
                                                        }
                                                        
                                                        $statusBadge = $periode->isCurrentlyActive() 
                                                            ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800">Aktif</span>' 
                                                            : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-danger-100 text-danger-800">Tidak Aktif</span>';
                                                        
                                                        $acceptedCount = PendaftaranMagang::where('status', 'diterima')
                                                            ->where('created_at', '>=', $periode->created_at)
                                                            ->where('created_at', '<=', $periode->deadline)
                                                            ->count();
                                                        
                                                        $kuotaInfo = "<div class='mt-2'>Kuota: <strong>{$acceptedCount}/{$periode->quota}</strong></div>";
                                                        
                                                        if ($acceptedCount >= $periode->quota) {
                                                            $kuotaInfo .= "<div class='text-danger-500 font-medium'>Kuota penuh!</div>";
                                                        }
                                                        
                                                        return new HtmlString("Pendaftaran termasuk dalam periode: <strong>{$periode->period}</strong> {$statusBadge}{$kuotaInfo}");
                                                    }),
                                                    
                                                Forms\Components\Placeholder::make('status_periode')
                                                    ->label('Status Periode')
                                                    ->content(function (PendaftaranMagang $record) {
                                                        $periode = InternshipRequirement::where('deadline', '>=', $record->created_at)
                                                            ->where('created_at', '<=', $record->created_at)
                                                            ->orderBy('deadline', 'asc')
                                                            ->first();
                                                            
                                                        if (!$periode) {
                                                            return new HtmlString('<span class="text-danger-500">Tidak terkait dengan periode manapun</span>');
                                                        }
                                                        
                                                        $daysLeft = now()->diffInDays($periode->deadline, false);
                                                        
                                                        if ($daysLeft < 0) {
                                                            return new HtmlString('<span class="text-danger-500">Periode telah berakhir</span>');
                                                        } else if ($daysLeft == 0) {
                                                            return new HtmlString('<span class="text-warning-500 font-medium">Periode berakhir hari ini!</span>');
                                                        } else if ($daysLeft <= 7) {
                                                            return new HtmlString("<span class='text-warning-500 font-medium'>Periode berakhir dalam {$daysLeft} hari lagi</span>");
                                                        } else {
                                                            return new HtmlString("<span class='text-success-500'>Periode masih berlangsung ({$daysLeft} hari lagi)</span>");
                                                        }
                                                    }),
                                            ]),
                                    ])
                                    ->columnSpan(['lg' => 2]),
                                    
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\Section::make('Status Pendaftaran')
                                            ->icon('heroicon-o-check-circle')
                                            ->description('Kelola status pendaftaran')
                                            ->collapsible()
                                            ->schema([
                                                Forms\Components\Select::make('status')
                                                    ->label('Status Pendaftaran')
                                                    ->options([
                                                        'pending' => 'Pending',
                                                        'diterima' => 'Diterima',
                                                        'ditolak' => 'Ditolak',
                                                    ])
                                                    ->default('pending')
                                                    ->required()
                                                    ->reactive()
                                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                                        if ($state === 'ditolak') {
                                                            // Tidak mengubah alasan jika sudah ada
                                                            if (!$get('alasan_penolakan')) {
                                                                $set('alasan_penolakan', 'Mohon maaf, pendaftaran Anda tidak dapat diproses karena:');
                                                            }
                                                        } else {
                                                            $set('alasan_penolakan', null);
                                                        }
                                                    })
                                                    ->disabled(function ($record) {
                                                        // Cek kuota penuh jika status akan diubah menjadi diterima
                                                        if ($record && $record->status !== 'diterima') {
                                                            $periode = InternshipRequirement::where('deadline', '>=', $record->created_at)
                                                                ->where('created_at', '<=', $record->created_at)
                                                                ->orderBy('deadline', 'asc')
                                                                ->first();
                                                                
                                                            if ($periode) {
                                                                $acceptedCount = PendaftaranMagang::where('status', 'diterima')
                                                                    ->where('created_at', '>=', $periode->created_at)
                                                                    ->where('created_at', '<=', $periode->deadline)
                                                                    ->count();
                                                                
                                                                // Jika kuota sudah penuh, tidak bisa menerima pendaftar baru
                                                                if ($acceptedCount >= $periode->quota) {
                                                                    return true;
                                                                }
                                                            }
                                                        }
                                                        return false;
                                                    })
                                                    ->helperText(function ($record) {
                                                        if ($record) {
                                                            $periode = InternshipRequirement::where('deadline', '>=', $record->created_at)
                                                                ->where('created_at', '<=', $record->created_at)
                                                                ->orderBy('deadline', 'asc')
                                                                ->first();
                                                                
                                                            if ($periode) {
                                                                $acceptedCount = PendaftaranMagang::where('status', 'diterima')
                                                                    ->where('created_at', '>=', $periode->created_at)
                                                                    ->where('created_at', '<=', $periode->deadline)
                                                                    ->count();
                                                                 
                                                                $persentase = $periode->quota > 0 ? round(($acceptedCount / $periode->quota) * 100) : 0;
                                                                return "Kuota: $acceptedCount/$periode->quota ($persentase%)";
                                                            }
                                                        }
                                                        return 'Kuota tidak tersedia';
                                                    })
                                                    ->suffixAction(function ($record, $state) {
                                                        if ($record && $state === 'pending') {
                                                            return FormAction::make('forceApprove')
                                                                ->label('Force Approve')
                                                                ->icon('heroicon-m-check-circle')
                                                                ->color('success')
                                                                ->action(function ($record, $livewire) {
                                                                    // Force approval meskipun kuota penuh
                                                                    $record->update(['status' => 'diterima']);
                                                                    
                                                                    if ($livewire->data['kirim_notifikasi'] ?? true) {
                                                                        Mail::to($record->user->email)->send(new PendaftaranMagangMail('diterima'));
                                                                    }
                                                                    
                                                                    Notification::make()
                                                                        ->title('Pendaftaran berhasil disetujui secara paksa')
                                                                        ->success()
                                                                        ->send();
                                                                    
                                                                    return redirect()->back();
                                                                })
                                                                ->requiresConfirmation();
                                                        }
                                                        
                                                        return null;
                                                    }),

                                                Forms\Components\Textarea::make('alasan_penolakan')
                                                    ->label('Alasan Penolakan')
                                                    ->visible(fn ($get) => $get('status') === 'ditolak')
                                                    ->required(fn ($get) => $get('status') === 'ditolak')
                                                    ->rows(4)
                                                    ->columnSpanFull()
                                                    ->helperText('Alasan ini akan ditampilkan pada pendaftar'),
                                                    
                                                Forms\Components\Placeholder::make('status_info')
                                                    ->label('Informasi Status')
                                                    ->content(function (PendaftaranMagang $record): string {
                                                        if (!$record->exists) {
                                                            return '-';
                                                        }
                                                        
                                                        $statusTime = Carbon::parse($record->updated_at)->format('d M Y H:i');
                                                        
                                                        switch ($record->status) {
                                                            case 'pending':
                                                                $waitTime = now()->diffForHumans($record->created_at, true);
                                                                return "Menunggu verifikasi admin (sudah menunggu selama {$waitTime}).";
                                                            case 'diterima':
                                                                return "Pendaftaran telah disetujui pada {$statusTime}";
                                                            case 'ditolak':
                                                                return "Pendaftaran ditolak pada {$statusTime}";
                                                            default:
                                                                return '-';
                                                        }
                                                    }),
                                            ]),
                                            
                                        Forms\Components\Section::make('Notifikasi')
                                            ->icon('heroicon-o-bell')
                                            ->description('Pengaturan notifikasi email')
                                            ->collapsible()
                                            ->schema([
                                                Forms\Components\Placeholder::make('notifikasi_info')
                                                    ->label('Notifikasi Email')
                                                    ->content('Email notifikasi akan dikirim kepada pendaftar saat status diubah.')
                                                    ->helperText('Email akan dikirim ke alamat pendaftar'),
                                                    
                                                Forms\Components\Checkbox::make('kirim_notifikasi')
                                                    ->label('Kirim Notifikasi')
                                                    ->helperText('Centang untuk mengirim notifikasi email ke pendaftar')
                                                    ->default(true),
                                                    
                                                Forms\Components\Checkbox::make('notifikasi_admin')
                                                    ->label('Notifikasi Admin')
                                                    ->helperText('Centang untuk mengirim salinan notifikasi ke admin')
                                                    ->default(function () {
                                                        $setting = Setting::first();
                                                        return $setting ? $setting->send_admin_notifications : false;
                                                    })
                                                    ->dehydrated(false),
                                            ]),
                                            
                                        Forms\Components\Section::make('Catatan Admin')
                                            ->icon('heroicon-o-clipboard-document')
                                            ->description('Catatan internal untuk admin')
                                            ->collapsible()
                                            ->collapsed()
                                            ->schema([
                                                Forms\Components\Textarea::make('admin_notes')
                                                    ->label('Catatan Internal')
                                                    ->placeholder('Tambahkan catatan internal di sini...')
                                                    ->helperText('Catatan ini hanya terlihat oleh admin')
                                                    ->rows(4)
                                                    ->columnSpanFull()
                                                    ->dehydrated(false),
                                            ]),
                                    ])
                                    ->columnSpan(['lg' => 1]),
                            ]),
                            
                        Tabs\Tab::make('Dokumen')
                            ->icon('heroicon-o-document')
                            ->schema([
                                Forms\Components\Section::make('Dokumen Pendaftaran')
                                    ->icon('heroicon-o-document')
                                    ->description('Dokumen yang diunggah oleh pendaftar')
                                    ->schema([
                                        Forms\Components\FileUpload::make('surat_pengantar')
                                            ->label('Surat Pengantar')
                                            ->downloadable()
                                            ->openable()
                                            ->columnSpanFull()
                                            ->directory('surat-pengantar')
                                            ->visibility('private')
                                            ->imagePreviewHeight('400')
                                            ->loadingIndicatorPosition('left')
                                            ->panelAspectRatio('2:1')
                                            ->panelLayout('integrated')
                                            ->acceptedFileTypes(['application/pdf'])
                                            ->helperText('File PDF surat pengantar dari universitas/institusi'),
                                    ]),
                            ]),
                            
                        Tabs\Tab::make('Anggota Tim')
                            ->icon('heroicon-o-users')
                            ->schema([
                                Forms\Components\Section::make('Anggota Tim Magang')
                                    ->icon('heroicon-o-users')
                                    ->description('Daftar anggota tim magang')
                                    ->schema([
                                        Forms\Components\Repeater::make('anggota')
                                            ->relationship('anggota')
                                            ->label('Anggota Pendaftaran')
                                            ->schema([
                                                Forms\Components\TextInput::make('nama_anggota')
                                                    ->label('Nama Anggota')
                                                    ->required()
                                                    ->maxLength(255),
                                                    
                                                Forms\Components\TextInput::make('nim_anggota')
                                                    ->label('NIM Anggota')
                                                    ->required()
                                                    ->maxLength(50),
                                                    
                                                Forms\Components\TextInput::make('no_hp_anggota')
                                                    ->label('No HP Anggota')
                                                    ->required()
                                                    ->tel()
                                                    ->maxLength(15),
                                                    
                                                Forms\Components\TextInput::make('email_anggota')
                                                    ->label('Email Anggota')
                                                    ->required()
                                                    ->email()
                                                    ->maxLength(255),
                                                    
                                                Forms\Components\TextInput::make('jurusan')
                                                    ->label('Jurusan Anggota')
                                                    ->maxLength(255),
                                            ])
                                            ->columns(2)
                                            ->maxItems(6)
                                            ->collapsible()
                                            ->collapsed()
                                            ->itemLabel(fn (array $state): ?string => $state['nama_anggota'] ?? null)
                                            ->collapsible()
                                            ->reorderable()
                                            ->orderColumn('urutan'),
                                    ]),
                            ]),
                            
                        Tabs\Tab::make('Riwayat & Log')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                Forms\Components\Section::make('Riwayat Pendaftaran')
                                    ->icon('heroicon-o-clock')
                                    ->description('Riwayat perubahan status pendaftaran')
                                    ->schema([
                                        Forms\Components\Placeholder::make('created_log')
                                            ->label('Pendaftaran Dibuat')
                                            ->content(function (PendaftaranMagang $record): string {
                                                return $record->created_at ? $record->created_at->format('d M Y H:i:s') : '-';
                                            }),
                                            
                                        Forms\Components\Placeholder::make('updated_log')
                                            ->label('Terakhir Diperbarui')
                                            ->content(function (PendaftaranMagang $record): string {
                                                return $record->updated_at ? $record->updated_at->format('d M Y H:i:s') : '-';
                                            }),
                                            
                                        Forms\Components\Placeholder::make('status_logs')
                                            ->label('Log Perubahan Status')
                                            ->content(function (PendaftaranMagang $record): string {
                                                if (!$record->exists) {
                                                    return '-';
                                                }
                                                
                                                // Placeholder content for status logs
                                                // In a real implementation, you would query a status_logs table
                                                $html = "<div class='space-y-2'>";
                                                $html .= "<div class='flex items-center gap-2 text-sm text-gray-600'>";
                                                $html .= "<span class='w-24'>" . $record->created_at->format('d M Y H:i') . "</span>";
                                                $html .= "<span class='inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800'>Dibuat</span>";
                                                $html .= "<span>Pendaftaran dibuat dengan status Pending</span>";
                                                $html .= "</div>";
                                                
                                                if ($record->status !== 'pending') {
                                                    $html .= "<div class='flex items-center gap-2 text-sm text-gray-600'>";
                                                    $html .= "<span class='w-24'>" . $record->updated_at->format('d M Y H:i') . "</span>";
                                                    
                                                    if ($record->status === 'diterima') {
                                                        $html .= "<span class='inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-success-100 text-success-800'>Diterima</span>";
                                                        $html .= "<span>Pendaftaran disetujui</span>";
                                                    } else {
                                                        $html .= "<span class='inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-danger-100 text-danger-800'>Ditolak</span>";
                                                        $html .= "<span>Pendaftaran ditolak dengan alasan: " . $record->alasan_penolakan . "</span>";
                                                    }
                                                    
                                                    $html .= "</div>";
                                                }
                                                
                                                $html .= "</div>";
                                                
                                                return new HtmlString($html);
                                            }),
                                    ]),
                            ]),
                    ])
                    ->persistTabInQueryString()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->user->email)
                    ->copyable()
                    ->copyMessage('Email disalin!')
                    ->copyMessageDuration(1500),
                    
                Tables\Columns\TextColumn::make('asal_kampus')
                    ->label('Asal Kampus')
                    ->searchable()
                    ->sortable()
                    ->tooltip(function ($record) {
                        return "Jurusan: {$record->jurusan}";
                    }),
                    
                Tables\Columns\TextColumn::make('jurusan')
                    ->label('Jurusan')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable()
                    ->tooltip(function ($record) {
                        if (!$record->tanggal_mulai || !$record->tanggal_selesai) {
                            return null;
                        }
                        
                        $start = Carbon::parse($record->tanggal_mulai);
                        $end = Carbon::parse($record->tanggal_selesai);
                        $diffInDays = $end->diffInDays($start) + 1;
                        
                        return "Durasi: {$diffInDays} hari";
                    }),
                    
                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->label('Selesai')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('durasi_display')
                    ->label('Durasi')
                    ->getStateUsing(function (PendaftaranMagang $record) {
                        if (!$record->tanggal_mulai || !$record->tanggal_selesai) {
                            return '-';
                        }
                        
                        $start = Carbon::parse($record->tanggal_mulai);
                        $end = Carbon::parse($record->tanggal_selesai);
                        $diffInDays = $end->diffInDays($start) + 1;
                        $diffInWeeks = ceil($diffInDays / 7);
                        
                        return "{$diffInWeeks} minggu";
                    })
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('periode_magang')
                    ->label('Periode')
                    ->getStateUsing(function (PendaftaranMagang $record) {
                        // Mencari periode yang sesuai berdasarkan tanggal pendaftaran
                        $periode = InternshipRequirement::where('deadline', '>=', $record->created_at)
                            ->where('created_at', '<=', $record->created_at)
                            ->orderBy('deadline', 'asc')
                            ->first();
                            
                        if (!$periode) {
                            return '-';
                        }
                        
                        $isActive = $periode->isCurrentlyActive();
                        $indicator = $isActive ? '🟢' : '🔴';
                        
                        return "{$indicator} {$periode->period}";
                    })
                    ->tooltip(function ($record) {
                        $periode = InternshipRequirement::where('deadline', '>=', $record->created_at)
                            ->where('created_at', '<=', $record->created_at)
                            ->orderBy('deadline', 'asc')
                            ->first();
                            
                        if (!$periode) {
                            return 'Tidak ada periode terkait';
                        }
                        
                        $acceptedCount = PendaftaranMagang::where('status', 'diterima')
                            ->where('created_at', '>=', $periode->created_at)
                            ->where('created_at', '<=', $periode->deadline)
                            ->count();
                            
                        return "Kuota: {$acceptedCount}/{$periode->quota}";
                    })
                    ->searchable(false)
                    ->sortable(false),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'diterima' => 'success',
                        'ditolak' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'diterima' => 'heroicon-o-check-circle',
                        'ditolak' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'diterima' => 'Diterima',
                        'ditolak' => 'Ditolak',
                        default => $state,
                    })
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('anggota_count')
                    ->label('Anggota')
                    ->counts('anggota')
                    ->sortable()
                    ->alignCenter()
                    ->tooltip('Jumlah anggota tim'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Daftar')
                    ->date('d M Y')
                    ->sortable()
                    ->tooltip(fn ($record) => $record->created_at->format('d M Y H:i:s')),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        'pending' => 'Pending',
                        'diterima' => 'Diterima',
                        'ditolak' => 'Ditolak',
                    ])
                    ->indicator('Status'),
                    
                SelectFilter::make('periode_magang')
                    ->label('Periode Magang')
                    ->options(function () {
                        return InternshipRequirement::orderBy('deadline', 'desc')
                            ->get()
                            ->mapWithKeys(function ($item) {
                                $isActive = $item->isCurrentlyActive() ? '🟢 ' : '🔴 ';
                                return [$item->id => $isActive . $item->period . ' (' . $item->deadline->format('d M Y') . ')'];
                            });
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        
                        $periode = InternshipRequirement::find($data['value']);
                        if (!$periode) {
                            return $query;
                        }
                        
                        return $query
                            ->where('created_at', '>=', $periode->created_at)
                            ->where('created_at', '<=', $periode->deadline);
                    })
                    ->indicator('Periode'),
                    
                SelectFilter::make('asal_kampus')
                    ->options(function () {
                        return PendaftaranMagang::distinct('asal_kampus')
                            ->pluck('asal_kampus', 'asal_kampus')
                            ->toArray();
                    })
                    ->searchable()
                    ->indicator('Kampus'),
                    
                Filter::make('durasi')
                    ->form([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('min_durasi')
                                    ->label('Durasi Minimal (hari)')
                                    ->numeric()
                                    ->minValue(1),
                                Forms\Components\TextInput::make('max_durasi')
                                    ->label('Durasi Maksimal (hari)')
                                    ->numeric()
                                    ->minValue(1),
                            ])
                            ->columns(2),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_durasi'],
                                function (Builder $query, $min): Builder {
                                    return $query->whereRaw('DATEDIFF(tanggal_selesai, tanggal_mulai) + 1 >= ?', [$min]);
                                },
                            )
                            ->when(
                                $data['max_durasi'],
                                function (Builder $query, $max): Builder {
                                    return $query->whereRaw('DATEDIFF(tanggal_selesai, tanggal_mulai) + 1 <= ?', [$max]);
                                },
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        
                        if ($data['min_durasi'] ?? null) {
                            $indicators[] = "Durasi min: {$data['min_durasi']} hari";
                        }
                        
                        if ($data['max_durasi'] ?? null) {
                            $indicators[] = "Durasi max: {$data['max_durasi']} hari";
                        }
                        
                        return $indicators;
                    }),
                    
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal')
                            ->displayFormat('d M Y'),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal')
                            ->displayFormat('d M Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        
                        if ($data['dari_tanggal'] ?? null) {
                            $indicators[] = 'Daftar dari ' . Carbon::parse($data['dari_tanggal'])->format('d M Y');
                        }
                        
                        if ($data['sampai_tanggal'] ?? null) {
                            $indicators[] = 'Daftar sampai ' . Carbon::parse($data['sampai_tanggal'])->format('d M Y');
                        }
                        
                        return $indicators;
                    }),
                    
                Filter::make('tanggal_magang')
                    ->form([
                        Forms\Components\DatePicker::make('mulai_magang')
                            ->label('Mulai Magang')
                            ->displayFormat('d M Y'),
                        Forms\Components\DatePicker::make('selesai_magang')
                            ->label('Selesai Magang')
                            ->displayFormat('d M Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['mulai_magang'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_mulai', '>=', $date),
                            )
                            ->when(
                                $data['selesai_magang'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_selesai', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        
                        if ($data['mulai_magang'] ?? null) {
                            $indicators[] = 'Mulai dari ' . Carbon::parse($data['mulai_magang'])->format('d M Y');
                        }
                        
                        if ($data['selesai_magang'] ?? null) {
                            $indicators[] = 'Selesai sebelum ' . Carbon::parse($data['selesai_magang'])->format('d M Y');
                        }
                        
                        return $indicators;
                    }),
                    
                Filter::make('anggota_count')
                    ->label('Jumlah Anggota')
                    ->form([
                        Forms\Components\Select::make('anggota_count')
                            ->label('Jumlah Anggota')
                            ->options([
                                '0' => 'Tidak ada anggota',
                                '1' => '1 anggota',
                                '2' => '2 anggota',
                                '3' => '3 anggota',
                                '4' => '4 anggota',
                                '5' => '5 anggota',
                                '6' => '6 anggota',
                                'more_than_0' => 'Memiliki anggota',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['anggota_count'] === '0', fn ($query) => $query->has('anggota', 0))
                            ->when($data['anggota_count'] === 'more_than_0', fn ($query) => $query->has('anggota', '>', 0))
                            ->when(is_numeric($data['anggota_count']), fn ($query) => $query->has('anggota', $data['anggota_count']));
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!isset($data['anggota_count']) || $data['anggota_count'] === '') {
                            return null;
                        }
                        
                        return match($data['anggota_count']) {
                            '0' => 'Tidak ada anggota',
                            'more_than_0' => 'Memiliki anggota',
                            default => "{$data['anggota_count']} anggota",
                        };
                    }),
            ])
            ->actions([
                ViewAction::make()
                    ->color('info')
                    ->icon('heroicon-o-eye')
                    ->tooltip('Lihat detail'),
                    
                EditAction::make()
                    ->icon('heroicon-o-pencil')
                    ->tooltip('Edit pendaftaran'),
                    
                DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->tooltip('Hapus pendaftaran'),

                // Tombol Terima
                Action::make('terima')
                    ->label('Terima')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Terima Pendaftaran')
                    ->modalDescription(function ($record) {
                        $totalPeople = 1 + $record->anggota()->count();
                        return "Apakah Anda yakin ingin menerima pendaftaran ini dengan total {$totalPeople} orang (1 pendaftar + {$record->anggota()->count()} anggota)?";
                    })
                    ->modalSubmitActionLabel('Ya, Terima Pendaftaran')
                    ->visible(function ($record) {
                        if ($record->status === 'diterima') {
                            return false;
                        }
                        
                        // Cek kuota periode terkait
                        $periode = InternshipRequirement::where('deadline', '>=', $record->created_at)
                            ->where('created_at', '<=', $record->created_at)
                            ->orderBy('deadline', 'asc')
                            ->first();
                            
                        if ($periode) {
                            // Hitung pendaftar yang diterima (termasuk anggota timnya)
                            $pendaftaranDiterima = PendaftaranMagang::where('status', 'diterima')
                                ->where('created_at', '>=', $periode->created_at)
                                ->where('created_at', '<=', $periode->deadline)
                                ->get();
                            
                            // Hitung jumlah total orang (pendaftar utama + anggota tim)
                            $totalPeople = 0;
                            
                            foreach ($pendaftaranDiterima as $pendaftaran) {
                                // Tambahkan 1 untuk pendaftar utama
                                $totalPeople++;
                                
                                // Tambahkan jumlah anggota tim
                                $totalPeople += $pendaftaran->anggota()->count();
                            }
                            
                            // Hitung berapa banyak orang yang akan ditambahkan (pendaftar + anggota)
                            $additionalPeople = 1 + $record->anggota()->count();
                            
                            // Jika kuota sudah penuh, tidak bisa menerima pendaftar baru
                            if ($totalPeople + $additionalPeople > $periode->quota) {
                                return false;
                            }
                        }
                        
                        return true;
                    })
                    ->action(function ($record) {
                        $record->update(['status' => 'diterima']);

                        // Kirim email ke user
                        Mail::to($record->user->email)->send(new PendaftaranMagangMail('diterima'));
                        
                        $totalPeople = 1 + $record->anggota()->count();
                        
                        Notification::make()
                            ->title('Pendaftaran berhasil disetujui')
                            ->body("Total {$totalPeople} orang (1 pendaftar + {$record->anggota()->count()} anggota) telah ditambahkan ke kuota.")
                            ->success()
                            ->send();
                    }),

                // Tombol Tolak
                Action::make('tolak')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Pendaftaran')
                    ->modalDescription('Apakah Anda yakin ingin menolak pendaftaran ini?')
                    ->modalSubmitActionLabel('Ya, Tolak Pendaftaran')
                    ->visible(fn ($record) => $record->status !== 'ditolak')
                    ->form([
                        Forms\Components\Textarea::make('alasan_penolakan')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3)
                            ->helperText('Alasan penolakan akan ditampilkan kepada pendaftar.')
                            ->default('Mohon maaf, pendaftaran Anda tidak dapat diproses karena:'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'ditolak',
                            'alasan_penolakan' => $data['alasan_penolakan'],
                        ]);

                        // Kirim email ke user
                        Mail::to($record->user->email)->send(new PendaftaranMagangMail('ditolak', $data['alasan_penolakan']));
                        
                        Notification::make()
                            ->title('Pendaftaran telah ditolak')
                            ->warning()
                            ->send();
                    }),
                    
                // Tombol lihat dokumen dengan modal popup
                Action::make('view_document')
                    ->label('Dokumen')
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->modalContent(function ($record) {
                        if (!$record->surat_pengantar) {
                            return 'Tidak ada dokumen surat pengantar.';
                        }
                        
                        $url = asset('storage/' . $record->surat_pengantar);
                        
                        // Buat iframe untuk menampilkan PDF
                        return new HtmlString('
                            <div class="flex flex-col space-y-4">
                                <div class="bg-gray-100 p-2 rounded">
                                    <strong>Dokumen Surat Pengantar</strong>
                                </div>
                                <div class="w-full" style="height: 70vh;">
                                    <iframe src="' . $url . '" style="width: 100%; height: 100%; border: none;"></iframe>
                                </div>
                                <div class="flex justify-end space-x-2">
                                    <a href="' . $url . '" download class="inline-flex items-center justify-center gap-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-xs hover:bg-gray-200 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                            <path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z" />
                                            <path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z" />
                                        </svg>
                                        Unduh Dokumen
                                    </a>
                                </div>
                            </div>
                        ');
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->visible(fn ($record) => $record->surat_pengantar)
                    ->tooltip('Lihat dokumen surat pengantar'),
                    
                // Tombol Force Accept
                Action::make('force_accept')
                    ->label('Force Accept')
                    ->icon('heroicon-o-shield-check')
                    ->color('success')
                    ->outlined()
                    ->tooltip(function ($record) {
                        $totalPeople = 1 + $record->anggota()->count();
                        return "Terima paksa (bypass kuota) - {$totalPeople} orang";
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Force Accept Pendaftaran')
                    ->modalDescription(function ($record) {
                        $totalPeople = 1 + $record->anggota()->count();
                        
                        // Ambil info kuota yang tersisa
                        $periode = InternshipRequirement::where('deadline', '>=', $record->created_at)
                            ->where('created_at', '<=', $record->created_at)
                            ->orderBy('deadline', 'asc')
                            ->first();
                            
                        if (!$periode) {
                            return "Tindakan ini akan menerima pendaftaran dengan total {$totalPeople} orang meskipun kuota penuh! Lanjutkan?";
                        }
                        
                        // Hitung pendaftar yang diterima (termasuk anggota timnya)
                        $pendaftaranDiterima = PendaftaranMagang::where('status', 'diterima')
                            ->where('created_at', '>=', $periode->created_at)
                            ->where('created_at', '<=', $periode->deadline)
                            ->get();
                        
                        // Hitung jumlah total orang (pendaftar utama + anggota tim)
                        $totalAccepted = 0;
                        
                        foreach ($pendaftaranDiterima as $pendaftaran) {
                            // Tambahkan 1 untuk pendaftar utama
                            $totalAccepted++;
                            
                            // Tambahkan jumlah anggota tim
                            $totalAccepted += $pendaftaran->anggota()->count();
                        }
                        
                        $newTotal = $totalAccepted + $totalPeople;
                        $overQuota = $newTotal - $periode->quota;
                        
                        return "Tindakan ini akan menerima pendaftaran dengan total {$totalPeople} orang meskipun kuota sudah penuh! Kuota saat ini: {$totalAccepted}/{$periode->quota} orang. Setelah diterima akan menjadi: {$newTotal}/{$periode->quota} orang (kelebihan {$overQuota} orang). Lanjutkan?";
                    })
                    ->modalSubmitActionLabel('Ya, Terima Paksa')
                    ->visible(function ($record) {
                        if ($record->status === 'diterima') {
                            return false;
                        }
                        
                        // Hanya tampilkan untuk pendaftaran yang tidak bisa diterima karena kuota
                        $periode = InternshipRequirement::where('deadline', '>=', $record->created_at)
                            ->where('created_at', '<=', $record->created_at)
                            ->orderBy('deadline', 'asc')
                            ->first();
                            
                        if ($periode) {
                            // Hitung pendaftar yang diterima (termasuk anggota timnya)
                            $pendaftaranDiterima = PendaftaranMagang::where('status', 'diterima')
                                ->where('created_at', '>=', $periode->created_at)
                                ->where('created_at', '<=', $periode->deadline)
                                ->get();
                            
                            // Hitung jumlah total orang (pendaftar utama + anggota tim)
                            $totalPeople = 0;
                            
                            foreach ($pendaftaranDiterima as $pendaftaran) {
                                // Tambahkan 1 untuk pendaftar utama
                                $totalPeople++;
                                
                                // Tambahkan jumlah anggota tim
                                $totalPeople += $pendaftaran->anggota()->count();
                            }
                            
                            // Hitung berapa banyak orang yang akan ditambahkan (pendaftar + anggota)
                            $additionalPeople = 1 + $record->anggota()->count();
                            
                            // Jika kuota sudah penuh, tampilkan tombol force accept
                            if ($totalPeople + $additionalPeople > $periode->quota) {
                                return true;
                            }
                        }
                        
                        return false;
                    })
                    ->action(function ($record) {
                        $record->update(['status' => 'diterima']);

                        // Kirim email ke user
                        Mail::to($record->user->email)->send(new PendaftaranMagangMail('diterima'));
                        
                        $totalPeople = 1 + $record->anggota()->count();
                        
                        // Ambil info kuota yang tersisa
                        $periode = InternshipRequirement::where('deadline', '>=', $record->created_at)
                            ->where('created_at', '<=', $record->created_at)
                            ->orderBy('deadline', 'asc')
                            ->first();
                            
                        $kuotaInfo = "";
                        if ($periode) {
                            // Hitung pendaftar yang diterima (termasuk anggota timnya)
                            $pendaftaranDiterima = PendaftaranMagang::where('status', 'diterima')
                                ->where('created_at', '>=', $periode->created_at)
                                ->where('created_at', '<=', $periode->deadline)
                                ->get();
                            
                            // Hitung jumlah total orang (pendaftar utama + anggota tim)
                            $totalAccepted = 0;
                            
                            foreach ($pendaftaranDiterima as $pendaftaran) {
                                // Tambahkan 1 untuk pendaftar utama
                                $totalAccepted++;
                                
                                // Tambahkan jumlah anggota tim
                                $totalAccepted += $pendaftaran->anggota()->count();
                            }
                            
                            $kuotaInfo = " Kuota saat ini: {$totalAccepted}/{$periode->quota}.";
                        }
                        
                        Notification::make()
                            ->title('Pendaftaran berhasil disetujui secara paksa')
                            ->warning()
                            ->body("Total {$totalPeople} orang (1 pendaftar + {$record->anggota()->count()} anggota) telah ditambahkan meskipun kuota sudah penuh.{$kuotaInfo}")
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->icon('heroicon-o-trash')
                        ->modalHeading('Hapus Pendaftaran')
                        ->modalDescription('Apakah Anda yakin ingin menghapus pendaftaran yang dipilih?')
                        ->modalSubmitActionLabel('Ya, Hapus Pendaftaran'),
                    
                    ExportBulkAction::make()
                        ->label('Export ke Excel')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success'),
                        
                    Tables\Actions\BulkAction::make('terima_batch')
                        ->label('Terima Pendaftar')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->modalHeading('Terima Pendaftaran Batch')
                        ->modalDescription('Sistem akan menerima pendaftaran sesuai kuota yang tersedia.')
                        ->modalSubmitActionLabel('Ya, Terima Pendaftaran')
                        ->action(function (Collection $records) {
                            // Kelompokkan records berdasarkan periode
                            $recordsByPeriode = [];
                            foreach ($records as $record) {
                                if ($record->status === 'diterima') {
                                    continue;
                                }
                                
                                // Cari periode terkait
                                $periode = InternshipRequirement::where('deadline', '>=', $record->created_at)
                                    ->where('created_at', '<=', $record->created_at)
                                    ->orderBy('deadline', 'asc')
                                    ->first();
                                    
                                if (!$periode) {
                                    continue;
                                }
                                
                                if (!isset($recordsByPeriode[$periode->id])) {
                                    $recordsByPeriode[$periode->id] = [
                                        'periode' => $periode,
                                        'records' => [],
                                    ];
                                }
                                
                                $recordsByPeriode[$periode->id]['records'][] = $record;
                            }
                            
                            $totalDiterima = 0;
                            $totalKurangKuota = 0;
                            
                            // Proses berdasarkan periode
                            foreach ($recordsByPeriode as $periodeId => $data) {
                                $periode = $data['periode'];
                                $periodeRecords = $data['records'];
                                
                                // Hitung kuota tersisa
                                $acceptedCount = PendaftaranMagang::where('status', 'diterima')
                                    ->where('created_at', '>=', $periode->created_at)
                                    ->where('created_at', '<=', $periode->deadline)
                                    ->count();
                                    
                                $sisaKuota = $periode->quota - $acceptedCount;
                                
                                // Jika kuota masih tersedia
                                if ($sisaKuota > 0) {
                                    // Tentukan berapa banyak yang bisa diterima
                                    $jumlahDiterima = min(count($periodeRecords), $sisaKuota);
                                    
                                    // Ambil records yang akan diterima
                                    $recordsDiterima = array_slice($periodeRecords, 0, $jumlahDiterima);
                                    
                                    // Update status dan kirim email
                                    foreach ($recordsDiterima as $record) {
                                        $record->update(['status' => 'diterima']);
                                        Mail::to($record->user->email)->send(new PendaftaranMagangMail('diterima'));
                                        $totalDiterima++;
                                    }
                                    
                                    // Jika ada yang tidak bisa diterima karena kuota
                                    if (count($periodeRecords) > $sisaKuota) {
                                        $totalKurangKuota += (count($periodeRecords) - $sisaKuota);
                                    }
                                } else {
                                    $totalKurangKuota += count($periodeRecords);
                                }
                            }
                            
                            // Tampilkan notifikasi hasil
                            if ($totalDiterima > 0) {
                                $message = "{$totalDiterima} pendaftaran berhasil diterima.";
                                
                                if ($totalKurangKuota > 0) {
                                    $message .= " {$totalKurangKuota} pendaftaran tidak dapat diterima karena kuota penuh.";
                                    
                                    Notification::make()
                                        ->title('Pendaftaran Batch Diproses')
                                        ->warning()
                                        ->body($message)
                                        ->persistent()
                                        ->send();
                                } else {
                                    Notification::make()
                                        ->title('Pendaftaran Batch Diterima')
                                        ->success()
                                        ->body($message)
                                        ->send();
                                }
                            } else if ($totalKurangKuota > 0) {
                                Notification::make()
                                    ->title('Tidak Ada Pendaftaran yang Diterima')
                                    ->danger()
                                    ->body("Semua pendaftaran ({$totalKurangKuota}) tidak dapat diterima karena kuota penuh.")
                                    ->persistent()
                                    ->send();
                            }
                        }),
                        
                    Tables\Actions\BulkAction::make('force_approve_batch')
                        ->label('Force Approve')
                        ->icon('heroicon-o-shield-check')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->modalHeading('Force Approve Pendaftaran')
                        ->modalDescription('Tindakan ini akan menerima semua pendaftaran yang dipilih meskipun kuota penuh! Lanjutkan?')
                        ->modalSubmitActionLabel('Ya, Approve Paksa')
                        ->action(function (Collection $records) {
                            $approved = 0;
                            
                            foreach ($records as $record) {
                                if ($record->status !== 'diterima') {
                                    $record->update(['status' => 'diterima']);
                                    Mail::to($record->user->email)->send(new PendaftaranMagangMail('diterima'));
                                    $approved++;
                                }
                            }
                            
                            Notification::make()
                                ->title("{$approved} Pendaftaran Disetujui Secara Paksa")
                                ->warning()
                                ->body('Pendaftaran telah disetujui meskipun mungkin melebihi kuota.')
                                ->persistent()
                                ->send();
                        }),
                        
                    Tables\Actions\BulkAction::make('tolak_batch')
                        ->label('Tolak Pendaftar')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->modalHeading('Tolak Pendaftaran Batch')
                        ->modalDescription('Apakah Anda yakin ingin menolak semua pendaftaran yang dipilih?')
                        ->modalSubmitActionLabel('Ya, Tolak Pendaftaran')
                        ->form([
                            Forms\Components\Textarea::make('alasan_penolakan')
                                ->label('Alasan Penolakan')
                                ->required()
                                ->rows(3)
                                ->helperText('Alasan penolakan akan ditampilkan kepada semua pendaftar yang dipilih.')
                                ->default('Mohon maaf, pendaftaran Anda tidak dapat diproses karena:'),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $rejected = 0;
                            
                            foreach ($records as $record) {
                                if ($record->status !== 'ditolak') {
                                    $record->update([
                                        'status' => 'ditolak',
                                        'alasan_penolakan' => $data['alasan_penolakan'],
                                    ]);
                                    Mail::to($record->user->email)->send(new PendaftaranMagangMail('ditolak', $data['alasan_penolakan']));
                                    $rejected++;
                                }
                            }
                            
                            Notification::make()
                                ->title("{$rejected} Pendaftaran Ditolak")
                                ->warning()
                                ->body('Email notifikasi telah dikirim ke semua pendaftar.')
                                ->send();
                        }),
                ]),
            ])
            ->headerActions([
                // Tombol Toggle Pendaftaran
                Action::make('toggle_pendaftaran')
                    ->label(fn () => Setting::first()?->status_pendaftaran ? 'Tutup Pendaftaran' : 'Buka Pendaftaran')
                    ->icon(fn () => Setting::first()?->status_pendaftaran ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
                    ->color(fn () => Setting::first()?->status_pendaftaran ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn () => Setting::first()?->status_pendaftaran ? 'Tutup Pendaftaran Magang' : 'Buka Pendaftaran Magang')
                    ->modalDescription(fn () => Setting::first()?->status_pendaftaran 
                        ? 'Menutup pendaftaran akan mencegah mahasiswa baru mendaftar. Yakin ingin menutup pendaftaran?' 
                        : 'Membuka pendaftaran akan memungkinkan mahasiswa baru mendaftar. Yakin ingin membuka pendaftaran?')
                    ->modalSubmitActionLabel(fn () => Setting::first()?->status_pendaftaran ? 'Ya, Tutup Pendaftaran' : 'Ya, Buka Pendaftaran')
                    ->action(function () {
                        $setting = Setting::first();
                        if (!$setting) {
                            $setting = Setting::create(['status_pendaftaran' => 1]);
                        }
                        
                        $oldStatus = $setting->status_pendaftaran;
                        $setting->update(['status_pendaftaran' => !$oldStatus]);
                        
                        Notification::make()
                            ->title($oldStatus ? 'Pendaftaran Ditutup' : 'Pendaftaran Dibuka')
                            ->icon($oldStatus ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
                            ->color($oldStatus ? 'danger' : 'success')
                            ->send();
                    }),
                    
                // Dashboard Mini Stats
                Action::make('dashboard_stats')
                    ->label('Statistik Pendaftaran')
                    ->icon('heroicon-o-chart-bar')
                    ->color('gray')
                    ->action(function () {
                        $pendingCount = PendaftaranMagang::where('status', 'pending')->count();
                        $acceptedCount = PendaftaranMagang::where('status', 'diterima')->count();
                        $rejectedCount = PendaftaranMagang::where('status', 'ditolak')->count();
                        $totalCount = PendaftaranMagang::count();
                        
                        $acceptRate = $totalCount > 0 ? round(($acceptedCount / $totalCount) * 100) : 0;
                        
                        $periode = InternshipRequirement::where('is_active', true)->first();
                        
                        $currentPeriodCount = 0;
                        $currentPeriodAccepted = 0;
                        $kuotaRemainingText = 'Tidak ada periode aktif';
                        
                        if ($periode) {
                            $currentPeriodCount = PendaftaranMagang::where('created_at', '>=', $periode->created_at)
                                ->where('created_at', '<=', $periode->deadline)
                                ->count();
                                
                            $currentPeriodAccepted = PendaftaranMagang::where('status', 'diterima')
                                ->where('created_at', '>=', $periode->created_at)
                                ->where('created_at', '<=', $periode->deadline)
                                ->count();
                                
                            $remaining = $periode->quota - $currentPeriodAccepted;
                            $kuotaRemainingText = "Kuota tersisa: {$remaining} dari {$periode->quota}";
                        }
                        
                        $message = "<div class='space-y-3'>";
                        $message .= "<div><span class='font-medium'>Total Pendaftaran:</span> {$totalCount}</div>";
                        $message .= "<div><span class='font-medium'>Pending:</span> {$pendingCount}</div>";
                        $message .= "<div><span class='font-medium'>Diterima:</span> {$acceptedCount}</div>";
                        $message .= "<div><span class='font-medium'>Ditolak:</span> {$rejectedCount}</div>";
                        $message .= "<div><span class='font-medium'>Tingkat Penerimaan:</span> {$acceptRate}%</div>";
                        
                        if ($periode) {
                            $message .= "<hr class='my-2'>";
                            $message .= "<div><span class='font-medium'>Periode Saat Ini:</span> {$periode->period}</div>";
                            $message .= "<div><span class='font-medium'>Pendaftar Periode Ini:</span> {$currentPeriodCount}</div>";
                            $message .= "<div><span class='font-medium'>Diterima Periode Ini:</span> {$currentPeriodAccepted}</div>";
                            $message .= "<div><span class='font-medium'>{$kuotaRemainingText}</span></div>";
                        }
                        
                        $message .= "</div>";
                        
                        Notification::make()
                            ->title('Statistik Pendaftaran Magang')
                            ->body(new HtmlString($message))
                            ->icon('heroicon-o-chart-bar')
                            ->color('primary')
                            ->persistent()
                            ->send();
                    }),
            ])
            ->groups([
                Group::make('status')
                    ->getTitleFromRecordUsing(fn (PendaftaranMagang $record): string => match ($record->status) {
                        'pending' => 'Pending',
                        'diterima' => 'Diterima',
                        'ditolak' => 'Ditolak',
                        default => $record->status,
                    })
                    ->collapsible(),
                Group::make('created_at')
                    ->date()
                    ->label('Bulan Pendaftaran')
                    ->collapsible(),
                Group::make('asal_kampus')
                    ->label('Kampus')
                    ->collapsible(),
            ])
            ->emptyStateIcon('heroicon-o-clipboard')
            ->emptyStateHeading('Belum ada pendaftaran')
            ->emptyStateDescription('Belum ada mahasiswa yang mendaftar magang untuk periode ini.')
            ->emptyStateActions([
                Action::make('buka_pendaftaran')
                    ->label('Buka Pendaftaran')
                    ->icon('heroicon-o-lock-open')
                    ->color('primary')
                    ->action(function () {
                        $setting = Setting::first();
                        if (!$setting) {
                            $setting = Setting::create(['status_pendaftaran' => 1]);
                        } else {
                            $setting->update(['status_pendaftaran' => 1]);
                        }
                        
                        Notification::make()
                            ->title('Pendaftaran Dibuka')
                            ->icon('heroicon-o-lock-open')
                            ->color('success')
                            ->send();
                    })
                    ->visible(fn () => !Setting::first()?->status_pendaftaran),
            ])
            ->recordClasses(fn (PendaftaranMagang $record) => match ($record->status) {
                'pending' => 'border-l-4 border-warning-500',
                'diterima' => 'border-l-4 border-success-500',
                'ditolak' => 'border-l-4 border-danger-500',
                default => null,
            })
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Grid::make(3)
                    ->schema([
                        Infolists\Components\Section::make('Informasi Pendaftar')
                            ->icon('heroicon-o-user')
                            ->collapsible()
                            ->description('Detail mahasiswa yang mendaftar magang')
                            ->schema([
                                Infolists\Components\TextEntry::make('user.name')
                                    ->label('Nama Lengkap')
                                    ->icon('heroicon-m-user')
                                    ->weight('bold')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                    
                                Infolists\Components\TextEntry::make('user.email')
                                    ->label('Email')
                                    ->icon('heroicon-m-envelope')
                                    ->copyable()
                                    ->url(fn ($record) => "mailto:{$record->user->email}"),
                                    
                                Infolists\Components\TextEntry::make('asal_kampus')
                                    ->label('Asal Kampus')
                                    ->icon('heroicon-m-academic-cap'),
                                    
                                // Infolists\Components\TextEntry::make('jurusan')
                                //     ->label('Jurusan')
                                //     ->icon('heroicon-m-book-open'),
                                    
                                Infolists\Components\Grid::make(2)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('tanggal_mulai')
                                            ->label('Tanggal Mulai')
                                            ->icon('heroicon-m-calendar')
                                            ->date(),
                                            
                                        Infolists\Components\TextEntry::make('tanggal_selesai')
                                            ->label('Tanggal Selesai')
                                            ->icon('heroicon-m-calendar')
                                            ->date(),
                                    ]),
                                    
                                Infolists\Components\TextEntry::make('durasi_magang')
                                    ->label('Durasi Magang')
                                    ->icon('heroicon-m-clock')
                                    ->getStateUsing(function (PendaftaranMagang $record): string {
                                        if (!$record->tanggal_mulai || !$record->tanggal_selesai) {
                                            return '-';
                                        }
                                        
                                        $start = Carbon::parse($record->tanggal_mulai);
                                        $end = Carbon::parse($record->tanggal_selesai);
                                        $diffInDays = $end->diffInDays($start) + 1;
                                        $diffInWeeks = ceil($diffInDays / 7);
                                        $diffInMonths = ceil($diffInDays / 30);
                                        
                                        $result = "{$diffInDays} hari ";
                                
                                        return new HtmlString($result);
                                    }),
                            ])
                            ->columnSpan(2),
                            
                        Infolists\Components\Section::make('Status Pendaftaran')
                            ->icon('heroicon-o-check-circle')
                            ->collapsible()
                            ->schema([
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'diterima' => 'success',
                                        'ditolak' => 'danger',
                                        default => 'warning',
                                    })
                                    ->icon(fn (string $state): string => match ($state) {
                                        'diterima' => 'heroicon-m-check-circle',
                                        'ditolak' => 'heroicon-m-x-circle',
                                        default => 'heroicon-m-clock',
                                    })
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                    
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Tanggal Pendaftaran')
                                    ->icon('heroicon-m-calendar')
                                    ->dateTime('d M Y H:i'),
                                    
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Terakhir Diperbarui')
                                    ->icon('heroicon-m-arrow-path')
                                    ->dateTime('d M Y H:i'),
                                    
                                Infolists\Components\TextEntry::make('alasan_penolakan')
                                    ->label('Alasan Penolakan')
                                    ->icon('heroicon-m-exclamation-circle')
                                    ->visible(fn ($record) => $record->status === 'ditolak')
                                    ->color('danger')
                                    ->markdown()
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan(1),
                    ]),
                    
                Infolists\Components\Grid::make(2)
                    ->schema([
                        Infolists\Components\Section::make('Periode Magang')
                            ->icon('heroicon-o-calendar')
                            ->collapsible()
                            ->collapsed(false)
                            ->schema([
                                Infolists\Components\TextEntry::make('periode_magang')
                                    ->label('Terdaftar Pada Periode')
                                    ->icon('heroicon-m-calendar-days')
                                    ->getStateUsing(function (PendaftaranMagang $record) {
                                        // Mencari periode yang sesuai berdasarkan tanggal pendaftaran
                                        $periode = InternshipRequirement::where('deadline', '>=', $record->created_at)
                                            ->where('created_at', '<=', $record->created_at)
                                            ->orderBy('deadline', 'asc')
                                            ->first();
                                            
                                        if (!$periode) {
                                            return new HtmlString('<span class="text-danger-500">Tidak terkait dengan periode manapun</span>');
                                        }
                                        
                                        $statusIcon = $periode->isCurrentlyActive() ? '🟢' : '🔴';
                                        $statusText = $periode->isCurrentlyActive() 
                                            ? '<span class="text-success-500 font-medium">Aktif</span>' 
                                            : '<span class="text-danger-500">Tidak Aktif</span>';
                                            
                                        return new HtmlString("{$statusIcon} {$periode->period} ({$statusText}) <br>Deadline: {$periode->deadline->format('d M Y')}");
                                    }),
                                    
                                Infolists\Components\TextEntry::make('kuota_periode')
                                    ->label('Kuota Periode')
                                    ->icon('heroicon-m-users')
                                    ->getStateUsing(function (PendaftaranMagang $record) {
                                        // Mencari periode yang sesuai
                                        $periode = InternshipRequirement::where('deadline', '>=', $record->created_at)
                                            ->where('created_at', '<=', $record->created_at)
                                            ->orderBy('deadline', 'asc')
                                            ->first();
                                            
                                        if (!$periode) {
                                            return new HtmlString('<span class="text-danger-500">Tidak terkait dengan periode manapun</span>');
                                        }
                                        
                                        // Hitung pendaftar yang diterima
                                        $acceptedCount = PendaftaranMagang::where('status', 'diterima')
                                            ->where('created_at', '>=', $periode->created_at)
                                            ->where('created_at', '<=', $periode->deadline)
                                            ->count();
                                            
                                        $persentase = $periode->quota > 0 ? round(($acceptedCount / $periode->quota) * 100) : 0;
                                        
                                        $colorClass = 'text-primary-500';
                                        if ($acceptedCount >= $periode->quota) {
                                            $colorClass = 'text-danger-500';
                                        } elseif ($acceptedCount >= $periode->quota * 0.8) {
                                            $colorClass = 'text-warning-500';
                                        }
                                        
                                        return new HtmlString("<span class='{$colorClass} font-medium'>{$acceptedCount}/{$periode->quota}</span> ({$persentase}%)");
                                    }),
                                    
                                Infolists\Components\TextEntry::make('status_periode')
                                    ->label('Status Periode')
                                    ->icon('heroicon-m-information-circle')
                                    ->getStateUsing(function (PendaftaranMagang $record) {
                                        $periode = InternshipRequirement::where('deadline', '>=', $record->created_at)
                                            ->where('created_at', '<=', $record->created_at)
                                            ->orderBy('deadline', 'asc')
                                            ->first();
                                            
                                        if (!$periode) {
                                            return new HtmlString('<span class="text-danger-500">Tidak terkait dengan periode manapun</span>');
                                        }
                                        
                                        $daysLeft = now()->diffInDays($periode->deadline, false);
                                        
                                        if ($daysLeft < 0) {
                                            return new HtmlString('<span class="text-danger-500">Periode telah berakhir</span>');
                                        } else if ($daysLeft == 0) {
                                            return new HtmlString('<span class="text-warning-500 font-medium">Periode berakhir hari ini!</span>');
                                        } else if ($daysLeft <= 7) {
                                            return new HtmlString("<span class='text-warning-500 font-medium'>Periode berakhir dalam {$daysLeft} hari lagi</span>");
                                        } else {
                                            return new HtmlString("<span class='text-success-500'>Periode masih berlangsung ({$daysLeft} hari lagi)</span>");
                                        }
                                    }),
                            ]),
                            
                        Infolists\Components\Section::make('Dokumen')
                            ->icon('heroicon-o-document')
                            ->collapsible()
                            ->collapsed(false)
                            ->schema([
                                Infolists\Components\TextEntry::make('surat_pengantar_info')
                                    ->label('Surat Pengantar')
                                    ->getStateUsing(function (PendaftaranMagang $record) {
                                        if (!$record->surat_pengantar) {
                                            return new HtmlString('<span class="text-danger-500">Tidak ada file surat pengantar</span>');
                                        }
                                        
                                        $url = asset('storage/' . $record->surat_pengantar);
                                        
                                        $html = "<div class='space-y-2'>";
                                        $html .= "<div class='flex items-center gap-3'>";
                                        $html .= "<span class='inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800'>PDF</span>";
                                        $html .= "<span>Surat Pengantar Magang</span>";
                                        $html .= "</div>";
                                        
                                        $html .= "<div class='flex gap-2 mt-2'>";
                                        $html .= "<a href='{$url}' target='_blank' class='inline-flex items-center justify-center gap-1 px-4 py-2 bg-primary-500 text-white rounded-md text-xs hover:bg-primary-600 transition-colors'>";
                                        $html .= "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='currentColor' class='w-4 h-4'><path d='M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z' /><path fill-rule='evenodd' d='M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z' clip-rule='evenodd' /></svg>";
                                        $html .= "Lihat Dokumen";
                                        $html .= "</a>";
                                        
                                        $html .= "<a href='{$url}' download class='inline-flex items-center justify-center gap-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-xs hover:bg-gray-200 transition-colors'>";
                                        $html .= "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='currentColor' class='w-4 h-4'><path d='M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z' /><path d='M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z' /></svg>";
                                        $html .= "Unduh Dokumen";
                                        $html .= "</a>";
                                        $html .= "</div>";
                                        
                                        $html .= "</div>";
                                        
                                        return new HtmlString($html);
                                    }),
                            ]),
                    ]),
                    
                Infolists\Components\Section::make('Anggota Tim')
                    ->icon('heroicon-o-users')
                    ->visible(fn ($record) => $record->anggota && $record->anggota->count() > 0)
                    ->collapsible()
                    ->collapsed(false)
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('anggota')
                            ->schema([
                                Infolists\Components\TextEntry::make('nama_anggota')
                                    ->label('Nama')
                                    ->icon('heroicon-m-user')
                                    ->weight('medium'),
                                    
                                Infolists\Components\TextEntry::make('nim_anggota')
                                    ->label('NIM')
                                    ->icon('heroicon-m-identification'),
                                    
                                Infolists\Components\TextEntry::make('no_hp_anggota')
                                    ->label('No. HP')
                                    ->icon('heroicon-m-phone')
                                    ->url(fn ($record) => "tel:{$record->no_hp_anggota}")
                                    ->copyable(),
                                    
                                Infolists\Components\TextEntry::make('email_anggota')
                                    ->label('Email')
                                    ->icon('heroicon-m-envelope')
                                    ->url(fn ($record) => "mailto:{$record->email_anggota}")
                                    ->copyable(),
                                    
                                Infolists\Components\TextEntry::make('jurusan')
                                    ->label('Jurusan')
                                    ->icon('heroicon-m-academic-cap'),
                            ])
                            ->columns(3),
                    ]),
                    
                Infolists\Components\Section::make('Tombol Aksi')
                    ->collapsible(false)
                    ->schema([
                        Infolists\Components\Actions::make([
                            Infolists\Components\Actions\Action::make('tolak')
                                ->label('Tolak Pendaftaran')
                                ->icon('heroicon-m-x-circle')
                                ->color('danger')
                                ->button()
                                ->requiresConfirmation()
                                ->modalHeading('Tolak Pendaftaran')
                                ->modalDescription('Apakah Anda yakin ingin menolak pendaftaran ini?')
                                ->modalSubmitActionLabel('Ya, Tolak Pendaftaran')
                                ->visible(fn ($record) => $record->status !== 'ditolak')
                                ->form([
                                    Forms\Components\Textarea::make('alasan_penolakan')
                                        ->label('Alasan Penolakan')
                                        ->required()
                                        ->rows(3)
                                        ->placeholder('Masukkan alasan penolakan...')
                                        ->helperText('Alasan ini akan ditampilkan kepada pendaftar.')
                                        ->default('Mohon maaf, pendaftaran Anda tidak dapat diproses karena:'),
                                        
                                    Forms\Components\Checkbox::make('kirim_email')
                                        ->label('Kirim email notifikasi')
                                        ->default(true),
                                ])
                                ->action(function (PendaftaranMagang $record, array $data) {
                                    $record->update([
                                        'status' => 'ditolak',
                                        'alasan_penolakan' => $data['alasan_penolakan'],
                                    ]);
                                    
                                    if ($data['kirim_email']) {
                                        Mail::to($record->user->email)->send(new PendaftaranMagangMail('ditolak', $data['alasan_penolakan']));
                                    }
                                    
                                    Notification::make()
                                        ->title('Pendaftaran telah ditolak')
                                        ->warning()
                                        ->send();
                                }),
                                
                            Infolists\Components\Actions\Action::make('terima')
                                ->label('Terima Pendaftaran')
                                ->icon('heroicon-m-check-circle')
                                ->color('success')
                                ->button()
                                ->requiresConfirmation()
                                ->modalHeading('Terima Pendaftaran')
                                ->modalDescription('Apakah Anda yakin ingin menerima pendaftaran ini?')
                                ->modalSubmitActionLabel('Ya, Terima Pendaftaran')
                                ->visible(function ($record) {
                                    if ($record->status === 'diterima') {
                                        return false;
                                    }
                                    
                                    // Cek kuota periode terkait
                                    $periode = InternshipRequirement::where('deadline', '>=', $record->created_at)
                                        ->where('created_at', '<=', $record->created_at)
                                        ->orderBy('deadline', 'asc')
                                        ->first();
                                        
                                    if ($periode) {
                                        $acceptedCount = PendaftaranMagang::where('status', 'diterima')
                                            ->where('created_at', '>=', $periode->created_at)
                                            ->where('created_at', '<=', $periode->deadline)
                                            ->count();
                                            
                                        // Jika kuota sudah penuh, tidak bisa menerima pendaftar baru
                                        if ($acceptedCount >= $periode->quota) {
                                            return false;
                                        }
                                    }
                                    
                                    return true;
                                })
                                ->form([
                                    Forms\Components\Checkbox::make('kirim_email')
                                        ->label('Kirim email notifikasi')
                                        ->default(true),
                                ])
                                ->action(function (PendaftaranMagang $record, array $data) {
                                    $record->update(['status' => 'diterima']);
                                    
                                    if ($data['kirim_email']) {
                                        Mail::to($record->user->email)->send(new PendaftaranMagangMail('diterima'));
                                    }
                                    
                                    Notification::make()
                                        ->title('Pendaftaran berhasil disetujui')
                                        ->success()
                                        ->send();
                                }),
                                
                            Infolists\Components\Actions\Action::make('force_accept')
                                ->label('Force Accept')
                                ->icon('heroicon-m-shield-check')
                                ->color('warning')
                                ->button()
                                ->requiresConfirmation()
                                ->modalHeading('Force Accept Pendaftaran')
                                ->modalDescription('Tindakan ini akan menerima pendaftaran meskipun kuota penuh! Lanjutkan?')
                                ->modalSubmitActionLabel('Ya, Terima Paksa')
                                ->visible(function ($record) {
                                    if ($record->status === 'diterima') {
                                        return false;
                                    }
                                    
                                    // Hanya tampilkan untuk pendaftaran yang tidak bisa diterima karena kuota
                                    $periode = InternshipRequirement::where('deadline', '>=', $record->created_at)
                                        ->where('created_at', '<=', $record->created_at)
                                        ->orderBy('deadline', 'asc')
                                        ->first();
                                        
                                    if ($periode) {
                                        $acceptedCount = PendaftaranMagang::where('status', 'diterima')
                                            ->where('created_at', '>=', $periode->created_at)
                                            ->where('created_at', '<=', $periode->deadline)
                                            ->count();
                                            
                                        // Jika kuota sudah penuh, tampilkan tombol force accept
                                        if ($acceptedCount >= $periode->quota) {
                                            return true;
                                        }
                                    }
                                    
                                    return false;
                                })
                                ->form([
                                    Forms\Components\Checkbox::make('kirim_email')
                                        ->label('Kirim email notifikasi')
                                        ->default(true),
                                ])
                                ->action(function (PendaftaranMagang $record, array $data) {
                                    $record->update(['status' => 'diterima']);
                                    
                                    if ($data['kirim_email']) {
                                        Mail::to($record->user->email)->send(new PendaftaranMagangMail('diterima'));
                                    }
                                    
                                    Notification::make()
                                        ->title('Pendaftaran berhasil disetujui secara paksa')
                                        ->warning()
                                        ->body('Pendaftaran ini disetujui meskipun kuota sudah penuh.')
                                        ->send();
                                }),
                                
                            Infolists\Components\Actions\Action::make('view_document')
                                ->label('Lihat Dokumen')
                                ->icon('heroicon-m-document-text')
                                ->color('gray')
                                ->button()
                                ->url(fn ($record) => $record->surat_pengantar ? asset('storage/' . $record->surat_pengantar) : '#')
                                ->openUrlInNewTab()
                                ->visible(fn ($record) => $record->surat_pengantar),
                        ]),
                    ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('anggota');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPendaftaranMagangs::route('/'),
            'create' => Pages\CreatePendaftaranMagang::route('/create'),
            'edit' => Pages\EditPendaftaranMagang::route('/{record}/edit'),
            'view' => Pages\ViewPendaftaranMagang::route('/{record}'),
        ];
    }
}