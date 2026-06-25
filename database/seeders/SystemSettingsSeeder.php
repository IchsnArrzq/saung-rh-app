<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\PaymentAccount;
use App\Models\Subscription;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAppSettings();
        $this->seedPaymentAccounts();
        $this->seedLicense();
    }

    private function seedAppSettings(): void
    {
        $settings = [
            ['key' => 'app.name', 'value' => 'CR Cafe & Resto', 'group' => 'profile'],
            ['key' => 'app.tagline', 'value' => 'Smart Cafe & Resto Experience', 'group' => 'profile'],
            ['key' => 'contact.address', 'value' => 'Jl. Merdeka No. 10, Bandung', 'group' => 'profile'],
            ['key' => 'contact.phone', 'value' => '0812-3456-7890', 'group' => 'profile'],
            ['key' => 'contact.email', 'value' => 'halo@crcafe.test', 'group' => 'profile'],
            ['key' => 'finance.currency', 'value' => 'IDR', 'group' => 'finance'],
            ['key' => 'finance.tax_percent', 'value' => '11', 'group' => 'finance', 'type' => 'number'],
            ['key' => 'finance.service_charge_percent', 'value' => '5', 'group' => 'finance', 'type' => 'number'],
            ['key' => 'social.instagram', 'value' => '@crcaferesto', 'group' => 'social'],
        ];

        foreach ($settings as $setting) {
            AppSetting::query()->updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'group' => $setting['group'],
                    'type' => $setting['type'] ?? 'text',
                ],
            );
        }
    }

    private function seedPaymentAccounts(): void
    {
        if (PaymentAccount::query()->exists()) {
            return;
        }

        $accounts = [
            ['label' => 'BCA Utama', 'type' => 'bank', 'provider' => 'BCA', 'account_number' => '1234567890', 'account_holder' => 'PT CR Cafe Resto', 'sort_order' => 1],
            ['label' => 'Mandiri', 'type' => 'bank', 'provider' => 'Mandiri', 'account_number' => '0987654321', 'account_holder' => 'PT CR Cafe Resto', 'sort_order' => 2],
            ['label' => 'GoPay', 'type' => 'ewallet', 'provider' => 'GoPay', 'account_number' => '0812-3456-7890', 'account_holder' => 'CR Cafe', 'sort_order' => 3],
            ['label' => 'QRIS Kasir', 'type' => 'qris', 'provider' => 'QRIS', 'instructions' => 'Scan QRIS di meja kasir.', 'sort_order' => 4],
        ];

        foreach ($accounts as $account) {
            PaymentAccount::query()->create($account + ['is_active' => true]);
        }
    }

    private function seedLicense(): void
    {
        if (Subscription::query()->exists()) {
            return;
        }

        Subscription::query()->create([
            'plan' => 'professional',
            'license_key' => 'CR-'.Str::upper(Str::random(4)).'-'.Str::upper(Str::random(4)).'-'.Str::upper(Str::random(4)),
            'status' => 'active',
            'seats' => 25,
            'started_at' => now()->subMonths(2),
            'expires_at' => now()->addMonths(10),
            'notes' => 'Lisensi demo Smart Cafe & Resto.',
        ]);
    }
}
