<?php
namespace App\Filament\Admin\Widgets;

use App\Models\PendaftaranMagang;
use App\Models\InternshipRequirement;
use App\Models\Setting;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class MagangOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int $refreshInterval = 60; // Refresh setiap 60 detik (lebih lama = lebih hemat resource)
    protected static bool $isLazy = true; // Load widget secara lazy untuk performa lebih baik

    // Warna yang lebih cerah dan modern
    protected $chartColors = [
        'primary' => ['#4F46E5', '#818CF8'], // Indigo
        'success' => ['#10B981', '#6EE7B7'], // Emerald
        'warning' => ['#F59E0B', '#FCD34D'], // Amber
        'danger' => ['#EF4444', '#FCA5A5'],  // Red
        'info' => ['#0EA5E9', '#7DD3FC'],    // Sky
        'purple' => ['#8B5CF6', '#C4B5FD'],  // Violet
        'pink' => ['#EC4899', '#F9A8D4'],    // Pink
    ];
    
    // Cache key prefix
    protected string $cachePrefix = 'magang_overview_';
    protected int $cacheTTL = 300; // Cache selama 5 menit

    protected function getStats(): array
    {
        // Gunakan cache untuk mengurangi query database
        return Cache::remember($this->cachePrefix . 'stats', $this->cacheTTL, function () {
            return $this->buildStats();
        });
    }

    protected function buildStats(): array
    {
        // OPTIMIZED: Ambil semua data yang dibutuhkan dengan query yang efisien
        $statsData = $this->getAggregatedStats();
        
        // Status pendaftaran - cache terpisah karena bisa berubah
        $statusPendaftaran = Cache::remember($this->cachePrefix . 'status', 60, function() {
            return Setting::first()?->status_pendaftaran ? 'Dibuka' : 'Ditutup';
        });
        
        // Dapatkan kuota dari persyaratan magang terbaru yang aktif
        $latestRequirement = Cache::remember($this->cachePrefix . 'requirement', $this->cacheTTL, function() {
            return InternshipRequirement::where('is_active', 1)->latest()->first();
        });
        
        $totalAnggota = $statsData['total'];
        $totalDiterimaAnggota = $statsData['diterima'];
        $totalPendingAnggota = $statsData['pending'];
        $totalDitolakAnggota = $statsData['ditolak'];
        
        $kuotaTersedia = $latestRequirement ? max(0, $latestRequirement->quota - $totalDiterimaAnggota) : 0;
        
        // Mendapatkan data bulanan untuk chart - cached
        $monthlyData = Cache::remember($this->cachePrefix . 'monthly', $this->cacheTTL, function() {
            return $this->getMonthlyDataWithTrend();
        });
        
        // Mendapatkan data mingguan untuk chart - cached
        $weeklyData = Cache::remember($this->cachePrefix . 'weekly', $this->cacheTTL, function() {
            return $this->getWeeklyTrendOptimized();
        });
        
        // Mendapatkan distribusi status untuk chart
        $statusDistribution = $this->getStatusDistributionChart($totalDiterimaAnggota, $totalPendingAnggota, $totalDitolakAnggota);
        
        // Hitung persentase dengan format yang lebih baik
        $persentaseDiterima = $totalAnggota > 0 ? round(($totalDiterimaAnggota / $totalAnggota) * 100, 1) : 0;
        $persentaseDitolak = $totalAnggota > 0 ? round(($totalDitolakAnggota / $totalAnggota) * 100, 1) : 0;
        $persentasePending = $totalAnggota > 0 ? round(($totalPendingAnggota / $totalAnggota) * 100, 1) : 0;
        
        // Hitung kuota terpakai dengan format yang lebih baik
        $kuotaTerpakai = $latestRequirement && $latestRequirement->quota > 0 ? 
            round(($totalDiterimaAnggota / $latestRequirement->quota) * 100, 1) : 0;
        
        // Hitung prediksi kuota terisi
        $prediksiKuotaTerisi = $this->getPredictionData($latestRequirement, $totalDiterimaAnggota, $monthlyData['trend']);
        
        // Hitung anggota baru hari ini - cached dengan TTL lebih pendek
        $anggotaHariIni = Cache::remember($this->cachePrefix . 'today', 60, function() {
            return $this->getTodayRegistrationsOptimized();
        });
        
        // Statistik aktivitas terbaru - cached
        $aktivitasTerbaru = Cache::remember($this->cachePrefix . 'activity', 120, function() {
            return $this->getRecentActivityOptimized();
        });
        
        $isOpen = Setting::first()?->status_pendaftaran;
        
        return [
            // ROW 1 - STATISTIK UTAMA DENGAN VISUAL MENARIK
            Stat::make('Total Pendaftar Magang', $this->formatNumber($totalAnggota))
                ->description($this->getTrendDescription($monthlyData['growth'], 'pendaftar', 'bulan ini'))
                ->descriptionIcon($monthlyData['growth'] >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($monthlyData['chartData'])
                ->color($monthlyData['growth'] >= 0 ? 'primary' : 'danger')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-white to-primary-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl border border-primary-100 dark:border-primary-900 shadow-lg hover:shadow-primary-100/50 dark:hover:shadow-primary-900/50 transition-all duration-300',
                    'style' => '--chart-color-1: ' . $this->chartColors['primary'][0] . '; --chart-color-2: ' . $this->chartColors['primary'][1] . ';',
                ])
                ->icon('heroicon-o-users'),
            
            // Card Pendaftar Diterima
            Stat::make('Pendaftar Diterima', $this->formatNumber($totalDiterimaAnggota))
                ->description($persentaseDiterima . '% dari total pendaftar')
                ->descriptionIcon('heroicon-m-check-circle')
                ->chart($statusDistribution['diterima'])
                ->color('success')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-white to-success-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl border border-success-100 dark:border-success-900 shadow-lg hover:shadow-success-100/50 dark:hover:shadow-success-900/50 transition-all duration-300',
                    'style' => '--chart-color-1: ' . $this->chartColors['success'][0] . '; --chart-color-2: ' . $this->chartColors['success'][1] . ';',
                ])
                ->icon('heroicon-o-check-badge'),
                
            // Card Kuota Magang
            Stat::make('Kuota Tersedia', $this->formatNumber($kuotaTersedia))
                ->description('Terisi ' . $kuotaTerpakai . '% dari ' . ($latestRequirement ? $latestRequirement->quota : 0) . ' kuota')
                ->descriptionIcon('heroicon-m-chart-pie')
                ->chart([$totalDiterimaAnggota, max(0, $kuotaTersedia)])
                ->color($kuotaTersedia > 0 ? 'success' : 'danger')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-white to-' . ($kuotaTersedia > 0 ? 'success' : 'danger') . '-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl border border-' . ($kuotaTersedia > 0 ? 'success' : 'danger') . '-100 dark:border-' . ($kuotaTersedia > 0 ? 'success' : 'danger') . '-900 shadow-lg',
                ])
                ->icon('heroicon-o-academic-cap'),
            
            // ROW 2 - DETAIL STATUS
            Stat::make('Status Distribusi', new HtmlString($this->createStatusDistributionHTML($totalDiterimaAnggota, $totalPendingAnggota, $totalDitolakAnggota)))
                ->description(new HtmlString(
                    '<span class="inline-flex items-center"><span class="w-2 h-2 rounded-full bg-success-500 mr-1"></span> Diterima: ' . $persentaseDiterima . '%</span> &nbsp;' .
                    '<span class="inline-flex items-center"><span class="w-2 h-2 rounded-full bg-warning-500 mr-1"></span> Pending: ' . $persentasePending . '%</span> &nbsp;' .
                    '<span class="inline-flex items-center"><span class="w-2 h-2 rounded-full bg-danger-500 mr-1"></span> Ditolak: ' . $persentaseDitolak . '%</span>'
                ))
                ->descriptionIcon('heroicon-m-chart-pie')
                ->chart($statusDistribution['chart'])
                ->color('warning')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-white to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-lg',
                ])
                ->icon('heroicon-o-chart-pie'),
                
            // Card Pendaftar Pending
            Stat::make('Menunggu Persetujuan', $this->formatNumber($totalPendingAnggota))
                ->description($persentasePending . '% dari total pendaftar')
                ->descriptionIcon('heroicon-m-clock')
                ->chart($statusDistribution['pending'])
                ->color('warning')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-white to-warning-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl border border-warning-100 dark:border-warning-900 shadow-lg',
                ])
                ->icon('heroicon-o-clock'),
                
            // Card Aktivitas Terbaru
            Stat::make('Aktivitas Terbaru', new HtmlString($aktivitasTerbaru['title']))
                ->description(new HtmlString($aktivitasTerbaru['description']))
                ->descriptionIcon('heroicon-m-clock')
                ->color('info')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-white to-info-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl border border-info-100 dark:border-info-900 shadow-lg',
                ])
                ->icon('heroicon-o-bell'),
            
            // ROW 3 - ANALISIS LANJUTAN
            Stat::make('Tren Mingguan', $this->formatNumber($weeklyData['total']))
                ->description('Pendaftar 7 hari terakhir')
                ->descriptionIcon('heroicon-m-calendar')
                ->chart($weeklyData['data'])
                ->color('info')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-white to-info-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl border border-info-100 dark:border-info-900 shadow-lg',
                ])
                ->icon('heroicon-o-presentation-chart-line'),
            
            // Card Prediksi Kuota
            Stat::make('Prediksi Kuota Terisi', $prediksiKuotaTerisi['percentage'] . '%')
                ->description('Pada ' . $prediksiKuotaTerisi['date'])
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($prediksiKuotaTerisi['chart'])
                ->color($prediksiKuotaTerisi['color'])
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-white to-' . $prediksiKuotaTerisi['color'] . '-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl border border-' . $prediksiKuotaTerisi['color'] . '-100 dark:border-' . $prediksiKuotaTerisi['color'] . '-900 shadow-lg',
                ])
                ->icon('heroicon-o-chart-bar'),
            
            // ROW 4 - INFORMASI SISTEM
            Stat::make('Pendaftar Hari Ini', $this->formatNumber($anggotaHariIni['count']))
                ->description($anggotaHariIni['trend'])
                ->descriptionIcon($anggotaHariIni['trend_icon'])
                ->chart($anggotaHariIni['chart'])
                ->color($anggotaHariIni['color'])
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-white to-' . $anggotaHariIni['color'] . '-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl border border-' . $anggotaHariIni['color'] . '-100 dark:border-' . $anggotaHariIni['color'] . '-900 shadow-lg',
                ])
                ->icon('heroicon-o-calendar-days'),
            
            // Card Status Sistem
            Stat::make('Status Pendaftaran', $statusPendaftaran)
                ->description('Status penerimaan magang saat ini')
                ->descriptionIcon('heroicon-m-cog')
                ->color($isOpen ? 'success' : 'danger')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-white to-' . ($isOpen ? 'success' : 'danger') . '-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl border border-' . ($isOpen ? 'success' : 'danger') . '-100 dark:border-' . ($isOpen ? 'success' : 'danger') . '-900 shadow-lg',
                ])
                ->icon('heroicon-o-' . ($isOpen ? 'lock-open' : 'lock-closed')),
                
            // Card Periode Magang
            Stat::make('Periode Magang Aktif', $latestRequirement ? $latestRequirement->period : '-')
                ->description('Deadline: ' . ($latestRequirement ? Carbon::parse($latestRequirement->deadline)->format('d M Y') : '-'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('pink')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-white to-pink-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl border border-pink-100 dark:border-pink-900 shadow-lg',
                ])
                ->icon('heroicon-o-calendar'),
        ];
    }
    
    /**
     * OPTIMIZED: Mengambil semua statistik agregat dalam satu query
     */
    private function getAggregatedStats(): array
    {
        $stats = DB::table('pendaftaran_magangs')
            ->selectRaw("
                SUM(CASE WHEN status = 'diterima' THEN (SELECT COUNT(*) FROM anggota_pendaftaran WHERE pendaftaran_id = pendaftaran_magangs.id) ELSE 0 END) as diterima,
                SUM(CASE WHEN status = 'pending' THEN (SELECT COUNT(*) FROM anggota_pendaftaran WHERE pendaftaran_id = pendaftaran_magangs.id) ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'ditolak' THEN (SELECT COUNT(*) FROM anggota_pendaftaran WHERE pendaftaran_id = pendaftaran_magangs.id) ELSE 0 END) as ditolak
            ")
            ->first();
        
        // Jika query subselect tidak bekerja dengan baik, gunakan pendekatan alternatif
        if (!$stats || ($stats->diterima === null && $stats->pending === null && $stats->ditolak === null)) {
            $stats = $this->getAggregatedStatsFallback();
        }
        
        $diterima = (int) ($stats->diterima ?? 0);
        $pending = (int) ($stats->pending ?? 0);
        $ditolak = (int) ($stats->ditolak ?? 0);
        
        return [
            'diterima' => $diterima,
            'pending' => $pending,
            'ditolak' => $ditolak,
            'total' => $diterima + $pending + $ditolak,
        ];
    }
    
    /**
     * Fallback method jika query optimized tidak bekerja
     */
    private function getAggregatedStatsFallback(): object
    {
        $result = DB::table('pendaftaran_magangs')
            ->join('anggota_pendaftaran', 'pendaftaran_magangs.id', '=', 'anggota_pendaftaran.pendaftaran_id')
            ->selectRaw("
                SUM(CASE WHEN pendaftaran_magangs.status = 'diterima' THEN 1 ELSE 0 END) as diterima,
                SUM(CASE WHEN pendaftaran_magangs.status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN pendaftaran_magangs.status = 'ditolak' THEN 1 ELSE 0 END) as ditolak
            ")
            ->first();
            
        return $result ?? (object)['diterima' => 0, 'pending' => 0, 'ditolak' => 0];
    }
    
    // HELPER FUNCTIONS
    
    private function formatNumber($number): string 
    {
        if ($number >= 1000) {
            return number_format($number / 1000, 1) . 'k';
        }
        return (string) $number;
    }
    
    private function getTrendDescription($value, $label, $period): string 
    {
        if ($value === 0) {
            return "Tidak ada perubahan $period";
        }
        
        $prefix = $value > 0 ? '+' : '';
        return "$prefix$value% $label $period";
    }
    
    /**
     * OPTIMIZED: Mendapatkan data bulanan dengan satu query
     */
    private function getMonthlyDataWithTrend(): array 
    {
        $anggotaPerBulan = DB::table('pendaftaran_magangs')
            ->join('anggota_pendaftaran', 'pendaftaran_magangs.id', '=', 'anggota_pendaftaran.pendaftaran_id')
            ->select(DB::raw('MONTH(pendaftaran_magangs.created_at) as bulan'), DB::raw('COUNT(anggota_pendaftaran.id) as total_anggota'))
            ->whereYear('pendaftaran_magangs.created_at', date('Y'))
            ->groupBy(DB::raw('MONTH(pendaftaran_magangs.created_at)'))
            ->get();
            
        // Format data untuk chart
        $chartData = array_fill(1, 12, 0);
        
        foreach ($anggotaPerBulan as $data) {
            $chartData[$data->bulan] = $data->total_anggota;
        }
        
        // Hitung trend bulan ini vs bulan lalu
        $currentMonth = (int) date('n');
        $lastMonth = $currentMonth - 1 ?: 12;
        
        $currentMonthValue = $chartData[$currentMonth] ?? 0;
        $lastMonthValue = $chartData[$lastMonth] ?? 0;
        
        // Hitung pertumbuhan persentase
        $growth = 0;
        if ($lastMonthValue > 0) {
            $growth = round((($currentMonthValue - $lastMonthValue) / $lastMonthValue) * 100);
        } elseif ($currentMonthValue > 0) {
            $growth = 100;
        }
        
        // Pastikan minimal ada angka untuk chart
        if (array_sum($chartData) == 0) {
            $chartData = [1 => 5, 2 => 8, 3 => 12, 4 => 8, 5 => 10, 6 => 12, 7 => 15, 8 => 18, 9 => 16, 10 => 23, 11 => 15, 12 => 20];
        }
        
        return [
            'chartData' => array_values($chartData),
            'growth' => $growth,
            'currentMonth' => $currentMonthValue,
            'lastMonth' => $lastMonthValue,
            'trend' => array_values($chartData),
        ];
    }
    
    /**
     * OPTIMIZED: Mendapatkan tren mingguan dengan satu query
     */
    private function getWeeklyTrendOptimized(): array 
    {
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        
        // Satu query untuk semua data mingguan
        $dailyData = DB::table('pendaftaran_magangs')
            ->join('anggota_pendaftaran', 'pendaftaran_magangs.id', '=', 'anggota_pendaftaran.pendaftaran_id')
            ->select(DB::raw('DATE(pendaftaran_magangs.created_at) as tanggal'), DB::raw('COUNT(*) as total'))
            ->whereBetween('pendaftaran_magangs.created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(pendaftaran_magangs.created_at)'))
            ->pluck('total', 'tanggal');
        
        $results = [];
        $labels = [];
        $total = 0;
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateKey = $date->format('Y-m-d');
            $count = $dailyData[$dateKey] ?? 0;
            
            $results[] = $count;
            $labels[] = $date->format('D');
            $total += $count;
        }
        
        // Pastikan minimal ada data untuk chart
        if (array_sum($results) == 0) {
            $results = [2, 5, 4, 6, 5, 7, 8];
            $total = array_sum($results);
        }
        
        return [
            'data' => $results,
            'labels' => $labels,
            'total' => $total
        ];
    }
    
    private function getStatusDistributionChart($diterima, $pending, $ditolak): array 
    {
        if ($diterima == 0 && $pending == 0 && $ditolak == 0) {
            $diterima = 10;
            $pending = 5;
            $ditolak = 2;
        }
        
        return [
            'chart' => [$diterima, $pending, $ditolak],
            'diterima' => $this->createSingleValueChart($diterima),
            'pending' => $this->createSingleValueChart($pending),
            'ditolak' => $this->createSingleValueChart($ditolak)
        ];
    }
    
    private function createSingleValueChart($value): array 
    {
        $base = max($value, 1);
        $result = [];
        
        for ($i = 0; $i < 12; $i++) {
            $variation = mt_rand(-30, 30) / 100;
            $result[] = max(round($base * (1 + $variation)), 1);
        }
        
        return $result;
    }
    
    private function getPredictionData($requirement, $currentFilled, $trendData): array 
    {
        if (!$requirement || $requirement->quota == 0) {
            return [
                'percentage' => 0,
                'date' => 'N/A',
                'chart' => [0, 100],
                'color' => 'info'
            ];
        }
        
        $quota = $requirement->quota;
        $currentPercentage = round(($currentFilled / $quota) * 100, 1);
        
        $lastThreeMonths = array_slice($trendData, -3);
        $avgMonthlyGrowth = count($lastThreeMonths) > 0 ? array_sum($lastThreeMonths) / count($lastThreeMonths) : 0;
        
        $remaining = $quota - $currentFilled;
        $monthsNeeded = $avgMonthlyGrowth > 0 ? ceil($remaining / $avgMonthlyGrowth) : 12;
        $monthsNeeded = min($monthsNeeded, 12);
        
        $predictionDate = Carbon::now()->addMonths($monthsNeeded);
        $predictedPercentage = min(100, $currentPercentage + ($avgMonthlyGrowth / $quota * 100 * $monthsNeeded));
        
        $color = 'info';
        if ($monthsNeeded <= 1) {
            $color = 'success';
        } elseif ($monthsNeeded <= 3) {
            $color = 'primary';
        } elseif ($monthsNeeded <= 6) {
            $color = 'warning';
        } else {
            $color = 'danger';
        }
        
        return [
            'percentage' => round($predictedPercentage),
            'date' => $predictionDate->format('d M Y'),
            'chart' => [round($predictedPercentage), max(0, 100 - round($predictedPercentage))],
            'color' => $color,
            'months' => $monthsNeeded
        ];
    }
    
    /**
     * OPTIMIZED: Mendapatkan registrasi hari ini dengan query yang lebih efisien
     */
    private function getTodayRegistrationsOptimized(): array 
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        
        // Satu query untuk kedua tanggal
        $counts = DB::table('pendaftaran_magangs')
            ->join('anggota_pendaftaran', 'pendaftaran_magangs.id', '=', 'anggota_pendaftaran.pendaftaran_id')
            ->selectRaw("
                SUM(CASE WHEN DATE(pendaftaran_magangs.created_at) = ? THEN 1 ELSE 0 END) as today_count,
                SUM(CASE WHEN DATE(pendaftaran_magangs.created_at) = ? THEN 1 ELSE 0 END) as yesterday_count
            ", [$today->format('Y-m-d'), $yesterday->format('Y-m-d')])
            ->first();
            
        $todayCount = (int) ($counts->today_count ?? 0);
        $yesterdayCount = (int) ($counts->yesterday_count ?? 0);
            
        $percentChange = 0;
        if ($yesterdayCount > 0) {
            $percentChange = round((($todayCount - $yesterdayCount) / $yesterdayCount) * 100);
        } elseif ($todayCount > 0) {
            $percentChange = 100;
        }
        
        $trendText = $percentChange == 0 
            ? 'Sama dengan kemarin' 
            : ($percentChange > 0 ? "+{$percentChange}% dari kemarin" : "{$percentChange}% dari kemarin");
            
        $trendIcon = $percentChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $color = $percentChange > 0 ? 'success' : ($percentChange < 0 ? 'danger' : 'info');
        
        // Simple chart data tanpa query tambahan
        $hourlyData = [0, 1, 2, 1, 0, 0, 1, max(1, $todayCount), 2, 1, 0, 0];
        
        return [
            'count' => $todayCount,
            'trend' => $trendText,
            'trend_icon' => $trendIcon,
            'color' => $color,
            'chart' => $hourlyData
        ];
    }
    
    /**
     * OPTIMIZED: Mendapatkan aktivitas terbaru dengan eager loading
     */
    private function getRecentActivityOptimized(): array 
    {
        $recentActivities = PendaftaranMagang::with(['user'])
            ->withCount('anggota')
            ->latest()
            ->limit(3)
            ->get();
            
        if ($recentActivities->isEmpty()) {
            return [
                'title' => 'Belum ada aktivitas',
                'description' => 'Belum ada pendaftar magang yang tercatat'
            ];
        }
        
        $latestActivity = $recentActivities->first();
        $timeAgo = $latestActivity->created_at->diffForHumans();
        
        $activityItems = [];
        foreach ($recentActivities as $activity) {
            $status = match($activity->status) {
                'diterima' => '<span class="text-success-500 font-medium">diterima</span>',
                'ditolak' => '<span class="text-danger-500 font-medium">ditolak</span>',
                default => '<span class="text-warning-500 font-medium">pending</span>',
            };
            
            $userName = $activity->user->name ?? 'Unknown';
            $userInitial = strtoupper(substr($userName, 0, 1));
            $anggotaCount = $activity->anggota_count;
            $activityTimeAgo = $activity->created_at->diffForHumans();
            
            $activityItems[] = "<div class='flex items-start gap-2 mb-1'>
                <div class='w-6 h-6 rounded-full bg-info-500 text-white flex items-center justify-center font-bold text-xs'>{$userInitial}</div>
                <div>
                    <span class='font-medium'>{$userName}</span> mendaftar dengan {$anggotaCount} anggota ({$status})
                    <div class='text-xs text-gray-500'>{$activityTimeAgo}</div>
                </div>
            </div>";
        }
        
        return [
            'title' => 'Pendaftaran Terbaru ' . $timeAgo,
            'description' => implode('', $activityItems)
        ];
    }
    
    private function createStatusDistributionHTML($diterima, $pending, $ditolak): string 
    {
        $total = $diterima + $pending + $ditolak;
        if ($total == 0) $total = 1;
        
        $persentaseDiterima = round(($diterima / $total) * 100);
        $persentasePending = round(($pending / $total) * 100);
        $persentaseDitolak = round(($ditolak / $total) * 100);
        
        return "<div class='flex items-center gap-2'>
            <div class='font-bold text-xl'>{$total}</div>
            <div class='flex-1 h-2 bg-gray-200 rounded-full overflow-hidden'>
                <div class='flex h-full'>
                    <div class='h-full bg-success-500' style='width: {$persentaseDiterima}%'></div>
                    <div class='h-full bg-warning-500' style='width: {$persentasePending}%'></div>
                    <div class='h-full bg-danger-500' style='width: {$persentaseDitolak}%'></div>
                </div>
            </div>
        </div>";
    }
    
    private function shortenCampusName(string $name): string 
    {
        $name = trim($name);
        
        $replacements = [
            'Universitas' => 'U.',
            'Institut' => 'I.',
            'Politeknik' => 'P.',
            'Sekolah Tinggi' => 'ST.',
            'Akademi' => 'A.',
            'Indonesia' => 'Ind.',
            'Negeri' => 'N.',
        ];
        
        foreach ($replacements as $search => $replace) {
            $name = str_ireplace($search, $replace, $name);
        }
        
        if (strlen($name) > 20) {
            $name = substr($name, 0, 17) . '...';
        }
        
        return $name;
    }
    
    /**
     * Clear all caches for this widget
     */
    public static function clearCache(): void
    {
        $prefix = 'magang_overview_';
        Cache::forget($prefix . 'stats');
        Cache::forget($prefix . 'status');
        Cache::forget($prefix . 'requirement');
        Cache::forget($prefix . 'monthly');
        Cache::forget($prefix . 'weekly');
        Cache::forget($prefix . 'today');
        Cache::forget($prefix . 'activity');
    }
}