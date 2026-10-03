<?php

session_start();


// --------------------------------------------------
// CHECK ADMIN LOGIN
// --------------------------------------------------

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}


// --------------------------------------------------
// DATABASE CONNECTION
// --------------------------------------------------

include '../includes/db.php';


// --------------------------------------------------
// UPDATE ORDER STATUS
// --------------------------------------------------

if (isset($_POST['update_status'])) {

    $order_id = (int) $_POST['order_id'];
    $status = $_POST['status'];


    // Only allow these statuses
    $allowed_statuses = [
        'Pending',
        'Shipped',
        'Delivered'
    ];


    if (in_array($status, $allowed_statuses, true)) {

        $stmt = $conn->prepare("
            UPDATE orders
            SET status = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $status,
            $order_id
        ]);
    }


    header("Location: manage_orders.php");
    exit();
}


// --------------------------------------------------
// GET ALL ORDERS
// --------------------------------------------------

$stmt = $conn->query("
    SELECT
        orders.id,
        orders.user_id,
        orders.total_amount,
        orders.status,
        orders.created_at,
        users.username,
        users.email
    FROM orders

    INNER JOIN users
        ON orders.user_id = users.id

    ORDER BY orders.created_at DESC
");

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Manage Orders | Admin
    </title>

    <link rel="stylesheet"
          href="../css/styles.css">

</head>


<body>


<!-- ==================================================
     HEADER
================================================== -->

<header class="admin-header">

    <div class="admin-header-container">

        <h1>
            My Ecommerce Store
        </h1>

        <nav>

            <a href="dashboard.php">
                Dashboard
            </a>

            <a href="add_product.php">
                Add Product
            </a>

            <a href="manage_products.php">
                Manage Products
            </a>

            <a href="manage_orders.php"
               class="active">
                Manage Orders
            </a>

            <a href="logout.php"
               class="logout-button">
                Logout
            </a>

        </nav>

    </div>

</header>


<!-- ==================================================
     MAIN
================================================== -->

<main class="admin-orders-page">

    <div class="admin-orders-container">


        <!-- PAGE HEADER -->

        <div class="admin-orders-title">

            <div>

                <h2>
                    Manage Orders
                </h2>

                <p>
                    View customer orders and update their status.
                </p>

            </div>

        </div>


        <?php if (empty($orders)): ?>

            <!-- NO ORDERS -->

            <div class="no-orders">

                <div class="no-orders-icon">
                    📦
                </div>

                <h3>
                    No Orders Found
                </h3>

                <p>
                    There are currently no customer orders.
                </p>

            </div>


        <?php else: ?>


            <!-- ORDERS CARD -->

            <div class="admin-orders-card">


                <div class="admin-orders-card-header">

                    <h3>
                        Customer Orders
                    </h3>

                    <span>
                        <?= count($orders); ?>
                        <?= count($orders) == 1 ? 'Order' : 'Orders'; ?>
                    </span>

                </div>


                <!-- TABLE -->

                <div class="admin-orders-table-wrapper">

                    <table class="admin-orders-table">


                        <thead>

                            <tr>

                                <th>
                                    Order
                                </th>

                                <th>
                                    Customer
                                </th>

                                <th>
                                    Email
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


                                <!-- ORDER -->

                                <td>

                                    <strong>
                                        #<?= htmlspecialchars(
                                            $order['id']
                                        ); ?>
                                    </strong>

                                </td>


                                <!-- CUSTOMER -->

                                <td>

                                    <?= htmlspecialchars(
                                        $order['username']
                                    ); ?>

                                </td>


                                <!-- EMAIL -->

                                <td>

                                    <?= htmlspecialchars(
                                        $order['email']
                                    ); ?>

                                </td>


                                <!-- DATE -->

                                <td>

                                    <div class="admin-order-date">

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

                                    <strong>

                                        $<?= number_format(
                                            $order['total_amount'],
                                            2
                                        ); ?>

                                    </strong>

                                </td>


                                <!-- STATUS -->

                                <td>

                                    <?php

                                    $status_class =
                                        strtolower(
                                            $order['status']
                                        );

                                    ?>

                                    <span
                                        class="status-badge status-<?= htmlspecialchars(
                                            $status_class
                                        ); ?>">

                                        <?= htmlspecialchars(
                                            $order['status']
                                        ); ?>

                                    </span>

                                </td>


                                <!-- UPDATE STATUS -->

                                <td>

                                    <form
                                        method="POST"
                                        class="status-form"
                                    >

                                        <input
                                            type="hidden"
                                            name="order_id"
                                            value="<?= $order['id']; ?>"
                                        >


                                        <select
                                            name="status"
                                            class="status-select"
                                        >

                                            <option
                                                value="Pending"
                                                <?= $order['status'] === 'Pending'
                                                    ? 'selected'
                                                    : ''; ?>
                                            >
                                                Pending
                                            </option>


                                            <option
                                                value="Shipped"
                                                <?= $order['status'] === 'Shipped'
                                                    ? 'selected'
                                                    : ''; ?>
                                            >
                                                Shipped
                                            </option>


                                            <option
                                                value="Delivered"
                                                <?= $order['status'] === 'Delivered'
                                                    ? 'selected'
                                                    : ''; ?>
                                            >
                                                Delivered
                                            </option>

                                        </select>


                                        <button
                                            type="submit"
                                            name="update_status"
                                            class="update-status-button"
                                        >

                                            Update

                                        </button>

                                    </form>

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