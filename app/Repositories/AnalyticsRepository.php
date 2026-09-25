<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class AnalyticsRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @param array<string, mixed> $event */
    public function recordView(array $event): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO page_views (
                product_id, offer_id, campaign_id, source, session_id, user_agent_family
             ) VALUES (
                :product_id, :offer_id, :campaign_id, :source, :session_id, :user_agent_family
             )'
        );
        $statement->execute($event);

        return (int) $this->pdo->lastInsertId();
    }

    /** @param array<string, mixed> $event */
    public function recordClick(array $event): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO clicks (
                product_id, offer_id, affiliate_link_id, campaign_id, source, session_id
             ) VALUES (
                :product_id, :offer_id, :affiliate_link_id, :campaign_id, :source, :session_id
             )'
        );
        $statement->execute($event);

        return (int) $this->pdo->lastInsertId();
    }
}
