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
    SELECT *
    FROM products
    WHERE id = ?
");

$stmt->execute([$product_id]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);


// --------------------------------------------------
// PRODUCT NOT FOUND
// --------------------------------------------------

if (!$product) {
    die("Product not found.");
}


// --------------------------------------------------
// UPDATE PRODUCT
// --------------------------------------------------

if (isset($_POST['update_product'])) {

    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $description = trim($_POST['description']);


    // Current image
    $image = $product['image'];


    // Check whether a new image was uploaded

    if (
        isset($_FILES['image']) &&
        $_FILES['image']['error'] === UPLOAD_ERR_OK
    ) {

        $new_image = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];


        // Upload new image

        move_uploaded_file(
            $image_tmp,
            "../images/" . $new_image
        );


        // Use new image

        $image = $new_image;
    }


    // Update database

    $stmt = $conn->prepare("
        UPDATE products
        SET
            name = ?,
            price = ?,
            description = ?,
            image = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $name,
        $price,
        $description,
        $image,
        $product_id
    ]);


    // Go back to manage products

    header("Location: manage_products.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Edit Product | Admin
    </title>

    <link
        rel="stylesheet"
        href="../css/styles.css"
    >

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

            <a href="manage_products.php"
               class="active">

                Manage Products

            </a>

            <a href="manage_orders.php">

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

<main class="edit-product-page">

    <div class="edit-product-container">


        <div class="edit-product-header">

            <div>

                <a
                    href="manage_products.php"
                    class="back-products"
                >
                    ← Back to Products
                </a>

                <h2>
                    Edit Product
                </h2>

                <p>
                    Update your product information below.
                </p>

            </div>

        </div>


        <!-- ==================================================
             FORM
        ================================================== -->

        <div class="edit-product-card">

            <form
                method="POST"
                enctype="multipart/form-data"
            >


                <!-- PRODUCT NAME -->

                <div class="form-group">

                    <label for="name">
                        Product Name
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?= htmlspecialchars($product['name']); ?>"
                        required
                    >

                </div>


                <!-- PRICE -->

                <div class="form-group">

                    <label for="price">
                        Price
                    </label>

                    <input
                        type="number"
                        id="price"
                        name="price"
                        value="<?= htmlspecialchars($product['price']); ?>"
                        step="0.01"
                        min="0"
                        required
                    >

                </div>


                <!-- DESCRIPTION -->

                <div class="form-group">

                    <label for="description">
                        Description
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="6"
                        required
                    ><?= htmlspecialchars($product['description']); ?></textarea>

                </div>


                <!-- CURRENT IMAGE -->

                <div class="current-image-section">

                    <label>
                        Current Image
                    </label>

                    <div class="current-image">

                        <img
                            src="../images/<?= htmlspecialchars($product['image']); ?>"
                            alt="<?= htmlspecialchars($product['name']); ?>"
                        >

                    </div>

                </div>


                <!-- NEW IMAGE -->

                <div class="form-group">

                    <label for="image">
                        Replace Image
                    </label>

                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept="image/*"
                    >

                    <small>
                        Leave this empty if you want to keep
                        the current image.
                    </small>

                </div>


                <!-- BUTTONS -->

                <div class="edit-product-actions">

                    <a
                        href="manage_products.php"
                        class="cancel-button"
                    >
                        Cancel
                    </a>


                    <button
                        type="submit"
                        name="update_product"
                        class="save-product-button"
                    >
                        Save Changes
                    </button>

                </div>


            </form>

        </div>

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