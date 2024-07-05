<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: unauthorized.php");
    exit();
}
@include 'Admin Module/configshop.php';

if (isset($_POST['update_update_btn'])) {
    $update_value = $_POST['update_quantity'];
    $update_id = $_POST['update_quantity_id'];
    $update_quantity_query = mysqli_query($conn, "UPDATE `cart` SET quantity = '$update_value' WHERE id = '$update_id'");
    if ($update_quantity_query) {
        header('location:cart.php');
    }
}

if (isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    mysqli_query($conn, "DELETE FROM `cart` WHERE id = '$remove_id'");
    header('location:cart.php');
}

if (isset($_GET['delete_all'])) {
    mysqli_query($conn, "DELETE FROM `cart`");
    header('location:cart.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>@import url("navBar.css");</style>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            width: 94%;
            margin: 80px 0 0 40px;
        }

        .container {
            width: 100%;
            padding: 0;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .shopping-cart {
            border-radius: 5px;
        }

        td img {
            max-width: 100px;
            height: auto;
        }

        table {
            margin-bottom: 25px;
            border-collapse: collapse;
            width: 100%;
            font-size: 14px;
            text-align: center;
        }

        td, th {
            padding: 10px;
            border: 1px solid #ccc;
        }

        th {
            background-color: #142850;
            color: #fff;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .table-bottom {
            font-weight: bold;
        }

        .option-btn, .delete-btn, .btn, .update-btn {
            display: inline-block;
            padding: 5px 10px;
            text-decoration: none;
            color: #fff;
            background-color: #333;
            border-radius: 5px;
            transition: background-color 0.3s;
            font-size: 14px;
            border: none;
            cursor: pointer;
        }

        .option-btn:hover, .delete-btn:hover, .btn:hover, .update-btn:hover {
            background-color: #555;
        }

        .delete-btn {
            background-color: #e74c3c;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }

        .update-btn {
            background-color: #27ae60;
        }

        .update-btn:hover {
            background-color: #2ecc71;
        }

        .disabled {
            pointer-events: none;
            background-color: #ccc;
        }

        .action-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .checkout-btn {
            text-align: right;
        }
    </style>
</head>
<body style="background-color: #f1f2f6">
    <?php include 'navBar.php'; ?>
    
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>Shopping Cart</h1>
                <button class="delete-btn" data-toggle="modal" data-target="#deleteAllModal"> <i class="fas fa-trash"></i> Delete All </button>
            </div>

            <section class="shopping-cart">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $select_cart = mysqli_query($conn, "SELECT * FROM `cart`");
                        $grand_total = 0;
                        if (mysqli_num_rows($select_cart) > 0) {
                            while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
                        ?>
                        <tr>
                            <td><img src="Admin Module/uploaded_img/<?php echo $fetch_cart['image']; ?>" alt="Product Image"></td>
                            <td><?php echo $fetch_cart['name']; ?></td>
                            <td>$<?php echo number_format($fetch_cart['price']); ?>/-</td>
                            <td>
                                <form action="" method="post">
                                    <input type="hidden" name="update_quantity_id" value="<?php echo $fetch_cart['id']; ?>">
                                    <input type="number" name="update_quantity" min="1" value="<?php echo $fetch_cart['quantity']; ?>">
                                    <input type="submit" value="Update" name="update_update_btn" class="update-btn">
                                </form>   
                            </td>
                            <td>$<?php echo $sub_total = number_format($fetch_cart['price'] * $fetch_cart['quantity']); ?>/-</td>
                            <td>
                                <button class="delete-btn" data-toggle="modal" data-target="#removeModal" data-id="<?php echo $fetch_cart['id']; ?>"> <i class="fas fa-trash"></i> Remove</button>
                            </td>
                        </tr>
                        <?php
                            $grand_total += $sub_total;  
                            }
                        }
                        ?>
                        <tr class="table-bottom">
                            <td colspan="4"></td>
                            <td colspan="2">Grand Total: $<?php echo $grand_total; ?>/-</td>
                        </tr>
                    </tbody>
                </table>
                <div class="action-buttons">
                    <a href="products.php" class="option-btn">Continue Shopping</a>
                    <a href="checkout.php" class="btn <?= ($grand_total > 1) ? '' : 'disabled'; ?>">Proceed to Checkout</a>
                </div>
            </section>
        </div>
    </div>
   
    <!-- Modals -->
    <!-- Remove Item Modal -->
    <div class="modal fade" id="removeModal" tabindex="-1" aria-labelledby="removeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="removeModalLabel">Remove Item</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to remove this item from the cart?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <a href="#" id="removeConfirm" class="btn btn-danger">Remove</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete All Modal -->
    <div class="modal fade" id="deleteAllModal" tabindex="-1" aria-labelledby="deleteAllModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAllModalLabel">Delete All Items</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete all items from the cart?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <a href="cart.php?delete_all" class="btn btn-danger">Delete All</a>
                </div>
            </div>
        </div>
    </div>
   
    <!-- custom js file link  -->
    <script src="Admin Module/js/script.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $('#removeModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var modal = $(this);
            modal.find('#removeConfirm').attr('href', 'cart.php?remove=' + id);
        });
    </script>
</body>
</html>
