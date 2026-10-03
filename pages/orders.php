<?php

session_start();

// --------------------------------------------------
// CHECK LOGIN
// --------------------------------------------------

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


// --------------------------------------------------
// DATABASE CONNECTION
// --------------------------------------------------

include '../includes/db.php';

$user_id = $_SESSION['user_id'];


// --------------------------------------------------
// GET CUSTOMER ORDERS
// --------------------------------------------------

$stmt = $conn->prepare("
    SELECT
        id,
        total_amount,
        status,
        created_at
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
");

$stmt->execute([$user_id]);

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Orders | My Ecommerce Store</title>

    <link rel="stylesheet" href="../css/styles.css">

</head>


<body>

<!-- ==================================================
     HEADER
================================================== -->

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


<!-- ==================================================
     MAIN CONTENT
================================================== -->

<main class="orders-page">

    <div class="orders-container">

        <div class="orders-header">

            <div>

                <h1>My Orders</h1>

                <p>
                    View your previous orders and track their status.
                </p>

            </div>

            <a href="../index.php"
               class="shop-now-button">

                Continue Shopping

            </a>

        </div>


        <?php if (empty($orders)): ?>

            <!-- EMPTY ORDERS -->

            <div class="empty-orders">

                <div class="empty-orders-icon">
                    🛍️
                </div>

                <h2>No Orders Yet</h2>

                <p>
                    You haven't placed any orders yet.
                </p>

                <a href="../index.php"
                   class="shop-now-button">

                    Start Shopping

                </a>

            </div>


        <?php else: ?>

            <!-- ORDERS TABLE -->

            <div class="orders-card">

                <div class="orders-card-header">

                    <h2>
                        Order History
                    </h2>

                    <span>
                        <?= count($orders); ?>
                        <?= count($orders) == 1 ? 'Order' : 'Orders'; ?>
                    </span>

                </div>


                <div class="orders-table-wrapper">

                    <table class="orders-table">

                        <thead>

                            <tr>

                                <th>
                                    Order
                                </th>

                                <th>
                                    Date
                                </th>

                                <th>
                                    Total
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php foreach ($orders as $order): ?>

                            <tr>

                                <!-- ORDER ID -->

                                <td>

                                    <div class="order-number">

                                        #<?= htmlspecialchars(
                                            $order['id']
                                        ); ?>

                                    </div>

                                </td>


                                <!-- DATE -->

                                <td>

                                    <div class="order-date">

                                        <?= date(
                                            "M d, Y",
                                            strtotime(
                                                $order['created_at']
                                            )
                                        ); ?>

                                        <small>
                                            <?= date(
                                                "h:i A",
                                                strtotime(
                                                    $order['created_at']
                                                )
                                            ); ?>
                                        </small>

                                    </div>

                                </td>


                                <!-- TOTAL -->

                                <td>

                                    <strong class="order-total">

                                        $<?= number_format(
                                            $order['total_amount'],
                                            2
                                        ); ?>

                                    </strong>

                                </td>


                                <!-- STATUS -->

                                <td>

                                    <?php

                                    $status = strtolower(
                                        $order['status']
                                    );

                                    ?>

                                    <span
                                        class="status-badge status-<?= htmlspecialchars(
                                            $status
                                        ); ?>">

                                        <?= htmlspecialchars(
                                            $order['status']
                                        ); ?>

                                    </span>

                                </td>


                                <!-- ACTION -->

                                <td>

                                    <a
                                        href="order_details.php?id=<?= $order['id']; ?>"
                                        class="view-order-button">

                                        View Details

                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        <?php endif; ?>

    </div>

</main>


<!-- ==================================================
     FOOTER
================================================== -->

<footer class="site-footer">

    <p>
        &copy; <?= date("Y"); ?>
        My Ecommerce Store.
        All rights reserved.
    </p>

</footer>


</body>

</html>