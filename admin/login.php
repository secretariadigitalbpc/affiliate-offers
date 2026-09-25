<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\AuthController;
use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Repositories\AdministratorRepository;
use App\Services\AuthService;

require_once dirname(__DIR__) . '/app/bootstrap.php';

if (Auth::check()) {
    header('Location: products.php');
    exit;
}

$errors = [];
$email = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));

    if (!Csrf::verify($_POST)) {
        $errors['csrf'] = 'A sessão expirou. Atualize a página e tente novamente.';
    } else {
        $controller = new AuthController(
            new AuthService(new AdministratorRepository(Database::connect()))
        );
        $errors = $controller->login($email, (string) ($_POST['password'] ?? ''));

        if ($errors === []) {
            header('Location: products.php');
            exit;
        }
    }
}

$csrfToken = Csrf::token();
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar — Sistema de Ofertas</title>
    <link rel="stylesheet" href="../public/assets/css/app.css">
</head>
<body>
    <main class="login-shell">
        <form method="post" class="panel login-card">
            <input
                type="hidden"
                name="_csrf"
                value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"
            >
            <span class="eyebrow">Área administrativa</span>
            <h1>Entrar</h1>
            <p>Use as credenciais do administrador local.</p>

            <label>
                E-mail
                <input
                    name="email"
                    type="email"
                    autocomplete="username"
                    value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                    required
                    autofocus
                >
            </label>

            <label>
                Senha
                <input name="password" type="password" autocomplete="current-password" required>
            </label>

            <?php if ($errors !== []): ?>
                <div class="message error" role="alert">
                    <?= htmlspecialchars((string) reset($errors), ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <button type="submit" class="primary-button">Entrar</button>
        </form>
    </main>
</body>
</html>

