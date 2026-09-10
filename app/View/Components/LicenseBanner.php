<?php

namespace App\View\Components;

use App\Domains\System\QueryUseCases\GetLicenseStatusQueryUseCase;
use App\Models\Subscription;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Peringatan lisensi di kerangka portal admin.
 *
 * Ini pemberitahuan, bukan pagar: tidak ada yang terkunci saat lisensi habis.
 * Jadi teksnya tidak boleh menjanjikan penghentian akses yang tidak pernah
 * terjadi — cukup sebutkan keadaannya dan ke mana harus pergi.
 */
class LicenseBanner extends Component
{
    /**
     * Keadaan 'none' (belum ada baris lisensi sama sekali) sengaja diam: itu
     * keadaan bawaan pemasangan baru, dan banner yang muncul sejak hari
     * pertama akan diabaikan sebelum sempat berarti apa-apa.
     *
     * @var array<int, string>
     */
    private const WARNING_STATES = ['expiring', 'expired'];

    /**
     * @var array{state:string, label:string, days:?int, plan:?string}
     */
    public array $summary;

    public function __construct(GetLicenseStatusQueryUseCase $license)
    {
        // Hanya admin & superadmin yang memegang izin lisensi. Gate diperiksa
        // lebih dulu supaya peran lain tidak ikut membayar query-nya di tiap
        // halaman yang mereka buka.
        $this->summary = Gate::allows('viewAny', Subscription::class)
            ? $license->summary()
            : ['state' => 'none', 'label' => '', 'days' => null, 'plan' => null];
    }

    public function shouldRender(): bool
    {
        return in_array($this->summary['state'], self::WARNING_STATES, true);
    }

    public function render(): View
    {
        return view('components.license-banner');
    }
}
