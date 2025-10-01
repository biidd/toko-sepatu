<?php
include 'config.php';
session_start();

$user_id = $_SESSION['user_id'];
if (!isset($user_id)) {
    header('location:login.php');
    exit;
}

$message = [];

if (isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $current_password = mysqli_real_escape_string($conn, $_POST['current_password']);
    $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    // Fetch current password hash from DB
    $user_query = mysqli_query($conn, "SELECT password FROM user_info WHERE id = '$user_id'") or die('query failed');
    $user_data = mysqli_fetch_assoc($user_query);

    if (md5($current_password) !== $user_data['password']) {
        $message[] = 'Current password is incorrect!';
    } else {
        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                $message[] = 'New password and confirm password do not match!';
            } else {
                $password_hash = md5($new_password);
                mysqli_query($conn, "UPDATE user_info SET name = '$name', email = '$email', password = '$password_hash' WHERE id = '$user_id'") or die('query failed');
                $message[] = 'Profile and password updated successfully!';
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
            }
        } else {
            mysqli_query($conn, "UPDATE user_info SET name = '$name', email = '$email' WHERE id = '$user_id'") or die('query failed');
            $message[] = 'Profile updated successfully!';
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
        }
    }
}

// Fetch user info for form
$user_query = mysqli_query($conn, "SELECT * FROM user_info WHERE id = '$user_id'") or die('query failed');
$user = mysqli_fetch_assoc($user_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Kelola Akun - 3STRIPES STORE</title>

  <!--font-->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,300;0,400;0,700;1,700&display=swap"
    rel="stylesheet" />

  <!-- feather icons -->
  <script src="https://unpkg.com/feather-icons"></script>

  <!--my style-->
  <link rel="stylesheet" href="assets/css/styles.css" />
</head>
<body>
  <section class="form-container">
    <h2>Kelola Akun</h2>

    <?php
    if (!empty($message)) {
        foreach ($message as $msg) {
            echo '<div class="message" onclick="this.remove();">' . htmlspecialchars($msg) . '</div>';
        }
    }
    ?>

    <form action="" method="post" class="auth-form">
      <label for="name">Nama:</label>
      <input type="text" name="name" id="name" required value="<?php echo htmlspecialchars($user['name']); ?>" />

      <label for="email">Email:</label>
      <input type="email" name="email" id="email" required value="<?php echo htmlspecialchars($user['email']); ?>" />

      <label for="current_password">Password Saat Ini:</label>
      <input type="password" name="current_password" id="current_password" required placeholder="Masukkan password saat ini" />

      <label for="new_password">Password Baru (kosongkan jika tidak ingin ganti):</label>
      <input type="password" name="new_password" id="new_password" placeholder="Masukkan password baru" />

      <label for="confirm_password">Konfirmasi Password Baru:</label>
      <input type="password" name="confirm_password" id="confirm_password" placeholder="Konfirmasi password baru" />

      <input type="submit" name="update_profile" value="Update Profil" class="btn primary" />
    </form>
    <a href="index.php" class="btn secondary" style="margin-top: 1rem; display: inline-block;">Kembali ke Home</a>
  </section>

  <!-- feather icons -->
  <script>
    feather.replace();
  </script>
</body>
</html>
