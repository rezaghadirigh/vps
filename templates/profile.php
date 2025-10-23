<h2>پروفایل</h2>
<?php if (!$user): ?>
    <p>برای مدیریت پروفایل <a href="login.php">وارد شوید</a>.</p>
<?php else: ?>
    <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
        <form method="post" class="card">
            <h3>اطلاعات کاربری</h3>
            <input type="hidden" name="action" value="save_profile">
            <label>نام و نام خانوادگی
                <input type="text" name="name" value="<?= e($user['name']) ?>">
            </label>
            <label>ایمیل
                <input type="email" value="<?= e($user['email']) ?>" disabled>
            </label>
            <label>شماره تماس
                <input type="text" name="phone" value="<?= e($user['phone'] ?? '') ?>">
            </label>
            <button type="submit">ذخیره</button>
        </form>
        <form method="post" class="card">
            <h3>تغییر رمز عبور</h3>
            <input type="hidden" name="action" value="change_password">
            <label>رمز عبور جدید
                <input type="password" name="password">
            </label>
            <button type="submit">به‌روزرسانی</button>
        </form>
    </div>
<?php endif; ?>
