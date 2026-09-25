<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\ValidationException;
use App\Repositories\AnalyticsRepository;
use App\Repositories\CampaignRepository;
use App\Repositories\PublicOfferRepository;
use OutOfBoundsException;

final class AnalyticsService
{
    public function __construct(
        private readonly AnalyticsRepository $analytics,
        private readonly PublicOfferRepository $offers,
        private readonly CampaignRepository $campaigns,
        private readonly AnalyticsEventValidator $validator,
    ) {
    }

    /** @param array<string, mixed> $input */
    public function recordView(array $input, string $userAgent): int
    {
        $event = $this->resolve($input);

        return $this->analytics->recordView([
            'product_id' => $event['product_id'],
            'offer_id' => $event['offer_id'],
            'campaign_id' => $event['campaign_id'],
            'source' => $event['source'],
            'session_id' => $event['session_id'],
            'user_agent_family' => $this->userAgentFamily($userAgent),
        ]);
    }

    /** @param array<string, mixed> $input */
    public function recordClick(array $input): int
    {
        $event = $this->resolve($input);

        return $this->analytics->recordClick([
            'product_id' => $event['product_id'],
            'offer_id' => $event['offer_id'],
            'affiliate_link_id' => $event['affiliate_link_id'],
            'campaign_id' => $event['campaign_id'],
            'source' => $event['source'],
            'session_id' => $event['session_id'],
        ]);
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, int|string|null>
     */
    private function resolve(array $input): array
    {
        $data = $this->validator->validate($input);
        $offer = $this->offers->findPublished($data['offer_id']);

        if ($offer === null) {
            throw new OutOfBoundsException('Oferta não encontrada ou indisponível.');
        }

        $campaignId = null;

        if ($data['campaign'] !== null) {
            $campaign = $this->campaigns->findActiveBySlug($data['campaign']);

            if ($campaign === null) {
                throw new ValidationException([
                    'campaign' => 'A campanha informada não existe ou está inativa.',
                ]);
            }

            $campaignId = $campaign->id;
        }

        return [
            'product_id' => $offer->productId,
            'offer_id' => $offer->offerId,
            'affiliate_link_id' => $offer->affiliateLinkId,
            'campaign_id' => $campaignId,
            'source' => $data['source'],
            'session_id' => $data['session_id'],
        ];
    }

    private function userAgentFamily(string $userAgent): string
    {
        $userAgent = strtolower($userAgent);

        return match (true) {
            str_contains($userAgent, 'edg/') => 'Edge',
            str_contains($userAgent, 'opr/'), str_contains($userAgent, 'opera') => 'Opera',
            str_contains($userAgent, 'firefox/') => 'Firefox',
            str_contains($userAgent, 'chrome/'), str_contains($userAgent, 'crios/') => 'Chrome',
            str_contains($userAgent, 'safari/') => 'Safari',
            default => 'Other',
        };
    }
}
