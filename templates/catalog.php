<h2>لیست محصولات</h2>
<form method="get" class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); align-items: end;">
    <input type="hidden" name="view" value="catalog">
    <label>دسته بندی
        <select name="category">
            <option value="">همه</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id'] ?>" <?= (($_GET['category'] ?? '') == $category['id']) ? 'selected' : '' ?>><?= e($category['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>برند
        <select name="brand">
            <option value="">همه</option>
            <?php foreach ($brands as $brand): ?>
                <option value="<?= $brand['id'] ?>" <?= (($_GET['brand'] ?? '') == $brand['id']) ? 'selected' : '' ?>><?= e($brand['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>جستجو
        <input type="text" name="search" value="<?= e($_GET['search'] ?? '') ?>">
    </label>
    <div>
        <button type="submit">اعمال فیلتر</button>
    </div>
</form>
<div class="grid">
    <?php foreach ($products as $product): ?>
        <div class="card">
            <?php if ($product['image_url']): ?>
                <img src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>">
            <?php endif; ?>
            <h3><?= e($product['name']) ?></h3>
            <p><?= e(mb_substr($product['description'], 0, 60)) ?>...</p>
            <p><strong><?= number_format($product['price']) ?> تومان</strong></p>
            <div class="actions">
                <a href="index.php?view=product&id=<?= $product['id'] ?>">مشاهده</a>
                <form method="post">
                    <input type="hidden" name="action" value="add_to_cart">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit">افزودن به سبد</button>
                </form>
                <?php if ($user): ?>
                    <form method="post">
                        <input type="hidden" name="action" value="toggle_wishlist">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <button type="submit">علاقه‌مندی</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php if (!$products): ?>
    <p>محصولی یافت نشد.</p>
<?php endif; ?>
