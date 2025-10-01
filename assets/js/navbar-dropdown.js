document.addEventListener("DOMContentLoaded", function () {
  const userBtn = document.getElementById('user');
  const userDropdown = document.getElementById('userDropdown');

  if (userBtn && userDropdown) {
    userBtn.addEventListener('click', function(e) {
      e.preventDefault();
      userDropdown.style.display =
        userDropdown.style.display === 'block' ? 'none' : 'block';
    });

    // klik luar area menutup dropdown
    document.addEventListener('click', function(e) {
      if (!userBtn.contains(e.target) && !userDropdown.contains(e.target)) {
        userDropdown.style.display = 'none';
      }
    });
  }
});
