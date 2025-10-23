<section>
    <h2>پیشنهادهای ویژه</h2>
    <?php if (!empty($banners)): ?>
        <div class="grid">
            <?php foreach ($banners as $banner): ?>
                <div class="card">
                    <h3><?= e($banner['title']) ?></h3>
                    <?php if ($banner['image_url']): ?>
                        <img src="<?= e($banner['image_url']) ?>" alt="<?= e($banner['title']) ?>">
                    <?php endif; ?>
                    <?php if ($banner['link_url']): ?>
                        <p><a href="<?= e($banner['link_url']) ?>" target="_blank">مشاهده</a></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>در حال حاضر بنری ثبت نشده است.</p>
    <?php endif; ?>
</section>
<section>
    <h2>محصولات جدید</h2>
    <div class="grid">
        <?php foreach (array_slice($featured, 0, 6) as $product): ?>
            <div class="card">
                <?php if ($product['image_url']): ?>
                    <img src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>">
                <?php endif; ?>
                <h3><?= e($product['name']) ?></h3>
                <p><?= e(mb_substr($product['description'], 0, 80)) ?>...</p>
                <p><strong><?= number_format($product['price']) ?> تومان</strong></p>
                <div class="actions">
                    <a href="index.php?view=product&id=<?= $product['id'] ?>">مشاهده</a>
                    <form method="post" action="index.php?view=cart">
                        <input type="hidden" name="action" value="add_to_cart">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit">افزودن به سبد</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<section>
    <h2>کدهای تخفیف</h2>
    <?php $coupons = get_coupons(); ?>
    <?php if ($coupons): ?>
        <ul>
            <?php foreach ($coupons as $coupon): ?>
                <?php if (!$coupon['active']) continue; ?>
                <li><strong><?= e($coupon['code']) ?></strong> - <?= e($coupon['description']) ?> (<?= e($coupon['discount_percent']) ?>%)</li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>کد تخفیفی فعال نیست.</p>
    <?php endif; ?>
</section>
