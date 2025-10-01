<?php
include 'config.php';
session_start();
$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    header('location:login.php');
};

if (isset($_GET['logout'])) {
    unset($user_id);
    session_destroy();
    header('location:login.php');
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
            $message[] = 'Product added to cart!';
        }
    }
};
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>3STRIPES STORE</title>

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
  <!-- navbar start -->
  <nav class="navbar">
    <a href="#" class="navbar-logo">3STRIPES<span>STORE</span>.</a>

    <div class="navbar-nav">
      <a href="#">Home</a>
      <a href="#about">Tentang Kami</a>
      <a href="#menu">Produk</a>
      <a href="#contact">Kontak</a>
    </div>

   <div class="navbar-extra">
  <!-- tombol user -->
  <div class="user-menu">
    <a href="#" id="user"><i data-feather="user"></i></a>
    <!-- dropdown -->
    <div class="dropdown" id="userDropdown">
      <div class="dropdown-header">
        <div class="avatar"><i data-feather="user"></i></div>
        <div class="user-info">
          <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Guest'); ?></strong>
          <p><?php echo htmlspecialchars($_SESSION['user_email'] ?? 'guest@example.com'); ?></p>
        </div>
      </div>
      <div class="dropdown-footer">
        <a href="profile.php">Kelola Akun</a> | 
        <a href="index.php?logout=true">Keluar</a>
      </div>
    </div>
  </div>

  <!-- tombol lain -->
  <a href="cart.php" id="shopping-cart"><i data-feather="shopping-cart"></i></a>
  <a href="#" id="hamburger-menu"><i data-feather="menu"></i></a>
</div>

  </nav>
  <!-- navbar end -->

  <!-- hero section start -->
  <section class="hero" id="home">
    <main class="content">
      <h1>Cari Apa? <span>Ya Pasti Garis Tiga!</span></h1>
      <p>"THE BRAND WITH THE 3 STRIPES DIE WELTMARKE MIT DEN 3 STREIFEN LA MARQUE AUX 3 BANDES."</p>
      <a href="cart.php" class="cta">Checkout Now</a>
    </main>
  </section>

  <!-- hero section end -->

  <!-- about section start -->
  <section id="about" class="about">
    <h2><span>Tentang</span> Kami</h2>

    <div class="row">
      <div class="about-img">
        <img src="assets/img/tentang-kami.jpg" alt="Tentang Kami" />
      </div>
      <div class="content">
        <h3>Kenapa memilih store kami?</h3>
        <p>
          Karena kami menjamin setiap pasang sepatu adidas yang Anda dapatkan adalah 100% Original. Tak perlu lagi khawatir barang palsu. Di sini, kepercayaan Anda adalah prioritas utama. Belanja aman, gaya maksimal!.
        </p>
        <p>
          Kami bukan sekadar toko, kami adalah destinasi resmi untuk koleksi terbaik adidas. Dapatkan hanya produk original dengan kualitas terjamin dan jaminan resmi. Pilihan yang terpercaya untuk langkah terbaik Anda.
        </p>
      </div>
    </div>
  </section>

  <!-- about section end -->


  <!--Menu Section start-->
  <section id="menu" class="menu products-section">
    <h2 class="section-heading"><span>Produk</span> Kami</h2>
    <p>Inilah The Dream Team di rak sepatu kami. Setiap pasang bukan hanya alas kaki, tapi cerita dan vibe yang berbeda. Mana nih pair adidas favoritmu?</p>
    <div class="product-grid">
      <?php
      include 'config.php';
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
        }
      }
      ?>
    </div>
  </section>
  <!--Menu Section end-->

  <!--kontak Section start-->
  <section id="contact" class="contact">
    <h2><span>Kontak</span> Kami</h2>
    <p>Kami selalu senang mendengar dari Anda! Jika Anda memiliki pertanyaan, umpan balik, atau ingin tahu lebih banyak tentang produk dan layanan kami, jangan ragu untuk menghubungi kami melalui cara berikut :</p>
    <div class="row">
      <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126455.15625949748!2d112.5139952582031!3d7.923908199999994!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e788377575980d9%3A0x2cf1177f865964df!2sKopi%20Kampoeng%20ID!5e0!3m2!1sid!2sid!4v1730789173575!5m2!1sid!2sid" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="map"></iframe>
      <form action="">
        <div class="input-group">
          <i data-feather="user"></i>
          <input type="text" placeholder="nama">
        </div>
        <div class="input-group">
          <i data-feather="mail"></i>
          <input type="text" placeholder="email">
        </div>
        <div class="input-group">
          <i data-feather="phone"></i>
          <input type="text" placeholder="no. telepon">
        </div>
        <button type="submit" class="btn">Kirim pesan</button>
      </form>
    </div>
    <!--kontak Section end-->

    <!--footer start-->
    <footer>
      <div class="socials">
        <a href="https://www.instagram.com/bidefg"><i data-feather="instagram"></i></a>
        <a href="https://x.com/AbidMhmmd664?t=i9_LcjEYIq4pSXhjcMJ1dA&s=09"><i data-feather="twitter"></i></a>
        <a href="https://www.facebook.com/bid.bid.96199?mibextid=JRoKGi "><i data-feather="facebook"></i></a>
      </div>
      <div class="links">
        <a href="#">Home</a>
        <a href="#about">Tentang Kami</a>
        <a href="#menu">Favorit</a>
        <a href="#contact">Kontak</a>
      </div>
      <div class="credit">
        <p>Created by <a href="https://wa.me/6285157756604">MuhammadAbid</a>. | &copy; 2024</p>
      </div>
    </footer>
    <!--footer end-->

  <!-- feather icons -->
  <script src="https://unpkg.com/feather-icons"></script>
   <!-- feather icons -->
    <script>
      feather.replace();
    </script>

  <!-- javascript -->
  <script src="assets/js/script.js"></script>
  <script src="assets/js/navbar-dropdown.js"></script>
</body>

</html>
