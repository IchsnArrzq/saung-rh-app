<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Validasi format form Karyawan. Aturan bisnis (peran yang boleh dipilih,
 * perlindungan Superadmin, larangan mengubah akun sendiri) ada di
 * SaveStaffUserUseCase; siapa yang boleh membuka form dijaga rute + UserPolicy.
 */
class StaffUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Email dinormalkan SEBELUM dicek unik: kolom `users.email` di Postgres peka
     * huruf besar-kecil, jadi "Budi@x.com" lolos cek unik terhadap "budi@x.com"
     * lalu tetap bentrok di constraint database — halaman 500, bukan pesan form.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower(trim($this->input('email')))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->route('admin_user');
        $editing = $user instanceof User;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($editing ? $user->id : null)],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+()\-\s]+$/'],
            'password' => [$editing ? 'nullable' : 'required', 'string', 'confirmed', Password::defaults()],
            'role' => [$editing && $user->hasRole(Role::LOCKED) ? 'nullable' : 'required', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi — dipakai untuk masuk.',
            'email.email' => 'Format email belum benar, mis. nama@restoran.com.',
            'email.unique' => 'Email ini sudah dipakai akun lain.',
            'phone.regex' => 'Nomor HP hanya boleh berisi angka, spasi, +, -, dan tanda kurung.',
            'phone.max' => 'Nomor HP maksimal 30 karakter.',
            'password.required' => 'Buat kata sandi untuk akun baru.',
            'password.confirmed' => 'Konfirmasi kata sandi belum sama.',
            'password.min' => 'Kata sandi minimal :min karakter.',
            'role.required' => 'Pilih peran karyawan.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'email' => 'email',
            'phone' => 'nomor HP',
            'password' => 'kata sandi',
            'role' => 'peran',
            'is_active' => 'status akun',
        ];
    }
}
