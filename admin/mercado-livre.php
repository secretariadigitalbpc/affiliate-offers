<?php

declare(strict_types=1);

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Integrations\MercadoLivre\MercadoLivreConfig;

require_once dirname(__DIR__) . '/app/bootstrap.php';

$administrator = Auth::requirePage();
$csrfToken = Csrf::token();
$status = MercadoLivreConfig::fromEnvironment()->publicStatus();
$isReady = $status['status'] === 'configuration_ready';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mercado Livre — Integrações</title>
    <link rel="stylesheet" href="../public/assets/css/app.css">
</head>
<body>
    <main class="admin-shell">
        <header class="admin-header">
            <div>
                <span class="eyebrow">Integrações</span>
                <h1>Mercado Livre</h1>
            </div>
            <div class="admin-actions">
                <span><?= htmlspecialchars($administrator->email, ENT_QUOTES, 'UTF-8') ?></span>
                <a class="secondary-link" href="dashboard.php">Dashboard</a>
                <a class="secondary-link" href="sales.php">Vendas</a>
                <a class="secondary-link" href="../public/">Página inicial</a>
                <form method="post" action="logout.php">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="text-button">Sair</button>
                </form>
            </div>
        </header>

        <section class="integration-grid">
            <article class="panel integration-status">
                <span class="status-pill <?= $isReady ? 'status-ready' : 'status-pending' ?>">
                    <?= $isReady ? 'Configuração pronta' : 'Configuração pendente' ?>
                </span>
                <h2>OAuth oficial</h2>
                <p>
                    O fluxo OAuth 2.0 oficial está preparado, mas permanece desativado por padrão.
                    Nenhuma senha da sua conta é solicitada ou armazenada.
                </p>
                <dl class="status-list">
                    <div><dt>OAuth habilitado</dt><dd><?= $status['oauth_enabled'] ? 'Sim' : 'Não' ?></dd></div>
                    <div><dt>Client ID configurado</dt><dd><?= $status['client_id_configured'] ? 'Sim' : 'Não' ?></dd></div>
                    <div><dt>Client Secret configurado</dt><dd><?= $status['client_secret_configured'] ? 'Sim' : 'Não' ?></dd></div>
                    <div><dt>Redirect URI</dt><dd><?= htmlspecialchars((string) $status['redirect_uri'], ENT_QUOTES, 'UTF-8') ?: 'Não configurada' ?></dd></div>
                </dl>
            </article>

            <article class="panel integration-status">
                <span class="status-pill status-pending">Limitação confirmada</span>
                <h2>Programa de Afiliados</h2>
                <p>
                    A documentação pública consultada não apresenta uma API oficial de afiliados para
                    vendas, comissões ou geração de links. O OAuth geral não será tratado como prova de compatibilidade.
                </p>
                <p>
                    Até existir documentação oficial compatível, use a importação CSV de vendas e cadastre
                    os links gerados no Hub de Afiliados.
                </p>
                <div class="integration-links">
                    <a class="secondary-link" href="https://developers.mercadolivre.com.br/pt_br/autenticacao-e-autorizacao" target="_blank" rel="noopener noreferrer">OAuth oficial</a>
                    <a class="secondary-link" href="sales.php">Importar CSV</a>
                </div>
            </article>
        </section>
    </main>
</body>
</html>
