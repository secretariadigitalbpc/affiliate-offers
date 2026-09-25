<?php

declare(strict_types=1);

use App\Config\Database;
use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Repositories\CampaignRepository;

require_once dirname(__DIR__) . '/app/bootstrap.php';

$administrator = Auth::requirePage();
$csrfToken = Csrf::token();
$campaigns = (new CampaignRepository(Database::connect()))->all();
$today = new DateTimeImmutable('today');
$defaultFrom = $today->modify('-29 days')->format('Y-m-d');
$defaultTo = $today->format('Y-m-d');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard — Sistema de Ofertas</title>
    <link rel="stylesheet" href="../public/assets/css/app.css">
</head>
<body data-analytics-api="../public/api/analytics/summary.php">
    <main class="admin-shell">
        <header class="admin-header">
            <div>
                <span class="eyebrow">Administração</span>
                <h1>Dashboard</h1>
            </div>
            <div class="admin-actions">
                <span><?= htmlspecialchars($administrator->email, ENT_QUOTES, 'UTF-8') ?></span>
                <a class="secondary-link" href="products.php">Produtos</a>
                <a class="secondary-link" href="affiliate-links.php">Links</a>
                <a class="secondary-link" href="offers.php">Ofertas</a>
                <a class="secondary-link" href="campaigns.php">Campanhas</a>
                <a class="secondary-link" href="sales.php">Vendas</a>
                <a class="secondary-link" href="mercado-livre.php">Mercado Livre</a>
                <a class="secondary-link" href="../public/">Página inicial</a>
                <form method="post" action="logout.php">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="text-button">Sair</button>
                </form>
            </div>
        </header>

        <form id="analytics-filters" class="panel analytics-filters">
            <label>
                De
                <input type="date" name="from" value="<?= $defaultFrom ?>" required>
            </label>
            <label>
                Até
                <input type="date" name="to" value="<?= $defaultTo ?>" required>
            </label>
            <label>
                Campanha
                <select name="campaign_id">
                    <option value="">Todas as campanhas</option>
                    <?php foreach ($campaigns as $campaign): ?>
                        <option value="<?= (int) $campaign->id ?>">
                            <?= htmlspecialchars($campaign->name, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit" class="primary-button">Aplicar filtros</button>
        </form>

        <p id="analytics-message" class="message" role="status" aria-live="polite"></p>

        <section class="analytics-cards" aria-label="Resumo de analytics">
            <article class="metric-card">
                <span>Visualizações</span>
                <strong id="metric-views">—</strong>
            </article>
            <article class="metric-card">
                <span>Cliques</span>
                <strong id="metric-clicks">—</strong>
            </article>
            <article class="metric-card">
                <span>CTR</span>
                <strong id="metric-ctr">—</strong>
            </article>
            <article class="metric-card">
                <span>Vendas aprovadas</span>
                <strong id="metric-sales">—</strong>
            </article>
            <article class="metric-card">
                <span>Conversão clique → venda</span>
                <strong id="metric-conversion">—</strong>
            </article>
            <article class="metric-card">
                <span>Valor bruto aprovado</span>
                <strong id="metric-gross-value">—</strong>
            </article>
            <article class="metric-card">
                <span>Comissão aprovada</span>
                <strong id="metric-commission-value">—</strong>
            </article>
        </section>

        <section class="panel analytics-table-panel" aria-labelledby="offer-metrics-title">
            <div class="panel-heading">
                <div>
                    <h2 id="offer-metrics-title">Desempenho por oferta</h2>
                    <small>Até 20 ofertas no período selecionado.</small>
                </div>
                <button type="button" id="refresh-analytics" class="text-button">Atualizar</button>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Oferta</th>
                            <th>Visualizações</th>
                            <th>Cliques</th>
                            <th>CTR</th>
                        </tr>
                    </thead>
                    <tbody id="analytics-offers"></tbody>
                </table>
            </div>
        </section>
    </main>
    <script src="../public/assets/js/admin-dashboard.js" defer></script>
</body>
</html>
