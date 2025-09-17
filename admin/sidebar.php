<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar collapse bg-dark">
    <div class="position-sticky pt-3">
        <div class="text-center mb-4">
            <h5 class="text-white">Pet World Admin</h5>
            <p class="text-light">Welcome, <?php echo $_SESSION['full_name']; ?></p>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                    <i class="fas fa-tachometer-alt mr-2"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>" href="products.php">
                    <i class="fas fa-box mr-2"></i>
                    Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>" href="categories.php">
                    <i class="fas fa-tags mr-2"></i>
                    Categories
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" href="users.php">
                    <i class="fas fa-users mr-2"></i>
                    Users
                </a>
            </li>
            <li class="nav-item mt-5">
                <a class="nav-link text-danger" href="../logout.php">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>
</nav>