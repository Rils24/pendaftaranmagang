<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SettingsResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\EditAction;

class SettingsResource extends Resource
{
    protected static ?string $model = Setting::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationLabel = 'Pengaturan';
    protected static ?int $navigationSort = 3;
    
    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Tabs::make('Pengaturan')
                    ->tabs([
                        Tabs\Tab::make('Pendaftaran')
                            ->icon('heroicon-o-clipboard')
                            ->schema([
                                Section::make('Status Pendaftaran')
                                    ->schema([
                                        Toggle::make('status_pendaftaran')
                                            ->label('Buka Pendaftaran')
                                            ->helperText('Mengaktifkan atau menonaktifkan sistem pendaftaran magang')
                                            ->onColor('success')
                                            ->offColor('danger')
                                            ->default(true),
                                            
                                        TextInput::make('max_anggota')
                                            ->label('Maksimal Anggota per Kelompok')
                                            ->default(6)
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(10),
                                            
                                        Toggle::make('require_surat_pengantar')
                                            ->label('Wajib Surat Pengantar')
                                            ->helperText('Mewajibkan peserta untuk mengunggah surat pengantar')
                                            ->default(true),
                                    ])
                                    ->columns(2),
                                    
                                Section::make('Email Notifikasi')
                                    ->schema([
                                        TextInput::make('admin_email')
                                            ->label('Email Admin')
                                            ->email()
                                            ->helperText('Email untuk menerima notifikasi pendaftaran baru'),
                                            
                                        Toggle::make('send_admin_notifications')
                                            ->label('Kirim Notifikasi ke Admin')
                                            ->helperText('Mengirim email ke admin saat ada pendaftaran baru')
                                            ->default(true),
                                            
                                        Toggle::make('send_user_notifications')
                                            ->label('Kirim Notifikasi ke Pengguna')
                                            ->helperText('Mengirim email ke pengguna saat status pendaftaran berubah')
                                            ->default(true),
                                    ])
                                    ->columns(2),
                            ]),
                            
                        Tabs\Tab::make('Tampilan Web')
                            ->icon('heroicon-o-paint-brush')
                            ->schema([
                                Section::make('Informasi Utama')
                                    ->schema([
                                        TextInput::make('site_title')
                                            ->label('Judul Situs')
                                            ->default('Sistem Magang')
                                            ->required(),
                                            
                                        TextInput::make('tagline')
                                            ->label('Tagline')
                                            ->default('Platform Pendaftaran Magang Online')
                                            ->nullable(),
                                            
                                        FileUpload::make('logo')
                                            ->label('Logo')
                                            ->image()
                                            ->directory('logos')
                                            ->visibility('public')
                                            ->nullable(),
                                    ])
                                    ->columns(2),
                                    
                                Section::make('Tema')
                                    ->schema([
                                        ColorPicker::make('primary_color')
                                            ->label('Warna Utama')
                                            ->default('#3B82F6'),
                                            
                                        ColorPicker::make('secondary_color')
                                            ->label('Warna Sekunder')
                                            ->default('#10B981'),
                                            
                                        Select::make('font_family')
                                            ->label('Font')
                                            ->options([
                                                'inter' => 'Inter',
                                                'poppins' => 'Poppins',
                                                'roboto' => 'Roboto',
                                                'opensans' => 'Open Sans',
                                            ])
                                            ->default('inter'),
                                    ])
                                    ->columns(2),
                            ]),
                            
                        Tabs\Tab::make('Konten')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                RichEditor::make('welcome_message')
                                    ->label('Pesan Selamat Datang')
                                    ->helperText('Pesan yang ditampilkan di halaman utama')
                                    ->default('Selamat datang di platform pendaftaran magang kami. Silakan daftar untuk memulai perjalanan magang Anda.')
                                    ->nullable(),
                                    
                                RichEditor::make('faq_content')
                                    ->label('Konten FAQ')
                                    ->helperText('Pertanyaan yang sering diajukan tentang magang')
                                    ->nullable(),
                                    
                                RichEditor::make('contact_info')
                                    ->label('Informasi Kontak')
                                    ->helperText('Informasi kontak untuk bantuan lebih lanjut')
                                    ->nullable(),
                            ]),
                            
                        Tabs\Tab::make('Sistem')
                            ->icon('heroicon-o-server')
                            ->schema([
                                Section::make('Notifikasi Sistem')
                                    ->schema([
                                        TextInput::make('smtp_host')
                                            ->label('SMTP Host')
                                            ->nullable(),
                                            
                                        TextInput::make('smtp_port')
                                            ->label('SMTP Port')
                                            ->numeric()
                                            ->nullable(),
                                            
                                        TextInput::make('smtp_username')
                                            ->label('SMTP Username')
                                            ->nullable(),
                                            
                                        TextInput::make('smtp_password')
                                            ->label('SMTP Password')
                                            ->password()
                                            ->nullable(),
                                            
                                        TextInput::make('mail_from_address')
                                            ->label('Alamat Pengirim Email')
                                            ->email()
                                            ->nullable(),
                                    ])
                                    ->columns(2),
                                    
                                Section::make('Penyimpanan')
                                    ->schema([
                                        Select::make('storage_driver')
                                            ->label('Driver Penyimpanan')
                                            ->options([
                                                'local' => 'Local',
                                                's3' => 'Amazon S3',
                                                'google' => 'Google Drive',
                                            ])
                                            ->default('local'),
                                            
                                        TextInput::make('max_upload_size')
                                            ->label('Ukuran Maksimal Upload (MB)')
                                            ->numeric()
                                            ->default(10)
                                            ->minValue(1)
                                            ->maxValue(50),
                                    ])
                                    ->columns(2),
                            ]),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('site_title')
                    ->label('Judul Situs')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('status_pendaftaran')
                    ->label('Status Pendaftaran')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Dibuka' : 'Ditutup')
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
                    
                Tables\Columns\TextColumn::make('max_anggota')
                    ->label('Maks. Anggota')
                    ->numeric(),
                    
                Tables\Columns\TextColumn::make('admin_email')
                    ->label('Email Admin')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSettings::route('/'),
        ];
    }
}