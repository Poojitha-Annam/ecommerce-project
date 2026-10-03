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
// GET CART ITEMS
// --------------------------------------------------

$stmt = $conn->prepare("
    SELECT
        cart.product_id,
        cart.quantity,
        products.name,
        products.price
    FROM cart
    INNER JOIN products
        ON cart.product_id = products.id
    WHERE cart.user_id = ?
");

$stmt->execute([$user_id]);

$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);


// --------------------------------------------------
// CHECK IF CART IS EMPTY
// --------------------------------------------------

if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}


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

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Checkout - My Ecommerce Store</title>

    <link rel="stylesheet" href="../css/styles.css">


    <!-- =========================================
         CHECKOUT PAGE CSS
    ========================================== -->

    <style>

        * {
            box-sizing: border-box;
        }


        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f6f8;
            color: #26384a;
        }


        /* =========================================
           HEADER
        ========================================== */

        header {
            width: 100%;
            background: #2d4358;
        }


        .header-container {
            width: 100%;
            max-width: 1400px;
            margin: auto;
            padding: 20px 35px;

            display: flex;
            align-items: center;
            justify-content: space-between;
        }


        .header-container h1 {
            margin: 0;
            color: white;
            font-size: 32px;
        }


        nav {
            display: flex;
            align-items: center;
            gap: 35px;
        }


        nav a {
            color: white;
            text-decoration: none;
            font-size: 18px;
        }


        nav a:hover {
            opacity: 0.8;
        }


        .logout-button {
            background: #ff4d32;
            padding: 12px 20px;
            border-radius: 6px;
        }


        /* =========================================
           MAIN CHECKOUT AREA
        ========================================== */

        main {
            width: 100%;
            padding: 45px 20px 70px;
        }


        .checkout-container {
            width: 100%;
            max-width: 1100px;
            margin: auto;
        }


        .checkout-title {
            font-size: 36px;
            margin: 0 0 35px;
            color: #26384a;
        }


        /* =========================================
           CARDS
        ========================================== */

        .checkout-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, 0.08);
        }


        .checkout-card h3 {
            margin: 0 0 25px;
            font-size: 24px;
            color: #26384a;
        }


        /* =========================================
           ORDER SUMMARY TABLE
        ========================================== */

        .order-table-wrapper {
            width: 100%;
            overflow-x: auto;
        }


        .order-table {
            width: 100%;
            border-collapse: collapse;
        }


        .order-table th {
            background: #2d4358;
            color: white;
            padding: 16px;
            text-align: left;
            font-size: 16px;
        }


        .order-table td {
            padding: 17px 16px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 16px;
        }


        .order-table tr:last-child td {
            border-bottom: none;
        }


        .order-table td:nth-child(2),
        .order-table td:nth-child(3),
        .order-table td:nth-child(4) {
            text-align: right;
        }


        .order-table th:nth-child(2),
        .order-table th:nth-child(3),
        .order-table th:nth-child(4) {
            text-align: right;
        }


        .product-name {
            font-weight: bold;
        }


        /* =========================================
           TOTAL
        ========================================== */

        .total-section {
            display: flex;
            justify-content: flex-end;
            align-items: center;

            border-top: 2px solid #e5e7eb;
            margin-top: 20px;
            padding-top: 20px;
        }


        .total-label {
            font-size: 20px;
            font-weight: bold;
            margin-right: 15px;
        }


        .total-price {
            font-size: 28px;
            font-weight: bold;
            color: #28a745;
        }


        /* =========================================
           SHIPPING FORM
        ========================================== */

        .shipping-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px 25px;
        }


        .form-group {
            display: flex;
            flex-direction: column;
        }


        .form-group.full-width {
            grid-column: 1 / -1;
        }


        .form-group label {
            font-weight: bold;
            font-size: 15px;
            margin-bottom: 8px;
            color: #26384a;
        }


        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 13px 14px;

            border: 1px solid #cbd5e1;
            border-radius: 6px;

            font-size: 16px;
            font-family: inherit;

            outline: none;

            transition: 0.2s;
        }


        .form-group input {
            height: 46px;
        }


        .form-group textarea {
            min-height: 110px;
            resize: vertical;
        }


        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #2d4358;

            box-shadow:
                0 0 0 3px rgba(45, 67, 88, 0.12);
        }


        /* =========================================
           PLACE ORDER
        ========================================== */

        .place-order-section {
            margin-top: 30px;

            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 15px;
        }


        .back-cart-button {
            padding: 13px 22px;

            border: 1px solid #2d4358;
            border-radius: 6px;

            color: #2d4358;
            background: white;

            text-decoration: none;
            font-weight: bold;
        }


        .back-cart-button:hover {
            background: #f1f5f9;
        }


        .place-order-button {
            border: none;

            background: #28a745;
            color: white;

            padding: 14px 30px;

            border-radius: 6px;

            font-size: 17px;
            font-weight: bold;

            cursor: pointer;

            transition: 0.2s;
        }


        .place-order-button:hover {
            background: #218838;
            transform: translateY(-1px);
        }


        /* =========================================
           FOOTER
        ========================================== */

        footer {
            background: #2d4358;
            color: white;
            text-align: center;
            padding: 25px;
        }


        footer p {
            margin: 0;
            font-size: 16px;
        }


        /* =========================================
           RESPONSIVE DESIGN
        ========================================== */

        @media (max-width: 768px) {

            .header-container {
                padding: 18px 20px;
                flex-direction: column;
                gap: 18px;
            }


            .header-container h1 {
                font-size: 27px;
            }


            nav {
                gap: 20px;
            }


            main {
                padding: 30px 12px 50px;
            }


            .checkout-title {
                font-size: 30px;
            }


            .checkout-card {
                padding: 20px;
            }


            .shipping-form {
                grid-template-columns: 1fr;
            }


            .form-group.full-width {
                grid-column: auto;
            }


            .total-section {
                justify-content: space-between;
            }


            .place-order-section {
                flex-direction: column;
                align-items: stretch;
            }


            .back-cart-button,
            .place-order-button {
                width: 100%;
                text-align: center;
            }

        }


    </style>

</head>


<body>


<!-- =========================================
     HEADER
========================================== -->

<header>

    <div class="header-container">

        <h1>
            My Ecommerce Store
        </h1>


        <nav>

            <a href="../index.php">
                Home
            </a>

            <a href="cart.php">
                Cart
            </a>

            <a
                href="logout.php"
                class="logout-button"
            >
                Logout
            </a>

        </nav>

    </div>

</header>


<!-- =========================================
     MAIN
========================================== -->

<main>

    <div class="checkout-container">


        <h2 class="checkout-title">
            Checkout
        </h2>


        <!-- =====================================
             ORDER SUMMARY
        ====================================== -->

        <div class="checkout-card">

            <h3>
                Order Summary
            </h3>


            <div class="order-table-wrapper">

                <table class="order-table">

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

                    <?php foreach ($cart_items as $item): ?>

                        <tr>

                            <td class="product-name">

                                <?= htmlspecialchars(
                                    $item['name']
                                ); ?>

                            </td>


                            <td>

                                $<?= number_format(
                                    $item['price'],
                                    2
                                ); ?>

                            </td>


                            <td>

                                <?= $item['quantity']; ?>

                            </td>


                            <td>

                                $<?= number_format(
                                    $item['price'] *
                                    $item['quantity'],
                                    2
                                ); ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>


            <!-- TOTAL -->

            <div class="total-section">

                <span class="total-label">
                    Total:
                </span>

                <span class="total-price">

                    $<?= number_format(
                        $total_cost,
                        2
                    ); ?>

                </span>

            </div>

        </div>


        <!-- =====================================
             SHIPPING INFORMATION
        ====================================== -->

        <div class="checkout-card">

            <h3>
                Shipping Information
            </h3>


            <form
                method="POST"
                action="place_order.php"
                class="shipping-form"
            >


                <!-- FULL NAME -->

                <div class="form-group">

                    <label for="full_name">
                        Full Name
                    </label>

                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        placeholder="Enter your full name"
                        required
                    >

                </div>


                <!-- EMAIL -->

                <div class="form-group">

                    <label for="email">
                        Email Address
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        required
                    >

                </div>


                <!-- PHONE -->

                <div class="form-group">

                    <label for="phone">
                        Phone Number
                    </label>

                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        placeholder="Enter your phone number"
                        required
                    >

                </div>


                <!-- CITY -->

                <div class="form-group">

                    <label for="city">
                        City
                    </label>

                    <input
                        type="text"
                        id="city"
                        name="city"
                        placeholder="Enter your city"
                        required
                    >

                </div>


                <!-- STATE -->

                <div class="form-group">

                    <label for="state">
                        State
                    </label>

                    <input
                        type="text"
                        id="state"
                        name="state"
                        placeholder="Enter your state"
                        required
                    >

                </div>


                <!-- PINCODE -->

                <div class="form-group">

                    <label for="pincode">
                        Pincode
                    </label>

                    <input
                        type="text"
                        id="pincode"
                        name="pincode"
                        placeholder="Enter your pincode"
                        required
                    >

                </div>


                <!-- ADDRESS -->

                <div class="form-group full-width">

                    <label for="address">
                        Delivery Address
                    </label>

                    <textarea
                        id="address"
                        name="address"
                        placeholder="Enter your complete delivery address"
                        required
                    ></textarea>

                </div>


                <!-- BUTTONS -->

                <div class="place-order-section full-width">

                    <a
                        href="cart.php"
                        class="back-cart-button"
                    >
                        ← Back to Cart
                    </a>


                    <button
                        type="submit"
                        name="place_order"
                        class="place-order-button"
                    >
                        Place Order
                    </button>

                </div>


            </form>

        </div>

    </div>

</main>


<!-- =========================================
     FOOTER
========================================== -->

<footer>

    <p>
        &copy; <?= date("Y"); ?>
        My Ecommerce Store
    </p>

</footer>


</body>

</html>