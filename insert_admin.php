<?php
include 'config.php';
mysqli_query($conn, "INSERT INTO user_info (name, email, password, role) VALUES ('Admin', 'admin@3stripes.com', MD5('admin123'), 'admin')") or die('Insert failed: ' . mysqli_error($conn));
echo 'Admin user inserted successfully.';
?>
