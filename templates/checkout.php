<h2>تسویه حساب</h2>
<?php if (!$user): ?>
    <p>برای ادامه ابتدا <a href="login.php">وارد شوید</a>.</p>
<?php elseif (!$cartItems): ?>
    <p>سبد خرید شما خالی است.</p>
<?php else: ?>
    <?php $total = 0; foreach ($cartItems as $item) { $total += $item['price'] * $item['quantity']; } ?>
    <h3>خلاصه سفارش</h3>
    <ul>
        <?php foreach ($cartItems as $item): ?>
            <li><?= e($item['name']) ?> × <?= $item['quantity'] ?> - <?= number_format($item['price'] * $item['quantity']) ?> تومان</li>
        <?php endforeach; ?>
    </ul>
    <p><strong>جمع کل: <?= number_format($total) ?> تومان</strong></p>
    <form method="post">
        <input type="hidden" name="action" value="checkout">
        <label>روش ارسال
            <select name="shipping_method_id">
                <?php foreach ($shippingMethods as $method): ?>
                    <option value="<?= $method['id'] ?>"><?= e($method['name']) ?> (<?= number_format($method['cost']) ?> تومان)</option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>روش پرداخت
            <select name="payment_method_id">
                <?php foreach ($paymentMethods as $method): ?>
                    <option value="<?= $method['id'] ?>"><?= e($method['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>توضیحات سفارش
            <textarea name="notes"></textarea>
        </label>
        <button type="submit">ثبت سفارش</button>
    </form>
<?php endif; ?>
