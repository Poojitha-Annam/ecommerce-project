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
// CHECK PRODUCT ID
// --------------------------------------------------

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_products.php");
    exit();
}

$product_id = (int) $_GET['id'];


// --------------------------------------------------
// GET PRODUCT
// --------------------------------------------------

$stmt = $conn->prepare("
    SELECT image
    FROM products
    WHERE id = ?
");

$stmt->execute([$product_id]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);


// --------------------------------------------------
// PRODUCT NOT FOUND
// --------------------------------------------------

if (!$product) {
    header("Location: manage_products.php");
    exit();
}


// --------------------------------------------------
// DELETE PRODUCT
// --------------------------------------------------

$stmt = $conn->prepare("
    DELETE FROM products
    WHERE id = ?
");

$stmt->execute([$product_id]);


// --------------------------------------------------
// DELETE PRODUCT IMAGE
// --------------------------------------------------

if (!empty($product['image'])) {

    $image_path = "../images/" . $product['image'];

    if (file_exists($image_path)) {
        unlink($image_path);
    }
}


// --------------------------------------------------
// GO BACK TO PRODUCTS
// --------------------------------------------------

header("Location: manage_products.php");
exit();

?>