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
    <title>Campanhas — Sistema de Ofertas</title>
    <link rel="stylesheet" href="../public/assets/css/app.css">
</head>
<body data-campaigns-api="../public/api/campaigns">
    <main class="admin-shell">
        <header class="admin-header">
            <div>
                <span class="eyebrow">Administração</span>
                <h1>Campanhas</h1>
            </div>
            <div class="admin-actions">
                <span><?= htmlspecialchars($administrator->email, ENT_QUOTES, 'UTF-8') ?></span>
                <a class="secondary-link" href="products.php">Produtos</a>
                <a class="secondary-link" href="affiliate-links.php">Links</a>
                <a class="secondary-link" href="offers.php">Ofertas</a>
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
            <form id="campaign-form" class="panel form-grid">
                <input type="hidden" name="id">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                <div class="panel-heading">
                    <h2 id="form-title">Nova campanha</h2>
                    <button type="button" id="cancel-edit" class="text-button" hidden>Cancelar edição</button>
                </div>

                <label class="field-wide">
                    Nome
                    <input name="name" maxlength="255" required>
                </label>

                <label class="field-wide">
                    Slug <small>(gerado pelo servidor quando vazio)</small>
                    <input name="slug" maxlength="255">
                </label>

                <label>
                    Origem
                    <input name="source" maxlength="100" placeholder="whatsapp" required>
                </label>

                <label>
                    Mídia
                    <input name="medium" maxlength="100" placeholder="social">
                </label>

                <label class="checkbox-field">
                    <input name="active" type="checkbox" value="1" checked>
                    Campanha ativa
                </label>

                <div class="field-wide form-actions">
                    <button type="submit" class="primary-button">Salvar campanha</button>
                </div>

                <div id="form-message" class="message field-wide" role="status" aria-live="polite"></div>
            </form>

            <section class="panel products-panel" aria-labelledby="campaigns-title">
                <div class="panel-heading">
                    <h2 id="campaigns-title">Campanhas cadastradas</h2>
                    <button type="button" id="refresh-campaigns" class="text-button">Atualizar</button>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Campanha</th>
                                <th>Origem / mídia</th>
                                <th>Status</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody id="campaigns-list"></tbody>
                    </table>
                </div>
                <p id="list-message" class="message" role="status" aria-live="polite"></p>
            </section>
        </section>
    </main>
    <script src="../public/assets/js/admin-campaigns.js" defer></script>
</body>
</html>
