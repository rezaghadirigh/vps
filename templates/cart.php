<h2>سبد خرید</h2>
<?php if (!$user): ?>
    <p>برای مشاهده سبد خرید <a href="login.php">وارد شوید</a>.</p>
<?php else: ?>
    <?php if ($cartItems): ?>
        <form method="post">
            <input type="hidden" name="action" value="update_cart">
            <table>
                <thead>
                    <tr>
                        <th>محصول</th>
                        <th>قیمت</th>
                        <th>تعداد</th>
                        <th>جمع</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $total = 0; ?>
                    <?php foreach ($cartItems as $item): ?>
                        <?php $line = $item['price'] * $item['quantity']; $total += $line; ?>
                        <tr>
                            <td><?= e($item['name']) ?></td>
                            <td><?= number_format($item['price']) ?></td>
                            <td>
                                <input type="number" name="items[<?= $item['id'] ?>]" value="<?= $item['quantity'] ?>" min="1">
                            </td>
                            <td><?= number_format($line) ?></td>
                            <td>
                                <button type="submit" name="remove_item" value="<?= $item['id'] ?>">حذف</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p><strong>جمع کل: <?= number_format($total) ?> تومان</strong></p>
            <div class="actions">
                <button type="submit">بروزرسانی سبد</button>
                <a href="index.php?view=checkout" class="button">تسویه حساب</a>
            </div>
        </form>
    <?php else: ?>
        <p>سبد خرید شما خالی است.</p>
    <?php endif; ?>
<?php endif; ?>
