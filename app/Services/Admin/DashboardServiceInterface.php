<?php

namespace App\Services\Admin;

interface DashboardServiceInterface
{
    /**
     * @return array<string, mixed>
     */
    public function summary(): array;
}
