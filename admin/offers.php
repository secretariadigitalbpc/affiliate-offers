<?php

declare(strict_types=1);

use App\Config\Database;
use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Repositories\AffiliateLinkRepository;
use App\Repositories\ProductRepository;

require_once dirname(__DIR__) . '/app/bootstrap.php';

$administrator = Auth::requirePage();
$csrfToken = Csrf::token();
$pdo = Database::connect();
$products = (new ProductRepository($pdo))->all();
$affiliateLinks = (new AffiliateLinkRepository($pdo))->all();
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ofertas — Sistema de Ofertas</title>
    <link rel="stylesheet" href="../public/assets/css/app.css">
</head>
<body data-offers-api="../public/api/offers">
    <main class="admin-shell">
        <header class="admin-header">
            <div>
                <span class="eyebrow">Administração</span>
                <h1>Ofertas</h1>
            </div>
            <div class="admin-actions">
                <span><?= htmlspecialchars($administrator->email, ENT_QUOTES, 'UTF-8') ?></span>
                <a class="secondary-link" href="products.php">Produtos</a>
                <a class="secondary-link" href="affiliate-links.php">Links</a>
                <a class="secondary-link" href="campaigns.php">Campanhas</a>
                <a class="secondary-link" href="dashboard.php">Dashboard</a>
                <a class="secondary-link" href="sales.php">Vendas</a>
                <a class="secondary-link" href="../public/">Página inicial</a>
                <form method="post" action="logout.php">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="text-button">Sair</button>
                </form>
            </div>
        </header>

        <section class="admin-grid">
            <form id="offer-form" class="panel form-grid">
                <input type="hidden" name="id">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                <div class="panel-heading">
                    <h2 id="form-title">Nova oferta</h2>
                    <button type="button" id="cancel-edit" class="text-button" hidden>Cancelar edição</button>
                </div>

                <label class="field-wide">
                    Produto
                    <select name="product_id" required <?= $products === [] ? 'disabled' : '' ?>>
                        <option value="">Selecione</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= (int) $product->id ?>">
                                <?= htmlspecialchars($product->title, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="field-wide">
                    Link de afiliado
                    <select name="affiliate_link_id" required <?= $affiliateLinks === [] ? 'disabled' : '' ?>>
                        <option value="">Selecione o produto primeiro</option>
                        <?php foreach ($affiliateLinks as $link): ?>
                            <option value="<?= (int) $link->id ?>" data-product-id="<?= (int) $link->productId ?>">
                                <?= htmlspecialchars(($link->tag ?: 'Sem tag') . ' — ' . $link->affiliateUrl, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Preço atual
                    <input name="price" inputmode="decimal" placeholder="99,90" required>
                </label>

                <label>
                    Preço anterior
                    <input name="old_price" inputmode="decimal" placeholder="129,90">
                </label>

                <label class="field-wide">
                    Cupom
                    <input name="coupon_text" maxlength="255">
                </label>

                <label class="field-wide">
                    Frete
                    <input name="shipping_text" maxlength="255">
                </label>

                <label>
                    Status
                    <select name="status" required>
                        <option value="draft">Rascunho</option>
                        <option value="approved">Aprovada</option>
                        <option value="published">Publicada</option>
                        <option value="expired">Expirada</option>
                        <option value="rejected">Rejeitada</option>
                    </select>
                </label>

                <label>
                    Início
                    <input name="starts_at" type="datetime-local">
                </label>

                <label>
                    Expiração
                    <input name="expires_at" type="datetime-local">
                </label>

                <div class="field-wide form-actions">
                    <button type="submit" class="primary-button" <?= $affiliateLinks === [] ? 'disabled' : '' ?>>
                        Salvar oferta
                    </button>
                </div>

                <?php if ($affiliateLinks === []): ?>
                    <p class="message error field-wide">Cadastre um produto e um link de afiliado antes de criar ofertas.</p>
                <?php endif; ?>
                <div id="form-message" class="message field-wide" role="status" aria-live="polite"></div>
            </form>

            <section class="panel products-panel" aria-labelledby="offers-title">
                <div class="panel-heading">
                    <h2 id="offers-title">Ofertas cadastradas</h2>
                    <button type="button" id="refresh-offers" class="text-button">Atualizar</button>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Preço</th>
                                <th>Status</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody id="offers-list"></tbody>
                    </table>
                </div>
                <p id="list-message" class="message" role="status" aria-live="polite"></p>
            </section>
        </section>
    </main>
    <script src="../public/assets/js/admin-offers.js" defer></script>
</body>
</html>
