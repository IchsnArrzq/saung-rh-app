<?php

namespace App\Livewire\Admin\Settings;

use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class NavigationForm extends Component
{
    public string $navigation_menu_preference = 'sidebar';

    public function mount(): void
    {
        $value = (string) (auth()->user()?->navigation_menu_preference ?? 'sidebar');

        $this->navigation_menu_preference = in_array($value, ['sidebar', 'navbar'], true)
            ? $value
            : 'sidebar';
    }

    public function save()
    {
        $validated = $this->validate([
            'navigation_menu_preference' => ['required', 'string', Rule::in(['sidebar', 'navbar'])],
        ]);

        $user = auth()->user();

        if (! $user) {
            abort(403);
        }

        $user->update([
            'navigation_menu_preference' => $validated['navigation_menu_preference'],
        ]);

        session()->flash('success', 'Pengaturan navigasi berhasil disimpan.');

        return $this->redirectRoute('settings.navigation', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.settings.navigation-form');
    }
}
