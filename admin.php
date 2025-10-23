<?php
require_once __DIR__ . '/bootstrap.php';

if (!is_installed()) {
    redirect('install.php');
}

$user = current_user();
if (!$user || $user['role'] !== 'admin') {
    redirect('login.php');
}

$section = $_GET['section'] ?? 'dashboard';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($_POST['action'] ?? '') {
        case 'save_category':
            save_category($_POST, $user['id']);
            flash('success', 'دسته‌بندی ذخیره شد.');
            redirect('admin.php?section=categories');
            break;
        case 'delete_category':
            delete_category((int)$_POST['id'], $user['id']);
            flash('success', 'دسته‌بندی حذف شد.');
            redirect('admin.php?section=categories');
            break;
        case 'save_brand':
            save_brand($_POST, $user['id']);
            flash('success', 'برند ذخیره شد.');
            redirect('admin.php?section=brands');
            break;
        case 'delete_brand':
            delete_brand((int)$_POST['id'], $user['id']);
            flash('success', 'برند حذف شد.');
            redirect('admin.php?section=brands');
            break;
        case 'save_product':
            save_product($_POST, $user['id']);
            flash('success', 'محصول ذخیره شد.');
            redirect('admin.php?section=products');
            break;
        case 'delete_product':
            delete_product((int)$_POST['id'], $user['id']);
            flash('success', 'محصول حذف شد.');
            redirect('admin.php?section=products');
            break;
        case 'adjust_stock':
            adjust_stock((int)$_POST['product_id'], (int)$_POST['quantity'], $user['id'], $_POST['note'] ?? '');
            flash('success', 'موجودی بروزرسانی شد.');
            redirect('admin.php?section=inventory');
            break;
        case 'update_order_status':
            update_order_status((int)$_POST['order_id'], $_POST['status'], $user['id']);
            flash('success', 'وضعیت سفارش بروزرسانی شد.');
            redirect('admin.php?section=orders');
            break;
        case 'update_user':
            update_user_role((int)$_POST['user_id'], $_POST['role'], $_POST['status'], $user['id']);
            flash('success', 'کاربر بروزرسانی شد.');
            redirect('admin.php?section=users');
            break;
        case 'update_ticket':
            update_ticket((int)$_POST['ticket_id'], $_POST, $user['id']);
            flash('success', 'تیکت بروزرسانی شد.');
            redirect('admin.php?section=tickets');
            break;
        case 'save_coupon':
            save_coupon($_POST, $user['id']);
            flash('success', 'کد تخفیف ذخیره شد.');
            redirect('admin.php?section=coupons');
            break;
        case 'delete_coupon':
            delete_coupon((int)$_POST['id'], $user['id']);
            flash('success', 'کد تخفیف حذف شد.');
            redirect('admin.php?section=coupons');
            break;
        case 'save_banner':
            save_banner($_POST, $user['id']);
            flash('success', 'بنر ذخیره شد.');
            redirect('admin.php?section=banners');
            break;
        case 'delete_banner':
            delete_banner((int)$_POST['id'], $user['id']);
            flash('success', 'بنر حذف شد.');
            redirect('admin.php?section=banners');
            break;
        case 'save_shipping':
            save_shipping_method($_POST, $user['id']);
            flash('success', 'روش ارسال ذخیره شد.');
            redirect('admin.php?section=shipping');
            break;
        case 'delete_shipping':
            delete_shipping_method((int)$_POST['id'], $user['id']);
            flash('success', 'روش ارسال حذف شد.');
            redirect('admin.php?section=shipping');
            break;
        case 'save_payment':
            save_payment_method($_POST, $user['id']);
            flash('success', 'روش پرداخت ذخیره شد.');
            redirect('admin.php?section=payments');
            break;
        case 'delete_payment':
            delete_payment_method((int)$_POST['id'], $user['id']);
            flash('success', 'روش پرداخت حذف شد.');
            redirect('admin.php?section=payments');
            break;
        case 'save_page':
            save_page($_POST, $user['id']);
            flash('success', 'صفحه ذخیره شد.');
            redirect('admin.php?section=pages');
            break;
        case 'delete_page':
            delete_page((int)$_POST['id'], $user['id']);
            flash('success', 'صفحه حذف شد.');
            redirect('admin.php?section=pages');
            break;
        case 'update_settings':
            update_store_settings([
                'store_name' => $_POST['store_name'],
                'store_logo' => $_POST['store_logo'],
                'store_tagline' => $_POST['store_tagline'],
                'support_email' => $_POST['support_email'],
            ], $user['id']);
            flash('success', 'تنظیمات ذخیره شد.');
            redirect('admin.php?section=settings');
            break;
    }
}

$settings = store_settings();
$categories = get_categories();
$brands = get_brands();
$products = get_products();
$orders = get_orders();
$users = get_users();
$tickets = get_tickets();
$coupons = get_coupons();
$banners = get_banners();
$shippingMethods = get_shipping_methods();
$paymentMethods = get_payment_methods();
$pages = get_pages();
$salesReport = get_sales_report();
$inventoryReport = get_inventory_report();
$movements = db()->query('SELECT im.*, p.name as product_name, u.name as user_name FROM inventory_movements im JOIN products p ON im.product_id = p.id LEFT JOIN users u ON im.created_by = u.id ORDER BY im.created_at DESC LIMIT 50')->fetchAll();
$logs = db()->query('SELECT al.*, u.name as user_name FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 50')->fetchAll();

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>مدیریت فروشگاه</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header>
    <div class="container">
        <h1>مدیریت فروشگاه - <?= e($settings['store_name'] ?? '') ?></h1>
        <nav>
            <a href="admin.php" class="<?= $section === 'dashboard' ? 'active' : '' ?>">داشبورد</a>
            <a href="admin.php?section=products" class="<?= $section === 'products' ? 'active' : '' ?>">محصولات</a>
            <a href="admin.php?section=inventory" class="<?= $section === 'inventory' ? 'active' : '' ?>">موجودی</a>
            <a href="admin.php?section=categories" class="<?= $section === 'categories' ? 'active' : '' ?>">دسته‌بندی</a>
            <a href="admin.php?section=brands" class="<?= $section === 'brands' ? 'active' : '' ?>">برندها</a>
            <a href="admin.php?section=orders" class="<?= $section === 'orders' ? 'active' : '' ?>">سفارش‌ها</a>
            <a href="admin.php?section=users" class="<?= $section === 'users' ? 'active' : '' ?>">کاربران</a>
            <a href="admin.php?section=tickets" class="<?= $section === 'tickets' ? 'active' : '' ?>">پشتیبانی</a>
            <a href="admin.php?section=coupons" class="<?= $section === 'coupons' ? 'active' : '' ?>">تخفیف‌ها</a>
            <a href="admin.php?section=banners" class="<?= $section === 'banners' ? 'active' : '' ?>">بنرها</a>
            <a href="admin.php?section=shipping" class="<?= $section === 'shipping' ? 'active' : '' ?>">ارسال</a>
            <a href="admin.php?section=payments" class="<?= $section === 'payments' ? 'active' : '' ?>">پرداخت</a>
            <a href="admin.php?section=pages" class="<?= $section === 'pages' ? 'active' : '' ?>">صفحات</a>
            <a href="admin.php?section=settings" class="<?= $section === 'settings' ? 'active' : '' ?>">تنظیمات</a>
            <a href="index.php">مشاهده سایت</a>
            <a href="logout.php">خروج</a>
        </nav>
    </div>
</header>
<main>
    <div class="container">
        <?php if ($message = flash('success')): ?>
            <div class="alert alert-success"><?= e($message) ?></div>
        <?php endif; ?>
        <?php
        switch ($section) {
            case 'dashboard':
                ?>
                <section class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                    <div class="card"><h3>کل محصولات</h3><p><?= count($products) ?></p></div>
                    <div class="card"><h3>کل سفارش‌ها</h3><p><?= count($orders) ?></p></div>
                    <div class="card"><h3>کاربران</h3><p><?= count($users) ?></p></div>
                    <div class="card"><h3>تیکت‌های باز</h3><p><?= count(array_filter($tickets, fn($t) => $t['status'] === 'open')) ?></p></div>
                </section>
                <section>
                    <h2>گزارش فروش ماهانه</h2>
                    <table>
                        <thead><tr><th>ماه</th><th>فروش</th></tr></thead>
                        <tbody>
                        <?php foreach ($salesReport as $row): ?>
                            <tr><td><?= e($row['period']) ?></td><td><?= number_format($row['total'] ?? 0) ?> تومان</td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
                <section>
                    <h2>گزارش موجودی</h2>
                    <table>
                        <thead><tr><th>محصول</th><th>موجودی</th></tr></thead>
                        <tbody>
                        <?php foreach ($inventoryReport as $row): ?>
                            <tr><td><?= e($row['name']) ?></td><td><?= e($row['stock']) ?></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
                <section>
                    <h2>آخرین فعالیت‌ها</h2>
                    <table>
                        <thead><tr><th>کاربر</th><th>عملیات</th><th>جزئیات</th><th>تاریخ</th></tr></thead>
                        <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= e($log['user_name'] ?? 'سیستم') ?></td>
                                <td><?= e($log['action']) ?></td>
                                <td><?= e($log['details']) ?></td>
                                <td><?= e($log['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
                <?php
                break;
            case 'products':
                ?>
                <section class="grid" style="grid-template-columns: 2fr 1fr;">
                    <div>
                        <h2>محصولات</h2>
                        <table>
                            <thead><tr><th>نام</th><th>قیمت</th><th>موجودی</th><th>وضعیت</th><th></th></tr></thead>
                            <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= e($product['name']) ?></td>
                                    <td><?= number_format($product['price']) ?></td>
                                    <td><?= e($product['stock']) ?></td>
                                    <td><?= e($product['status']) ?></td>
                                    <td>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="action" value="delete_product">
                                            <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                            <button type="submit">حذف</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <h2>افزودن / ویرایش محصول</h2>
                        <form method="post">
                            <input type="hidden" name="action" value="save_product">
                            <label>شناسه (برای ویرایش)
                                <input type="number" name="id">
                            </label>
                            <label>نام
                                <input type="text" name="name" required>
                            </label>
                            <label>توضیحات
                                <textarea name="description"></textarea>
                            </label>
                            <label>قیمت
                                <input type="number" step="0.01" name="price" required>
                            </label>
                            <label>موجودی
                                <input type="number" name="stock" required>
                            </label>
                            <label>دسته بندی
                                <select name="category_id">
                                    <option value="">--</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>"><?= e($category['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>برند
                                <select name="brand_id">
                                    <option value="">--</option>
                                    <?php foreach ($brands as $brand): ?>
                                        <option value="<?= $brand['id'] ?>"><?= e($brand['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>آدرس تصویر
                                <input type="text" name="image_url">
                            </label>
                            <label>وضعیت
                                <select name="status">
                                    <option value="active">فعال</option>
                                    <option value="draft">پیش‌نویس</option>
                                </select>
                            </label>
                            <button type="submit">ذخیره</button>
                        </form>
                    </div>
                </section>
                <?php
                break;
            case 'inventory':
                ?>
                <h2>مدیریت موجودی</h2>
                <table>
                    <thead><tr><th>محصول</th><th>موجودی</th></tr></thead>
                    <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr><td><?= e($product['name']) ?></td><td><?= e($product['stock']) ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <form method="post" class="card">
                    <h3>اصلاح موجودی</h3>
                    <input type="hidden" name="action" value="adjust_stock">
                    <label>محصول
                        <select name="product_id">
                            <?php foreach ($products as $product): ?>
                                <option value="<?= $product['id'] ?>"><?= e($product['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>تعداد (مثبت یا منفی)
                        <input type="number" name="quantity" required>
                    </label>
                    <label>توضیحات
                        <input type="text" name="note">
                    </label>
                    <button type="submit">اعمال</button>
                </form>
                <h3>سوابق تغییر موجودی</h3>
                <table>
                    <thead><tr><th>محصول</th><th>تغییر</th><th>توضیحات</th><th>کاربر</th><th>تاریخ</th></tr></thead>
                    <tbody>
                    <?php foreach ($movements as $movement): ?>
                        <tr>
                            <td><?= e($movement['product_name']) ?></td>
                            <td><?= e($movement['change_qty']) ?></td>
                            <td><?= e($movement['note']) ?></td>
                            <td><?= e($movement['user_name'] ?? 'سیستم') ?></td>
                            <td><?= e($movement['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php
                break;
            case 'categories':
                ?>
                <h2>دسته‌بندی‌ها</h2>
                <table>
                    <thead><tr><th>نام</th><th>توضیحات</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?= e($category['name']) ?></td>
                            <td><?= e($category['description']) ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="action" value="delete_category">
                                    <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                    <button type="submit">حذف</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <form method="post" class="card">
                    <h3>افزودن دسته</h3>
                    <input type="hidden" name="action" value="save_category">
                    <label>شناسه (برای ویرایش)
                        <input type="number" name="id">
                    </label>
                    <label>نام
                        <input type="text" name="name" required>
                    </label>
                    <label>توضیحات
                        <textarea name="description"></textarea>
                    </label>
                    <button type="submit">ذخیره</button>
                </form>
                <?php
                break;
            case 'brands':
                ?>
                <h2>برندها</h2>
                <table>
                    <thead><tr><th>نام</th><th>توضیحات</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($brands as $brand): ?>
                        <tr>
                            <td><?= e($brand['name']) ?></td>
                            <td><?= e($brand['description']) ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="action" value="delete_brand">
                                    <input type="hidden" name="id" value="<?= $brand['id'] ?>">
                                    <button type="submit">حذف</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <form method="post" class="card">
                    <h3>افزودن برند</h3>
                    <input type="hidden" name="action" value="save_brand">
                    <label>شناسه (برای ویرایش)
                        <input type="number" name="id">
                    </label>
                    <label>نام
                        <input type="text" name="name" required>
                    </label>
                    <label>توضیحات
                        <textarea name="description"></textarea>
                    </label>
                    <button type="submit">ذخیره</button>
                </form>
                <?php
                break;
            case 'orders':
                ?>
                <h2>سفارش‌ها</h2>
                <table>
                    <thead><tr><th>#</th><th>مشتری</th><th>مبلغ</th><th>وضعیت</th><th>عملیات</th></tr></thead>
                    <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= $order['id'] ?></td>
                            <td><?= e($order['customer_name']) ?></td>
                            <td><?= number_format($order['total_amount']) ?> تومان</td>
                            <td><?= e($order['status']) ?></td>
                            <td>
                                <?php $orderDetails = get_order($order['id']); ?>
                                <details>
                                    <summary>آیتم‌ها</summary>
                                    <ul>
                                        <?php foreach ($orderDetails['items'] as $item): ?>
                                            <li><?= e($item['name']) ?> × <?= $item['quantity'] ?> (<?= number_format($item['price']) ?>)</li>
                                        <?php endforeach; ?>
                                    </ul>
                                </details>
                                <form method="post" class="actions">
                                    <input type="hidden" name="action" value="update_order_status">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <select name="status">
                                        <?php foreach (['pending','processing','shipped','completed','cancelled'] as $status): ?>
                                            <option value="<?= $status ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= $status ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit">ذخیره</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php
                break;
            case 'users':
                ?>
                <h2>کاربران</h2>
                <table>
                    <thead><tr><th>نام</th><th>ایمیل</th><th>نقش</th><th>وضعیت</th><th>ویرایش</th></tr></thead>
                    <tbody>
                    <?php foreach ($users as $account): ?>
                        <tr>
                            <td><?= e($account['name']) ?></td>
                            <td><?= e($account['email']) ?></td>
                            <td><?= e($account['role']) ?></td>
                            <td><?= e($account['status']) ?></td>
                            <td>
                                <form method="post" class="actions">
                                    <input type="hidden" name="action" value="update_user">
                                    <input type="hidden" name="user_id" value="<?= $account['id'] ?>">
                                    <select name="role">
                                        <option value="customer" <?= $account['role'] === 'customer' ? 'selected' : '' ?>>مشتری</option>
                                        <option value="admin" <?= $account['role'] === 'admin' ? 'selected' : '' ?>>مدیر</option>
                                    </select>
                                    <select name="status">
                                        <option value="active" <?= $account['status'] === 'active' ? 'selected' : '' ?>>فعال</option>
                                        <option value="suspended" <?= $account['status'] === 'suspended' ? 'selected' : '' ?>>معلق</option>
                                    </select>
                                    <button type="submit">ذخیره</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php
                break;
            case 'tickets':
                ?>
                <h2>تیکت‌ها</h2>
                <table>
                    <thead><tr><th>مشتری</th><th>عنوان</th><th>وضعیت</th><th>پاسخ</th></tr></thead>
                    <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td><?= e($ticket['customer_name']) ?></td>
                            <td><?= e($ticket['subject']) ?></td>
                            <td><?= e($ticket['status']) ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="action" value="update_ticket">
                                    <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                                    <select name="status">
                                        <option value="open" <?= $ticket['status'] === 'open' ? 'selected' : '' ?>>باز</option>
                                        <option value="answered" <?= $ticket['status'] === 'answered' ? 'selected' : '' ?>>پاسخ داده شده</option>
                                        <option value="closed" <?= $ticket['status'] === 'closed' ? 'selected' : '' ?>>بسته شده</option>
                                    </select>
                                    <textarea name="response" placeholder="پاسخ"><?= e($ticket['response'] ?? '') ?></textarea>
                                    <button type="submit">ذخیره</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php
                break;
            case 'coupons':
                ?>
                <h2>کدهای تخفیف</h2>
                <table>
                    <thead><tr><th>کد</th><th>توضیحات</th><th>درصد</th><th>وضعیت</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($coupons as $coupon): ?>
                        <tr>
                            <td><?= e($coupon['code']) ?></td>
                            <td><?= e($coupon['description']) ?></td>
                            <td><?= e($coupon['discount_percent']) ?>%</td>
                            <td><?= $coupon['active'] ? 'فعال' : 'غیرفعال' ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="action" value="delete_coupon">
                                    <input type="hidden" name="id" value="<?= $coupon['id'] ?>">
                                    <button type="submit">حذف</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <form method="post" class="card">
                    <h3>افزودن کد</h3>
                    <input type="hidden" name="action" value="save_coupon">
                    <label>شناسه (برای ویرایش)
                        <input type="number" name="id">
                    </label>
                    <label>کد
                        <input type="text" name="code" required>
                    </label>
                    <label>توضیحات
                        <textarea name="description"></textarea>
                    </label>
                    <label>درصد تخفیف
                        <input type="number" name="discount_percent" required>
                    </label>
                    <label>فعال؟
                        <select name="active">
                            <option value="1">بله</option>
                            <option value="0">خیر</option>
                        </select>
                    </label>
                    <button type="submit">ذخیره</button>
                </form>
                <?php
                break;
            case 'banners':
                ?>
                <h2>بنرها</h2>
                <table>
                    <thead><tr><th>عنوان</th><th>لینک</th><th>وضعیت</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($banners as $banner): ?>
                        <tr>
                            <td><?= e($banner['title']) ?></td>
                            <td><?= e($banner['link_url']) ?></td>
                            <td><?= $banner['active'] ? 'فعال' : 'غیرفعال' ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="action" value="delete_banner">
                                    <input type="hidden" name="id" value="<?= $banner['id'] ?>">
                                    <button type="submit">حذف</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <form method="post" class="card">
                    <h3>افزودن بنر</h3>
                    <input type="hidden" name="action" value="save_banner">
                    <label>شناسه (برای ویرایش)
                        <input type="number" name="id">
                    </label>
                    <label>عنوان
                        <input type="text" name="title" required>
                    </label>
                    <label>آدرس تصویر
                        <input type="text" name="image_url">
                    </label>
                    <label>لینک
                        <input type="text" name="link_url">
                    </label>
                    <label>فعال؟
                        <select name="active">
                            <option value="1">بله</option>
                            <option value="0">خیر</option>
                        </select>
                    </label>
                    <button type="submit">ذخیره</button>
                </form>
                <?php
                break;
            case 'shipping':
                ?>
                <h2>روش‌های ارسال</h2>
                <table>
                    <thead><tr><th>نام</th><th>هزینه</th><th>توضیح</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($shippingMethods as $method): ?>
                        <tr>
                            <td><?= e($method['name']) ?></td>
                            <td><?= number_format($method['cost']) ?></td>
                            <td><?= e($method['description']) ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="action" value="delete_shipping">
                                    <input type="hidden" name="id" value="<?= $method['id'] ?>">
                                    <button type="submit">حذف</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <form method="post" class="card">
                    <h3>افزودن روش ارسال</h3>
                    <input type="hidden" name="action" value="save_shipping">
                    <label>شناسه (برای ویرایش)
                        <input type="number" name="id">
                    </label>
                    <label>نام
                        <input type="text" name="name" required>
                    </label>
                    <label>هزینه
                        <input type="number" step="0.01" name="cost" required>
                    </label>
                    <label>توضیح
                        <textarea name="description"></textarea>
                    </label>
                    <button type="submit">ذخیره</button>
                </form>
                <?php
                break;
            case 'payments':
                ?>
                <h2>روش‌های پرداخت</h2>
                <table>
                    <thead><tr><th>نام</th><th>دستورالعمل</th><th>فعال</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($paymentMethods as $method): ?>
                        <tr>
                            <td><?= e($method['name']) ?></td>
                            <td><?= e($method['instructions']) ?></td>
                            <td><?= $method['active'] ? 'بله' : 'خیر' ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="action" value="delete_payment">
                                    <input type="hidden" name="id" value="<?= $method['id'] ?>">
                                    <button type="submit">حذف</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <form method="post" class="card">
                    <h3>افزودن روش پرداخت</h3>
                    <input type="hidden" name="action" value="save_payment">
                    <label>شناسه (برای ویرایش)
                        <input type="number" name="id">
                    </label>
                    <label>نام
                        <input type="text" name="name" required>
                    </label>
                    <label>توضیحات پرداخت
                        <textarea name="instructions"></textarea>
                    </label>
                    <label>فعال؟
                        <select name="active">
                            <option value="1">بله</option>
                            <option value="0">خیر</option>
                        </select>
                    </label>
                    <button type="submit">ذخیره</button>
                </form>
                <?php
                break;
            case 'pages':
                ?>
                <h2>صفحات محتوا</h2>
                <table>
                    <thead><tr><th>عنوان</th><th>شناسه یکتا</th><th>فعال</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($pages as $page): ?>
                        <tr>
                            <td><?= e($page['title']) ?></td>
                            <td><?= e($page['slug']) ?></td>
                            <td><?= $page['active'] ? 'بله' : 'خیر' ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="action" value="delete_page">
                                    <input type="hidden" name="id" value="<?= $page['id'] ?>">
                                    <button type="submit">حذف</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <form method="post" class="card">
                    <h3>افزودن صفحه</h3>
                    <input type="hidden" name="action" value="save_page">
                    <label>شناسه (برای ویرایش)
                        <input type="number" name="id">
                    </label>
                    <label>عنوان
                        <input type="text" name="title" required>
                    </label>
                    <label>شناسه یکتا (slug)
                        <input type="text" name="slug" required>
                    </label>
                    <label>محتوا
                        <textarea name="content"></textarea>
                    </label>
                    <label>فعال؟
                        <select name="active">
                            <option value="1">بله</option>
                            <option value="0">خیر</option>
                        </select>
                    </label>
                    <button type="submit">ذخیره</button>
                </form>
                <?php
                break;
            case 'settings':
                ?>
                <h2>تنظیمات فروشگاه</h2>
                <form method="post" class="card">
                    <input type="hidden" name="action" value="update_settings">
                    <label>نام فروشگاه
                        <input type="text" name="store_name" value="<?= e($settings['store_name'] ?? '') ?>">
                    </label>
                    <label>لوگو (آدرس)
                        <input type="text" name="store_logo" value="<?= e($settings['store_logo'] ?? '') ?>">
                    </label>
                    <label>شعار
                        <input type="text" name="store_tagline" value="<?= e($settings['store_tagline'] ?? '') ?>">
                    </label>
                    <label>ایمیل پشتیبانی
                        <input type="email" name="support_email" value="<?= e($settings['support_email'] ?? '') ?>">
                    </label>
                    <button type="submit">ذخیره</button>
                </form>
                <?php
                break;
            default:
                echo '<p>بخش مورد نظر یافت نشد.</p>';
        }
        ?>
    </div>
</main>
</body>
</html>
