<?php

namespace App\Livewire\Admin\MenuMedia;

use App\Models\Menu;
use App\Services\Admin\MediaService;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class Manager extends Component
{
    use WithFileUploads;

    public Menu $menu;

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $newImages = [];

    public $newVideo = null;

    public function mount(Menu $menu): void
    {
        $this->menu = $menu;
    }

    public function updatedNewImages(): void
    {
        $this->validate(
            ['newImages.*' => ['image', 'max:4096']],
            [],
            ['newImages.*' => 'gambar'],
        );
    }

    public function uploadImages(MediaService $mediaService): void
    {
        $this->validate(
            [
                'newImages' => ['required', 'array', 'min:1'],
                'newImages.*' => ['image', 'max:4096'], // 4 MB per file
            ],
            [],
            ['newImages.*' => 'gambar'],
        );

        foreach ($this->newImages as $file) {
            $mediaService->addImage($this->menu, $file);
        }

        $this->newImages = [];
        session()->flash('success', 'Gambar berhasil diunggah.');
    }

    public function uploadVideo(MediaService $mediaService): void
    {
        $this->validate(
            ['newVideo' => ['required', 'file', 'mimetypes:video/mp4,video/webm,video/ogg', 'max:51200']], // 50 MB
            [],
            ['newVideo' => 'video'],
        );

        $mediaService->addVideo($this->menu, $this->newVideo);

        $this->newVideo = null;
        session()->flash('success', 'Video berhasil diunggah.');
    }

    public function setPrimary(string $mediaId, MediaService $mediaService): void
    {
        $mediaService->setPrimaryImage($this->menu, $mediaId);
        session()->flash('success', 'Gambar utama diperbarui.');
    }

    public function remove(string $mediaId, MediaService $mediaService): void
    {
        $media = $this->menu->media()->findOrFail($mediaId);
        $mediaService->delete($media);
        session()->flash('success', 'Media berhasil dihapus.');
    }

    public function render(): View
    {
        return view('livewire.admin.menu-media.manager', [
            'images' => $this->menu->images()->get(),
            'videos' => $this->menu->videos()->get(),
        ]);
    }
}
