<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: unaccessible.php");
    exit();
}
@include 'configshop.php';

if(isset($_POST['add_product'])){
   $p_name = $_POST['p_name'];
   $p_price = $_POST['p_price'];
   $p_image = $_FILES['p_image']['name'];
   $p_image_tmp_name = $_FILES['p_image']['tmp_name'];
   $p_image_folder = 'uploaded_img/'.$p_image;

   $insert_query = mysqli_query($conn, "INSERT INTO `products`(name, price, image) VALUES('$p_name', '$p_price', '$p_image')") or die('query failed');

   if($insert_query){
      move_uploaded_file($p_image_tmp_name, $p_image_folder);
      $message[] = 'product added successfully';
   }else{
      $message[] = 'could not add the product';
   }
};

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_query = mysqli_query($conn, "DELETE FROM `products` WHERE id = $delete_id ") or die('query failed');
   if($delete_query){
      header('location:adminshop.php');
      $message[] = 'product has been deleted';
   }else{
      header('location:adminshop.php');
      $message[] = 'product could not be deleted';
   };
};

if(isset($_POST['update_product'])){
   $update_p_id = $_POST['update_p_id'];
   $update_p_name = $_POST['update_p_name'];
   $update_p_price = $_POST['update_p_price'];
   $update_p_image = $_FILES['update_p_image']['name'];
   $update_p_image_tmp_name = $_FILES['update_p_image']['tmp_name'];
   $update_p_image_folder = 'uploaded_img/'.$update_p_image;

   $update_query = mysqli_query($conn, "UPDATE `products` SET name = '$update_p_name', price = '$update_p_price', image = '$update_p_image' WHERE id = '$update_p_id'");

   if($update_query){
      move_uploaded_file($update_p_image_tmp_name, $update_p_image_folder);
      $message[] = 'product updated successfully';
      header('location:adminshop.php');
   }else{
      $message[] = 'product could not be updated';
      header('location:adminshop.php');
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Panel</title>
   <style>@import url("sideNavAdmin.css");</style>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/shop.css">
   <style>
      @import url("https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap");

      body {
         font-family: 'Poppins', sans-serif;
         background-color: #f1f2f6;
         margin: 0;
         padding: 0;
      }

      .navigationBar {
         background-color: #fff;
         width: 100%;
         display: flex;
         color: hsl(0, 0%, 33%);
         box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
         padding: 10px;
         margin: 0;
      }

      .navigationBar h1 {
         font-size: 32px;
         margin-left: 20px;
         color: black;
      }

      .caa-wrapper {
         margin: 0 0 0 220px;
         width: calc(100% - 220px);
         height: calc(100vh - 0px);
         overflow-x: hidden;
      }

      .container {
         width: 85%;
         margin: 20px auto; /* Move the container up */
      }

      .form-group {
         margin-bottom: 20px;
      }

      label {
         display: block;
         margin-bottom: 5px;
         font-size: 16px;
      }

      input[type="text"], input[type="number"], input[type="file"], textarea {
         width: 100%;
         padding: 10px;
         border: 1px solid #ccc; /* Change border color for better visibility */
         border-radius: 5px;
         outline: none;
         font-size: 14px;
         font-weight: 400;
         background-color: #fff; /* Ensure input fields have a white background */
         color: #000; /* Ensure text color is black */
         box-sizing: border-box;
      }

      input[type="submit"], .btn, .delete-btn, .option-btn {
         padding: 8px 12px;
         background-color: #142850;
         border-radius: 5px;
         color: white;
         border: none;
         cursor: pointer;
         font-size: 14px;
      }

      input[type="submit"]:hover, .btn:hover, .delete-btn:hover, .option-btn:hover {
         background-color: #0c7b93;
      }

      .btn-primary {
         background-color: #142850;
         border-color: #142850;
         padding: 6px 10px;
         font-size: 11px;
      }

      .btn-primary:hover {
         background-color: #0c7b93;
         border-color: #0c7b93;
      }

      .btn-primary i {
         margin-right: 5px;
      }

      table {
         width: 100%;
         border-collapse: collapse;
         margin: 20px 0;
         box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      }

      table, th, td {
         border: 1px solid #ddd;
      }

      th, td {
         padding: 12px;
         text-align: left;
      }

      .display-product-table table thead th {
         background-color: #142850;
         color: white;
      }

      .empty {
         text-align: center;
         margin: 20px 0;
         color: #888;
      }

      .message {
         background-color: #f8d7da;
         color: #721c24;
         padding: 10px;
         margin: 20px 0;
         border-radius: 5px;
         display: flex;
         justify-content: space-between;
         align-items: center;
      }

      .message i {
         cursor: pointer;
      }

      .edit-form-container {
         display: none;
         position: fixed;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background-color: rgba(0, 0, 0, 0.5);
         justify-content: center;
         align-items: center;
      }

      .edit-form-container form {
         background-color: white;
         padding: 20px;
         border-radius: 5px;
         box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      }

      .edit-form-container img {
         max-width: 100%;
         height: auto;
         margin-bottom: 20px;
      }

      .edit-form-container .box {
         width: calc(100% - 22px);
         margin-bottom: 20px;
      }

      .add-product-form, .edit-form-container form {
         background-color: #ffffff;
         padding: 20px;
         border-radius: 10px;
         box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      }

      .delete-btn {
         background-color: #dc3545;
      }

      .delete-btn:hover {
         background-color: #c82333;
      }

      .option-btn {
         background-color: #28a745;
      }

      .option-btn:hover {
         background-color: #218838;
      }
   </style>
</head>
<body style="background-color: #f1f2f6">
   <div id="navBar">
        <?php include 'sideNavAdmin.php'; ?>
    </div>
    <div class="caa-wrapper">
        <div class="row">
            <div class="col">
                <div class="navigationBar">
                    <h1>Manage Products</h1>
                </div>
            </div>
        </div>
        <div class="container">
            <?php
            if(isset($message)){
                foreach($message as $message){
                    echo '<div class="message"><span>'.$message.'</span> <i class="fas fa-times" onclick="this.parentElement.style.display = `none`;"></i> </div>';
                };
            };
            ?>
            <section>
                <form action="" method="post" class="add-product-form" enctype="multipart/form-data">
                    <h3>Add a New Product</h3>
                    <div class="form-group">
                        <label for="p_name">Product Name:</label>
                        <input type="text" id="p_name" name="p_name" placeholder="Enter the product name" class="box" required>
                    </div>
                    <div class="form-group">
                        <label for="p_price">Product Price:</label>
                        <input type="number" id="p_price" name="p_price" min="0" placeholder="Enter the product price" class="box" required>
                    </div>
                    <div class="form-group">
                        <label for="p_image">Product Image:</label>
                        <input type="file" id="p_image" name="p_image" accept="image/png, image/jpg, image/jpeg" class="box" required>
                    </div>
                    <input type="submit" value="Add the Product" name="add_product" class="btn">
                </form>
            </section>

            <section class="display-product-table">
                <table>
                    <thead>
                        <th>Product Image</th>
                        <th>Product Name</th>
                        <th>Product Price</th>
                        <th>Action</th>
                    </thead>
                    <tbody>
                        <?php
                        $select_products = mysqli_query($conn, "SELECT * FROM `products`");
                        if(mysqli_num_rows($select_products) > 0){
                            while($row = mysqli_fetch_assoc($select_products)){
                        ?>
                        <tr>
                            <td><img src="uploaded_img/<?php echo $row['image']; ?>" height="100" alt=""></td>
                            <td><?php echo $row['name']; ?></td>
                            <td>$<?php echo $row['price']; ?>/-</td>
                            <td>
                                <a href="adminshop.php?delete=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this?');"> <i class="fas fa-trash"></i> Delete </a>
                                <a href="adminshop.php?edit=<?php echo $row['id']; ?>" class="option-btn"> <i class="fas fa-edit"></i> Update </a>
                            </td>
                        </tr>
                        <?php
                            };
                        }else{
                            echo '<tr><td colspan="4" class="empty">no product added</td></tr>';
                        };
                        ?>
                    </tbody>
                </table>
            </section>

            <section class="edit-form-container">
                <?php
                if(isset($_GET['edit'])){
                    $edit_id = $_GET['edit'];
                    $edit_query = mysqli_query($conn, "SELECT * FROM `products` WHERE id = $edit_id");
                    if(mysqli_num_rows($edit_query) > 0){
                        while($fetch_edit = mysqli_fetch_assoc($edit_query)){
                ?>
                <form action="" method="post" enctype="multipart/form-data">
                    <img src="uploaded_img/<?php echo $fetch_edit['image']; ?>" height="200" alt="">
                    <input type="hidden" name="update_p_id" value="<?php echo $fetch_edit['id']; ?>">
                    <input type="text" class="box" required name="update_p_name" value="<?php echo $fetch_edit['name']; ?>">
                    <input type="number" min="0" class="box" required name="update_p_price" value="<?php echo $fetch_edit['price']; ?>">
                    <input type="file" class="box" required name="update_p_image" accept="image/png, image/jpg, image/jpeg">
                    <input type="submit" value="Update the Product" name="update_product" class="btn">
                    <input type="reset" value="Cancel" id="close-edit" class="option-btn">
                </form>
                <?php
                        };
                    };
                    echo "<script>document.querySelector('.edit-form-container').style.display = 'flex';</script>";
                };
                ?>
            </section>
        </div>
    </div>
</body>
</html>
