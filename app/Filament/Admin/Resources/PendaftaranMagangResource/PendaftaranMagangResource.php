<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PendaftaranMagangResource\Pages;
use App\Models\PendaftaranMagang;
use App\Models\InternshipRequirement;
use App\Models\Setting;
use App\Models\User;
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
use Filament\Tables\Grouping\Group;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        return \Illuminate\Support\Facades\Cache::remember('badge_pending_count', 60, function() {
            return static::getModel()::where('status', 'pending')->count();
        });
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $pendingCount = (int) static::getNavigationBadge();
        
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
                                Forms\Components\Section::make('Informasi Pendaftar')
                                    ->icon('heroicon-o-user')
                                    ->description('Detail pendaftar magang')
                                    ->collapsible()
                                    ->schema([
                                        // Select User untuk create mode atau display untuk edit mode
                                        Forms\Components\Select::make('user_id')
                                            ->label('Pengguna')
                                            ->relationship(
                                                'user',
                                                'name',
                                                fn (Builder $query) => $query->whereNotExists(
                                                    fn ($query) => $query->select('id')
                                                        ->from('pendaftaran_magangs')
                                                        ->whereColumn('pendaftaran_magangs.user_id', 'users.id')
                                                        ->where('status', '!=', 'ditolak')
                                                )
                                            )
                                            ->preload()
                                            ->searchable()
                                            ->required()
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Nama Lengkap')
                                                    ->required()
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('email')
                                                    ->label('Email')
                                                    ->email()
                                                    ->required()
                                                    ->unique('users', 'email')
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('password')
                                                    ->label('Password')
                                                    ->password()
                                                    ->required()
                                                    ->minLength(8),
                                            ])
                                            ->createOptionUsing(function (array $data): int {
                                                return User::create([
                                                    'name' => $data['name'],
                                                    'email' => $data['email'],
                                                    'password' => bcrypt($data['password']),
                                                    'email_verified_at' => now(),
                                                ])->id;
                                            })
                                            ->hiddenOn('edit'),
                                        
                                        // Display user info on edit page
                                        Forms\Components\Placeholder::make('user_info')
                                            ->label('Informasi Pengguna')
                                            ->content(function (PendaftaranMagang $record): string {
                                                if (!$record->exists) {
                                                    return '-';
                                                }
                                                return "{$record->user->name} ({$record->user->email})";
                                            })
                                            ->visibleOn('edit'),
                                        
                                        Forms\Components\Select::make('asal_kampus')
                                            ->label('Asal Kampus')
                                            ->options(function () {
                                                // Ambil data dari tabel universitas
                                                return DB::table('universitas')
                                                    ->pluck('nama_universitas', 'nama_universitas')
                                                    ->toArray();
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('nama_universitas')
                                                    ->label('Nama Universitas/Kampus')
                                                    ->required()
                                                    ->maxLength(255),
                                            ])
                                            ->createOptionUsing(function (array $data) {
                                                // Simpan ke tabel universitas
                                                DB::table('universitas')->insert([
                                                    'nama_universitas' => $data['nama_universitas'],
                                                    'created_at' => now(),
                                                    'updated_at' => now(),
                                                ]);
                                                
                                                // Return nama kampus untuk field asal_kampus
                                                return $data['nama_universitas'];
                                            })
                                            ->required()
                                            ->suffixIcon('heroicon-m-academic-cap'),
                                            
                                        // Hidden field untuk jurusan (default value)
                                        Forms\Components\Hidden::make('jurusan')
                                            ->default('Lihat di data anggota'),
                                        
                                        Forms\Components\Grid::make()
                                            ->schema([
                                                Forms\Components\DatePicker::make('tanggal_mulai')
                                                    ->label('Tanggal Mulai')
                                                    ->required()
                                                    ->displayFormat('d M Y')
                                                    ->suffixIcon('heroicon-m-calendar-days')
                                                    ->reactive()
                                                    ->afterStateUpdated(function ($state, callable $set) {
                                                        if ($state) {
                                                            $set('tanggal_selesai', Carbon::parse($state)->addMonths(1)->format('Y-m-d'));
                                                        }
                                                    }),
                                                    
                                                Forms\Components\DatePicker::make('tanggal_selesai')
                                                    ->label('Tanggal Selesai')
                                                    ->required()
                                                    ->displayFormat('d M Y')
                                                    ->suffixIcon('heroicon-m-calendar-days')
                                                    ->rules([
                                                        fn (Forms\Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                                            if ($value && $get('tanggal_mulai') && $value <= $get('tanggal_mulai')) {
                                                                $fail('Tanggal selesai harus setelah tanggal mulai');
                                                            }
                                                        },
                                                    ]),
                                            ])
                                            ->columns(2),
                                            
                                        Forms\Components\FileUpload::make('surat_pengantar')
                                            ->label('Surat Pengantar')
                                            ->required()
                                            ->directory('surat-pengantar')
                                            ->visibility('public')
                                            ->acceptedFileTypes(['application/pdf'])
                                            ->maxSize(5120) // 5MB
                                            ->helperText('Upload file PDF surat pengantar dari universitas/institusi (Maksimal 5MB)')
                                            ->disk('public')
                                            ->preserveFilenames()
                                            ->openable()
                                            ->downloadable()
                                            ->maxWidth('full'),
                                            
                                        Forms\Components\Placeholder::make('durasi_magang')
                                            ->label('Durasi Magang')
                                            ->content(function (Forms\Get $get): string {
                                                if (!$get('tanggal_mulai') || !$get('tanggal_selesai')) {
                                                    return '-';
                                                }
                                                
                                                $start = Carbon::parse($get('tanggal_mulai'));
                                                $end = Carbon::parse($get('tanggal_selesai'));
                                                $diffInDays = $end->diffInDays($start) + 1;
                                                
                                                return "{$diffInDays} hari";
                                            })
                                            ->reactive(),
                                    ])
                                    ->columns(2),
                                    
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
                                                    if (!$get('alasan_penolakan')) {
                                                        $set('alasan_penolakan', 'Mohon maaf, pendaftaran Anda tidak dapat diproses karena:');
                                                    }
                                                } else {
                                                    $set('alasan_penolakan', null);
                                                }
                                            }),

                                        Forms\Components\Textarea::make('alasan_penolakan')
                                            ->label('Alasan Penolakan')
                                            ->visible(fn ($get) => $get('status') === 'ditolak')
                                            ->required(fn ($get) => $get('status') === 'ditolak')
                                            ->rows(4)
                                            ->columnSpanFull()
                                            ->helperText('Alasan ini akan ditampilkan pada pendaftar'),
                                            
                                        Forms\Components\Checkbox::make('kirim_notifikasi')
                                            ->label('Kirim Notifikasi Email')
                                            ->helperText('Centang untuk mengirim notifikasi email ke pendaftar')
                                            ->default(true)
                                            ->dehydrated(false),
                                    ]),
                            ]),
                            
                        Tabs\Tab::make('Anggota Tim')
                            ->icon('heroicon-o-users')
                            ->schema([
                                Forms\Components\Section::make('Anggota Tim Magang')
                                    ->icon('heroicon-o-users')
                                    ->description('Daftar anggota tim magang (maksimal 6 anggota)')
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
                                                    ->maxLength(50)
                                                    ->unique('anggota_pendaftaran', 'nim_anggota', ignoreRecord: true),
                                                    
                                                Forms\Components\TextInput::make('no_hp_anggota')
                                                    ->label('No HP Anggota')
                                                    ->required()
                                                    ->tel()
                                                    ->maxLength(15)
                                                    ->rules(['regex:/^(\+62|62|0)([0-9]){9,}$/']),
                                                    
                                                Forms\Components\TextInput::make('email_anggota')
                                                    ->label('Email Anggota')
                                                    ->required()
                                                    ->email()
                                                    ->maxLength(255)
                                                    ->unique('anggota_pendaftaran', 'email_anggota', ignoreRecord: true),
                                                    
                                                Forms\Components\TextInput::make('jurusan')
                                                    ->label('Jurusan Anggota')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->hint('Masukkan jurusan/program studi anggota'),
                                            ])
                                            ->columns(2)
                                            ->maxItems(6)
                                            ->minItems(0)
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string => $state['nama_anggota'] ?? null)
                                            ->deleteAction(
                                                fn (Forms\Components\Actions\Action $action) => $action->requiresConfirmation()
                                            )
                                            ->reorderable()
                                            ->defaultItems(0),
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
                        
                        return "{$diffInDays} hari";
                    })
                    ->toggleable(),
                    
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
                // Hanya filter yang penting
                SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        'pending' => 'Pending',
                        'diterima' => 'Diterima',
                        'ditolak' => 'Ditolak',
                    ])
                    ->indicator('Status'),
                    
                SelectFilter::make('asal_kampus')
                    ->options(function () {
                        return PendaftaranMagang::distinct('asal_kampus')
                            ->pluck('asal_kampus', 'asal_kampus')
                            ->toArray();
                    })
                    ->searchable()
                    ->indicator('Kampus'),

                Filter::make('created_at')
                    ->label('Tanggal Pendaftaran')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
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
                    ->visible(fn ($record) => $record->status !== 'diterima')
                    ->action(function ($record) {
                        $record->update(['status' => 'diterima']);
                        Mail::to($record->user->email)->send(new PendaftaranMagangMail('diterima'));
                        
                        $totalPeople = 1 + $record->anggota()->count();
                        
                        Notification::make()
                            ->title('Pendaftaran berhasil disetujui')
                            ->body("Total {$totalPeople} orang telah diterima.")
                            ->success()
                            ->send();
                    }),

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

                        Mail::to($record->user->email)->send(new PendaftaranMagangMail('ditolak', $data['alasan_penolakan']));
                        
                        Notification::make()
                            ->title('Pendaftaran telah ditolak')
                            ->warning()
                            ->send();
                    }),
                    
                Action::make('view_document')
                    ->label('Dokumen')
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->modalContent(function ($record) {
                        if (!$record->surat_pengantar) {
                            return 'Tidak ada dokumen surat pengantar.';
                        }
                        
                        $url = asset('storage/' . $record->surat_pengantar);
                        
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
                        ->modalDescription('Apakah Anda yakin ingin menerima semua pendaftaran yang dipilih?')
                        ->modalSubmitActionLabel('Ya, Terima Pendaftaran')
                        ->action(function (Collection $records) {
                            $totalDiterima = 0;
                            
                            foreach ($records as $record) {
                                if ($record->status !== 'diterima') {
                                    $record->update(['status' => 'diterima']);
                                    Mail::to($record->user->email)->send(new PendaftaranMagangMail('diterima'));
                                    $totalDiterima++;
                                }
                            }
                            
                            Notification::make()
                                ->title("{$totalDiterima} Pendaftaran Diterima")
                                ->success()
                                ->body('Email notifikasi telah dikirim ke semua pendaftar.')
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
                Action::make('toggle_pendaftaran')
                    ->label(fn () => Setting::getCached()?->status_pendaftaran ? 'Tutup Pendaftaran' : 'Buka Pendaftaran')
                    ->icon(fn () => Setting::getCached()?->status_pendaftaran ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
                    ->color(fn () => Setting::getCached()?->status_pendaftaran ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn () => Setting::getCached()?->status_pendaftaran ? 'Tutup Pendaftaran Magang' : 'Buka Pendaftaran Magang')
                    ->modalDescription(fn () => Setting::getCached()?->status_pendaftaran 
                        ? 'Menutup pendaftaran akan mencegah mahasiswa baru mendaftar. Yakin ingin menutup pendaftaran?' 
                        : 'Membuka pendaftaran akan memungkinkan mahasiswa baru mendaftar. Yakin ingin membuka pendaftaran?')
                    ->modalSubmitActionLabel(fn () => Setting::getCached()?->status_pendaftaran ? 'Ya, Tutup Pendaftaran' : 'Ya, Buka Pendaftaran')
                    ->action(function () {
                        $setting = Setting::first(); // Still use first() for update action to be safe
                        if (!$setting) {
                            $setting = Setting::create(['status_pendaftaran' => 1]);
                        }
                        
                        $oldStatus = $setting->status_pendaftaran;
                        $setting->update(['status_pendaftaran' => !$oldStatus]);
                        Setting::clearCache(); // Explicit clear after update
                        
                        Notification::make()
                            ->title($oldStatus ? 'Pendaftaran Ditutup' : 'Pendaftaran Dibuka')
                            ->icon($oldStatus ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
                            ->color($oldStatus ? 'danger' : 'success')
                            ->send();
                    }),
                    
                Action::make('dashboard_stats')
                    ->label('Statistik')
                    ->icon('heroicon-o-chart-bar')
                    ->color('gray')
                    ->action(function () {
                        $pendingCount = PendaftaranMagang::where('status', 'pending')->count();
                        $acceptedCount = PendaftaranMagang::where('status', 'diterima')->count();
                        $rejectedCount = PendaftaranMagang::where('status', 'ditolak')->count();
                        $totalCount = PendaftaranMagang::count();
                        
                        $acceptRate = $totalCount > 0 ? round(($acceptedCount / $totalCount) * 100) : 0;
                        
                        $message = "<div class='space-y-3'>";
                        $message .= "<div><span class='font-medium'>Total Pendaftaran:</span> {$totalCount}</div>";
                        $message .= "<div><span class='font-medium'>Pending:</span> {$pendingCount}</div>";
                        $message .= "<div><span class='font-medium'>Diterima:</span> {$acceptedCount}</div>";
                        $message .= "<div><span class='font-medium'>Ditolak:</span> {$rejectedCount}</div>";
                        $message .= "<div><span class='font-medium'>Tingkat Penerimaan:</span> {$acceptRate}%</div>";
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
                        Setting::clearCache();
                        
                        Notification::make()
                            ->title('Pendaftaran Dibuka')
                            ->icon('heroicon-o-lock-open')
                            ->color('success')
                            ->send();
                    })
                    ->visible(fn () => !Setting::getCached()?->status_pendaftaran),
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
                            ->schema([
                                Infolists\Components\TextEntry::make('user.name')
                                    ->label('Nama Lengkap')
                                    ->icon('heroicon-m-user')
                                    ->weight('bold'),
                                    
                                Infolists\Components\TextEntry::make('user.email')
                                    ->label('Email')
                                    ->icon('heroicon-m-envelope')
                                    ->copyable(),
                                    
                                Infolists\Components\TextEntry::make('asal_kampus')
                                    ->label('Asal Kampus')
                                    ->icon('heroicon-m-academic-cap'),
                                    
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
                                        
                                        return "{$diffInDays} hari";
                                    }),
                            ])
                            ->columnSpan(2),
                            
                        Infolists\Components\Section::make('Status')
                            ->icon('heroicon-o-check-circle')
                            ->schema([
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'diterima' => 'success',
                                        'ditolak' => 'danger',
                                        default => 'warning',
                                    }),
                                    
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Tanggal Pendaftaran')
                                    ->dateTime(),
                                    
                                Infolists\Components\TextEntry::make('alasan_penolakan')
                                    ->label('Alasan Penolakan')
                                    ->visible(fn ($record) => $record->status === 'ditolak')
                                    ->color('danger'),
                            ])
                            ->columnSpan(1),
                    ]),
                    
                Infolists\Components\Section::make('Dokumen')
                    ->icon('heroicon-o-document')
                    ->schema([
                        Infolists\Components\TextEntry::make('surat_pengantar')
                            ->label('Surat Pengantar')
                            ->getStateUsing(function (PendaftaranMagang $record) {
                                if (!$record->surat_pengantar) {
                                    return new HtmlString('<span class="text-danger-500">Tidak ada file surat pengantar</span>');
                                }
                                
                                $url = asset('storage/' . $record->surat_pengantar);
                                
                                return new HtmlString("
                                    <div class='flex gap-2'>
                                        <a href='{$url}' target='_blank' class='inline-flex items-center gap-1 px-3 py-1 bg-primary-500 text-white rounded-md text-xs hover:bg-primary-600'>
                                            <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='currentColor' class='w-4 h-4'><path d='M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z' /><path fill-rule='evenodd' d='M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z' clip-rule='evenodd' /></svg>
                                            Lihat
                                        </a>
                                        <a href='{$url}' download class='inline-flex items-center gap-1 px-3 py-1 bg-gray-500 text-white rounded-md text-xs hover:bg-gray-600'>
                                            <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='currentColor' class='w-4 h-4'><path d='M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z' /><path d='M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z' /></svg>
                                            Unduh
                                        </a>
                                    </div>
                                ");
                            }),
                    ]),
                    
                Infolists\Components\Section::make('Anggota Tim')
                    ->icon('heroicon-o-users')
                    ->visible(fn ($record) => $record->anggota && $record->anggota->count() > 0)
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('anggota')
                            ->schema([
                                Infolists\Components\TextEntry::make('nama_anggota')
                                    ->label('Nama'),
                                Infolists\Components\TextEntry::make('nim_anggota')
                                    ->label('NIM'),
                                Infolists\Components\TextEntry::make('email_anggota')
                                    ->label('Email'),
                                Infolists\Components\TextEntry::make('jurusan')
                                    ->label('Jurusan'),
                            ])
                            ->columns(4),
                    ]),
                    
                Infolists\Components\Section::make('Aksi')
                    ->schema([
                        Infolists\Components\Actions::make([
                            Infolists\Components\Actions\Action::make('terima')
                                ->label('Terima Pendaftaran')
                                ->icon('heroicon-m-check-circle')
                                ->color('success')
                                ->button()
                                ->requiresConfirmation()
                                ->modalHeading('Terima Pendaftaran')
                                ->modalDescription('Apakah Anda yakin ingin menerima pendaftaran ini?')
                                ->modalSubmitActionLabel('Ya, Terima')
                                ->visible(fn ($record) => $record->status !== 'diterima')
                                ->action(function (PendaftaranMagang $record) {
                                    $record->update(['status' => 'diterima']);
                                    Mail::to($record->user->email)->send(new PendaftaranMagangMail('diterima'));
                                    
                                    Notification::make()
                                        ->title('Pendaftaran berhasil disetujui')
                                        ->success()
                                        ->send();
                                }),
                                
                            Infolists\Components\Actions\Action::make('tolak')
                                ->label('Tolak Pendaftaran')
                                ->icon('heroicon-m-x-circle')
                                ->color('danger')
                                ->button()
                                ->requiresConfirmation()
                                ->modalHeading('Tolak Pendaftaran')
                                ->modalDescription('Apakah Anda yakin ingin menolak pendaftaran ini?')
                                ->modalSubmitActionLabel('Ya, Tolak')
                                ->visible(fn ($record) => $record->status !== 'ditolak')
                                ->form([
                                    Forms\Components\Textarea::make('alasan_penolakan')
                                        ->label('Alasan Penolakan')
                                        ->required()
                                        ->rows(3)
                                        ->default('Mohon maaf, pendaftaran Anda tidak dapat diproses karena:'),
                                ])
                                ->action(function (PendaftaranMagang $record, array $data) {
                                    $record->update([
                                        'status' => 'ditolak',
                                        'alasan_penolakan' => $data['alasan_penolakan'],
                                    ]);
                                    
                                    Mail::to($record->user->email)->send(new PendaftaranMagangMail('ditolak', $data['alasan_penolakan']));
                                    
                                    Notification::make()
                                        ->title('Pendaftaran telah ditolak')
                                        ->warning()
                                        ->send();
                                }),
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
            // 'create' => Pages\CreatePendaftaranMagang::route('/create'),
            'edit' => Pages\EditPendaftaranMagang::route('/{record}/edit'),
            'view' => Pages\ViewPendaftaranMagang::route('/{record}'),
        ];
    }
}