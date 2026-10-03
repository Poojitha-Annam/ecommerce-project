```php
<?php
session_start();

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
require_once '../includes/db.php';

// 1. Total products
$stmt = $conn->query("SELECT COUNT(*) FROM products");
$total_products = (int) $stmt->fetchColumn();

// 2. Total orders
$stmt = $conn->query("SELECT COUNT(*) FROM orders");
$total_orders = (int) $stmt->fetchColumn();

// 3. Registered customers
$stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
$total_customers = (int) $stmt->fetchColumn();

// 4. Total order value
$stmt = $conn->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders");
$total_order_value = (float) $stmt->fetchColumn();

// 5. Recent orders
$stmt = $conn->query("
    SELECT
        orders.id,
        orders.total_amount,
        orders.status,
        orders.created_at,
        users.username,
        users.email
    FROM orders
    INNER JOIN users ON orders.user_id = users.id
    ORDER BY orders.created_at DESC
    LIMIT 5
");

$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | My Ecommerce Store</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f7fb;
            color: #1f2937;
        }

        .topbar {
            background: #26394d;
            color: white;
            padding: 20px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .topbar h1 {
            font-size: 24px;
            margin: 0;
        }

        .topbar p {
            color: #d5deea;
            margin: 6px 0 0;
            font-size: 13px;
        }

        .topbar nav {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .topbar nav a {
            color: white;
            text-decoration: none;
            padding: 10px 13px;
            border-radius: 6px;
            font-size: 14px;
            transition: background 0.2s;
        }

        .topbar nav a:hover,
        .topbar nav a.active {
            background: #40566f;
        }

        .topbar nav a.logout {
            background: #dc3545;
        }

        .topbar nav a.logout:hover {
            background: #bd2937;
        }

        .dashboard {
            max-width: 1250px;
            margin: 0 auto;
            padding: 35px 24px 55px;
        }

        .welcome {
            margin-bottom: 28px;
        }

        .welcome h2 {
            margin: 0 0 8px;
            font-size: 30px;
            color: #111827;
        }

        .welcome p {
            margin: 0;
            color: #6b7280;
            line-height: 1.6;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            padding: 23px;
            border-radius: 12px;
            border: 1px solid #e5eaf1;
            box-shadow: 0 4px 14px rgba(31, 41, 55, 0.04);
        }

        .stat-label {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .stat-value {
            color: #111827;
            font-size: 30px;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .stat-note {
            color: #9ca3af;
            font-size: 12px;
            margin-top: 10px;
        }

        .section-card {
            background: white;
            border: 1px solid #e5eaf1;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(31, 41, 55, 0.04);
            overflow: hidden;
            margin-bottom: 28px;
        }

        .section-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 22px 24px;
            border-bottom: 1px solid #e5eaf1;
        }

        .section-heading h3 {
            margin: 0;
            font-size: 19px;
        }

        .section-heading a {
            color: #2563eb;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .section-heading a:hover {
            text-decoration: underline;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 650px;
            border-collapse: collapse;
        }

        th {
            background: #f8fafc;
            color: #6b7280;
            font-size: 12px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        th, td {
            padding: 16px 22px;
            border-bottom: 1px solid #edf0f4;
        }

        td {
            font-size: 14px;
            color: #374151;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background: #f9fbfe;
        }

        .customer-email {
            display: block;
            color: #9ca3af;
            font-size: 12px;
            margin-top: 4px;
        }

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .status-pending {
            background: #fff4d6;
            color: #8a5a00;
        }

        .status-shipped {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .status-delivered {
            background: #dcfce7;
            color: #166534;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .empty-state {
            padding: 35px 20px;
            text-align: center;
            color: #6b7280;
        }

        .quick-links {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .quick-link {
            display: block;
            background: white;
            border: 1px solid #e5eaf1;
            border-radius: 10px;
            padding: 20px;
            text-decoration: none;
            color: #1f2937;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .quick-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(31, 41, 55, 0.08);
        }

        .quick-link strong {
            display: block;
            margin-bottom: 7px;
            font-size: 15px;
        }

        .quick-link span {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.5;
        }

        footer {
            padding: 22px;
            text-align: center;
            color: #9ca3af;
            font-size: 13px;
        }

        @media (max-width: 950px) {
            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .quick-links {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .topbar {
                padding: 20px;
            }

            .topbar h1 {
                font-size: 21px;
            }

            .topbar nav {
                width: 100%;
            }

            .topbar nav a {
                padding: 9px;
                font-size: 13px;
            }

            .dashboard {
                padding: 28px 15px 40px;
            }

            .welcome h2 {
                font-size: 25px;
            }

            .stats-grid {
                gap: 12px;
            }

            .stat-card {
                padding: 17px;
            }

            .stat-value {
                font-size: 24px;
            }

            .section-heading {
                padding: 18px;
            }
        }
    </style>
</head>

<body>

<header class="topbar">
    <div>
        <h1>My Ecommerce Store</h1>
        <p>Administration Panel</p>
    </div>

    <nav>
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="add_product.php">Add Product</a>
        <a href="manage_products.php">Manage Products</a>
        <a href="manage_orders.php">Manage Orders</a>
        <a href="logout.php" class="logout">Logout</a>
    </nav>
</header>

<main class="dashboard">

    <section class="welcome">
        <h2>Dashboard Overview</h2>
        <p>Monitor your store's products, customers, orders, and order value.</p>
    </section>

    <section class="stats-grid">

        <div class="stat-card">
            <div class="stat-label">Total Products</div>
            <div class="stat-value"><?= number_format($total_products); ?></div>
            <div class="stat-note">Products in your catalogue</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Total Orders</div>
            <div class="stat-value"><?= number_format($total_orders); ?></div>
            <div class="stat-note">All saved orders</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Registered Customers</div>
            <div class="stat-value"><?= number_format($total_customers); ?></div>
            <div class="stat-note">Accounts with the user role</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Total Order Value</div>
            <div class="stat-value">$<?= number_format($total_order_value, 2); ?></div>
            <div class="stat-note">Sum of all saved order totals</div>
        </div>

    </section>

    <section class="section-card">
        <div class="section-heading">
            <h3>Recent Orders</h3>
            <a href="manage_orders.php">View all orders →</a>
        </div>

        <?php if (empty($recent_orders)): ?>

            <div class="empty-state">
                No orders have been placed yet.
            </div>

        <?php else: ?>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($recent_orders as $order): ?>
                        <?php
                        $status_text = (string) $order['status'];
                        $status_class = strtolower($status_text);
                        ?>

                        <tr>
                            <td><strong>#<?= (int) $order['id']; ?></strong></td>

                            <td>
                                <?= htmlspecialchars($order['username'] ?? 'Customer'); ?>
                                <span class="customer-email">
                                    <?= htmlspecialchars($order['email'] ?? ''); ?>
                                </span>
                            </td>

                            <td>
                                <?= date('M d, Y', strtotime($order['created_at'])); ?>
                            </td>

                            <td>
                                <strong>$<?= number_format((float) $order['total_amount'], 2); ?></strong>
                            </td>

                            <td>
                                <span class="status status-<?= htmlspecialchars($status_class); ?>">
                                    <?= htmlspecialchars($status_text); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>
    </section>

    <section>
        <div class="section-heading" style="padding-left: 0; padding-right: 0; border: none;">
            <h3>Quick Actions</h3>
        </div>

        <div class="quick-links">
            <a href="add_product.php" class="quick-link">
                <strong>＋ Add Product</strong>
                <span>Add a new item to your store catalogue.</span>
            </a>

            <a href="manage_products.php" class="quick-link">
                <strong>▦ Manage Products</strong>
                <span>Edit product details or manage existing items.</span>
            </a>

            <a href="manage_orders.php" class="quick-link">
                <strong>↗ Manage Orders</strong>
                <span>Review orders and update their delivery status.</span>
            </a>
        </div>
    </section>

</main>

<footer>
    &copy; <?= date('Y'); ?> My Ecommerce Store. All rights reserved.
</footer>

</body>
</html>
```
