<?php

declare(strict_types=1);

use App\Config\Database;
use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Repositories\ProductRepository;

require_once dirname(__DIR__) . '/app/bootstrap.php';

$administrator = Auth::requirePage();
$csrfToken = Csrf::token();
$products = (new ProductRepository(Database::connect()))->all();
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Links de afiliado — Sistema de Ofertas</title>
    <link rel="stylesheet" href="../public/assets/css/app.css">
</head>
<body data-affiliate-links-api="../public/api/affiliate-links">
    <main class="admin-shell">
        <header class="admin-header">
            <div>
                <span class="eyebrow">Administração</span>
                <h1>Links de afiliado</h1>
            </div>
            <div class="admin-actions">
                <span><?= htmlspecialchars($administrator->email, ENT_QUOTES, 'UTF-8') ?></span>
                <a class="secondary-link" href="products.php">Produtos</a>
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
            <form id="affiliate-link-form" class="panel form-grid">
                <input type="hidden" name="id">
                <input type="hidden" name="marketplace">
                <input
                    type="hidden"
                    name="_csrf"
                    value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"
                >

                <div class="panel-heading">
                    <h2 id="form-title">Novo link</h2>
                    <button type="button" id="cancel-edit" class="text-button" hidden>Cancelar edição</button>
                </div>

                <label class="field-wide">
                    Produto
                    <select name="product_id" required <?= $products === [] ? 'disabled' : '' ?>>
                        <option value="">Selecione</option>
                        <?php foreach ($products as $product): ?>
                            <option
                                value="<?= (int) $product->id ?>"
                                data-marketplace="<?= htmlspecialchars($product->marketplace, ENT_QUOTES, 'UTF-8') ?>"
                            >
                                <?= htmlspecialchars($product->title, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="field-wide">
                    URL oficial de afiliado
                    <input name="affiliate_url" type="url" maxlength="2048" required>
                </label>

                <label>
                    Tag
                    <input name="tag" maxlength="100">
                </label>

                <label class="checkbox-field">
                    <input name="active" type="checkbox" value="1" checked>
                    Link ativo
                </label>

                <div class="field-wide form-actions">
                    <button type="submit" class="primary-button" <?= $products === [] ? 'disabled' : '' ?>>
                        Salvar link
                    </button>
                </div>

                <?php if ($products === []): ?>
                    <p class="message error field-wide">Cadastre um produto antes de criar links.</p>
                <?php endif; ?>
                <div id="form-message" class="message field-wide" role="status" aria-live="polite"></div>
            </form>

            <section class="panel products-panel" aria-labelledby="links-title">
                <div class="panel-heading">
                    <h2 id="links-title">Links cadastrados</h2>
                    <button type="button" id="refresh-links" class="text-button">Atualizar</button>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Link</th>
                                <th>Status</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody id="affiliate-links-list"></tbody>
                    </table>
                </div>
                <p id="list-message" class="message" role="status" aria-live="polite"></p>
            </section>
        </section>
    </main>
    <script src="../public/assets/js/admin-affiliate-links.js" defer></script>
</body>
</html>
