<?php

include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('location:login.php');
    exit;
}
$user_id = $_SESSION['user_id'];

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('location:index.php');
    exit;
};

if (isset($_POST['add_to_cart'])) {

    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_image = $_POST['product_image'];
    $product_quantity = $_POST['product_quantity'];
    $product_size = $_POST['product_size'];

    // Get category from products
    $product_query = mysqli_query($conn, "SELECT category, stock FROM products WHERE name = '$product_name'") or die('query failed');
    $product_data = mysqli_fetch_assoc($product_query);
    $product_category = $product_data['category'];
    $available_stock = $product_data['stock'];

    if ($available_stock < $product_quantity) {
        $message[] = 'stok tidak mencukupi untuk pesanan ini!';
    } else {
        $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE name = '$product_name' AND size = '$product_size' AND user_id = '$user_id'") or die('query failed');

        if (mysqli_num_rows($select_cart) > 0) {
            $message[] = 'Product already added to cart!';
        } else {
            mysqli_query($conn, "INSERT INTO `cart`(user_id, name, price, image, quantity, size, category) VALUES('$user_id', '$product_name', '$product_price', '$product_image', '$product_quantity', '$product_size', '$product_category')") or die('query failed');
            // Do not update product stock here, stock will be updated only when cart quantity is updated
            $message[] = 'Product added to cart!';
        }
    }
};

if (isset($_POST['update_cart'])) {
    $update_quantity = $_POST['cart_quantity'];
    $update_id = $_POST['cart_id'];

    // Get current quantity and product id from cart
    $cart_item = mysqli_query($conn, "SELECT * FROM cart WHERE id = '$update_id'") or die('query failed');
    if (mysqli_num_rows($cart_item) > 0) {
        $item = mysqli_fetch_assoc($cart_item);
        $product_name = $item['name'];
        $current_quantity = $item['quantity'];
        $quantity_diff = $update_quantity - $current_quantity;

        // Check if stock is sufficient
        $product = mysqli_query($conn, "SELECT stock FROM products WHERE name = '$product_name'") or die('query failed');
        $product_data = mysqli_fetch_assoc($product);

        // Debug logs
        error_log("Current quantity in cart: $current_quantity");
        error_log("Requested update quantity: $update_quantity");
        error_log("Quantity difference: $quantity_diff");
        error_log("Available stock: " . $product_data['stock']);

        if ($quantity_diff > 0 && $product_data['stock'] < $quantity_diff) {
            error_log("Stock habis! Available stock: " . $product_data['stock'] . ", Quantity diff: $quantity_diff");
            $message[] = 'Stock habis!';
        } else {
            // Update cart quantity
            mysqli_query($conn, "UPDATE `cart` SET quantity = '$update_quantity' WHERE id = '$update_id'") or die('query failed');
            // Update product stock
            if ($quantity_diff > 0) {
                // Reduce stock by quantity_diff
                mysqli_query($conn, "UPDATE products SET stock = stock - $quantity_diff WHERE name = '$product_name'") or die('query failed');
            } else if ($quantity_diff < 0) {
                // Increase stock by absolute quantity_diff
                $quantity_diff_abs = abs($quantity_diff);
                mysqli_query($conn, "UPDATE products SET stock = stock + $quantity_diff_abs WHERE name = '$product_name'") or die('query failed');
            }
            $message[] = 'Cart quantity updated successfully!';
        }
    }
}

if (isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    mysqli_query($conn, "DELETE FROM `cart` WHERE id = '$remove_id'") or die('query failed');
    header('location:cart.php');
}

if (isset($_GET['delete_all'])) {
    mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
    header('location:cart.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>shopping cart</title>

    <!-- custom css file link  -->
    <link rel="stylesheet" href="assets/css/styles.css">

</head>

<body>

    <?php
    if (isset($message)) {
        foreach ($message as $message) {
            echo '<div class="message" onclick="this.remove();">' . $message . '</div>';
        }
    }
    ?>

    <div class="container">

        <section class="user-profile-section">
            <div class="user-profile">
                <?php
                $select_user = mysqli_query($conn, "SELECT * FROM `user_info` WHERE id = '$user_id'") or die('query failed');
                if (mysqli_num_rows($select_user) > 0) {
                    $fetch_user = mysqli_fetch_assoc($select_user);
                } else {
                    $fetch_user = null;
                }
                ?>
                <h2>Welcome, <span><?php echo $fetch_user ? htmlspecialchars($fetch_user['name']) : 'Guest'; ?></span></h2>
                <p>Email: <span><?php echo $fetch_user ? htmlspecialchars($fetch_user['email']) : '-'; ?></span></p>
                <div class="profile-actions">
                    <a href="index.php" class="btn primary">Home</a>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') : ?>
                        <a href="admin.php" class="btn secondary">Admin Dashboard</a>
                    <?php endif; ?>
                    <a href="cart.php?logout=1" onclick="return confirm('Are you sure you want to logout?');" class="btn danger">Logout</a>
                </div>
            </div>
        </section>

        <section class="products-section">
            <h1 class="section-heading">Our Products</h1>
            <div class="product-grid">
                <?php
                $select_product = mysqli_query($conn, "SELECT * FROM `products`") or die('query failed');
                if (mysqli_num_rows($select_product) > 0) {
                    while ($fetch_product = mysqli_fetch_assoc($select_product)) {
                ?>
                        <div class="product-card">
                            <form method="post" class="product-form" action="">
                                <div class="product-image">
                                    <img src="image/<?php echo $fetch_product['image']; ?>" alt="<?php echo $fetch_product['name']; ?>">
                                    <div class="product-price">Rp. <?php echo number_format($fetch_product['price'], 0, ',', '.'); ?></div>
                                </div>
                                <div class="product-info">
                                    <h3 class="product-name"><?php echo $fetch_product['name']; ?></h3>
                                    <p class="product-category"><?php echo $fetch_product['category']; ?></p>
                                    <div class="product-controls">
                                        <input type="number" min="1" name="product_quantity" value="1" class="quantity-input">
                                        <select name="product_size" required class="size-select">
                                            <option value="">Size</option>
                                            <option value="38">38</option>
                                            <option value="39">39</option>
                                            <option value="40">40</option>
                                            <option value="41">41</option>
                                            <option value="42">42</option>
                                            <option value="43">43</option>
                                            <option value="44">44</option>
                                        </select>
                                    </div>
                                    <input type="hidden" name="product_image" value="<?php echo $fetch_product['image']; ?>">
                                    <input type="hidden" name="product_name" value="<?php echo $fetch_product['name']; ?>">
                                    <input type="hidden" name="product_price" value="<?php echo $fetch_product['price']; ?>">
                                    <button type="submit" name="add_to_cart" class="btn primary add-to-cart">Add to Cart</button>
                                </div>
                            </form>
                        </div>
                <?php
                    };
                };
                ?>
            </div>
        </section>

        <section class="cart-section">
            <h1 class="section-heading">Shopping Cart</h1>
            <div class="cart-content">
                <?php
                $cart_query = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
                $grand_total = 0;
                if (mysqli_num_rows($cart_query) > 0) {
                ?>
                    <div class="cart-items">
                        <?php
                        while ($fetch_cart = mysqli_fetch_assoc($cart_query)) {
                        ?>
                            <div class="cart-item">
                                <div class="item-image">
                                    <img src="image/<?php echo $fetch_cart['image']; ?>" alt="<?php echo $fetch_cart['name']; ?>">
                                </div>
                                <div class="item-details">
                                    <h3><?php echo $fetch_cart['name']; ?></h3>
                                    <p class="item-category">Category: <?php echo $fetch_cart['category']; ?></p>
                                    <p class="item-size">Size: <?php echo $fetch_cart['size']; ?></p>
                                    <p class="item-price">Price: Rp. <?php echo number_format($fetch_cart['price'], 0, ',', '.'); ?></p>
                                    <form action="" method="post" class="quantity-update">
                                        <input type="hidden" name="cart_id" value="<?php echo $fetch_cart['id']; ?>">
                                        <div class="quantity-controls">
                                            <input type="number" min="1" name="cart_quantity" value="<?php echo $fetch_cart['quantity']; ?>" class="quantity-input">
                                            <button type="submit" name="update_cart" class="btn secondary update-btn">Update</button>
                                        </div>
                                    </form>
                                    <p class="item-total">Subtotal: Rp. <?php echo number_format($sub_total = ((float)$fetch_cart['price'] * (int)$fetch_cart['quantity']), 0, ',', '.'); ?></p>
                                    <a href="cart.php?remove=<?php echo $fetch_cart['id']; ?>" class="btn danger remove-btn" onclick="return confirm('Remove item from cart?');">Remove</a>
                                </div>
                            </div>
                        <?php
                            $grand_total += $sub_total;
                        }
                        ?>
                    </div>

                    <div class="cart-summary">
                        <div class="summary-row">
                            <span class="summary-label">Grand Total:</span>
                            <span class="summary-value">Rp. <?php echo number_format($grand_total, 0, ',', '.'); ?></span>
                        </div>
                        <div class="summary-actions">
                            <a href="cart.php?delete_all" onclick="return confirm('Delete all from cart?');" class="btn danger clear-cart <?php echo ($grand_total > 1) ? '' : 'disabled'; ?>">Clear Cart</a>
                            <?php
                            $checkout_message = "Halo mimin, saya ingin checkout:\n\n";
                            $checkout_message .= "Nama: " . $fetch_user['name'] . "\n";
                            $checkout_message .= "Email: " . $fetch_user['email'] . "\n\n";
                            $checkout_message .= "Pesanan:\n";
                            $cart_query_checkout = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
                            while ($fetch_cart_checkout = mysqli_fetch_assoc($cart_query_checkout)) {
                            $checkout_message .= "- " . $fetch_cart_checkout['name'] . " (Category: " . $fetch_cart_checkout['category'] . ", Size: " . $fetch_cart_checkout['size'] . ", Jumlah: " . $fetch_cart_checkout['quantity'] . ", Price: Rp. " . $fetch_cart_checkout['price'] . ")\n";
                            }
                            $checkout_message .= "\nTotal: Rp. " . $grand_total;
                            ?>
                            <a href="https://wa.me/6285157756604?text=<?php echo urlencode($checkout_message); ?>" class="btn success checkout-btn <?php echo ($grand_total > 1) ? '' : 'disabled'; ?>" <?php echo ($grand_total > 1) ? '' : 'onclick="return false;"'; ?>>Checkout via WhatsApp</a>
                        </div>
                    </div>
                <?php
                } else {
                    echo '<div class="empty-cart">
                            <div class="empty-icon">🛒</div>
                            <h3>Your cart is empty</h3>
                            <p>Add some products to get started!</p>
                          </div>';
                }
                ?>
            </div>
        </section>

    </div>
</body>

</html>
