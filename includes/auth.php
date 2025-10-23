<?php

function find_user_by_email(string $email)
{
    $stmt = db()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => strtolower($email)]);
    return $stmt->fetch();
}

function create_user(array $data)
{
    $stmt = db()->prepare('INSERT INTO users (name, email, password, role, phone, status) VALUES (:name, :email, :password, :role, :phone, :status)');
    $stmt->execute([
        'name' => $data['name'],
        'email' => strtolower($data['email']),
        'password' => password_hash($data['password'], PASSWORD_BCRYPT),
        'role' => $data['role'] ?? 'customer',
        'phone' => $data['phone'] ?? null,
        'status' => $data['status'] ?? 'active',
    ]);

    log_activity((int)db()->lastInsertId(), 'user_created', json_encode($data));
}

function authenticate(string $email, string $password)
{
    $user = find_user_by_email($email);
    if ($user && password_verify($password, $user['password']) && $user['status'] === 'active') {
        unset($user['password']);
        $_SESSION['user'] = $user;
        log_activity($user['id'], 'login', 'User logged in');
        return true;
    }

    return false;
}

function logout(): void
{
    if (isset($_SESSION['user'])) {
        log_activity($_SESSION['user']['id'], 'logout', 'User logged out');
    }
    $_SESSION = [];
    session_destroy();
}
