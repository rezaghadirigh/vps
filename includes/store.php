<?php

function log_activity(?int $userId, string $action, string $details = ''): void
{
    $stmt = db()->prepare('INSERT INTO activity_logs (user_id, action, details, created_at) VALUES (:user_id, :action, :details, :created_at)');
    $stmt->execute([
        'user_id' => $userId,
        'action' => $action,
        'details' => $details,
        'created_at' => date('Y-m-d H:i:s'),
    ]);
}

function store_settings(): array
{
    $rows = db()->query('SELECT key, value FROM store_settings')->fetchAll();
    $settings = [];
    foreach ($rows as $row) {
        $settings[$row['key']] = $row['value'];
    }
    return $settings;
}

function store_setting(string $key, $default = null)
{
    $stmt = db()->prepare('SELECT value FROM store_settings WHERE key = :key');
    $stmt->execute(['key' => $key]);
    $value = $stmt->fetchColumn();
    return $value !== false ? $value : $default;
}

function update_store_settings(array $data, int $userId): void
{
    foreach ($data as $key => $value) {
        $stmt = db()->prepare('INSERT INTO store_settings (key, value) VALUES (:key, :value)
            ON CONFLICT(key) DO UPDATE SET value = excluded.value');
        $stmt->execute(['key' => $key, 'value' => $value]);
    }

    log_activity($userId, 'store_settings_updated', json_encode($data));
}

function get_categories(): array
{
    return db()->query('SELECT * FROM categories ORDER BY name')->fetchAll();
}

function save_category(array $data, int $userId): void
{
    if (!empty($data['id'])) {
        $stmt = db()->prepare('UPDATE categories SET name = :name, description = :description WHERE id = :id');
        $stmt->execute([
            'id' => $data['id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
        log_activity($userId, 'category_updated', json_encode($data));
    } else {
        $stmt = db()->prepare('INSERT INTO categories (name, description) VALUES (:name, :description)');
        $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
        log_activity($userId, 'category_created', json_encode($data));
    }
}

function delete_category(int $id, int $userId): void
{
    $stmt = db()->prepare('DELETE FROM categories WHERE id = :id');
    $stmt->execute(['id' => $id]);
    log_activity($userId, 'category_deleted', (string)$id);
}

function get_brands(): array
{
    return db()->query('SELECT * FROM brands ORDER BY name')->fetchAll();
}

function save_brand(array $data, int $userId): void
{
    if (!empty($data['id'])) {
        $stmt = db()->prepare('UPDATE brands SET name = :name, description = :description WHERE id = :id');
        $stmt->execute([
            'id' => $data['id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
        log_activity($userId, 'brand_updated', json_encode($data));
    } else {
        $stmt = db()->prepare('INSERT INTO brands (name, description) VALUES (:name, :description)');
        $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
        log_activity($userId, 'brand_created', json_encode($data));
    }
}

function delete_brand(int $id, int $userId): void
{
    $stmt = db()->prepare('DELETE FROM brands WHERE id = :id');
    $stmt->execute(['id' => $id]);
    log_activity($userId, 'brand_deleted', (string)$id);
}

function get_products(array $filters = []): array
{
    $sql = 'SELECT p.*, c.name as category_name, b.name as brand_name FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN brands b ON p.brand_id = b.id WHERE 1=1';
    $params = [];
    if (!empty($filters['category_id'])) {
        $sql .= ' AND p.category_id = :category_id';
        $params['category_id'] = $filters['category_id'];
    }
    if (!empty($filters['brand_id'])) {
        $sql .= ' AND p.brand_id = :brand_id';
        $params['brand_id'] = $filters['brand_id'];
    }
    if (!empty($filters['search'])) {
        $sql .= ' AND p.name LIKE :search';
        $params['search'] = '%' . $filters['search'] . '%';
    }
    if (!empty($filters['status'])) {
        $sql .= ' AND p.status = :status';
        $params['status'] = $filters['status'];
    }
    $sql .= ' ORDER BY p.created_at DESC';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_product(int $id)
{
    $stmt = db()->prepare('SELECT * FROM products WHERE id = :id');
    $stmt->execute(['id' => $id]);
    return $stmt->fetch();
}

function save_product(array $data, int $userId): void
{
    if (!empty($data['id'])) {
        $stmt = db()->prepare('UPDATE products SET name = :name, description = :description, price = :price, stock = :stock,
            category_id = :category_id, brand_id = :brand_id, image_url = :image_url, status = :status WHERE id = :id');
        $stmt->execute([
            'id' => $data['id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'stock' => $data['stock'],
            'category_id' => $data['category_id'] ?: null,
            'brand_id' => $data['brand_id'] ?: null,
            'image_url' => $data['image_url'] ?? null,
            'status' => $data['status'] ?? 'active',
        ]);
        log_activity($userId, 'product_updated', json_encode($data));
    } else {
        $stmt = db()->prepare('INSERT INTO products (name, description, price, stock, category_id, brand_id, image_url, status, created_at)
            VALUES (:name, :description, :price, :stock, :category_id, :brand_id, :image_url, :status, :created_at)');
        $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'stock' => $data['stock'],
            'category_id' => $data['category_id'] ?: null,
            'brand_id' => $data['brand_id'] ?: null,
            'image_url' => $data['image_url'] ?? null,
            'status' => $data['status'] ?? 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        log_activity($userId, 'product_created', json_encode($data));
    }
}

function adjust_stock(int $productId, int $quantity, int $userId, string $note = ''): void
{
    $stmt = db()->prepare('UPDATE products SET stock = stock + :quantity WHERE id = :id');
    $stmt->execute(['quantity' => $quantity, 'id' => $productId]);

    $stmt = db()->prepare('INSERT INTO inventory_movements (product_id, change_qty, note, created_by, created_at)
        VALUES (:product_id, :change_qty, :note, :created_by, :created_at)');
    $stmt->execute([
        'product_id' => $productId,
        'change_qty' => $quantity,
        'note' => $note,
        'created_by' => $userId,
        'created_at' => date('Y-m-d H:i:s'),
    ]);
    log_activity($userId, 'stock_adjusted', json_encode(['product_id' => $productId, 'qty' => $quantity, 'note' => $note]));
}

function delete_product(int $id, int $userId): void
{
    $stmt = db()->prepare('DELETE FROM products WHERE id = :id');
    $stmt->execute(['id' => $id]);
    log_activity($userId, 'product_deleted', (string)$id);
}

function get_cart_items(int $userId): array
{
    $stmt = db()->prepare('SELECT ci.*, p.name, p.price, p.image_url FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.user_id = :user_id');
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll();
}

function add_to_cart(int $userId, int $productId, int $quantity = 1): void
{
    $stmt = db()->prepare('SELECT id FROM cart_items WHERE user_id = :user_id AND product_id = :product_id');
    $stmt->execute(['user_id' => $userId, 'product_id' => $productId]);
    $item = $stmt->fetch();
    if ($item) {
        $stmt = db()->prepare('UPDATE cart_items SET quantity = quantity + :quantity WHERE id = :id');
        $stmt->execute(['quantity' => $quantity, 'id' => $item['id']]);
    } else {
        $stmt = db()->prepare('INSERT INTO cart_items (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)');
        $stmt->execute(['user_id' => $userId, 'product_id' => $productId, 'quantity' => $quantity]);
    }
    log_activity($userId, 'cart_updated', json_encode(['product_id' => $productId, 'quantity' => $quantity]));
}

function update_cart_item(int $itemId, int $quantity, int $userId): void
{
    if ($quantity <= 0) {
        remove_cart_item($itemId, $userId);
        return;
    }
    $stmt = db()->prepare('UPDATE cart_items SET quantity = :quantity WHERE id = :id AND user_id = :user_id');
    $stmt->execute(['quantity' => $quantity, 'id' => $itemId, 'user_id' => $userId]);
    log_activity($userId, 'cart_item_updated', json_encode(['item_id' => $itemId, 'quantity' => $quantity]));
}

function remove_cart_item(int $itemId, int $userId): void
{
    $stmt = db()->prepare('DELETE FROM cart_items WHERE id = :id AND user_id = :user_id');
    $stmt->execute(['id' => $itemId, 'user_id' => $userId]);
    log_activity($userId, 'cart_item_removed', (string)$itemId);
}

function clear_cart(int $userId): void
{
    $stmt = db()->prepare('DELETE FROM cart_items WHERE user_id = :user_id');
    $stmt->execute(['user_id' => $userId]);
}

function create_order(int $userId, array $cartItems, array $data): int
{
    $total = 0;
    foreach ($cartItems as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    $stmt = db()->prepare('INSERT INTO orders (user_id, total_amount, status, shipping_method_id, payment_method_id, notes, created_at)
        VALUES (:user_id, :total_amount, :status, :shipping_method_id, :payment_method_id, :notes, :created_at)');
    $stmt->execute([
        'user_id' => $userId,
        'total_amount' => $total,
        'status' => 'pending',
        'shipping_method_id' => $data['shipping_method_id'] ?? null,
        'payment_method_id' => $data['payment_method_id'] ?? null,
        'notes' => $data['notes'] ?? null,
        'created_at' => date('Y-m-d H:i:s'),
    ]);

    $orderId = (int)db()->lastInsertId();

    $stmt = db()->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)');
    foreach ($cartItems as $item) {
        $stmt->execute([
            'order_id' => $orderId,
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'price' => $item['price'],
        ]);

        adjust_stock($item['product_id'], -$item['quantity'], $userId, 'Order #' . $orderId);
    }

    clear_cart($userId);

    log_activity($userId, 'order_created', (string)$orderId);
    return $orderId;
}

function get_orders(array $filters = []): array
{
    $sql = 'SELECT o.*, u.name as customer_name FROM orders o JOIN users u ON o.user_id = u.id WHERE 1=1';
    $params = [];
    if (!empty($filters['user_id'])) {
        $sql .= ' AND o.user_id = :user_id';
        $params['user_id'] = $filters['user_id'];
    }
    if (!empty($filters['status'])) {
        $sql .= ' AND o.status = :status';
        $params['status'] = $filters['status'];
    }
    $sql .= ' ORDER BY o.created_at DESC';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_order(int $id)
{
    $stmt = db()->prepare('SELECT o.*, u.name as customer_name, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = :id');
    $stmt->execute(['id' => $id]);
    $order = $stmt->fetch();
    if ($order) {
        $stmt = db()->prepare('SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = :order_id');
        $stmt->execute(['order_id' => $id]);
        $order['items'] = $stmt->fetchAll();
    }
    return $order;
}

function update_order_status(int $orderId, string $status, int $userId): void
{
    $stmt = db()->prepare('UPDATE orders SET status = :status WHERE id = :id');
    $stmt->execute(['status' => $status, 'id' => $orderId]);
    log_activity($userId, 'order_status_updated', json_encode(['order_id' => $orderId, 'status' => $status]));
}

function get_wishlist(int $userId): array
{
    $stmt = db()->prepare('SELECT w.*, p.name, p.price, p.image_url FROM wishlists w JOIN products p ON w.product_id = p.id WHERE w.user_id = :user_id');
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll();
}

function toggle_wishlist(int $userId, int $productId): void
{
    $stmt = db()->prepare('SELECT id FROM wishlists WHERE user_id = :user_id AND product_id = :product_id');
    $stmt->execute(['user_id' => $userId, 'product_id' => $productId]);
    $row = $stmt->fetch();
    if ($row) {
        $stmt = db()->prepare('DELETE FROM wishlists WHERE id = :id');
        $stmt->execute(['id' => $row['id']]);
        log_activity($userId, 'wishlist_removed', json_encode(['product_id' => $productId]));
    } else {
        $stmt = db()->prepare('INSERT INTO wishlists (user_id, product_id, created_at) VALUES (:user_id, :product_id, :created_at)');
        $stmt->execute(['user_id' => $userId, 'product_id' => $productId, 'created_at' => date('Y-m-d H:i:s')]);
        log_activity($userId, 'wishlist_added', json_encode(['product_id' => $productId]));
    }
}

function submit_ticket(int $userId, array $data): void
{
    $stmt = db()->prepare('INSERT INTO support_tickets (user_id, subject, message, status, created_at) VALUES (:user_id, :subject, :message, :status, :created_at)');
    $stmt->execute([
        'user_id' => $userId,
        'subject' => $data['subject'],
        'message' => $data['message'],
        'status' => 'open',
        'created_at' => date('Y-m-d H:i:s'),
    ]);
    log_activity($userId, 'ticket_submitted', json_encode($data));
}

function get_tickets(array $filters = []): array
{
    $sql = 'SELECT t.*, u.name as customer_name FROM support_tickets t JOIN users u ON t.user_id = u.id WHERE 1=1';
    $params = [];
    if (!empty($filters['user_id'])) {
        $sql .= ' AND t.user_id = :user_id';
        $params['user_id'] = $filters['user_id'];
    }
    if (!empty($filters['status'])) {
        $sql .= ' AND t.status = :status';
        $params['status'] = $filters['status'];
    }
    $sql .= ' ORDER BY t.created_at DESC';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function update_ticket(int $ticketId, array $data, int $userId): void
{
    $stmt = db()->prepare('UPDATE support_tickets SET status = :status, response = :response, updated_at = :updated_at WHERE id = :id');
    $stmt->execute([
        'status' => $data['status'],
        'response' => $data['response'] ?? null,
        'updated_at' => date('Y-m-d H:i:s'),
        'id' => $ticketId,
    ]);
    log_activity($userId, 'ticket_updated', json_encode(['ticket_id' => $ticketId, 'status' => $data['status']]));
}

function save_coupon(array $data, int $userId): void
{
    if (!empty($data['id'])) {
        $stmt = db()->prepare('UPDATE coupons SET code = :code, description = :description, discount_percent = :discount_percent, active = :active WHERE id = :id');
        $stmt->execute([
            'code' => strtoupper($data['code']),
            'description' => $data['description'],
            'discount_percent' => $data['discount_percent'],
            'active' => $data['active'],
            'id' => $data['id'],
        ]);
        log_activity($userId, 'coupon_updated', json_encode($data));
    } else {
        $stmt = db()->prepare('INSERT INTO coupons (code, description, discount_percent, active, created_at) VALUES (:code, :description, :discount_percent, :active, :created_at)');
        $stmt->execute([
            'code' => strtoupper($data['code']),
            'description' => $data['description'],
            'discount_percent' => $data['discount_percent'],
            'active' => $data['active'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        log_activity($userId, 'coupon_created', json_encode($data));
    }
}

function get_coupons(): array
{
    return db()->query('SELECT * FROM coupons ORDER BY created_at DESC')->fetchAll();
}

function delete_coupon(int $id, int $userId): void
{
    $stmt = db()->prepare('DELETE FROM coupons WHERE id = :id');
    $stmt->execute(['id' => $id]);
    log_activity($userId, 'coupon_deleted', (string)$id);
}

function save_banner(array $data, int $userId): void
{
    if (!empty($data['id'])) {
        $stmt = db()->prepare('UPDATE banners SET title = :title, image_url = :image_url, link_url = :link_url, active = :active WHERE id = :id');
        $stmt->execute([
            'title' => $data['title'],
            'image_url' => $data['image_url'],
            'link_url' => $data['link_url'],
            'active' => $data['active'],
            'id' => $data['id'],
        ]);
        log_activity($userId, 'banner_updated', json_encode($data));
    } else {
        $stmt = db()->prepare('INSERT INTO banners (title, image_url, link_url, active, created_at) VALUES (:title, :image_url, :link_url, :active, :created_at)');
        $stmt->execute([
            'title' => $data['title'],
            'image_url' => $data['image_url'],
            'link_url' => $data['link_url'],
            'active' => $data['active'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        log_activity($userId, 'banner_created', json_encode($data));
    }
}

function get_banners(bool $onlyActive = false): array
{
    $sql = 'SELECT * FROM banners';
    if ($onlyActive) {
        $sql .= ' WHERE active = 1';
    }
    $sql .= ' ORDER BY created_at DESC';
    return db()->query($sql)->fetchAll();
}

function delete_banner(int $id, int $userId): void
{
    $stmt = db()->prepare('DELETE FROM banners WHERE id = :id');
    $stmt->execute(['id' => $id]);
    log_activity($userId, 'banner_deleted', (string)$id);
}

function save_shipping_method(array $data, int $userId): void
{
    if (!empty($data['id'])) {
        $stmt = db()->prepare('UPDATE shipping_methods SET name = :name, cost = :cost, description = :description WHERE id = :id');
        $stmt->execute([
            'name' => $data['name'],
            'cost' => $data['cost'],
            'description' => $data['description'],
            'id' => $data['id'],
        ]);
        log_activity($userId, 'shipping_method_updated', json_encode($data));
    } else {
        $stmt = db()->prepare('INSERT INTO shipping_methods (name, cost, description) VALUES (:name, :cost, :description)');
        $stmt->execute([
            'name' => $data['name'],
            'cost' => $data['cost'],
            'description' => $data['description'],
        ]);
        log_activity($userId, 'shipping_method_created', json_encode($data));
    }
}

function get_shipping_methods(): array
{
    return db()->query('SELECT * FROM shipping_methods ORDER BY name')->fetchAll();
}

function delete_shipping_method(int $id, int $userId): void
{
    $stmt = db()->prepare('DELETE FROM shipping_methods WHERE id = :id');
    $stmt->execute(['id' => $id]);
    log_activity($userId, 'shipping_method_deleted', (string)$id);
}

function save_payment_method(array $data, int $userId): void
{
    if (!empty($data['id'])) {
        $stmt = db()->prepare('UPDATE payment_methods SET name = :name, instructions = :instructions, active = :active WHERE id = :id');
        $stmt->execute([
            'name' => $data['name'],
            'instructions' => $data['instructions'],
            'active' => $data['active'],
            'id' => $data['id'],
        ]);
        log_activity($userId, 'payment_method_updated', json_encode($data));
    } else {
        $stmt = db()->prepare('INSERT INTO payment_methods (name, instructions, active) VALUES (:name, :instructions, :active)');
        $stmt->execute([
            'name' => $data['name'],
            'instructions' => $data['instructions'],
            'active' => $data['active'],
        ]);
        log_activity($userId, 'payment_method_created', json_encode($data));
    }
}

function get_payment_methods(bool $onlyActive = false): array
{
    $sql = 'SELECT * FROM payment_methods';
    if ($onlyActive) {
        $sql .= ' WHERE active = 1';
    }
    $sql .= ' ORDER BY name';
    return db()->query($sql)->fetchAll();
}

function delete_payment_method(int $id, int $userId): void
{
    $stmt = db()->prepare('DELETE FROM payment_methods WHERE id = :id');
    $stmt->execute(['id' => $id]);
    log_activity($userId, 'payment_method_deleted', (string)$id);
}

function save_page(array $data, int $userId): void
{
    if (!empty($data['id'])) {
        $stmt = db()->prepare('UPDATE pages SET title = :title, slug = :slug, content = :content, active = :active WHERE id = :id');
        $stmt->execute([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'content' => $data['content'],
            'active' => $data['active'],
            'id' => $data['id'],
        ]);
        log_activity($userId, 'page_updated', json_encode($data));
    } else {
        $stmt = db()->prepare('INSERT INTO pages (title, slug, content, active, created_at) VALUES (:title, :slug, :content, :active, :created_at)');
        $stmt->execute([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'content' => $data['content'],
            'active' => $data['active'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        log_activity($userId, 'page_created', json_encode($data));
    }
}

function get_pages(bool $onlyActive = false): array
{
    $sql = 'SELECT * FROM pages';
    if ($onlyActive) {
        $sql .= ' WHERE active = 1';
    }
    $sql .= ' ORDER BY created_at DESC';
    return db()->query($sql)->fetchAll();
}

function delete_page(int $id, int $userId): void
{
    $stmt = db()->prepare('DELETE FROM pages WHERE id = :id');
    $stmt->execute(['id' => $id]);
    log_activity($userId, 'page_deleted', (string)$id);
}

function save_review(int $userId, int $productId, array $data): void
{
    $stmt = db()->prepare('INSERT INTO product_reviews (user_id, product_id, rating, comment, created_at) VALUES (:user_id, :product_id, :rating, :comment, :created_at)');
    $stmt->execute([
        'user_id' => $userId,
        'product_id' => $productId,
        'rating' => $data['rating'],
        'comment' => $data['comment'],
        'created_at' => date('Y-m-d H:i:s'),
    ]);
    log_activity($userId, 'review_submitted', json_encode(['product_id' => $productId, 'rating' => $data['rating']]));
}

function get_product_reviews(int $productId): array
{
    $stmt = db()->prepare('SELECT pr.*, u.name FROM product_reviews pr JOIN users u ON pr.user_id = u.id WHERE pr.product_id = :product_id ORDER BY pr.created_at DESC');
    $stmt->execute(['product_id' => $productId]);
    return $stmt->fetchAll();
}

function update_profile(int $userId, array $data): void
{
    $stmt = db()->prepare('UPDATE users SET name = :name, phone = :phone WHERE id = :id');
    $stmt->execute([
        'name' => $data['name'],
        'phone' => $data['phone'],
        'id' => $userId,
    ]);
    log_activity($userId, 'profile_updated', json_encode($data));
}

function change_password(int $userId, string $password): void
{
    $stmt = db()->prepare('UPDATE users SET password = :password WHERE id = :id');
    $stmt->execute([
        'password' => password_hash($password, PASSWORD_BCRYPT),
        'id' => $userId,
    ]);
    log_activity($userId, 'password_changed', 'Password updated');
}

function update_user_role(int $userId, string $role, string $status, int $actorId): void
{
    $stmt = db()->prepare('UPDATE users SET role = :role, status = :status WHERE id = :id');
    $stmt->execute([
        'role' => $role,
        'status' => $status,
        'id' => $userId,
    ]);
    log_activity($actorId, 'user_updated', json_encode(['user_id' => $userId, 'role' => $role, 'status' => $status]));
}

function get_users(): array
{
    return db()->query('SELECT * FROM users ORDER BY created_at DESC')->fetchAll();
}

function search_products_with_filters(array $filters): array
{
    return get_products($filters);
}

function get_sales_report(): array
{
    $stmt = db()->query('SELECT strftime("%Y-%m", created_at) as period, SUM(total_amount) as total FROM orders GROUP BY period ORDER BY period DESC LIMIT 12');
    return $stmt->fetchAll();
}

function get_inventory_report(): array
{
    $stmt = db()->query('SELECT p.name, p.stock FROM products p ORDER BY p.stock ASC');
    return $stmt->fetchAll();
}
