<h2>پشتیبانی</h2>
<?php if (!$user): ?>
    <p>برای ثبت تیکت <a href="login.php">وارد شوید</a>.</p>
<?php else: ?>
    <form method="post" class="card">
        <h3>ثبت درخواست جدید</h3>
        <input type="hidden" name="action" value="submit_ticket">
        <label>عنوان
            <input type="text" name="subject" required>
        </label>
        <label>شرح مشکل
            <textarea name="message" required></textarea>
        </label>
        <button type="submit">ارسال</button>
    </form>
    <h3>تاریخچه درخواست‌ها</h3>
    <?php if ($tickets): ?>
        <table>
            <thead>
                <tr>
                    <th>عنوان</th>
                    <th>وضعیت</th>
                    <th>پاسخ</th>
                    <th>تاریخ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $ticket): ?>
                    <tr>
                        <td><?= e($ticket['subject']) ?></td>
                        <td><span class="badge"><?= e($ticket['status']) ?></span></td>
                        <td><?= $ticket['response'] ? e($ticket['response']) : 'در انتظار پاسخ' ?></td>
                        <td><?= e($ticket['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>تیکتی ثبت نکرده‌اید.</p>
    <?php endif; ?>
<?php endif; ?>
