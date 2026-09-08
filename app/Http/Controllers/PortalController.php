<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PortalController extends Controller
{
    public function manager(): View
    {
        return view('portal.dashboard', [
            'title' => 'Dashboard Manager',
            'subtitle' => 'Panel kontrol produktivitas staf & analitik bisnis.',
            'modules' => [
                ['label' => 'Jadwal shift staf', 'icon' => 'ri-calendar-schedule-line', 'desc' => 'Atur jadwal kerja staf.', 'route' => 'manager.shifts'],
                ['label' => 'KPI pegawai', 'icon' => 'ri-trophy-line', 'desc' => 'Ringkasan performa pegawai.', 'route' => 'manager.kpi'],
                ['label' => 'Pelanggan paling sering datang', 'icon' => 'ri-vip-crown-line', 'desc' => 'Pelanggan dengan kunjungan terbanyak.', 'route' => 'manager.top-customers'],
                ['label' => 'Persetujuan permintaan khusus', 'icon' => 'ri-checkbox-circle-line', 'desc' => 'Setujui atau tolak permintaan khusus.', 'route' => 'manager.special-requests'],
                ['label' => 'Menu paling laku', 'icon' => 'ri-bar-chart-box-line', 'desc' => 'Menu & minuman terlaris.', 'route' => 'receptionist.analytics'],
                ['label' => 'Kelola reservasi', 'icon' => 'ri-calendar-check-line', 'desc' => 'Kelola reservasi masuk.', 'route' => 'receptionist.bookings'],
            ],
        ]);
    }

    public function receptionist(): View
    {
        return view('portal.dashboard', [
            'title' => 'Dashboard Resepsionis',
            'subtitle' => 'Monitoring operasional cafe secara real-time.',
            'modules' => [
                ['label' => 'Monitor dapur', 'icon' => 'ri-radar-line', 'desc' => 'Pantau status makanan dari dapur.', 'route' => 'kds.index'],
                ['label' => 'Peta meja', 'icon' => 'ri-layout-grid-line', 'desc' => 'Peta visual meja kosong/terisi.', 'route' => 'receptionist.table-map'],
                ['label' => 'Kelola reservasi', 'icon' => 'ri-calendar-check-line', 'desc' => 'Kelola reservasi masuk.', 'route' => 'receptionist.bookings'],
                ['label' => 'Hitung pengunjung', 'icon' => 'ri-group-line', 'desc' => 'Jumlah pengunjung harian.', 'route' => 'receptionist.visitors'],
                ['label' => 'Menu paling laku', 'icon' => 'ri-bar-chart-box-line', 'desc' => 'Menu & minuman terlaris.', 'route' => 'receptionist.analytics'],
                ['label' => 'Antrean lagu', 'icon' => 'ri-music-2-line', 'desc' => 'Kelola permintaan lagu dari meja.', 'route' => 'songs.queue'],
            ],
        ]);
    }

    public function waiter(): View
    {
        return view('portal.dashboard', [
            'title' => 'Portal pelayan',
            'subtitle' => 'Bantuan mobilitas pelayanan di area resto.',
            'modules' => [
                ['label' => 'Ubah status meja', 'icon' => 'ri-refresh-line', 'desc' => 'Ubah status meja secara instan.', 'route' => 'waiter.tables'],
                ['label' => 'Catatan tip & layanan', 'icon' => 'ri-hand-coin-line', 'desc' => 'Catat layanan & tip.', 'route' => 'waiter.tips'],
                ['label' => 'Antrean lagu', 'icon' => 'ri-music-2-line', 'desc' => 'Kelola permintaan lagu dari meja.', 'route' => 'songs.queue'],
                ['label' => 'Permintaan khusus pelanggan', 'icon' => 'ri-customer-service-2-line', 'desc' => 'Terima instruksi pelanggan.', 'route' => 'waiter.special-requests'],
            ],
        ]);
    }

    public function ob(): View
    {
        return view('portal.dashboard', [
            'title' => 'Portal kebersihan',
            'subtitle' => 'Dukungan kebersihan & kesiapan meja.',
            'modules' => [
                ['label' => 'Pembersihan meja', 'icon' => 'ri-brush-line', 'desc' => 'Ubah status meja jadi siap dipakai.', 'route' => 'ob.tables'],
            ],
        ]);
    }

    // --- Fase 3 feature pages (embedded Livewire via generic wrapper) ---

    public function waiterTables(): View
    {
        return view('staff.page', [
            'title' => 'Update Status Meja',
            'subtitle' => 'Ubah status meja secara instan dari lantai resto.',
            'icon' => 'ri-refresh-line',
            'livewireComponent' => 'staff.waiter.table-status-updater',
        ]);
    }

    public function waiterTips(): View
    {
        return view('staff.page', [
            'title' => 'Catatan tip & layanan',
            'subtitle' => 'Catat tip yang diterima dan aktivitas pelayanan.',
            'icon' => 'ri-hand-coin-line',
            'livewireComponent' => 'staff.waiter.tips-service-log',
        ]);
    }

    public function receptionistTableMap(): View
    {
        return view('staff.page', [
            'title' => 'Peta meja',
            'subtitle' => 'Peta visual status meja secara real-time.',
            'icon' => 'ri-layout-grid-line',
            'livewireComponent' => 'staff.receptionist.table-map',
        ]);
    }

    public function receptionistVisitors(): View
    {
        return view('staff.page', [
            'title' => 'Hitung pengunjung',
            'subtitle' => 'Hitung pengunjung harian dari QR & walk-in.',
            'icon' => 'ri-group-line',
            'livewireComponent' => 'staff.receptionist.visitor-counter',
        ]);
    }

    public function receptionistBookings(): View
    {
        return view('staff.page', [
            'title' => 'Kelola reservasi',
            'subtitle' => 'Kelola dan ubah status reservasi yang masuk.',
            'icon' => 'ri-calendar-check-line',
            'livewireComponent' => 'staff.receptionist.booking-board',
        ]);
    }

    public function receptionistAnalytics(): View
    {
        return view('staff.page', [
            'title' => 'Menu paling laku',
            'subtitle' => 'Menu & minuman terlaris beserta pendapatan.',
            'icon' => 'ri-bar-chart-box-line',
            'livewireComponent' => 'staff.receptionist.top-analytics',
        ]);
    }

    public function managerShifts(): View
    {
        return view('staff.page', [
            'title' => 'Jadwal shift staf',
            'subtitle' => 'Atur jadwal kerja staf per minggu.',
            'icon' => 'ri-calendar-schedule-line',
            'livewireComponent' => 'staff.manager.shift-scheduler',
        ]);
    }

    public function managerKpi(): View
    {
        return view('staff.page', [
            'title' => 'Employee KPI',
            'subtitle' => 'Peringkat pegawai terbaik berdasarkan tip, layanan & permintaan.',
            'icon' => 'ri-trophy-line',
            'livewireComponent' => 'staff.manager.staff-kpi',
        ]);
    }

    public function managerTopCustomers(): View
    {
        return view('staff.page', [
            'title' => 'Top Customer',
            'subtitle' => 'Pelanggan paling loyal berdasarkan belanja.',
            'icon' => 'ri-vip-crown-line',
            'livewireComponent' => 'staff.manager.top-customers',
        ]);
    }

    public function managerSpecialRequests(): View
    {
        return view('staff.page', [
            'title' => 'Persetujuan permintaan khusus',
            'subtitle' => 'Setujui atau tolak permintaan khusus, lalu cocokkan ke waiter.',
            'icon' => 'ri-checkbox-circle-line',
            'livewireComponent' => 'staff.manager.special-request-approver',
        ]);
    }

    public function waiterSpecialRequests(): View
    {
        return view('staff.page', [
            'title' => 'Permintaan khusus pelanggan',
            'subtitle' => 'Permintaan khusus yang ditugaskan kepada Anda.',
            'icon' => 'ri-customer-service-2-line',
            'livewireComponent' => 'staff.waiter.special-request-handler',
        ]);
    }

    public function songQueue(): View
    {
        return view('staff.page', [
            'title' => 'Antrean Lagu',
            'subtitle' => 'Kelola request lagu/karaoke dari meja pelanggan.',
            'icon' => 'ri-music-2-line',
            'livewireComponent' => 'staff.song-queue-board',
        ]);
    }

    public function obTables(): View
    {
        return view('staff.page', [
            'title' => 'Pembersihan Meja',
            'subtitle' => 'Set status meja menjadi siap/kosong setelah dibersihkan.',
            'icon' => 'ri-brush-line',
            'livewireComponent' => 'staff.waiter.table-status-updater',
        ]);
    }
}
