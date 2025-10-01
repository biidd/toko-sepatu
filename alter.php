<?php
include 'config.php';
mysqli_query($conn, "ALTER TABLE user_info ADD COLUMN role VARCHAR(10) DEFAULT 'user'") or die('Alter failed: ' . mysqli_error($conn));
echo 'Role column added successfully.';
?>
