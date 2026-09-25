<?php

declare(strict_types=1);

use App\Config\Database;
use App\Helpers\Csrf;
use App\Helpers\Session;
use App\Repositories\AdministratorRepository;

require_once dirname(__DIR__) . '/app/bootstrap.php';

define('BASE_URL', rtrim(getenv('E2E_BASE_URL') ?: 'http://localhost/whatsappAF', '/'));

/**
 * @param array<string, mixed> $data
 * @return array{status: int, body: string, json: array<string, mixed>|null}
 */
function httpRequest(string $method, string $path, array $data = [], ?string $sessionId = null, ?string $csrf = null): array
{
    $headers = ['Accept: application/json'];
    $content = '';

    if ($sessionId !== null) {
        $headers[] = 'Cookie: affiliate_session=' . $sessionId;
    }

    if ($csrf !== null) {
        $headers[] = 'X-CSRF-Token: ' . $csrf;
    }

    if ($method === 'POST') {
        $headers[] = 'Content-Type: application/json';
        $content = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers),
            'content' => $content,
            'ignore_errors' => true,
            'follow_location' => 0,
            'timeout' => 15,
        ],
    ]);
    $body = file_get_contents(BASE_URL . $path, false, $context);

    if ($body === false) {
        throw new RuntimeException('Falha HTTP ao acessar ' . $path . '.');
    }

    $responseHeaders = $http_response_header ?? [];
    preg_match('/\s(\d{3})\s/', $responseHeaders[0] ?? '', $matches);
    $status = isset($matches[1]) ? (int) $matches[1] : 0;
    $json = null;

    if (str_contains(implode("\n", $responseHeaders), 'application/json')) {
        $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        $json = is_array($decoded) ? $decoded : null;
    }

    return ['status' => $status, 'body' => $body, 'json' => $json];
}

/** @param array{status: int, body: string, json: array<string, mixed>|null} $response */
function expectStatus(array $response, int $expected, string $step): void
{
    if ($response['status'] !== $expected) {
        throw new RuntimeException(
            sprintf('%s retornou HTTP %d em vez de %d: %s', $step, $response['status'], $expected, $response['body'])
        );
    }
}

$pdo = Database::connect();
$token = bin2hex(random_bytes(8));
$email = "e2e-$token@example.test";
$externalProductId = "E2E-$token";
$campaignSlug = "e2e-$token";
$saleReference = "E2E-SALE-$token";
$administratorId = null;
$productId = null;
$campaignId = null;
$affiliateLinkId = null;
$offerId = null;
$saleId = null;
$sessionId = null;
$completed = false;

try {
    $administrator = (new AdministratorRepository($pdo))->create($email, password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT));
    $administratorId = $administrator->id;

    Session::start();
    $_SESSION['administrator_id'] = $administratorId;
    $csrf = Csrf::token();
    $sessionId = session_id();
    session_write_close();

    $anonymousApi = httpRequest('GET', '/public/api/products/list.php');
    expectStatus($anonymousApi, 401, 'Isolamento da API administrativa');
    $anonymousDashboard = httpRequest('GET', '/admin/dashboard.php');
    expectStatus($anonymousDashboard, 302, 'Isolamento do dashboard');

    $productResponse = httpRequest('POST', '/public/api/products/create.php', [
        'marketplace' => 'mercado_livre',
        'marketplace_product_id' => $externalProductId,
        'title' => 'Produto E2E Mercado Livre',
        'slug' => '',
        'image_url' => 'https://http2.mlstatic.com/teste-e2e.jpg',
        'seller_name' => 'Vendedor E2E',
        'rating' => '4.80',
        'sales_count' => '20',
        'active' => '1',
    ], $sessionId, $csrf);
    expectStatus($productResponse, 201, 'Cadastro de produto');
    $productId = (int) ($productResponse['json']['data']['product']['id'] ?? 0);

    $campaignResponse = httpRequest('POST', '/public/api/campaigns/create.php', [
        'name' => 'Campanha E2E',
        'slug' => $campaignSlug,
        'source' => 'whatsapp',
        'medium' => 'social',
        'active' => '1',
    ], $sessionId, $csrf);
    expectStatus($campaignResponse, 201, 'Cadastro de campanha');
    $campaignId = (int) ($campaignResponse['json']['data']['campaign']['id'] ?? 0);

    $linkResponse = httpRequest('POST', '/public/api/affiliate-links/create.php', [
        'product_id' => $productId,
        'marketplace' => 'mercado_livre',
        'affiliate_url' => 'https://www.mercadolivre.com.br/afiliados/hub',
        'tag' => 'e2e',
        'active' => '1',
    ], $sessionId, $csrf);
    expectStatus($linkResponse, 201, 'Cadastro de link afiliado');
    $affiliateLinkId = (int) ($linkResponse['json']['data']['affiliate_link']['id'] ?? 0);

    $offerResponse = httpRequest('POST', '/public/api/offers/create.php', [
        'product_id' => $productId,
        'affiliate_link_id' => $affiliateLinkId,
        'price' => '80,00',
        'old_price' => '100,00',
        'coupon_text' => 'E2E20',
        'shipping_text' => 'Frete grátis',
        'status' => 'published',
        'starts_at' => '',
        'expires_at' => '',
    ], $sessionId, $csrf);
    expectStatus($offerResponse, 201, 'Cadastro de oferta');
    $offerId = (int) ($offerResponse['json']['data']['offer']['id'] ?? 0);

    if (min($productId, $campaignId, $affiliateLinkId, $offerId) < 1) {
        throw new RuntimeException('Um dos cadastros E2E não retornou identificador válido.');
    }

    $storefront = httpRequest('GET', '/public/ofertas.php?campaign=' . urlencode($campaignSlug) . '&source=whatsapp');
    expectStatus($storefront, 200, 'Vitrine pública');

    if (!str_contains($storefront['body'], 'Produto E2E Mercado Livre') || !str_contains($storefront['body'], 'campaign=' . $campaignSlug)) {
        throw new RuntimeException('A vitrine não exibiu a oferta ou não preservou a campanha.');
    }

    $detail = httpRequest('GET', '/public/produto.php?offer_id=' . $offerId . '&campaign=' . urlencode($campaignSlug) . '&source=whatsapp');
    expectStatus($detail, 200, 'Detalhe público');

    if (
        !str_contains($detail['body'], 'https://www.mercadolivre.com.br/afiliados/hub')
        || !str_contains($detail['body'], 'rel="sponsored noopener noreferrer"')
    ) {
        throw new RuntimeException('O detalhe não apresentou o link afiliado com atributos seguros.');
    }

    $viewResponse = httpRequest('POST', '/public/api/events/view.php', [
        'offer_id' => $offerId,
        'campaign' => $campaignSlug,
        'source' => 'whatsapp',
        'session_id' => "e2e-$token",
    ]);
    expectStatus($viewResponse, 202, 'Registro de visualização');

    $clickResponse = httpRequest('POST', '/public/api/events/click.php', [
        'offer_id' => $offerId,
        'campaign' => $campaignSlug,
        'source' => 'whatsapp',
        'session_id' => "e2e-$token",
    ]);
    expectStatus($clickResponse, 202, 'Registro de clique');

    $saleResponse = httpRequest('POST', '/public/api/sales/manual.php', [
        'marketplace' => 'mercado_livre',
        'external_sale_reference' => $saleReference,
        'product_id' => $productId,
        'campaign_id' => $campaignId,
        'quantity' => '1',
        'gross_value' => '100,00',
        'commission_value' => '10,00',
        'status' => 'approved',
        'sale_date' => date('Y-m-d\TH:i'),
    ], $sessionId, $csrf);
    expectStatus($saleResponse, 201, 'Registro de venda');
    $saleId = (int) ($saleResponse['json']['data']['sale']['id'] ?? 0);

    $today = date('Y-m-d');
    $summaryResponse = httpRequest(
        'GET',
        '/public/api/analytics/summary.php?from=' . $today . '&to=' . $today . '&campaign_id=' . $campaignId,
        [],
        $sessionId,
    );
    expectStatus($summaryResponse, 200, 'Resumo do dashboard');
    $totals = $summaryResponse['json']['data']['summary']['totals'] ?? [];

    if (
        ($totals['views'] ?? null) !== 1
        || ($totals['clicks'] ?? null) !== 1
        || ($totals['sales'] ?? null) !== 1
        || (string) ($totals['gross_value'] ?? '') !== '100.00'
        || (string) ($totals['commission_value'] ?? '') !== '10.00'
        || (float) ($totals['ctr'] ?? -1) !== 100.0
        || (float) ($totals['conversion'] ?? -1) !== 100.0
    ) {
        throw new RuntimeException('As métricas finais do E2E estão incorretas: ' . json_encode($totals));
    }

    $dashboard = httpRequest('GET', '/admin/dashboard.php', [], $sessionId);
    expectStatus($dashboard, 200, 'Dashboard autenticado');

    $completed = true;
} finally {
    $deletions = [
        ['DELETE FROM page_views WHERE offer_id = :id', $offerId],
        ['DELETE FROM clicks WHERE offer_id = :id', $offerId],
        ['DELETE FROM sales WHERE id = :id', $saleId],
        ['DELETE FROM offers WHERE id = :id', $offerId],
        ['DELETE FROM affiliate_links WHERE id = :id', $affiliateLinkId],
        ['DELETE FROM campaigns WHERE id = :id', $campaignId],
        ['DELETE FROM products WHERE id = :id', $productId],
        ['DELETE FROM administrators WHERE id = :id', $administratorId],
    ];

    foreach ($deletions as [$sql, $id]) {
        if (is_int($id) && $id > 0) {
            $statement = $pdo->prepare($sql);
            $statement->execute(['id' => $id]);
        }
    }

    if ($sessionId !== null && session_status() !== PHP_SESSION_ACTIVE) {
        session_id($sessionId);
        Session::start();
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        Session::destroy();
    }
}

if ($completed) {
    echo "MVP E2E: produto → link → oferta → vitrine → view/click → venda → dashboard = OK\n";
    echo "Métricas: 1 view, 1 clique, 1 venda, R$ 100,00 bruto e R$ 10,00 de comissão = OK\n";
}
