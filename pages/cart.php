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
// ADD PRODUCT TO CART
// --------------------------------------------------

if (isset($_POST['add_to_cart'])) {

    $product_id = (int) $_POST['product_id'];

    $stmt = $conn->prepare("
        SELECT id, quantity
        FROM cart
        WHERE user_id = ? AND product_id = ?
    ");

    $stmt->execute([$user_id, $product_id]);

    $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cart_item) {

        $new_quantity = $cart_item['quantity'] + 1;

        $stmt = $conn->prepare("
            UPDATE cart
            SET quantity = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $new_quantity,
            $cart_item['id']
        ]);

    } else {

        $stmt = $conn->prepare("
            INSERT INTO cart (user_id, product_id, quantity)
            VALUES (?, ?, 1)
        ");

        $stmt->execute([
            $user_id,
            $product_id
        ]);
    }

    header("Location: cart.php");
    exit();
}


// --------------------------------------------------
// UPDATE CART QUANTITY
// --------------------------------------------------

if (isset($_POST['update_cart'])) {

    $product_id = (int) $_POST['product_id'];
    $quantity = (int) $_POST['quantity'];

    if ($quantity > 0) {

        $stmt = $conn->prepare("
            UPDATE cart
            SET quantity = ?
            WHERE user_id = ? AND product_id = ?
        ");

        $stmt->execute([
            $quantity,
            $user_id,
            $product_id
        ]);

    } else {

        $stmt = $conn->prepare("
            DELETE FROM cart
            WHERE user_id = ? AND product_id = ?
        ");

        $stmt->execute([
            $user_id,
            $product_id
        ]);
    }

    header("Location: cart.php");
    exit();
}


// --------------------------------------------------
// REMOVE PRODUCT FROM CART
// --------------------------------------------------

if (isset($_POST['remove_from_cart'])) {

    $product_id = (int) $_POST['product_id'];

    $stmt = $conn->prepare("
        DELETE FROM cart
        WHERE user_id = ? AND product_id = ?
    ");

    $stmt->execute([
        $user_id,
        $product_id
    ]);

    header("Location: cart.php");
    exit();
}


// --------------------------------------------------
// CLEAR ENTIRE CART
// --------------------------------------------------

if (isset($_POST['clear_cart'])) {

    $stmt = $conn->prepare("
        DELETE FROM cart
        WHERE user_id = ?
    ");

    $stmt->execute([$user_id]);

    header("Location: cart.php");
    exit();
}


// --------------------------------------------------
// GET CART PRODUCTS
// --------------------------------------------------

$stmt = $conn->prepare("
    SELECT
        cart.product_id,
        cart.quantity,
        products.name,
        products.price,
        products.image
    FROM cart
    INNER JOIN products
        ON cart.product_id = products.id
    WHERE cart.user_id = ?
");

$stmt->execute([$user_id]);

$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);


// --------------------------------------------------
// CALCULATE TOTAL
// --------------------------------------------------

$total_cost = 0;

foreach ($cart_items as $item) {
    $total_cost += $item['price'] * $item['quantity'];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Shopping Cart</title>

    <link rel="stylesheet" href="../css/styles.css">

    <!-- CART PAGE DESIGN -->
    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f6f8;
            color: #222;
        }

        /* ---------------------------------
           MAIN CART AREA
        --------------------------------- */

        main {
            width: 100%;
            padding: 40px 20px 60px;
        }

        .cart-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .cart-container h2 {
            font-size: 32px;
            margin: 0 0 25px;
            color: #26384a;
        }

        /* ---------------------------------
           TABLE CONTAINER
        --------------------------------- */

        .cart-table-wrapper {
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow-x: auto;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
        }

        /* ---------------------------------
           CART TABLE
        --------------------------------- */

        .cart-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .cart-table th {
            background: #2d4358;
            color: white;
            padding: 18px 15px;
            text-align: center;
            font-size: 16px;
        }

        .cart-table td {
            padding: 20px 15px;
            text-align: center;
            vertical-align: middle;
            border-bottom: 1px solid #e5e5e5;
        }

        .cart-table tr:last-child td {
            border-bottom: none;
        }

        /* COLUMN WIDTHS */

        .cart-table th:nth-child(1),
        .cart-table td:nth-child(1) {
            width: 22%;
            text-align: left;
        }

        .cart-table th:nth-child(2),
        .cart-table td:nth-child(2) {
            width: 16%;
        }

        .cart-table th:nth-child(3),
        .cart-table td:nth-child(3) {
            width: 14%;
        }

        .cart-table th:nth-child(4),
        .cart-table td:nth-child(4) {
            width: 22%;
        }

        .cart-table th:nth-child(5),
        .cart-table td:nth-child(5) {
            width: 14%;
        }

        .cart-table th:nth-child(6),
        .cart-table td:nth-child(6) {
            width: 12%;
        }

        /* ---------------------------------
           PRODUCT NAME
        --------------------------------- */

        .product-name {
            font-weight: bold;
            font-size: 17px;
            color: #26384a;
        }

        /* ---------------------------------
           PRODUCT IMAGE
        --------------------------------- */

        .product-image {
            width: 230px;
            height: 230px;
            object-fit: contain;
            border-radius: 8px;
            background: #fafafa;
            padding: 8px;
            
        }

        /* ---------------------------------
           PRICE
        --------------------------------- */

        .price {
            font-weight: bold;
            font-size: 16px;
        }

        /* ---------------------------------
           QUANTITY FORM
        --------------------------------- */

        .quantity-form {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        .quantity-form input {
            width: 65px;
            height: 38px;
            padding: 5px;
            text-align: center;
            border: 1px solid #bbb;
            border-radius: 5px;
            font-size: 15px;
        }

        /* ---------------------------------
           BUTTONS
        --------------------------------- */

        .update-button,
        .remove-button,
        .checkout-button {
            border: none;
            border-radius: 5px;
            padding: 9px 14px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .update-button {
            background: #2d4358;
            color: white;
        }

        .update-button:hover {
            background: #1f3040;
        }

        .remove-button {
            background: #ff4d32;
            color: white;
        }

        .remove-button:hover {
            background: #e63d25;
        }

        .checkout-button {
            background: #28a745;
            color: white;
            padding: 12px 25px;
            font-weight: bold;
        }

        .checkout-button:hover {
            background: #218838;
        }

        /* ---------------------------------
           TOTAL
        --------------------------------- */

        .cart-summary {
            background: white;
            margin-top: 20px;
            padding: 20px 25px;
            border-radius: 8px;
            text-align: right;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .cart-summary h3 {
            margin: 0;
            font-size: 24px;
            color: #26384a;
        }

        /* ---------------------------------
           CART ACTIONS
        --------------------------------- */

        .cart-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 12px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .continue-shopping {
            color: #2d4358;
            font-weight: bold;
            text-decoration: none;
            padding: 10px 15px;
        }

        .continue-shopping:hover {
            text-decoration: underline;
        }

        /* ---------------------------------
           EMPTY CART
        --------------------------------- */

        .empty-cart {
            background: white;
            padding: 50px;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
        }

        .empty-cart p {
            font-size: 20px;
            margin-bottom: 25px;
        }

        /* ---------------------------------
           MOBILE
        --------------------------------- */

        @media (max-width: 768px) {

            main {
                padding: 25px 10px;
            }

            .cart-container h2 {
                font-size: 26px;
            }

            .cart-table {
                min-width: 850px;
            }

            .cart-actions {
                justify-content: center;
            }

        }

    </style>

</head>


<body>

<header>

    <div class="header-container">

        <h1>My Ecommerce Store</h1>

        <nav>

            <a href="../index.php">
                Home
            </a>

            <a href="cart.php">
                Cart
            </a>

            <a href="logout.php" class="logout-button">
                Logout
            </a>

        </nav>

    </div>

</header>


<main>

    <div class="cart-container">

        <h2>Shopping Cart</h2>


        <?php if (empty($cart_items)): ?>

            <div class="empty-cart">

                <p>Your cart is empty.</p>

                <a href="../index.php" class="continue-shopping">
                    Continue Shopping
                </a>

            </div>


        <?php else: ?>


            <!-- TABLE WRAPPER -->

            <div class="cart-table-wrapper">

                <table class="cart-table">

                    <thead>

                        <tr>

                            <th>Product</th>

                            <th>Image</th>

                            <th>Price</th>

                            <th>Quantity</th>

                            <th>Subtotal</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php foreach ($cart_items as $item): ?>

                        <tr>

                            <!-- PRODUCT -->

                            <td>

                                <div class="product-name">

                                    <?= htmlspecialchars($item['name']); ?>

                                </div>

                            </td>


                            <!-- IMAGE -->

                            <td>

                                <img
                                    class="product-image"
                                    src="../images/<?= htmlspecialchars($item['image']); ?>"
                                    alt="<?= htmlspecialchars($item['name']); ?>"
                                >

                            </td>


                            <!-- PRICE -->

                            <td>

                                <span class="price">

                                    $<?= number_format($item['price'], 2); ?>

                                </span>

                            </td>


                            <!-- QUANTITY -->

                            <td>

                                <form method="POST" class="quantity-form">

                                    <input
                                        type="hidden"
                                        name="product_id"
                                        value="<?= $item['product_id']; ?>"
                                    >

                                    <input
                                        type="number"
                                        name="quantity"
                                        value="<?= $item['quantity']; ?>"
                                        min="1"
                                    >

                                    <button
                                        type="submit"
                                        name="update_cart"
                                        class="update-button"
                                    >
                                        Update
                                    </button>

                                </form>

                            </td>


                            <!-- SUBTOTAL -->

                            <td>

                                <span class="price">

                                    $<?= number_format(
                                        $item['price'] * $item['quantity'],
                                        2
                                    ); ?>

                                </span>

                            </td>


                            <!-- REMOVE -->

                            <td>

                                <form method="POST">

                                    <input
                                        type="hidden"
                                        name="product_id"
                                        value="<?= $item['product_id']; ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="remove_from_cart"
                                        class="remove-button"
                                    >
                                        Remove
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>


            <!-- TOTAL -->

            <div class="cart-summary">

                <h3>

                    Total:
                    $<?= number_format($total_cost, 2); ?>

                </h3>

            </div>


            <!-- ACTION BUTTONS -->

            <div class="cart-actions">

                <a
                    href="../index.php"
                    class="continue-shopping"
                >
                    Continue Shopping
                </a>


                <form method="POST">

                    <button
                        type="submit"
                        name="clear_cart"
                        class="remove-button"
                    >
                        Clear Cart
                    </button>

                </form>


                <a
                     href="checkout.php"
                    class="checkout-button">
                    Checkout
                </a>

            </div>


        <?php endif; ?>

    </div>

</main>


<footer>

    <p>
        &copy; <?= date("Y"); ?> My Ecommerce Store
    </p>

</footer>


</body>

</html>