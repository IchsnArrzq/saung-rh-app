<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // spatie/laravel-permission menyimpan peta nama→model role & permission
        // di dalam proses. RefreshDatabase membatalkan transaksinya, tapi cache
        // itu tidak ikut hilang, jadi test berikutnya bisa memegang ID role yang
        // barisnya sudah tidak ada — dan hasilnya bergantung urutan test.
        //
        // Gejalanya pernah muncul sebagai AuthenticationTest yang gagal hanya
        // saat dijalankan bersama test lain: SidebarNavigation memanggil
        // hasRole(), yang menjawab salah karena cache basi.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
