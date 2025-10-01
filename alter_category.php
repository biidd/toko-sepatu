<?php
include 'config.php';
mysqli_query($conn, "ALTER TABLE products ADD COLUMN category VARCHAR(50) DEFAULT 'General'") or die('Alter failed: ' . mysqli_error($conn));
echo 'Category column added successfully.';
?>
