<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Support\Collection;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Pengguna';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?int $navigationSort = 4;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Akun')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignorable: fn ($record) => $record),
                        
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => !empty($state) ? bcrypt($state) : null)
                            ->required(fn ($record) => !$record)
                            ->dehydrated(fn ($state) => filled($state))
                            ->maxLength(255),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Status & Role')
                    ->schema([
                        Toggle::make('is_verified')
                            ->label('Terverifikasi')
                            ->default(false),
                        
                        Select::make('role')
                            ->label('Role')
                            ->options([
                                'admin' => 'Admin',
                                'user' => 'User',
                            ])
                            ->default('user')
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                
                IconColumn::make('is_verified')
                    ->label('Terverifikasi')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn ($state) => $state === 'admin' ? 'primary' : 'secondary'),
                
                TextColumn::make('created_at')
                    ->label('Tanggal Daftar')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('is_verified')
                    ->label('Terverifikasi')
                    ->query(fn (Builder $query) => $query->where('is_verified', true)),
                
                Filter::make('not_verified')
                    ->label('Belum Terverifikasi')
                    ->query(fn (Builder $query) => $query->where('is_verified', false)),
                
                SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'admin' => 'Admin',
                        'user' => 'User',
                    ]),
                
                Filter::make('created_at')
                    ->label('Tanggal Pendaftaran')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal'),
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
                ViewAction::make()->color('info'),
                EditAction::make(),
                DeleteAction::make(),
                
                Tables\Actions\Action::make('verify_user')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => !$record->is_verified)
                    ->action(function ($record) {
                        $record->update(['is_verified' => true]);
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    BulkAction::make('verify_bulk')
                        ->label('Verifikasi')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                if (!$record->is_verified) {
                                    $record->update(['is_verified' => true]);
                                }
                            }
                        }),
                ]),
            ]);
    }
    
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Pengguna')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Nama'),
                        
                        Infolists\Components\TextEntry::make('email')
                            ->label('Email'),
                        
                        Infolists\Components\IconEntry::make('is_verified')
                            ->label('Terverifikasi')
                            ->boolean(),
                        
                        Infolists\Components\TextEntry::make('role')
                            ->label('Role')
                            ->badge()
                            ->color(fn ($state) => $state === 'admin' ? 'primary' : 'secondary'),
                    ])
                    ->columns(2),
                
                Infolists\Components\Section::make('OTP Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('otp_code')
                            ->label('Kode OTP')
                            ->visible(fn ($record) => !empty($record->otp_code)),
                        
                        Infolists\Components\TextEntry::make('otp_expires_at')
                            ->label('Masa Berlaku OTP')
                            ->dateTime()
                            ->visible(fn ($record) => !empty($record->otp_expires_at)),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => !empty($record->otp_code) || !empty($record->otp_expires_at)),
                
                Infolists\Components\Section::make('Waktu')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Tanggal Daftar')
                            ->dateTime(),
                        
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Terakhir Diperbarui')
                            ->dateTime(),
                    ])
                    ->columns(2),
                
                Infolists\Components\Section::make('Pendaftaran Magang')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('pendaftaranMagang')
                            ->schema([
                                Infolists\Components\TextEntry::make('asal_kampus')
                                    ->label('Asal Kampus'),
                                
                                Infolists\Components\TextEntry::make('jurusan')
                                    ->label('Jurusan'),
                                
                                Infolists\Components\TextEntry::make('tanggal_mulai')
                                    ->label('Tanggal Mulai')
                                    ->date(),
                                
                                Infolists\Components\TextEntry::make('tanggal_selesai')
                                    ->label('Tanggal Selesai')
                                    ->date(),
                                
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'diterima' => 'success',
                                        'ditolak' => 'danger',
                                        default => 'warning',
                                    }),
                            ])
                            ->columns(2),
                    ])
                    ->visible(fn ($record) => $record->pendaftaranMagang && $record->pendaftaranMagang->count() > 0),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('/{record}'),
        ];
    }
}