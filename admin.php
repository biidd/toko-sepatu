<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('location:login.php');
    exit;
}
// echo "Debug: Logged in as admin."; // Removed debug message

if (isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    mysqli_query($conn, "DELETE FROM `cart` WHERE id = '$remove_id'") or die('query failed');
    header('location:admin.php');
    exit;
}

if (isset($_GET['delete_all_user'])) {
    $user_id = $_GET['delete_all_user'];
    mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
    header('location:admin.php');
    exit;
}

$users = mysqli_query($conn, "SELECT * FROM `user_info` WHERE id != '{$_SESSION['user_id']}'") or die('query failed');

?>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('location:login.php');
    exit;
}

if (isset($_POST['add_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $image_folder = 'image/' . $image;

    if (empty($name) || $price <= 0 || $stock < 0 || empty($category) || empty($image)) {
        $message[] = 'Please fill all fields correctly!';
    } else {
        // Check if product with same name exists
        $check_product = mysqli_query($conn, "SELECT * FROM products WHERE name = '$name'") or die('query failed');
        if (mysqli_num_rows($check_product) > 0) {
            $message[] = 'Product with this name already exists!';
        } else {
            move_uploaded_file($image_tmp, $image_folder);
            mysqli_query($conn, "INSERT INTO products (name, price, stock, image, category) VALUES ('$name', '$price', '$stock', '$image', '$category')") or die('query failed');
            $message[] = 'Product added successfully!';
        }
    }
}

if (isset($_GET['delete_product'])) {
    $delete_id = $_GET['delete_product'];
    mysqli_query($conn, "DELETE FROM products WHERE id = '$delete_id'") or die('query failed');
    header('location:admin.php');
    exit;
}

/* Added update_product handler to process product updates */
if (isset($_POST['update_product'])) {
    $update_id = $_POST['product_id'];
    $update_name = mysqli_real_escape_string($conn, $_POST['update_name']);
    $update_category = mysqli_real_escape_string($conn, $_POST['update_category']);
    $update_price = floatval($_POST['update_price']);
    $update_stock = intval($_POST['update_stock']);

    $update_image = $_FILES['update_image']['name'];
    $update_image_tmp = $_FILES['update_image']['tmp_name'];
    $update_image_folder = 'image/' . $update_image;

    if (!empty($update_image)) {
        if (!move_uploaded_file($update_image_tmp, $update_image_folder)) {
            $message[] = 'Failed to upload image.';
        }
        $update_query = "UPDATE products SET name = '$update_name', category = '$update_category', price = '$update_price', stock = '$update_stock', image = '$update_image' WHERE id = '$update_id'";
    } else {
        $update_query = "UPDATE products SET name = '$update_name', category = '$update_category', price = '$update_price', stock = '$update_stock' WHERE id = '$update_id'";
    }

    if (!mysqli_query($conn, $update_query)) {
        $message[] = 'Failed to update product: ' . mysqli_error($conn);
    } else {
        $message[] = 'Product updated successfully!';
    }
}

$products = mysqli_query($conn, "SELECT * FROM products") or die('query failed');

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard - Manage Products</title>
    <link rel="stylesheet" href="assets/css/styles.css" />
</head>

<body>
    <header class="admin-header">
        <h1>Admin Dashboard - Manage Products</h1>
    </header>
    <nav class="admin-nav">
        <div>Welcome, Admin</div>
        <div>
            <a href="cart.php?logout=<?php echo $_SESSION['user_id']; ?>" onclick="return confirm('Are you sure you want to logout?');" class="admin-btn admin-btn-danger">Logout</a>
            <a href="index.php" class="admin-btn">Home</a>
        </div>
    </nav>
    <div class="admin-container">
        <?php
        if (isset($message)) {
            foreach ($message as $msg) {
                echo '<div class="admin-message">' . htmlspecialchars($msg) . '</div>';
            }
        }
        ?>

        <h2>Manage User Carts</h2>
        <?php while ($user = mysqli_fetch_assoc($users)) : ?>
            <div class="user-cart">
                <h3>User: <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)</h3>
                <?php
                $cart_items = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '{$user['id']}'") or die('query failed');
                if (mysqli_num_rows($cart_items) > 0) {
                    $grand_total = 0;
                ?>
                    <table class="admin-table" border="1" cellpadding="5" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Size</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = mysqli_fetch_assoc($cart_items)) :
                                $subtotal = $item['price'] * $item['quantity'];
                                $grand_total += $subtotal;
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['size']); ?></td>
                                    <td>Rp. <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                    <td><?php echo (int)$item['quantity']; ?></td>
                                    <td>Rp. <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                                    <td><a href="admin.php?remove=<?php echo $item['id']; ?>" onclick="return confirm('Remove this item?');" class="admin-btn admin-btn-danger">Remove</a></td>
                                </tr>
                            <?php endwhile; ?>
                            <tr>
                                <td colspan="4"><strong>Grand Total</strong></td>
                                <td><strong>Rp. <?php echo number_format($grand_total, 0, ',', '.'); ?></strong></td>
                                <td><a href="admin.php?delete_all_user=<?php echo $user['id']; ?>" onclick="return confirm('Delete all items for this user?');" class="admin-btn admin-btn-danger">Clear Cart</a></td>
                            </tr>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <p>No items in cart.</p>
                <?php } ?>
            </div>
        <?php endwhile; ?>

        <h2>Manage Products</h2>
        <form class="admin-form" action="" method="post" enctype="multipart/form-data">
            <h3>Add New Product</h3>
            <input type="text" name="name" placeholder="Product Name" required />
            <select name="category" required>
                <option value="" disabled selected>Select Category</option>
                <option value="Casual">Casual</option>
                <option value="Sports">Sports</option>
                <option value="Running">Running</option>
                <option value="Outdoor">Outdoor</option>
                <option value="Formal">Formal</option>
            </select>
            <input type="number" name="price" placeholder="Price" step="0.01" min="0" required />
            <input type="number" name="stock" placeholder="Stock" min="0" required />
            <input type="file" name="image" accept="image/*" required />
            <input type="submit" name="add_product" value="Add Product" />
        </form>

        <h3>Existing Products</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Update Stock</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = mysqli_fetch_assoc($products)) : ?>
                    <tr>
                        <td><img src="image/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 80px; height: auto; border-radius: 4px;" /></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['category']); ?></td>
                        <td>Rp. <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                        <td><?php echo (int)$product['stock']; ?></td>
                        <td>
                            <form action="" method="post" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 10px; align-items: flex-start;">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>" />
                                <input type="text" name="update_name" value="<?php echo htmlspecialchars($product['name']); ?>" required />
                                <select name="update_category" required>
                                    <option value="Casual" <?php if ($product['category'] === 'Casual') echo 'selected'; ?>>Casual</option>
                                    <option value="Sports" <?php if ($product['category'] === 'Sports') echo 'selected'; ?>>Sports</option>
                                    <option value="Running" <?php if ($product['category'] === 'Running') echo 'selected'; ?>>Running</option>
                                    <option value="Outdoor" <?php if ($product['category'] === 'Outdoor') echo 'selected'; ?>>Outdoor</option>
                                    <option value="Formal" <?php if ($product['category'] === 'Formal') echo 'selected'; ?>>Formal</option>
                                </select>
                                <input type="number" name="update_price" value="<?php echo $product['price']; ?>" step="0.01" min="0" required />
                                <input type="number" name="update_stock" value="<?php echo (int)$product['stock']; ?>" min="0" required />
                                <input type="file" name="update_image" accept="image/*" />
                                <input type="submit" name="update_product" value="Update" class="admin-btn" />
                            </form>
                        </td>
                        <td>
                            <a href="admin.php?delete_product=<?php echo $product['id']; ?>" onclick="return confirm('Delete this product?');" class="admin-btn admin-btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
