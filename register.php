<?php
require_once __DIR__ . '/bootstrap.php';

if (!is_installed()) {
    redirect('install.php');
}

if (current_user()) {
    redirect('index.php');
}

$errors = [];
$data = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($data['name'])) {
        $errors[] = 'نام الزامی است.';
    }
    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'ایمیل معتبر نیست.';
    }
    if (find_user_by_email($data['email'])) {
        $errors[] = 'این ایمیل قبلاً ثبت شده است.';
    }
    if (empty($data['password']) || strlen($data['password']) < 6) {
        $errors[] = 'رمز عبور باید حداقل ۶ کاراکتر باشد.';
    }

    if (!$errors) {
        create_user([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'customer',
            'phone' => $data['phone'] ?? null,
        ]);
        flash('success', 'ثبت نام با موفقیت انجام شد. اکنون وارد شوید.');
        redirect('login.php');
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ثبت نام</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="install">
    <div class="container">
        <h1>ثبت نام مشتری</h1>
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
            <label>نام و نام خانوادگی
                <input type="text" name="name" value="<?= e(form_value($data, 'name')) ?>">
            </label>
            <label>ایمیل
                <input type="email" name="email" value="<?= e(form_value($data, 'email')) ?>">
            </label>
            <label>شماره تماس
                <input type="text" name="phone" value="<?= e(form_value($data, 'phone')) ?>">
            </label>
            <label>رمز عبور
                <input type="password" name="password">
            </label>
            <button type="submit">ثبت نام</button>
        </form>
        <p>حساب دارید؟ <a href="login.php">وارد شوید</a></p>
    </div>
</body>
</html>
