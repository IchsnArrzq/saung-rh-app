<?php

namespace App\Domains\System\DTO;

/**
 * Isian form akun karyawan (layar Karyawan) ke SaveStaffUserUseCase.
 */
final readonly class StaffUserData
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone,
        /** Null = kata sandi tidak diganti (hanya sah saat mengubah akun). */
        public ?string $password,
        /** Nama peran; null untuk akun Superadmin, yang perannya tidak pernah diganti. */
        public ?string $role,
        public bool $isActive,
    ) {}
}
