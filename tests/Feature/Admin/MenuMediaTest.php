<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\MenuMedia\Manager;
use App\Models\Media;
use App\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MenuMediaTest extends TestCase
{
    use RefreshDatabase;

    private function menu(): Menu
    {
        return Menu::create(['name' => 'Sate Ayam', 'slug' => 'sate-ayam', 'price' => 20000]);
    }

    public function test_upload_images_first_becomes_primary(): void
    {
        Storage::fake('public');
        $menu = $this->menu();

        Livewire::test(Manager::class, ['menu' => $menu])
            ->set('newImages', [
                UploadedFile::fake()->image('a.jpg'),
                UploadedFile::fake()->image('b.jpg'),
            ])
            ->call('uploadImages');

        $this->assertSame(2, $menu->images()->count());
        $this->assertSame(1, $menu->images()->where('is_primary', true)->count());

        $stored = Media::where('mediable_id', $menu->id)->first();
        Storage::disk('public')->assertExists($stored->path);
    }

    public function test_set_primary_moves_flag(): void
    {
        Storage::fake('public');
        $menu = $this->menu();

        $c = Livewire::test(Manager::class, ['menu' => $menu])
            ->set('newImages', [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')])
            ->call('uploadImages');

        $second = $menu->images()->where('is_primary', false)->first();
        $c->call('setPrimary', $second->id);

        $this->assertTrue($second->fresh()->is_primary);
        $this->assertSame(1, $menu->images()->where('is_primary', true)->count());
    }

    public function test_upload_video(): void
    {
        Storage::fake('public');
        $menu = $this->menu();

        Livewire::test(Manager::class, ['menu' => $menu])
            ->set('newVideo', UploadedFile::fake()->create('clip.mp4', 1000, 'video/mp4'))
            ->call('uploadVideo');

        $this->assertSame(1, $menu->videos()->count());
    }

    public function test_remove_primary_promotes_next(): void
    {
        Storage::fake('public');
        $menu = $this->menu();

        $c = Livewire::test(Manager::class, ['menu' => $menu])
            ->set('newImages', [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')])
            ->call('uploadImages');

        $primary = $menu->images()->where('is_primary', true)->first();
        $c->call('remove', $primary->id);

        $this->assertSame(1, $menu->images()->count());
        $this->assertSame(1, $menu->images()->where('is_primary', true)->count());
    }
}
