<?php

declare(strict_types=1);

use App\Helpers\Auth;
use App\Helpers\Csrf;

require_once dirname(__DIR__) . '/app/bootstrap.php';

$administrator = Auth::requirePage();
$csrfToken = Csrf::token();
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Produtos — Sistema de Ofertas</title>
    <link rel="stylesheet" href="../public/assets/css/app.css">
</head>
<body data-products-api="../public/api/products">
    <main class="admin-shell">
        <header class="admin-header">
            <div>
                <span class="eyebrow">Administração</span>
                <h1>Produtos</h1>
            </div>
            <div class="admin-actions">
                <span><?= htmlspecialchars($administrator->email, ENT_QUOTES, 'UTF-8') ?></span>
                <a class="secondary-link" href="affiliate-links.php">Links de afiliado</a>
                <a class="secondary-link" href="offers.php">Ofertas</a>
                <a class="secondary-link" href="campaigns.php">Campanhas</a>
                <a class="secondary-link" href="dashboard.php">Dashboard</a>
                <a class="secondary-link" href="sales.php">Vendas</a>
                <a class="secondary-link" href="../public/">Página inicial</a>
                <form method="post" action="logout.php">
                    <input
                        type="hidden"
                        name="_csrf"
                        value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"
                    >
                    <button type="submit" class="text-button">Sair</button>
                </form>
            </div>
        </header>

        <section class="admin-grid">
            <form id="product-form" class="panel form-grid">
                <input type="hidden" id="product-id" name="id">
                <input
                    type="hidden"
                    id="csrf-token"
                    name="_csrf"
                    value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"
                >

                <div class="panel-heading">
                    <h2 id="form-title">Novo produto</h2>
                    <button type="button" id="cancel-edit" class="text-button" hidden>Cancelar edição</button>
                </div>

                <label>
                    Marketplace
                    <select name="marketplace" required>
                        <option value="mercado_livre">Mercado Livre</option>
                        <option value="shopee">Shopee</option>
                    </select>
                </label>

                <label>
                    ID no marketplace
                    <input name="marketplace_product_id" maxlength="100" required>
                </label>

                <label class="field-wide">
                    Título
                    <input name="title" maxlength="255" required>
                </label>

                <label class="field-wide">
                    Slug <small>(gerado pelo servidor quando vazio)</small>
                    <input name="slug" maxlength="255">
                </label>

                <label class="field-wide">
                    URL da imagem
                    <input name="image_url" type="url">
                </label>

                <label>
                    Vendedor
                    <input name="seller_name" maxlength="255">
                </label>

                <label>
                    Avaliação
                    <input name="rating" type="number" min="0" max="5" step="0.01">
                </label>

                <label>
                    Quantidade vendida
                    <input name="sales_count" type="number" min="0" step="1">
                </label>

                <label class="checkbox-field">
                    <input name="active" type="checkbox" value="1" checked>
                    Produto ativo
                </label>

                <div class="field-wide form-actions">
                    <button type="submit" class="primary-button">Salvar produto</button>
                </div>

                <div id="form-message" class="message field-wide" role="status" aria-live="polite"></div>
            </form>

            <section class="panel products-panel" aria-labelledby="products-title">
                <div class="panel-heading">
                    <h2 id="products-title">Produtos cadastrados</h2>
                    <button type="button" id="refresh-products" class="text-button">Atualizar</button>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Marketplace</th>
                                <th>Status</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody id="products-list"></tbody>
                    </table>
                </div>
                <p id="list-message" class="message" role="status" aria-live="polite"></p>
            </section>
        </section>
    </main>
    <script src="../public/assets/js/admin-products.js" defer></script>
</body>
</html>
