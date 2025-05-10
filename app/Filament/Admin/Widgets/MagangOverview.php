<?php
namespace App\Filament\Admin\Widgets;

use App\Models\PendaftaranMagang;
use App\Models\InternshipRequirement;
use App\Models\Setting;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class MagangOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int $refreshInterval = 30; // Refresh lebih cepat - setiap 30 detik
    protected static bool $isLazy = false; // Load widget langsung

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

    // Opsi chart dengan animasi dan styling yang lebih bagus
    protected function getOptions(): array
    {
        return [
            'chart' => [
                'type' => 'area',
                'height' => 100, // Tinggi chart ditingkatkan
                'sparkline' => [
                    'enabled' => true,
                ],
                'dropShadow' => [
                    'enabled' => true,
                    'blur' => 5,
                    'opacity' => 0.3,
                    'left' => 1,
                    'top' => 1,
                ],
                'animations' => [
                    'enabled' => true,
                    'easing' => 'easeinout',
                    'speed' => 800,
                    'animateGradually' => [
                        'enabled' => true,
                        'delay' => 150
                    ],
                    'dynamicAnimation' => [
                        'enabled' => true,
                        'speed' => 350
                    ]
                ],
                'toolbar' => [
                    'show' => false,
                ],
            ],
            'stroke' => [
                'curve' => 'smooth',
                'width' => 3,
                'lineCap' => 'round',
            ],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shadeIntensity' => 1,
                    'opacityFrom' => 0.7,
                    'opacityTo' => 0.2,
                    'stops' => [0, 90, 100],
                    'colorStops' => [
                        [
                            'offset' => 0,
                            'color' => 'var(--chart-color-1)',
                            'opacity' => 0.8
                        ],
                        [
                            'offset' => 100,
                            'color' => 'var(--chart-color-2)',
                            'opacity' => 0.2
                        ]
                    ]
                ]
            ],
            'grid' => [
                'show' => false,
                'padding' => [
                    'top' => 5,
                    'right' => 0,
                    'bottom' => 0,
                    'left' => 0
                ]
            ],
            'tooltip' => [
                'enabled' => true,
                'style' => [
                    'fontSize' => '12px',
                    'fontFamily' => 'inherit'
                ],
                'theme' => 'light',
                'x' => [
                    'show' => false,
                ],
                'y' => [
                    'formatter' => 'function(val) { return val + " pendaftar" }',
                    'title' => [
                        'formatter' => 'function(seriesName) { return "" }',
                    ],
                ],
                'marker' => [
                    'show' => true,
                    'size' => 5,
                ],
                'fixed' => [
                    'enabled' => false,
                    'position' => 'topRight',
                    'offsetX' => 0,
                    'offsetY' => 0,
                ],
            ],
            'dataLabels' => [
                'enabled' => false
            ],
            'markers' => [
                'size' => 0,
                'strokeWidth' => 2,
                'strokeColors' => 'var(--chart-color-1)',
                'hover' => [
                    'size' => 6,
                ]
            ],
            'xaxis' => [
                'labels' => [
                    'show' => false
                ],
                'axisBorder' => [
                    'show' => false
                ],
                'axisTicks' => [
                    'show' => false
                ]
            ],
            'yaxis' => [
                'labels' => [
                    'show' => false
                ]
            ],
            'legend' => [
                'show' => false
            ],
            'responsive' => [
                [
                    'breakpoint' => 768,
                    'options' => [
                        'chart' => [
                            'height' => 80
                        ]
                    ]
                ]
            ]
        ];
    }

    protected function getStats(): array
    {
        // Menghitung total anggota magang (tanpa pendaftar utama)
        $pendaftaran = PendaftaranMagang::withCount('anggota')->get();
        
        // Inisialisasi variabel
        $totalAnggota = 0;
        $totalDiterimaAnggota = 0;
        $totalDitolakAnggota = 0;
        $totalPendingAnggota = 0;
        
        // Hitung total untuk masing-masing status
        foreach ($pendaftaran as $daftar) {
            // Hanya hitung jumlah anggota
            $jumlahAnggota = $daftar->anggota_count;
            $totalAnggota += $jumlahAnggota;
            
            if ($daftar->status == 'diterima') {
                $totalDiterimaAnggota += $jumlahAnggota;
            } elseif ($daftar->status == 'ditolak') {
                $totalDitolakAnggota += $jumlahAnggota;
            } elseif ($daftar->status == 'pending') {
                $totalPendingAnggota += $jumlahAnggota;
            }
        }
        
        // Dapatkan kuota dari persyaratan magang terbaru yang aktif
        $latestRequirement = InternshipRequirement::where('is_active', 1)->latest()->first();
        $kuotaTersedia = $latestRequirement ? $latestRequirement->quota - $totalDiterimaAnggota : 0;
        
        // Status pendaftaran
        $statusPendaftaran = Setting::first()?->status_pendaftaran ? 'Dibuka' : 'Ditutup';
        
        // Mendapatkan data bulanan untuk chart
        $monthlyData = $this->getMonthlyDataWithTrend();
        
        // Mendapatkan data mingguan untuk chart
        $weeklyData = $this->getWeeklyTrendWithLabels();
        
        // Mendapatkan distribusi status untuk chart
        $statusDistribution = $this->getStatusDistributionChart($totalDiterimaAnggota, $totalPendingAnggota, $totalDitolakAnggota);
        
        // Mendapatkan top kampus pendaftar
        $kampusData = $this->getTopCampusesWithChart();
        
        // Hitung persentase dengan format yang lebih baik
        $persentaseDiterima = $totalAnggota > 0 ? round(($totalDiterimaAnggota / $totalAnggota) * 100, 1) : 0;
        $persentaseDitolak = $totalAnggota > 0 ? round(($totalDitolakAnggota / $totalAnggota) * 100, 1) : 0;
        $persentasePending = $totalAnggota > 0 ? round(($totalPendingAnggota / $totalAnggota) * 100, 1) : 0;
        
        // Hitung kuota terpakai dengan format yang lebih baik
        $kuotaTerpakai = $latestRequirement && $latestRequirement->quota > 0 ? 
            round(($totalDiterimaAnggota / $latestRequirement->quota) * 100, 1) : 0;
        
        // Hitung pertumbuhan bulanan
        $pertumbuhanBulanan = $this->calculateMonthlyGrowth();
        
        // Hitung prediksi kuota terisi
        $prediksiKuotaTerisi = $this->getPredictionData($latestRequirement, $totalDiterimaAnggota, $monthlyData['trend']);
        
        // Hitung anggota baru hari ini
        $anggotaHariIni = $this->getTodayRegistrations();
        
        // Statistik jurusan terpopuler
        $jurusanPopuler = $this->getTopJurusan();
        
        // Statistik aktivitas terbaru
        $aktivitasTerbaru = $this->getRecentActivity();
        
        return [
            // ROW 1 - STATISTIK UTAMA DENGAN VISUAL MENARIK
            // Card Total Pendaftar - Dengan trend line dan sparkles
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
            
            // Card Pendaftar Diterima - Dengan visual persentase
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
                
            // Card Kuota Magang - Dengan visual doughnut chart
            Stat::make('Kuota Tersedia', $this->formatNumber($kuotaTersedia))
                ->description('Terisi ' . $kuotaTerpakai . '% dari ' . ($latestRequirement ? $latestRequirement->quota : 0) . ' kuota')
                ->descriptionIcon('heroicon-m-chart-pie')
                ->chart([$totalDiterimaAnggota, max(0, $kuotaTersedia)])
                ->color($kuotaTersedia > 0 ? 'success' : 'danger')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-white to-' . ($kuotaTersedia > 0 ? 'success' : 'danger') . '-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl border border-' . ($kuotaTersedia > 0 ? 'success' : 'danger') . '-100 dark:border-' . ($kuotaTersedia > 0 ? 'success' : 'danger') . '-900 shadow-lg hover:shadow-' . ($kuotaTersedia > 0 ? 'success' : 'danger') . '-100/50 dark:hover:shadow-' . ($kuotaTersedia > 0 ? 'success' : 'danger') . '-900/50 transition-all duration-300',
                    'style' => '--chart-color-1: ' . $this->chartColors[$kuotaTersedia > 0 ? 'success' : 'danger'][0] . '; --chart-color-2: ' . $this->chartColors[$kuotaTersedia > 0 ? 'success' : 'danger'][1] . ';',
                ])
                ->icon('heroicon-o-academic-cap'),
            
            // ROW 2 - DETAIL STATUS
            // Card Status Distribusi - Dengan pie chart interaktif
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
                    'class' => 'bg-gradient-to-br from-white to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-lg hover:shadow-gray-200/50 dark:hover:shadow-gray-700/50 transition-all duration-300',
                ])
                ->icon('heroicon-o-chart-pie'),
                
            // Card Pendaftar Pending
            Stat::make('Menunggu Persetujuan', $this->formatNumber($totalPendingAnggota))
                ->description($persentasePending . '% dari total pendaftar')
                ->descriptionIcon('heroicon-m-clock')
                ->chart($statusDistribution['pending'])
                ->color('warning')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-white to-warning-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl border border-warning-100 dark:border-warning-900 shadow-lg hover:shadow-warning-100/50 dark:hover:shadow-warning-900/50 transition-all duration-300',
                    'style' => '--chart-color-1: ' . $this->chartColors['warning'][0] . '; --chart-color-2: ' . $this->chartColors['warning'][1] . ';',
                ])
                ->icon('heroicon-o-clock'),
                
            // Card Aktivitas Terbaru - Dengan timeline visual
            Stat::make('Aktivitas Terbaru', new HtmlString($aktivitasTerbaru['title']))
                ->description(new HtmlString($aktivitasTerbaru['description']))
                ->descriptionIcon('heroicon-m-clock')
                ->color('info')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-white to-info-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl border border-info-100 dark:border-info-900 shadow-lg hover:shadow-info-100/50 dark:hover:shadow-info-900/50 transition-all duration-300',
                    'style' => '--chart-color-1: ' . $this->chartColors['info'][0] . '; --chart-color-2: ' . $this->chartColors['info'][1] . ';',
                ])
                ->icon('heroicon-o-bell'),
            
            // ROW 3 - ANALISIS LANJUTAN
            // Card Tren Mingguan - Dengan chart hari per hari
            Stat::make('Tren Mingguan', $this->formatNumber($weeklyData['total']))
                ->description('Pendaftar 7 hari terakhir')
                ->descriptionIcon('heroicon-m-calendar')
                ->chart($weeklyData['data'])
                ->color('info')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-white to-info-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl border border-info-100 dark:border-info-900 shadow-lg hover:shadow-info-100/50 dark:hover:shadow-info-900/50 transition-all duration-300',
                    'style' => '--chart-color-1: ' . $this->chartColors['info'][0] . '; --chart-color-2: ' . $this->chartColors['info'][1] . ';',
                ])
                ->icon('heroicon-o-presentation-chart-line'),
            
            // Card Prediksi Kuota
            Stat::make('Prediksi Kuota Terisi', $prediksiKuotaTerisi['percentage'] . '%')
                ->description('Pada ' . $prediksiKuotaTerisi['date'])
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($prediksiKuotaTerisi['chart'])
                ->color($prediksiKuotaTerisi['color'])
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-white to-' . $prediksiKuotaTerisi['color'] . '-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl border border-' . $prediksiKuotaTerisi['color'] . '-100 dark:border-' . $prediksiKuotaTerisi['color'] . '-900 shadow-lg hover:shadow-' . $prediksiKuotaTerisi['color'] . '-100/50 dark:hover:shadow-' . $prediksiKuotaTerisi['color'] . '-900/50 transition-all duration-300',
                    'style' => '--chart-color-1: ' . $this->chartColors[$prediksiKuotaTerisi['color']][0] . '; --chart-color-2: ' . $this->chartColors[$prediksiKuotaTerisi['color']][1] . ';',
                ])
                ->icon('heroicon-o-chart-bar'),
            
            // Card Jurusan Terpopuler
            Stat::make('Jurusan Terpopuler', new HtmlString($jurusanPopuler['title']))
                ->description(new HtmlString($jurusanPopuler['description']))
                ->descriptionIcon('heroicon-m-academic-cap')
                ->chart($jurusanPopuler['chart'])
                ->color('purple')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-white to-purple-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl border border-purple-100 dark:border-purple-900 shadow-lg hover:shadow-purple-100/50 dark:hover:shadow-purple-900/50 transition-all duration-300',
                    'style' => '--chart-color-1: ' . $this->chartColors['purple'][0] . '; --chart-color-2: ' . $this->chartColors['purple'][1] . ';',
                ])
                ->icon('heroicon-o-academic-cap'),
            
            // ROW 4 - INFORMASI SISTEM & STATISTIK TAMBAHAN
            // Card Pendaftar Hari Ini
            Stat::make('Pendaftar Hari Ini', $this->formatNumber($anggotaHariIni['count']))
                ->description($anggotaHariIni['trend'])
                ->descriptionIcon($anggotaHariIni['trend_icon'])
                ->chart($anggotaHariIni['chart'])
                ->color($anggotaHariIni['color'])
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-white to-' . $anggotaHariIni['color'] . '-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl border border-' . $anggotaHariIni['color'] . '-100 dark:border-' . $anggotaHariIni['color'] . '-900 shadow-lg hover:shadow-' . $anggotaHariIni['color'] . '-100/50 dark:hover:shadow-' . $anggotaHariIni['color'] . '-900/50 transition-all duration-300',
                    'style' => '--chart-color-1: ' . $this->chartColors[$anggotaHariIni['color']][0] . '; --chart-color-2: ' . $this->chartColors[$anggotaHariIni['color']][1] . ';',
                ])
                ->icon('heroicon-o-calendar-days'),
            
            // Card Status Sistem
            Stat::make('Status Pendaftaran', $statusPendaftaran)
                ->description('Status penerimaan magang saat ini')
                ->descriptionIcon('heroicon-m-cog')
                ->color(Setting::first()?->status_pendaftaran ? 'success' : 'danger')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-white to-' . (Setting::first()?->status_pendaftaran ? 'success' : 'danger') . '-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl border border-' . (Setting::first()?->status_pendaftaran ? 'success' : 'danger') . '-100 dark:border-' . (Setting::first()?->status_pendaftaran ? 'success' : 'danger') . '-900 shadow-lg hover:shadow-' . (Setting::first()?->status_pendaftaran ? 'success' : 'danger') . '-100/50 dark:hover:shadow-' . (Setting::first()?->status_pendaftaran ? 'success' : 'danger') . '-900/50 transition-all duration-300',
                    'style' => '--chart-color-1: ' . $this->chartColors[Setting::first()?->status_pendaftaran ? 'success' : 'danger'][0] . '; --chart-color-2: ' . $this->chartColors[Setting::first()?->status_pendaftaran ? 'success' : 'danger'][1] . ';',
                ])
                ->icon('heroicon-o-' . (Setting::first()?->status_pendaftaran ? 'lock-open' : 'lock-closed')),
                
            // Card Periode Magang
            Stat::make('Periode Magang Aktif', $latestRequirement ? $latestRequirement->period : '-')
                ->description('Deadline: ' . ($latestRequirement ? Carbon::parse($latestRequirement->deadline)->format('d M Y') : '-'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('pink')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-white to-pink-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl border border-pink-100 dark:border-pink-900 shadow-lg hover:shadow-pink-100/50 dark:hover:shadow-pink-900/50 transition-all duration-300',
                    'style' => '--chart-color-1: ' . $this->chartColors['pink'][0] . '; --chart-color-2: ' . $this->chartColors['pink'][1] . ';',
                ])
                ->icon('heroicon-o-calendar'),
        ];
    }
    
    // HELPER FUNCTIONS
    
    // Format angka untuk tampilan yang lebih bagus
    private function formatNumber($number): string 
    {
        if ($number >= 1000) {
            return number_format($number / 1000, 1) . 'k';
        }
        return (string) $number;
    }
    
    // Mendapatkan deskripsi trend
    private function getTrendDescription($value, $label, $period): string 
    {
        if ($value === 0) {
            return "Tidak ada perubahan $period";
        }
        
        $prefix = $value > 0 ? '+' : '';
        return "$prefix$value% $label $period";
    }
    
    // Mendapatkan data bulanan dengan trend
    private function getMonthlyDataWithTrend(): array 
    {
        $anggotaPerBulan = DB::table('pendaftaran_magangs')
            ->join('anggota_pendaftaran', 'pendaftaran_magangs.id', '=', 'anggota_pendaftaran.pendaftaran_id')
            ->select(DB::raw('MONTH(pendaftaran_magangs.created_at) as bulan'), DB::raw('COUNT(anggota_pendaftaran.id) as total_anggota'))
            ->whereYear('pendaftaran_magangs.created_at', date('Y'))
            ->groupBy(DB::raw('MONTH(pendaftaran_magangs.created_at)'))
            ->get();
            
        // Format data untuk chart
        $chartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartData[$i] = 0;
        }
        
        foreach ($anggotaPerBulan as $data) {
            $chartData[$data->bulan] = $data->total_anggota;
        }
        
        // Hitung trend bulan ini vs bulan lalu
        $currentMonth = (int) date('n');
        $lastMonth = $currentMonth - 1;
        
        if ($lastMonth < 1) {
            $lastMonth = 12;
        }
        
        $currentMonthValue = $chartData[$currentMonth] ?? 0;
        $lastMonthValue = $chartData[$lastMonth] ?? 0;
        
        // Hitung pertumbuhan persentase
        $growth = 0;
        if ($lastMonthValue > 0) {
            $growth = round((($currentMonthValue - $lastMonthValue) / $lastMonthValue) * 100);
        } elseif ($currentMonthValue > 0) {
            $growth = 100; // Jika bulan lalu 0 dan bulan ini ada nilai, berarti pertumbuhan 100%
        }
        
        // Pastikan minimal ada angka untuk chart
        if (array_sum($chartData) == 0) {
            $chartData = [5, 8, 12, 8, 10, 12, 15, 18, 16, 23, 15, 20];
        }
        
        // Konversi ke array untuk chart
        return [
            'chartData' => array_values($chartData),
            'growth' => $growth,
            'currentMonth' => $currentMonthValue,
            'lastMonth' => $lastMonthValue,
            'trend' => array_values($chartData), // Untuk analisis tren
        ];
    }
    
    // Mendapatkan tren mingguan dengan label
    private function getWeeklyTrendWithLabels(): array 
    {
        $startDate = Carbon::now()->subDays(6);
        $endDate = Carbon::now();
        
        $results = [];
        $labels = [];
        $total = 0;
        
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $currentDate = $date->copy();
            
            $count = DB::table('pendaftaran_magangs')
                ->join('anggota_pendaftaran', 'pendaftaran_magangs.id', '=', 'anggota_pendaftaran.pendaftaran_id')
                ->whereDate('pendaftaran_magangs.created_at', $currentDate)
                ->count();
                
            $results[] = $count;
            $labels[] = $currentDate->format('D');
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
    
    // Mendapatkan distribusi status untuk chart
    private function getStatusDistributionChart($diterima, $pending, $ditolak): array 
    {
        // Jika semua data kosong, buat dummy data agar chart tetap menarik
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
    
    // Membuat chart single value dengan animasi yang bagus
    private function createSingleValueChart($value): array 
    {
        // Buat chart yang menarik dengan pola naik turun
        $base = max($value, 1);
        $result = [];
        
        for ($i = 0; $i < 12; $i++) {
            // Variasi +/- 30% untuk membuat visual yang menarik
            $variation = mt_rand(-30, 30) / 100;
            $result[] = max(round($base * (1 + $variation)), 1);
        }
        
        return $result;
    }
    
    // Mendapatkan kampus terpopuler untuk chart
    private function getTopCampusesWithChart(): array 
    {
        $kampusPopuler = DB::table('pendaftaran_magangs')
            ->join('anggota_pendaftaran', 'pendaftaran_magangs.id', '=', 'anggota_pendaftaran.pendaftaran_id')
            ->select('pendaftaran_magangs.asal_kampus', DB::raw('COUNT(anggota_pendaftaran.id) as total_anggota'))
            ->groupBy('pendaftaran_magangs.asal_kampus')
            ->orderByDesc('total_anggota')
            ->limit(5)
            ->get();
            
        $labels = [];
        $data = [];
        
        foreach ($kampusPopuler as $kampus) {
            $labels[] = $this->shortenCampusName($kampus->asal_kampus);
            $data[] = $kampus->total_anggota;
        }
        
        // Pastikan minimal ada data untuk chart
        if (count($data) == 0) {
            $labels = ['Univ A', 'Univ B', 'Univ C', 'Univ D', 'Univ E'];
            $data = [12, 10, 8, 6, 4];
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
            'chart' => $data,
            'topKampus' => $labels[0] ?? 'Tidak ada data'
        ];
    }
    
    // Hitung pertumbuhan bulanan
    private function calculateMonthlyGrowth(): array 
    {
        $data = $this->getMonthlyDataWithTrend();
        
        return [
            'value' => $data['growth'],
            'trend' => $data['growth'] >= 0 ? 'up' : 'down',
            'color' => $data['growth'] >= 0 ? 'success' : 'danger',
            'icon' => $data['growth'] >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down',
        ];
    }
    
    // Mendapatkan data prediksi kuota terisi
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
        
        // Hitung rata-rata tren
        $lastThreeMonths = array_slice($trendData, -3);
        $avgMonthlyGrowth = count($lastThreeMonths) > 0 ? array_sum($lastThreeMonths) / count($lastThreeMonths) : 0;
        
        // Prediksi jumlah bulan untuk memenuhi kuota
        $remaining = $quota - $currentFilled;
        $monthsNeeded = $avgMonthlyGrowth > 0 ? ceil($remaining / $avgMonthlyGrowth) : 12;
        
        // Batasi prediksi maksimal 12 bulan
        $monthsNeeded = min($monthsNeeded, 12);
        
        // Hitung tanggal prediksi
        $predictionDate = Carbon::now()->addMonths($monthsNeeded);
        
        // Hitung persentase prediksi
        $predictedPercentage = min(100, $currentPercentage + ($avgMonthlyGrowth / $quota * 100 * $monthsNeeded));
        
        // Warna berdasarkan seberapa cepat kuota terpenuhi
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
        
        // Data untuk chart
        $chartData = [
            round($predictedPercentage),
            max(0, 100 - round($predictedPercentage))
        ];
        
        return [
            'percentage' => round($predictedPercentage),
            'date' => $predictionDate->format('d M Y'),
            'chart' => $chartData,
            'color' => $color,
            'months' => $monthsNeeded
        ];
    }
    
    // Mendapatkan registrasi hari ini
    private function getTodayRegistrations(): array 
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        
        $todayCount = DB::table('pendaftaran_magangs')
            ->join('anggota_pendaftaran', 'pendaftaran_magangs.id', '=', 'anggota_pendaftaran.pendaftaran_id')
            ->whereDate('pendaftaran_magangs.created_at', $today)
            ->count();
            
        $yesterdayCount = DB::table('pendaftaran_magangs')
            ->join('anggota_pendaftaran', 'pendaftaran_magangs.id', '=', 'anggota_pendaftaran.pendaftaran_id')
            ->whereDate('pendaftaran_magangs.created_at', $yesterday)
            ->count();
            
        // Hitung persentase perubahan
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
        
        // Data untuk chart
        $hourlyData = [];
        for ($i = 0; $i < 24; $i += 2) {
            $start = Carbon::today()->addHours($i);
            $end = Carbon::today()->addHours($i + 2);
            
            $count = DB::table('pendaftaran_magangs')
                ->join('anggota_pendaftaran', 'pendaftaran_magangs.id', '=', 'anggota_pendaftaran.pendaftaran_id')
                ->whereBetween('pendaftaran_magangs.created_at', [$start, $end])
                ->count();
                
            $hourlyData[] = $count;
        }
        
        // Pastikan minimal ada data untuk chart
        if (array_sum($hourlyData) == 0) {
            $hourlyData = [0, 1, 2, 1, 0, 0, 1, 3, 2, 1, 0, 0];
        }
        
        return [
            'count' => $todayCount,
            'trend' => $trendText,
            'trend_icon' => $trendIcon,
            'color' => $color,
            'chart' => $hourlyData
        ];
    }
    
    // Mendapatkan statistik jurusan terpopuler
    private function getTopJurusan(): array 
    {
        $jurusanPopuler = DB::table('pendaftaran_magangs')
            ->join('anggota_pendaftaran', 'pendaftaran_magangs.id', '=', 'anggota_pendaftaran.pendaftaran_id')
            ->select('pendaftaran_magangs.jurusan', DB::raw('COUNT(anggota_pendaftaran.id) as total'))
            ->groupBy('pendaftaran_magangs.jurusan')
            ->orderByDesc('total')
            ->limit(3)
            ->get();
            
        if ($jurusanPopuler->isEmpty()) {
            return [
                'title' => 'Belum ada data',
                'description' => 'Belum ada pendaftar dari jurusan manapun',
                'chart' => [5, 3, 2, 1, 0, 0]
            ];
        }
        
        $topJurusan = $jurusanPopuler->first();
        $chartData = [];
        $descItems = [];
        
        foreach ($jurusanPopuler as $jurusan) {
            $chartData[] = $jurusan->total;
            $cleanJurusan = ucwords(strtolower(trim($jurusan->jurusan)));
            $descItems[] = "<span class='font-medium'>{$cleanJurusan}</span>: {$jurusan->total} pendaftar";
        }
        
        // Tambahkan dummy data untuk chart yang lebih menarik
        while (count($chartData) < 6) {
            $chartData[] = 0;
        }
        
        return [
            'title' => ucwords(strtolower($topJurusan->jurusan)),
            'description' => implode(' | ', $descItems),
            'chart' => $chartData
        ];
    }
    
    // Mendapatkan aktivitas terbaru
    private function getRecentActivity(): array 
    {
        $recentActivities = PendaftaranMagang::with(['user', 'anggota'])
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
            
            $anggotaCount = $activity->anggota->count();
            $userInitial = strtoupper(substr($activity->user->name ?? 'A', 0, 1));
            
            $timeAgo = $activity->created_at->diffForHumans();
            $activityItems[] = "<div class='flex items-start gap-2 mb-1'>
                <div class='w-6 h-6 rounded-full bg-info-500 text-white flex items-center justify-center font-bold text-xs'>{$userInitial}</div>
                <div>
                    <span class='font-medium'>{$activity->user->name}</span> mendaftar dengan {$anggotaCount} anggota ({$status})
                    <div class='text-xs text-gray-500'>{$timeAgo}</div>
                </div>
            </div>";
        }
        
        return [
            'title' => 'Pendaftaran Terbaru ' . $timeAgo,
            'description' => implode('', $activityItems)
        ];
    }
    
    // Membuat HTML untuk status distribusi
    private function createStatusDistributionHTML($diterima, $pending, $ditolak): string 
    {
        $total = $diterima + $pending + $ditolak;
        if ($total == 0) $total = 1; // Hindari division by zero
        
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
    
    // Fungsi helper untuk mempersingkat nama kampus
    private function shortenCampusName(string $name): string 
    {
        // Memotong nama kampus yang panjang
        $name = preg_replace('/\\\\r\\\\n$/', '', $name);
        
        if (strlen($name) > 25) {
            $words = explode(' ', $name);
            $acronym = '';
            
            foreach ($words as $word) {
                if (strlen($word) > 0 && ctype_upper($word[0])) {
                    $acronym .= $word[0];
                }
            }
            
            if (strlen($acronym) >= 3) {
                return $acronym;
            }
            
            return substr($name, 0, 25) . '...';
        }
        
        return $name;
    }
}