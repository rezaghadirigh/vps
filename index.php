<?php
require_once __DIR__ . '/bootstrap.php';

if (!is_installed()) {
    redirect('install.php');
}

$user = current_user();
$view = $_GET['view'] ?? 'home';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$user) {
        flash('error', 'برای استفاده از این بخش باید وارد شوید.');
        redirect('login.php');
    }

    switch ($_POST['action'] ?? '') {
        case 'add_to_cart':
            add_to_cart($user['id'], (int)$_POST['product_id'], (int)$_POST['quantity']);
            flash('success', 'محصول به سبد خرید اضافه شد.');
            redirect('index.php?view=cart');
            break;
        case 'update_cart':
            if (!empty($_POST['remove_item'])) {
                remove_cart_item((int)$_POST['remove_item'], $user['id']);
                flash('success', 'محصول از سبد حذف شد.');
            } else {
                if (!empty($_POST['items'])) {
                    foreach ($_POST['items'] as $itemId => $quantity) {
                        update_cart_item((int)$itemId, (int)$quantity, $user['id']);
                    }
                }
                flash('success', 'سبد خرید بروزرسانی شد.');
            }
            redirect('index.php?view=cart');
            break;
        case 'checkout':
            $cart = get_cart_items($user['id']);
            if (!$cart) {
                flash('error', 'سبد خرید خالی است.');
                redirect('index.php?view=cart');
            }
            $orderId = create_order($user['id'], $cart, $_POST);
            flash('success', 'سفارش شما با شماره ' . $orderId . ' ثبت شد.');
            redirect('index.php?view=orders');
            break;
        case 'toggle_wishlist':
            toggle_wishlist($user['id'], (int)$_POST['product_id']);
            flash('success', 'لیست علاقه‌مندی بروزرسانی شد.');
            redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
            break;
        case 'submit_ticket':
            submit_ticket($user['id'], $_POST);
            flash('success', 'تیکت شما ثبت شد.');
            redirect('index.php?view=tickets');
            break;
        case 'save_profile':
            update_profile($user['id'], $_POST);
            $_SESSION['user']['name'] = $_POST['name'];
            $_SESSION['user']['phone'] = $_POST['phone'];
            flash('success', 'پروفایل بروزرسانی شد.');
            redirect('index.php?view=profile');
            break;
        case 'change_password':
            if (empty($_POST['password']) || strlen($_POST['password']) < 6) {
                flash('error', 'رمز عبور باید حداقل ۶ کاراکتر باشد.');
            } else {
                change_password($user['id'], $_POST['password']);
                flash('success', 'رمز عبور جدید ذخیره شد.');
            }
            redirect('index.php?view=profile');
            break;
        case 'add_review':
            save_review($user['id'], (int)$_POST['product_id'], $_POST);
            flash('success', 'نظر شما ثبت شد.');
            redirect('index.php?view=product&id=' . (int)$_POST['product_id']);
            break;
    }
}

$settings = store_settings();
$categories = get_categories();
$brands = get_brands();

function render_header($settings, $user)
{
    ?>
    <header>
        <div class="container">
            <h1><?= e($settings['store_name'] ?? 'CosmoStore') ?></h1>
            <p><?= e($settings['store_tagline'] ?? 'فروشگاه تخصصی لوازم آرایشی') ?></p>
            <nav>
                <a href="index.php" class="<?= !isset($_GET['view']) || $_GET['view'] === 'home' ? 'active' : '' ?>">خانه</a>
                <a href="index.php?view=catalog" class="<?= ($_GET['view'] ?? '') === 'catalog' ? 'active' : '' ?>">محصولات</a>
                <a href="index.php?view=cart" class="<?= ($_GET['view'] ?? '') === 'cart' ? 'active' : '' ?>">سبد خرید</a>
                <?php if ($user): ?>
                    <a href="index.php?view=orders" class="<?= ($_GET['view'] ?? '') === 'orders' ? 'active' : '' ?>">سفارش‌ها</a>
                    <a href="index.php?view=wishlist" class="<?= ($_GET['view'] ?? '') === 'wishlist' ? 'active' : '' ?>">علاقه‌مندی</a>
                    <a href="index.php?view=tickets" class="<?= ($_GET['view'] ?? '') === 'tickets' ? 'active' : '' ?>">پشتیبانی</a>
                    <a href="index.php?view=profile" class="<?= ($_GET['view'] ?? '') === 'profile' ? 'active' : '' ?>">پروفایل</a>
                    <a href="logout.php">خروج</a>
                <?php else: ?>
                    <a href="login.php">ورود</a>
                    <a href="register.php">ثبت نام</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <?php
}

function render_footer()
{
    ?>
    <footer>
        <div class="container">
            <p>© <?= date('Y') ?>. تمامی حقوق محفوظ است.</p>
        </div>
    </footer>
    <?php
}

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title><?= e($settings['store_name'] ?? 'CosmoStore') ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php render_header($settings, $user); ?>
<main>
    <div class="container">
        <?php if ($message = flash('success')): ?>
            <div class="alert alert-success"><?= e($message) ?></div>
        <?php endif; ?>
        <?php if ($message = flash('error')): ?>
            <div class="alert alert-danger"><?= e($message) ?></div>
        <?php endif; ?>
        <?php
        switch ($view) {
            case 'catalog':
                $filters = [
                    'category_id' => $_GET['category'] ?? null,
                    'brand_id' => $_GET['brand'] ?? null,
                    'search' => $_GET['search'] ?? null,
                    'status' => 'active',
                ];
                $products = search_products_with_filters($filters);
                include __DIR__ . '/templates/catalog.php';
                break;
            case 'product':
                $product = get_product((int)($_GET['id'] ?? 0));
                $reviews = $product ? get_product_reviews($product['id']) : [];
                include __DIR__ . '/templates/product.php';
                break;
            case 'cart':
                $cartItems = $user ? get_cart_items($user['id']) : [];
                include __DIR__ . '/templates/cart.php';
                break;
            case 'checkout':
                $cartItems = $user ? get_cart_items($user['id']) : [];
                $shippingMethods = get_shipping_methods();
                $paymentMethods = get_payment_methods(true);
                include __DIR__ . '/templates/checkout.php';
                break;
            case 'orders':
                $orders = $user ? get_orders(['user_id' => $user['id']]) : [];
                include __DIR__ . '/templates/orders.php';
                break;
            case 'wishlist':
                $wishlist = $user ? get_wishlist($user['id']) : [];
                include __DIR__ . '/templates/wishlist.php';
                break;
            case 'tickets':
                $tickets = $user ? get_tickets(['user_id' => $user['id']]) : [];
                include __DIR__ . '/templates/tickets.php';
                break;
            case 'profile':
                include __DIR__ . '/templates/profile.php';
                break;
            default:
                $banners = get_banners(true);
                $featured = get_products(['status' => 'active']);
                include __DIR__ . '/templates/home.php';
                break;
        }
        ?>
    </div>
</main>
<?php render_footer(); ?>
</body>
</html>
