<?php

use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\MenuCategoryController;
use App\Http\Controllers\Admin\MenuController;
use App\Models\Menu;
use App\Models\MenuCategory;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Menu — modul referensi untuk otorisasi berbasis policy
|--------------------------------------------------------------------------
|
| `->can()` memanggil policy yang sama dengan `@can` di Blade dan
| `$this->authorize()` di komponen Livewire, jadi ketiga lapisan menjawab satu
| sumber kebenaran: MenuPolicy. Route melindungi halamannya, Livewire melindungi
| aksinya, Blade menyembunyikan tombol yang tidak boleh ditekan.
|
| Argumen kedua `->can('update', 'menu')` adalah NAMA PARAMETER route, bukan
| kelas: Laravel mengambil model hasil route-model-binding lalu meneruskannya ke
| policy, sehingga aturan per-baris ("hanya menu miliknya") tetap mungkin nanti.
| Untuk ability tanpa model (viewAny/create), kelasnya yang dioper.
|
| Catatan: grup induk di routes/admin.php masih memakai `role:` yang kaku.
| Selama itu ada, role baru tetap tidak bisa masuk meski permission-nya lengkap.
| Gerbang role itu dilepas setelah semua modul dipindah ke pola ini.
*/

Route::get('menus', [MenuController::class, 'index'])
    ->name('menus.index')
    ->can('viewAny', Menu::class);

Route::get('menus/create', [MenuController::class, 'create'])
    ->name('menus.create')
    ->can('create', Menu::class);

Route::get('menus/{menu}/edit', [MenuController::class, 'edit'])
    ->name('menus.edit')
    ->can('update', 'menu');

Route::get('menus/{menu}/media', [MenuController::class, 'media'])
    ->name('menus.media.edit')
    ->can('update', 'menu');

// Resep per menu — mengubah komposisi bahan berarti mengubah menunya.
Route::get('menus/{menu}/ingredients', [IngredientController::class, 'menuIngredients'])
    ->name('menus.ingredients.edit')
    ->can('update', 'menu');

Route::get('menu-categories', [MenuCategoryController::class, 'index'])
    ->name('menu-categories.index')
    ->can('viewAny', MenuCategory::class);

Route::get('menu-categories/create', [MenuCategoryController::class, 'create'])
    ->name('menu-categories.create')
    ->can('create', MenuCategory::class);

Route::get('menu-categories/{menuCategory}/edit', [MenuCategoryController::class, 'edit'])
    ->name('menu-categories.edit')
    ->can('update', 'menuCategory');
