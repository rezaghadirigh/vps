<h2>علاقه‌مندی‌ها</h2>
<?php if (!$user): ?>
    <p>برای مشاهده علاقه‌مندی‌ها <a href="login.php">وارد شوید</a>.</p>
<?php elseif (!$wishlist): ?>
    <p>محصولی در علاقه‌مندی‌ها ندارید.</p>
<?php else: ?>
    <div class="grid">
        <?php foreach ($wishlist as $item): ?>
            <div class="card">
                <?php if ($item['image_url']): ?>
                    <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['name']) ?>">
                <?php endif; ?>
                <h3><?= e($item['name']) ?></h3>
                <p><strong><?= number_format($item['price']) ?> تومان</strong></p>
                <div class="actions">
                    <a href="index.php?view=product&id=<?= $item['product_id'] ?>">مشاهده</a>
                    <form method="post">
                        <input type="hidden" name="action" value="add_to_cart">
                        <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit">افزودن به سبد</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
