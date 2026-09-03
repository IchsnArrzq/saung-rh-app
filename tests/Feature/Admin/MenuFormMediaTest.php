<?php

namespace Tests\Feature\Admin;

use App\Domains\Menu\Services\MediaService;
use App\Livewire\Admin\Menus\Form;
use App\Models\Media;
use App\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithAuthorization;
use Tests\TestCase;

class MenuFormMediaTest extends TestCase
{
    use InteractsWithAuthorization, RefreshDatabase;

    /** Yang diuji unggahan media, bukan otorisasi. */
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAsSuperadmin();
    }

    public function test_create_menu_with_staged_images_and_video(): void
    {
        Storage::fake('public');

        Livewire::test(Form::class)
            ->set('name', 'Ayam Bakar')
            ->set('price', '30000')
            ->set('newImages', [
                UploadedFile::fake()->image('a.jpg'),
                UploadedFile::fake()->image('b.jpg'),
            ])
            ->set('newVideo', UploadedFile::fake()->create('clip.mp4', 800, 'video/mp4'))
            ->call('save');

        $menu = Menu::where('name', 'Ayam Bakar')->firstOrFail();
        $this->assertSame(2, $menu->images()->count());
        $this->assertSame(1, $menu->images()->where('is_primary', true)->count());
        $this->assertSame(1, $menu->videos()->count());
    }

    public function test_edit_menu_delete_existing_media(): void
    {
        Storage::fake('public');
        $menu = Menu::create(['name' => 'Soto', 'slug' => 'soto', 'price' => 15000]);

        app(MediaService::class)->addImage($menu, UploadedFile::fake()->image('a.jpg'));
        $media = $menu->media()->first();

        Livewire::test(Form::class, ['menu' => $menu])
            ->call('deleteMedia', $media->id);

        $this->assertSame(0, $menu->images()->count());
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    public function test_edit_menu_set_primary(): void
    {
        Storage::fake('public');
        $menu = Menu::create(['name' => 'Bakso', 'slug' => 'bakso', 'price' => 12000]);

        $service = app(MediaService::class);
        $service->addImage($menu, UploadedFile::fake()->image('a.jpg'));
        $service->addImage($menu, UploadedFile::fake()->image('b.jpg'));

        $second = $menu->images()->where('is_primary', false)->first();

        Livewire::test(Form::class, ['menu' => $menu])
            ->call('setPrimary', $second->id);

        $this->assertTrue($second->fresh()->is_primary);
        $this->assertSame(1, $menu->images()->where('is_primary', true)->count());
    }
}
