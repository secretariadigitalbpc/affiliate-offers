<?php

declare(strict_types=1);

use App\Helpers\Auth;
use App\Helpers\Csrf;

require_once dirname(__DIR__) . '/app/bootstrap.php';

Auth::requirePage();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

if (!Csrf::verify($_POST)) {
    http_response_code(403);
    exit;
}

Auth::logout();
header('Location: login.php');
exit;

