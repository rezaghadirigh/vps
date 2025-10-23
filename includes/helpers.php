<?php

function is_installed(): bool
{
    $config = require __DIR__ . '/config.php';
    return file_exists($config['db_path']) && file_exists($config['install_lock']);
}

function redirect(string $location): void
{
    header('Location: ' . $location);
    exit;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function form_value(array $data, string $key, $default = '')
{
    return $data[$key] ?? $default;
}

function require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method Not Allowed');
    }
}

function flash(string $key, ?string $message = null)
{
    if ($message === null) {
        $msg = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $msg;
    }

    $_SESSION['flash'][$key] = $message;
}

function paginate(array $items, int $page = 1, int $perPage = 10): array
{
    $total = count($items);
    $offset = ($page - 1) * $perPage;
    return [
        'items' => array_slice($items, $offset, $perPage),
        'total' => $total,
        'page' => $page,
        'pages' => max(1, (int)ceil($total / $perPage)),
    ];
}

function current_user()
{
    return $_SESSION['user'] ?? null;
}

function require_role(string $role): void
{
    $user = current_user();
    if (!$user || $user['role'] !== $role) {
        redirect('login.php');
    }
}
