<?php
session_start();

$config = require __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';

if (is_installed()) {
    redirect('index.php');
}

$errors = [];
$data = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($data['store_name'])) {
        $errors[] = 'نام فروشگاه الزامی است.';
    }
    if (empty($data['admin_email']) || !filter_var($data['admin_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'ایمیل مدیر معتبر نیست.';
    }
    if (empty($data['admin_password']) || strlen($data['admin_password']) < 6) {
        $errors[] = 'رمز عبور مدیر باید حداقل ۶ کاراکتر باشد.';
    }

    if (!$errors) {
        if (!is_dir(__DIR__ . '/data')) {
            mkdir(__DIR__ . '/data', 0777, true);
        }

        $pdo = new PDO('sqlite:' . $config['db_path']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $schema = file_get_contents(__DIR__ . '/templates/schema.sql');
        $pdo->exec($schema);

        $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, status, created_at) VALUES (:name, :email, :password, :role, :status, :created_at)');
        $stmt->execute([
            'name' => $data['admin_name'] ?: 'مدیر',
            'email' => strtolower($data['admin_email']),
            'password' => password_hash($data['admin_password'], PASSWORD_BCRYPT),
            'role' => 'admin',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $stmt = $pdo->prepare('INSERT INTO store_settings (key, value) VALUES (:key, :value)');
        foreach ([
            'store_name' => $data['store_name'],
            'store_logo' => $data['store_logo'] ?? '',
            'store_tagline' => $data['store_tagline'] ?? '',
            'support_email' => $data['support_email'] ?? $data['admin_email'],
        ] as $key => $value) {
            $stmt->execute(['key' => $key, 'value' => $value]);
        }

        file_put_contents($config['install_lock'], (string)time());

        $_SESSION['flash']['success'] = 'نصب با موفقیت انجام شد. اکنون می‌توانید وارد شوید.';
        redirect('login.php');
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>نصب فروشگاه</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="install">
    <div class="container">
        <h1>نصب فروشگاه لوازم آرایشی</h1>
        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="post">
            <label>نام فروشگاه
                <input type="text" name="store_name" value="<?= e(form_value($data, 'store_name')) ?>">
            </label>
            <label>شعار فروشگاه
                <input type="text" name="store_tagline" value="<?= e(form_value($data, 'store_tagline')) ?>">
            </label>
            <label>آدرس لوگو (اختیاری)
                <input type="text" name="store_logo" value="<?= e(form_value($data, 'store_logo')) ?>">
            </label>
            <label>ایمیل پشتیبانی
                <input type="email" name="support_email" value="<?= e(form_value($data, 'support_email')) ?>">
            </label>
            <label>نام مدیر
                <input type="text" name="admin_name" value="<?= e(form_value($data, 'admin_name')) ?>">
            </label>
            <label>ایمیل مدیر
                <input type="email" name="admin_email" value="<?= e(form_value($data, 'admin_email')) ?>">
            </label>
            <label>رمز عبور مدیر
                <input type="password" name="admin_password">
            </label>
            <button type="submit">نصب</button>
        </form>
    </div>
</body>
</html>
