<?php
require_once __DIR__ . '/bootstrap.php';

if (!is_installed()) {
    redirect('install.php');
}

if (current_user()) {
    $user = current_user();
    redirect($user['role'] === 'admin' ? 'admin.php' : 'index.php');
}

$errors = [];
$data = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($data['email']) || empty($data['password'])) {
        $errors[] = 'ایمیل و رمز عبور الزامی است.';
    } else {
        if (authenticate($data['email'], $data['password'])) {
            $user = current_user();
            redirect($user['role'] === 'admin' ? 'admin.php' : 'index.php');
        }
        $errors[] = 'اطلاعات ورود نادرست است یا حساب فعال نیست.';
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ورود</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="install">
    <div class="container">
        <h1>ورود</h1>
        <?php if ($message = flash('success')): ?>
            <div class="alert alert-success"><?= e($message) ?></div>
        <?php endif; ?>
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
            <label>ایمیل
                <input type="email" name="email" value="<?= e(form_value($data, 'email')) ?>">
            </label>
            <label>رمز عبور
                <input type="password" name="password">
            </label>
            <button type="submit">ورود</button>
        </form>
        <p>حساب کاربری ندارید؟ <a href="register.php">ثبت نام کنید</a></p>
    </div>
</body>
</html>
