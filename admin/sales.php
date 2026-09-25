<?php

declare(strict_types=1);

use App\Config\Database;
use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Repositories\CampaignRepository;
use App\Repositories\ProductRepository;

require_once dirname(__DIR__) . '/app/bootstrap.php';

$administrator = Auth::requirePage();
$csrfToken = Csrf::token();
$pdo = Database::connect();
$products = (new ProductRepository($pdo))->all();
$campaigns = (new CampaignRepository($pdo))->all();
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vendas — Sistema de Ofertas</title>
    <link rel="stylesheet" href="../public/assets/css/app.css">
</head>
<body data-sales-api="../public/api/sales">
    <main class="admin-shell">
        <header class="admin-header">
            <div>
                <span class="eyebrow">Administração</span>
                <h1>Vendas e comissões</h1>
            </div>
            <div class="admin-actions">
                <span><?= htmlspecialchars($administrator->email, ENT_QUOTES, 'UTF-8') ?></span>
                <a class="secondary-link" href="products.php">Produtos</a>
                <a class="secondary-link" href="affiliate-links.php">Links</a>
                <a class="secondary-link" href="offers.php">Ofertas</a>
                <a class="secondary-link" href="campaigns.php">Campanhas</a>
                <a class="secondary-link" href="dashboard.php">Dashboard</a>
                <a class="secondary-link" href="../public/">Página inicial</a>
                <form method="post" action="logout.php">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="text-button">Sair</button>
                </form>
            </div>
        </header>

        <form id="sales-import-form" class="panel import-panel" enctype="multipart/form-data">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <div>
                <h2>Importar vendas por CSV</h2>
                <p>
                    Até 2 MB e 5.000 linhas. Referências já importadas são ignoradas.
                    <a href="../public/assets/examples/sales-import-example.csv" download>Baixar CSV de exemplo</a>.
                </p>
            </div>
            <label>
                Arquivo CSV
                <input type="file" name="sales_file" accept=".csv,text/csv" required>
            </label>
            <button type="submit" class="primary-button">Importar arquivo</button>
            <div id="import-message" class="message" role="status" aria-live="polite"></div>
            <ul id="import-errors" class="import-errors"></ul>
        </form>

        <section class="admin-grid">
            <form id="sale-form" class="panel form-grid">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                <div class="panel-heading">
                    <h2>Registrar venda manual</h2>
                </div>

                <label>
                    Marketplace
                    <select name="marketplace" required>
                        <option value="mercado_livre">Mercado Livre</option>
                        <option value="shopee">Shopee</option>
                    </select>
                </label>

                <label>
                    Referência externa <small>(opcional)</small>
                    <input name="external_sale_reference" maxlength="255">
                </label>

                <label class="field-wide">
                    Produto <small>(opcional)</small>
                    <select name="product_id">
                        <option value="">Sem produto vinculado</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= (int) $product->id ?>" data-marketplace="<?= htmlspecialchars($product->marketplace, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($product->title, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="field-wide">
                    Campanha <small>(opcional)</small>
                    <select name="campaign_id">
                        <option value="">Sem campanha vinculada</option>
                        <?php foreach ($campaigns as $campaign): ?>
                            <option value="<?= (int) $campaign->id ?>">
                                <?= htmlspecialchars($campaign->name, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Quantidade
                    <input name="quantity" type="number" min="1" max="1000000" value="1" required>
                </label>

                <label>
                    Valor bruto
                    <input name="gross_value" inputmode="decimal" placeholder="100,00" required>
                </label>

                <label>
                    Comissão
                    <input name="commission_value" inputmode="decimal" value="0,00" required>
                </label>

                <label>
                    Status
                    <select name="status" required>
                        <option value="pending">Pendente</option>
                        <option value="approved">Aprovada</option>
                        <option value="cancelled">Cancelada</option>
                        <option value="refunded">Reembolsada</option>
                    </select>
                </label>

                <label class="field-wide">
                    Data da venda
                    <input name="sale_date" type="datetime-local" value="<?= date('Y-m-d\TH:i') ?>" required>
                </label>

                <div class="field-wide form-actions">
                    <button type="submit" class="primary-button">Registrar venda</button>
                </div>

                <div id="form-message" class="message field-wide" role="status" aria-live="polite"></div>
            </form>

            <section class="panel products-panel" aria-labelledby="sales-title">
                <div class="panel-heading">
                    <h2 id="sales-title">Vendas registradas</h2>
                    <button type="button" id="refresh-sales" class="text-button">Atualizar</button>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Venda</th>
                                <th>Produto / campanha</th>
                                <th>Valor bruto</th>
                                <th>Comissão</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="sales-list"></tbody>
                    </table>
                </div>
                <p id="list-message" class="message" role="status" aria-live="polite"></p>
            </section>
        </section>
    </main>
    <script src="../public/assets/js/admin-sales.js" defer></script>
</body>
</html>
