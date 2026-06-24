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
                ['label' => 'Employee Shifting', 'icon' => 'ri-calendar-schedule-line', 'desc' => 'Atur jadwal kerja staf.', 'phase' => 'Fase 6'],
                ['label' => 'Employee KPI (Top Staff)', 'icon' => 'ri-trophy-line', 'desc' => 'Performa & penilaian pegawai terbaik.', 'phase' => 'Fase 6'],
                ['label' => 'Customer Analytics (Top Customer)', 'icon' => 'ri-vip-crown-line', 'desc' => 'Pelanggan paling loyal.', 'phase' => 'Fase 6'],
                ['label' => 'Special Order Approver', 'icon' => 'ri-checkbox-circle-line', 'desc' => 'Approve/Reject special request berbayar.', 'phase' => 'Fase 6'],
                ['label' => 'F&B Top Analytics', 'icon' => 'ri-bar-chart-box-line', 'desc' => 'Menu & minuman terlaris.', 'phase' => 'Aktif', 'route' => 'receptionist.analytics'],
                ['label' => 'Booking Management', 'icon' => 'ri-calendar-check-line', 'desc' => 'Kelola reservasi masuk.', 'phase' => 'Aktif', 'route' => 'receptionist.bookings'],
            ],
        ]);
    }

    public function receptionist(): View
    {
        return view('portal.dashboard', [
            'title' => 'Dashboard Resepsionis',
            'subtitle' => 'Monitoring operasional cafe secara real-time.',
            'modules' => [
                ['label' => 'Live Kitchen Monitor', 'icon' => 'ri-radar-line', 'desc' => 'Pantau status makanan dari dapur.', 'phase' => 'Aktif', 'route' => 'kds.index'],
                ['label' => 'Table Map', 'icon' => 'ri-layout-grid-line', 'desc' => 'Peta visual meja kosong/terisi.', 'phase' => 'Aktif', 'route' => 'receptionist.table-map'],
                ['label' => 'Booking Management', 'icon' => 'ri-calendar-check-line', 'desc' => 'Kelola reservasi masuk.', 'phase' => 'Aktif', 'route' => 'receptionist.bookings'],
                ['label' => 'Visitor Counter', 'icon' => 'ri-group-line', 'desc' => 'Jumlah pengunjung harian.', 'phase' => 'Aktif', 'route' => 'receptionist.visitors'],
                ['label' => 'F&B Top Analytics', 'icon' => 'ri-bar-chart-box-line', 'desc' => 'Menu & minuman terlaris.', 'phase' => 'Aktif', 'route' => 'receptionist.analytics'],
            ],
        ]);
    }

    public function waiter(): View
    {
        return view('portal.dashboard', [
            'title' => 'Portal Waiter',
            'subtitle' => 'Bantuan mobilitas pelayanan di area resto.',
            'modules' => [
                ['label' => 'Table Status Updater', 'icon' => 'ri-refresh-line', 'desc' => 'Ubah status meja secara instan.', 'phase' => 'Aktif', 'route' => 'waiter.tables'],
                ['label' => 'Tips & Service Log', 'icon' => 'ri-hand-coin-line', 'desc' => 'Catat layanan & tip.', 'phase' => 'Aktif', 'route' => 'waiter.tips'],
                ['label' => 'Special Request Handler', 'icon' => 'ri-customer-service-2-line', 'desc' => 'Terima instruksi pelanggan.', 'phase' => 'Fase 6'],
            ],
        ]);
    }

    public function ob(): View
    {
        return view('portal.dashboard', [
            'title' => 'Portal Office Boy',
            'subtitle' => 'Dukungan kebersihan & kesiapan meja.',
            'modules' => [
                ['label' => 'Pembersihan Meja', 'icon' => 'ri-brush-line', 'desc' => 'Set status meja menjadi siap/kosong.', 'phase' => 'Aktif', 'route' => 'ob.tables'],
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
            'component' => 'staff.waiter.table-status-updater',
        ]);
    }

    public function waiterTips(): View
    {
        return view('staff.page', [
            'title' => 'Tips & Service Log',
            'subtitle' => 'Catat tip yang diterima dan aktivitas pelayanan.',
            'icon' => 'ri-hand-coin-line',
            'component' => 'staff.waiter.tips-service-log',
        ]);
    }

    public function receptionistTableMap(): View
    {
        return view('staff.page', [
            'title' => 'Table Map',
            'subtitle' => 'Peta visual status meja secara real-time.',
            'icon' => 'ri-layout-grid-line',
            'component' => 'staff.receptionist.table-map',
        ]);
    }

    public function receptionistVisitors(): View
    {
        return view('staff.page', [
            'title' => 'Visitor Counter',
            'subtitle' => 'Hitung pengunjung harian dari QR & walk-in.',
            'icon' => 'ri-group-line',
            'component' => 'staff.receptionist.visitor-counter',
        ]);
    }

    public function receptionistBookings(): View
    {
        return view('staff.page', [
            'title' => 'Booking Management',
            'subtitle' => 'Kelola dan ubah status reservasi yang masuk.',
            'icon' => 'ri-calendar-check-line',
            'component' => 'staff.receptionist.booking-board',
        ]);
    }

    public function receptionistAnalytics(): View
    {
        return view('staff.page', [
            'title' => 'F&B Top Analytics',
            'subtitle' => 'Menu & minuman terlaris beserta pendapatan.',
            'icon' => 'ri-bar-chart-box-line',
            'component' => 'staff.receptionist.top-analytics',
        ]);
    }

    public function obTables(): View
    {
        return view('staff.page', [
            'title' => 'Pembersihan Meja',
            'subtitle' => 'Set status meja menjadi siap/kosong setelah dibersihkan.',
            'icon' => 'ri-brush-line',
            'component' => 'staff.waiter.table-status-updater',
        ]);
    }
}
