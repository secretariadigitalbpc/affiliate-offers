<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class AnalyticsSummaryRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @param array{from_start: string, to_exclusive: string, campaign_id: ?int} $filters
     * @return array{views: int, clicks: int, sales: int, gross_value: string, commission_value: string, offers: list<array<string, mixed>>}
     */
    public function summary(array $filters): array
    {
        $views = $this->countEvents('page_views', $filters);
        $clicks = $this->countEvents('clicks', $filters);
        $sales = $this->salesSummary($filters);
        [$viewsWhere, $viewsParameters] = $this->eventFilter('page_views', $filters);
        [$clicksWhere, $clicksParameters] = $this->eventFilter('clicks', $filters);

        $statement = $this->pdo->prepare(
            "SELECT events.product_id, events.offer_id, products.title,
                    SUM(events.views) AS views, SUM(events.clicks) AS clicks
             FROM (
                 SELECT page_views.product_id, page_views.offer_id,
                        COUNT(*) AS views, 0 AS clicks
                 FROM page_views
                 WHERE $viewsWhere
                 GROUP BY page_views.product_id, page_views.offer_id
                 UNION ALL
                 SELECT clicks.product_id, clicks.offer_id,
                        0 AS views, COUNT(*) AS clicks
                 FROM clicks
                 WHERE $clicksWhere
                 GROUP BY clicks.product_id, clicks.offer_id
             ) AS events
             INNER JOIN products ON products.id = events.product_id
             GROUP BY events.product_id, events.offer_id, products.title
             ORDER BY views DESC, clicks DESC, products.title ASC
             LIMIT 20"
        );
        $statement->execute(array_merge($viewsParameters, $clicksParameters));

        return [
            'views' => $views,
            'clicks' => $clicks,
            'sales' => $sales['sales'],
            'gross_value' => $sales['gross_value'],
            'commission_value' => $sales['commission_value'],
            'offers' => $statement->fetchAll(),
        ];
    }

    /**
     * @param array{from_start: string, to_exclusive: string, campaign_id: ?int} $filters
     * @return array{sales: int, gross_value: string, commission_value: string}
     */
    private function salesSummary(array $filters): array
    {
        $where = "sales.status = 'approved' AND sales.sale_date >= ? AND sales.sale_date < ?";
        $parameters = [$filters['from_start'], $filters['to_exclusive']];

        if ($filters['campaign_id'] !== null) {
            $where .= ' AND sales.campaign_id = ?';
            $parameters[] = $filters['campaign_id'];
        }

        $statement = $this->pdo->prepare(
            "SELECT COUNT(*) AS sales,
                    CAST(COALESCE(SUM(sales.gross_value), 0) AS DECIMAL(14,2)) AS gross_value,
                    CAST(COALESCE(SUM(sales.commission_value), 0) AS DECIMAL(14,2)) AS commission_value
             FROM sales
             WHERE $where"
        );
        $statement->execute($parameters);
        $row = $statement->fetch();

        return [
            'sales' => (int) $row['sales'],
            'gross_value' => (string) $row['gross_value'],
            'commission_value' => (string) $row['commission_value'],
        ];
    }

    /** @param array{from_start: string, to_exclusive: string, campaign_id: ?int} $filters */
    private function countEvents(string $table, array $filters): int
    {
        [$where, $parameters] = $this->eventFilter($table, $filters);
        $statement = $this->pdo->prepare("SELECT COUNT(*) FROM $table WHERE $where");
        $statement->execute($parameters);

        return (int) $statement->fetchColumn();
    }

    /**
     * @param array{from_start: string, to_exclusive: string, campaign_id: ?int} $filters
     * @return array{0: string, 1: list<string|int>}
     */
    private function eventFilter(string $table, array $filters): array
    {
        $where = "$table.created_at >= ? AND $table.created_at < ?";
        $parameters = [$filters['from_start'], $filters['to_exclusive']];

        if ($filters['campaign_id'] !== null) {
            $where .= " AND $table.campaign_id = ?";
            $parameters[] = $filters['campaign_id'];
        }

        return [$where, $parameters];
    }
}
