// toggle class active
const navbarNav = document.querySelector(".navbar-nav");
//ketika menu di klik
document.querySelector("#hamburger-menu").onclick = () => {
  navbarNav.classList.toggle("active");
};

//klik di luar sidebar untuk menghilangkan nav
const hamburger = document.querySelector("#hamburger-menu");

document.addEventListener("click", function (e) {
  if (!hamburger.contains(e.target) && !navbarNav.contains(e.target)) {
    navbarNav.classList.remove("active");
  }
});

// Ambil data produk dari backend
window.onload = function () {
  fetch("http://localhost:8000/api/product")
    .then((response) => response.json()) // Mengubah respons menjadi format JSON
    .then((data) => {
      const menuSection = document.getElementById("menu");
      const menuRow = menuSection.querySelector(".row");

      // Tambahkan produk ke menu
      data.forEach((product) => {
        const productCard = document.createElement("div");
        productCard.classList.add("menu-card");

        const productImage = document.createElement("img");
        productImage.classList.add("menu-card-img");
        productImage.src = "assets/img/menu/default.jpg"; // Ganti dengan gambar produk yang sesuai

        const productTitle = document.createElement("h3");
        productTitle.classList.add("menu-card-title");
        productTitle.innerText = `- ${product.name} -`;

        const productPrice = document.createElement("p");
        productPrice.classList.add("menu-card-price");
        productPrice.innerText = `IDR ${product.price}`;

        productCard.appendChild(productImage);
        productCard.appendChild(productTitle);
        productCard.appendChild(productPrice);

        menuRow.appendChild(productCard);
      });
    })
    .catch((error) => console.error("Error fetching products:", error));
};

// Call fetchProducts on page load
document.getElementById("addProductForm").addEventListener("submit", function (e) {
  e.preventDefault(); // Mencegah form untuk submit secara default

  const productName = document.getElementById("productName").value;

  const newProduct = {
    name: productName,
    price: 15000, 
  };

  fetch("http://localhost:8000/api/product", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(newProduct), // Mengirimkan data sebagai JSON
  })
    .then((response) => response.json())
    .then((data) => {
      console.log("Product added:", data);
      alert("Produk berhasil ditambahkan!");
      // Reset form
      document.getElementById("addProductForm").reset();
    })
    .catch((error) => {
      console.error("Error adding product:", error);
      alert("Gagal menambahkan produk.");
    });
});

// toggle dropdown user
const userBtn = document.getElementById('user');
const userDropdown = document.getElementById('userDropdown');

userBtn.addEventListener('click', function(e) {
  e.preventDefault();
  userDropdown.style.display = userDropdown.style.display === 'block' ? 'none' : 'block';
});

// klik luar area menutup dropdown
document.addEventListener('click', function(e) {
  if (!userBtn.contains(e.target) && !userDropdown.contains(e.target)) {
    userDropdown.style.display = 'none';
  }
});

