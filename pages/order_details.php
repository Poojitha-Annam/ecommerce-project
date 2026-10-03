<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';

$user_id = $_SESSION['user_id'];


// --------------------------------------------------
// GET ORDER ID
// --------------------------------------------------

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = (int) $_GET['id'];


// --------------------------------------------------
// GET ORDER
// --------------------------------------------------

$stmt = $conn->prepare("
    SELECT
        id,
        full_name,
        email,
        phone,
        address,
        city,
        state,
        pincode,
        total_amount,
        status,
        created_at
    FROM orders
    WHERE id = ? AND user_id = ?
");

$stmt->execute([
    $order_id,
    $user_id
]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);


// --------------------------------------------------
// CHECK ORDER
// --------------------------------------------------

if (!$order) {
    die("Order not found.");
}


// --------------------------------------------------
// GET ORDER ITEMS
// --------------------------------------------------

$stmt = $conn->prepare("
    SELECT
        product_name,
        price,
        quantity,
        subtotal
    FROM order_items
    WHERE order_id = ?
");

$stmt->execute([$order_id]);

$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Order #<?= htmlspecialchars($order['id']); ?>
        | My Ecommerce Store
    </title>

    <link rel="stylesheet" href="../css/styles.css">

</head>


<body>

<header class="site-header">

    <div class="header-container">

        <div class="logo">
            My Ecommerce Store
        </div>

        <nav class="main-nav">

            <a href="../index.php">
                Home
            </a>

            <a href="cart.php">
                Cart
            </a>

            <a href="orders.php" class="active">
                My Orders
            </a>

            <a href="logout.php" class="logout-button">
                Logout
            </a>

        </nav>

    </div>

</header>


<main class="order-details-page">

    <div class="order-details-container">

        <!-- PAGE HEADER -->

        <div class="order-details-header">

            <div>

                <a href="orders.php"
                   class="back-orders">

                    ← Back to My Orders

                </a>

                <h1>
                    Order #<?= htmlspecialchars($order['id']); ?>
                </h1>

                <p>
                    Placed on
                    <?= date(
                        "M d, Y h:i A",
                        strtotime($order['created_at'])
                    ); ?>
                </p>

            </div>

            <div>

                <?php
                $status = strtolower($order['status']);
                ?>

                <span class="status-badge status-<?= htmlspecialchars($status); ?>">

                    <?= htmlspecialchars($order['status']); ?>

                </span>

            </div>

        </div>


        <!-- ORDER CONTENT -->

        <div class="order-details-grid">


            <!-- CUSTOMER / SHIPPING -->

            <div class="order-info-card">

                <h2>
                    Shipping Information
                </h2>

                <div class="customer-info">

                    <p>
                        <strong>Name</strong>
                        <?= htmlspecialchars($order['full_name']); ?>
                    </p>

                    <p>
                        <strong>Email</strong>
                        <?= htmlspecialchars($order['email']); ?>
                    </p>

                    <p>
                        <strong>Phone</strong>
                        <?= htmlspecialchars($order['phone']); ?>
                    </p>

                    <p>
                        <strong>Address</strong>
                        <?= htmlspecialchars($order['address']); ?>
                    </p>

                    <p>
                        <strong>City</strong>
                        <?= htmlspecialchars($order['city']); ?>
                    </p>

                    <p>
                        <strong>State</strong>
                        <?= htmlspecialchars($order['state']); ?>
                    </p>

                    <p>
                        <strong>Pincode</strong>
                        <?= htmlspecialchars($order['pincode']); ?>
                    </p>

                </div>

            </div>


            <!-- ORDER STATUS -->

            <div class="order-info-card">

                <h2>
                    Order Status
                </h2>

                <div class="order-status-display">

                    <span class="status-dot"></span>

                    <strong>
                        <?= htmlspecialchars($order['status']); ?>
                    </strong>

                </div>

                <p class="status-message">

                    <?php if ($status === 'pending'): ?>

                        Your order has been received and is waiting
                        to be processed.

                    <?php elseif ($status === 'shipped'): ?>

                        Your order has been shipped.

                    <?php elseif ($status === 'delivered'): ?>

                        Your order has been delivered.

                    <?php elseif ($status === 'cancelled'): ?>

                        This order has been cancelled.

                    <?php else: ?>

                        Your order status is
                        <?= htmlspecialchars($order['status']); ?>.

                    <?php endif; ?>

                </p>

            </div>


        </div>


        <!-- PRODUCTS -->

        <div class="order-products-card">

            <div class="order-products-header">

                <h2>
                    Order Items
                </h2>

                <span>
                    <?= count($order_items); ?>
                    <?= count($order_items) == 1 ? 'Item' : 'Items'; ?>
                </span>

            </div>


            <div class="order-products-table-wrapper">

                <table class="order-products-table">

                    <thead>

                        <tr>

                            <th>
                                Product
                            </th>

                            <th>
                                Price
                            </th>

                            <th>
                                Quantity
                            </th>

                            <th>
                                Subtotal
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php foreach ($order_items as $item): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars(
                                    $item['product_name']
                                ); ?>
                            </td>

                            <td>
                                $<?= number_format(
                                    $item['price'],
                                    2
                                ); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $item['quantity']
                                ); ?>
                            </td>

                            <td>
                                <strong>
                                    $<?= number_format(
                                        $item['subtotal'],
                                        2
                                    ); ?>
                                </strong>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>


            <!-- TOTAL -->

            <div class="order-total-section">

                <span>
                    Total Amount
                </span>

                <strong>
                    $<?= number_format(
                        $order['total_amount'],
                        2
                    ); ?>
                </strong>

            </div>

        </div>


        <!-- BUTTONS -->

        <div class="order-details-actions">

            <a href="orders.php"
               class="back-button">

                ← My Orders

            </a>

            <a href="../index.php"
               class="shop-now-button">

                Continue Shopping

            </a>

        </div>

    </div>

</main>


<footer class="site-footer">

    <p>
        &copy; <?= date("Y"); ?>
        My Ecommerce Store.
        All rights reserved.
    </p>

</footer>

</body>

</html>