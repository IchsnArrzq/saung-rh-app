<?php

namespace App\Domains\Reporting\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

/**
 * Buckets settled order totals into the chart series for a date range.
 *
 * Pure arithmetic over an already-fetched collection — no query of its own, so
 * it is a Service and not a Repository (AGENTS.md § Service).
 *
 * The bucket size follows the range, because a chart with 400 daily columns is
 * unreadable and a one-day chart with a single column says nothing:
 *   ≤ 1 day  → 24 hourly buckets
 *   ≤ 60 day → one bucket per day
 *   longer   → one bucket per month
 *
 * Empty buckets are emitted as zero rather than skipped, so the x-axis stays
 * evenly spaced and a quiet Tuesday is visible instead of invisible.
 */
class SalesTrendService
{
    private const DAILY_BUCKET_LIMIT = 60;

    /**
     * @param  Collection<int, object{ordered_at: mixed, total: mixed}>  $orders
     * @return array{labels: array<int, string>, values: array<int, float>}
     */
    public function build(Collection $orders, CarbonInterface $start, CarbonInterface $end): array
    {
        $days = $start->diffInDays($end);

        $trend = match (true) {
            $days <= 1 => $this->byHour($orders),
            $days <= self::DAILY_BUCKET_LIMIT => $this->byDay($orders, $start, $end),
            default => $this->byMonth($orders, $start, $end),
        };

        return [
            'labels' => $trend->keys()->values()->all(),
            'values' => $trend->values()->all(),
        ];
    }

    /**
     * @param  Collection<int, object>  $orders
     * @return Collection<string, float>
     */
    private function byHour(Collection $orders): Collection
    {
        $grouped = $this->sumBy($orders, 'H:00');
        $trend = collect();

        foreach (range(0, 23) as $hour) {
            $key = str_pad((string) $hour, 2, '0', STR_PAD_LEFT).':00';
            $trend->put($key, $grouped->get($key, 0.0));
        }

        return $trend;
    }

    /**
     * @param  Collection<int, object>  $orders
     * @return Collection<string, float>
     */
    private function byDay(Collection $orders, CarbonInterface $start, CarbonInterface $end): Collection
    {
        $grouped = $this->sumBy($orders, 'Y-m-d');
        $trend = collect();

        foreach (CarbonPeriod::create($start, $end) as $date) {
            $trend->put($date->format('d M'), $grouped->get($date->format('Y-m-d'), 0.0));
        }

        return $trend;
    }

    /**
     * @param  Collection<int, object>  $orders
     * @return Collection<string, float>
     */
    private function byMonth(Collection $orders, CarbonInterface $start, CarbonInterface $end): Collection
    {
        $grouped = $this->sumBy($orders, 'Y-m');
        $trend = collect();
        $period = CarbonPeriod::create($start->copy()->startOfMonth(), '1 month', $end->copy()->startOfMonth());

        foreach ($period as $date) {
            $trend->put($date->format('M Y'), $grouped->get($date->format('Y-m'), 0.0));
        }

        return $trend;
    }

    /**
     * @param  Collection<int, object>  $orders
     * @return Collection<string, float>
     */
    private function sumBy(Collection $orders, string $format): Collection
    {
        return $orders
            ->groupBy(fn ($order): string => Carbon::parse($order->ordered_at)->format($format))
            ->map(fn (Collection $group): float => (float) $group->sum('total'));
    }
}
