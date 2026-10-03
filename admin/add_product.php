<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Connect to database
include '../includes/db.php';

// Add product
if (isset($_POST['add_product'])) {

    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    // Get uploaded image name
    $image = $_FILES['image']['name'];

    // Temporary location of uploaded image
    $image_tmp = $_FILES['image']['tmp_name'];

    // Upload image to images folder
    move_uploaded_file($image_tmp, "../images/" . $image);

    // Insert product into database
    $stmt = $conn->prepare("
        INSERT INTO products (name, price, description, image)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $name,
        $price,
        $description,
        $image
    ]);

    echo "<p style='color: green; text-align: center;'>
            Product added successfully!
          </p>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Product</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
        }

        .container {
            width: 50%;
            margin: 50px auto;
            background-color: #fff;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 5px;
            font-weight: bold;
        }

        input,
        textarea {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 15px;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #45a049;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            text-decoration: none;
            color: #4CAF50;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="container">

        <h2>Add Product</h2>

        <form method="POST" enctype="multipart/form-data">

            <label for="name">Product Name:</label>

            <input
                type="text"
                name="name"
                id="name"
                required
            >


            <label for="price">Price:</label>

            <input
                type="number"
                name="price"
                id="price"
                step="0.01"
                required
            >


            <label for="description">Description:</label>

            <textarea
                name="description"
                id="description"
                required
            ></textarea>


            <label for="image">Product Image:</label>

            <input
                type="file"
                name="image"
                id="image"
                accept="image/*"
                required
            >


            <button type="submit" name="add_product">
                Add Product
            </button>

        </form>


        <div class="back-link">

            <a href="dashboard.php">
                Back to Dashboard
            </a>

            <br><br>

            <a href="manage_products.php">
                Manage Products
            </a>

        </div>

    </div>

</body>

</html>