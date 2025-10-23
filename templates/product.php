<?php if (!$product): ?>
    <p>محصول یافت نشد.</p>
<?php else: ?>
    <div class="grid" style="grid-template-columns: 1fr 2fr;">
        <div>
            <?php if ($product['image_url']): ?>
                <img src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>">
            <?php endif; ?>
        </div>
        <div>
            <h2><?= e($product['name']) ?></h2>
            <p><?= nl2br(e($product['description'])) ?></p>
            <p><strong><?= number_format($product['price']) ?> تومان</strong></p>
            <p>موجودی: <span class="badge"><?= e($product['stock']) ?></span></p>
            <div class="actions">
                <form method="post">
                    <input type="hidden" name="action" value="add_to_cart">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <label>تعداد
                        <input type="number" name="quantity" value="1" min="1" max="<?= max(1, $product['stock']) ?>">
                    </label>
                    <button type="submit">افزودن به سبد</button>
                </form>
                <?php if ($user): ?>
                    <form method="post">
                        <input type="hidden" name="action" value="toggle_wishlist">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <button type="submit">افزودن به علاقه‌مندی</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <section>
        <h3>نظرات کاربران</h3>
        <?php if ($user): ?>
            <form method="post">
                <input type="hidden" name="action" value="add_review">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <label>امتیاز (۱ تا ۵)
                    <input type="number" name="rating" min="1" max="5" value="5">
                </label>
                <label>نظر شما
                    <textarea name="comment"></textarea>
                </label>
                <button type="submit">ارسال نظر</button>
            </form>
        <?php else: ?>
            <p>برای ثبت نظر ابتدا <a href="login.php">وارد شوید</a>.</p>
        <?php endif; ?>
        <?php if ($reviews): ?>
            <ul>
                <?php foreach ($reviews as $review): ?>
                    <li><strong><?= e($review['name']) ?></strong> (امتیاز <?= e($review['rating']) ?>): <?= e($review['comment']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>نظری ثبت نشده است.</p>
        <?php endif; ?>
    </section>
<?php endif; ?>
