<?php

namespace App\Services\Admin;

interface ReportServiceInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getReportData(string $startDate, string $endDate): array;
}
