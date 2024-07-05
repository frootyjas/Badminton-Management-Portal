<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: unauthorized.php");
    exit();
}

@include '../../php/admin-module/configshop.php';

if(isset($_POST['add_to_cart'])){
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_image = $_POST['product_image'];
    $product_quantity = 1;

    $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE name = '$product_name'");

    if(mysqli_num_rows($select_cart) > 0){
        $message[] = 'Product already added to cart';
    } else {
        $insert_product = mysqli_query($conn, "INSERT INTO `cart`(name, price, image, quantity) VALUES('$product_name', '$product_price', '$product_image', '$product_quantity')");
        $message[] = 'Product added to cart successfully';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <style>@import url("../css/navBar.css");</style>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            width: 94%;
            margin: 80px auto 0; /* Centered horizontally */
        }

        .container {
            width: 100%;
            padding: 0;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header .cart {
            position: relative;
            color: #000;
            font-size: 30px; /* Increased font size for better visibility */
            text-decoration: none;
            margin-right: 20px;
            display: flex;
            align-items: center;
        }

        .header .cart i {
            margin-right: 5px; /* Optional: adds space between the icon and text */
        }

        .header .cart span {
            background-color: #0c7b93;
            color: #fff;
            border-radius: 50%;
            padding: 5px 10px;
            font-size: 16px; /* Adjusted font size for the cart count */
            position: absolute;
            top: -10px;
            right: -15px; /* Adjusted positioning for better alignment */
        }

        .box-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .box {
            flex: 1 1 calc(20% - 20px); /* Adjusted to fit 5 items per row */
            box-sizing: border-box;
            border: 1px solid #ccc;
            padding: 20px;
            text-align: center;
            background-color: #fff;
        }

        .box img {
            width: 150px; /* Set fixed width */
            height: 150px; /* Set fixed height */
            object-fit: cover; /* Ensure images cover the area */
            display: block;
            margin: 0 auto;
        }

        .box h3 {
            font-size: 1.2em;
            margin: 10px 0;
        }

        .price {
            font-size: 1.1em;
            color: #000;
            margin-bottom: 10px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            border: none;
            outline: none;
            color: #fff;
            font-size: 13px;
            border-radius: 5px;
            background-color: #142850;
            transition: all 0.1s linear;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #0c7b93;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="background-color: #f1f2f6">
    <?php include 'navBar.php'; ?>
    
    <div class="wrapper">
        <div class="header">
            <h1>Latest Products</h1>
            <?php
            $select_rows = mysqli_query($conn, "SELECT * FROM `cart`") or die('query failed');
            $row_count = mysqli_num_rows($select_rows);
            ?>
            <a href="cart.php" class="cart"><i class="fa fa-shopping-cart"></i> <span><?php echo $row_count; ?></span></a>
        </div>

        <!--<?php
            if(isset($message)){
                foreach($message as $message){
                    echo '<div class="message"><span>'.$message.'</span> <i class="fas fa-times" onclick="this.parentElement.style.display = `none`;"></i> </div>';
                }
            }
        ?>-->

        <div class="container">
            <section class="products">
                <div class="box-container">
                    <?php
                    $select_products = mysqli_query($conn, "SELECT * FROM `products`");
                    if(mysqli_num_rows($select_products) > 0){
                        while($fetch_product = mysqli_fetch_assoc($select_products)){
                    ?>
                    <form action="" method="post">
                        <div class="box">
                            <img src="../../admin-module/uploaded_img/<?php echo $fetch_product['image']; ?>" alt="">
                            <h3><?php echo $fetch_product['name']; ?></h3>
                            <div class="price">$<?php echo $fetch_product['price']; ?>/-</div>
                            <input type="hidden" name="product_name" value="<?php echo $fetch_product['name']; ?>">
                            <input type="hidden" name="product_price" value="<?php echo $fetch_product['price']; ?>">
                            <input type="hidden" name="product_image" value="<?php echo $fetch_product['image']; ?>">
                            <input type="submit" class="btn" value="Add to Cart" name="add_to_cart">
                        </div>
                    </form>
                    <?php
                        }
                    }
                    ?>
                </div>
            </section>
        </div>
    </div>

    <!-- Custom JS file link -->
    <script src="../../admin-module/js/shop.js"></script>
</body>
</html>
