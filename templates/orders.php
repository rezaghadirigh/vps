<h2>سفارش‌های من</h2>
<?php if (!$user): ?>
    <p>برای مشاهده سفارش‌ها <a href="login.php">وارد شوید</a>.</p>
<?php elseif (!$orders): ?>
    <p>سفارشی ثبت نکرده‌اید.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>شماره</th>
                <th>تاریخ</th>
                <th>وضعیت</th>
                <th>مبلغ</th>
                <th>جزئیات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?= $order['id'] ?></td>
                    <td><?= e($order['created_at']) ?></td>
                    <td><span class="badge"><?= e($order['status']) ?></span></td>
                    <td><?= number_format($order['total_amount']) ?> تومان</td>
                    <td>
                        <?php $full = get_order($order['id']); ?>
                        <ul>
                            <?php foreach ($full['items'] as $item): ?>
                                <li><?= e($item['name']) ?> × <?= $item['quantity'] ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
