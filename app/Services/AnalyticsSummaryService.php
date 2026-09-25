<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\ValidationException;
use App\Repositories\AnalyticsSummaryRepository;
use App\Repositories\CampaignRepository;

final class AnalyticsSummaryService
{
    public function __construct(
        private readonly AnalyticsSummaryRepository $analytics,
        private readonly CampaignRepository $campaigns,
        private readonly AnalyticsSummaryValidator $validator,
    ) {
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function summary(array $input): array
    {
        $filters = $this->validator->validate($input);
        $campaign = $filters['campaign_id'] === null
            ? null
            : $this->campaigns->find($filters['campaign_id']);

        if ($filters['campaign_id'] !== null && $campaign === null) {
            throw new ValidationException(['campaign_id' => 'A campanha selecionada não existe.']);
        }

        $summary = $this->analytics->summary($filters);

        return [
            'filters' => [
                'from' => $filters['from'],
                'to' => $filters['to'],
                'campaign_id' => $filters['campaign_id'],
                'campaign_name' => $campaign?->name,
            ],
            'totals' => [
                'views' => $summary['views'],
                'clicks' => $summary['clicks'],
                'ctr' => $this->ctr($summary['views'], $summary['clicks']),
                'sales' => $summary['sales'],
                'gross_value' => $summary['gross_value'],
                'commission_value' => $summary['commission_value'],
                'conversion' => $this->percentage($summary['clicks'], $summary['sales']),
            ],
            'offers' => array_map(fn (array $row): array => [
                'product_id' => (int) $row['product_id'],
                'offer_id' => $row['offer_id'] === null ? null : (int) $row['offer_id'],
                'title' => (string) $row['title'],
                'views' => (int) $row['views'],
                'clicks' => (int) $row['clicks'],
                'ctr' => $this->ctr((int) $row['views'], (int) $row['clicks']),
            ], $summary['offers']),
        ];
    }

    private function ctr(int $views, int $clicks): float
    {
        return $this->percentage($views, $clicks);
    }

    private function percentage(int $base, int $result): float
    {
        return $base === 0 ? 0.0 : round(($result / $base) * 100, 2);
    }
}
