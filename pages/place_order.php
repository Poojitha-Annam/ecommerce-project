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
// CHECK FORM SUBMISSION
// --------------------------------------------------

if (!isset($_POST['place_order'])) {
    header("Location: checkout.php");
    exit();
}


// --------------------------------------------------
// GET CUSTOMER DETAILS
// --------------------------------------------------

$full_name = trim($_POST['full_name']);
$email     = trim($_POST['email']);
$phone     = trim($_POST['phone']);
$address   = trim($_POST['address']);
$city      = trim($_POST['city']);
$state     = trim($_POST['state']);
$pincode   = trim($_POST['pincode']);


// --------------------------------------------------
// BASIC VALIDATION
// --------------------------------------------------

if (
    empty($full_name) ||
    empty($email) ||
    empty($phone) ||
    empty($address) ||
    empty($city) ||
    empty($state) ||
    empty($pincode)
) {
    die("Please fill in all required fields.");
}


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
// CHECK CART
// --------------------------------------------------

if (empty($cart_items)) {
    die("Your cart is empty.");
}


// --------------------------------------------------
// CALCULATE TOTAL
// --------------------------------------------------

$total_amount = 0;

foreach ($cart_items as $item) {

    $total_amount +=
        $item['price'] * $item['quantity'];
}


// --------------------------------------------------
// START DATABASE TRANSACTION
// --------------------------------------------------

try {

    $conn->beginTransaction();


    // --------------------------------------------------
    // CREATE ORDER
    // --------------------------------------------------

    $stmt = $conn->prepare("
        INSERT INTO orders
        (
            user_id,
            full_name,
            email,
            phone,
            address,
            city,
            state,
            pincode,
            total_amount,
            status
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')
    ");

    $stmt->execute([
        $user_id,
        $full_name,
        $email,
        $phone,
        $address,
        $city,
        $state,
        $pincode,
        $total_amount
    ]);


    // Get the newly created order ID
    $order_id = $conn->lastInsertId();


    // --------------------------------------------------
    // SAVE ORDER ITEMS
    // --------------------------------------------------

    $stmt = $conn->prepare("
        INSERT INTO order_items
        (
            order_id,
            product_id,
            product_name,
            price,
            quantity,
            subtotal
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ");


    foreach ($cart_items as $item) {

        $subtotal =
            $item['price'] * $item['quantity'];

        $stmt->execute([
            $order_id,
            $item['product_id'],
            $item['name'],
            $item['price'],
            $item['quantity'],
            $subtotal
        ]);
    }


    // --------------------------------------------------
    // CLEAR CUSTOMER CART
    // --------------------------------------------------

    $stmt = $conn->prepare("
        DELETE FROM cart
        WHERE user_id = ?
    ");

    $stmt->execute([$user_id]);


    // --------------------------------------------------
    // COMPLETE TRANSACTION
    // --------------------------------------------------

    $conn->commit();


} catch (Exception $e) {

    // Something went wrong → undo database changes

    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    die("Order could not be placed. Please try again.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Order Placed</title>

    <link rel="stylesheet" href="../css/styles.css">

</head>


<body>

<header>

    <div class="header-container">

        <h1>My Ecommerce Store</h1>

        <nav>

            <a href="../index.php">
                Home
            </a>

            <a href="orders.php">
                My Orders
            </a>

            <a href="logout.php"
               class="logout-button">
                Logout
            </a>

        </nav>

    </div>

</header>


<main>

    <div class="cart-container">

        <h2>Order Placed Successfully! 🎉</h2>

        <p>
            Thank you for your order,
            <strong><?= htmlspecialchars($full_name); ?></strong>.
        </p>

        <p>
            Your Order ID is:
            <strong>#<?= htmlspecialchars($order_id); ?></strong>
        </p>

        <p>
            Order Status:
            <strong>Pending</strong>
        </p>

        <p>
            Total Amount:
            <strong>
                $<?= number_format($total_amount, 2); ?>
            </strong>
        </p>


        <div class="cart-actions">

            <a href="orders.php"
               class="checkout-button">
                View My Orders
            </a>

            <a href="../index.php"
               class="continue-shopping">
                Continue Shopping
            </a>

        </div>

    </div>

</main>


<footer>

    <p>
        &copy; <?= date("Y"); ?> My Ecommerce Store
    </p>

</footer>

</body>

</html>