<?php
session_start();
require_once 'config/database.php';
require_once 'utils/functions.php';


$database = new Database();
$db = $database->getConnection();


$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $query = "SELECT COUNT(*) as count FROM cart_items WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $cart_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - Pet World</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
    .error-container {
        text-align: center;
        padding: 100px 0;
    }

    .error-code {
        font-size: 120px;
        font-weight: bold;
        color: #dc3545;
        margin-bottom: 0;
    }

    .error-message {
        font-size: 24px;
        margin-bottom: 30px;
    }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="home.php">Pet World</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="food.php">Pet Food</a>
                    </li>
                </ul>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
                            <i class="fas fa-shopping-cart"></i> Cart
                            <?php if ($cart_count > 0): ?>
                            <span class="badge badge-pill badge-primary"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user"></i> <?php echo $_SESSION['full_name']; ?>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="profile.php">My Profile</a>
                            <a class="dropdown-item" href="my_orders.php">My Orders</a>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="admin/index.php">Admin Panel</a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="logout.php">Logout</a>
                        </div>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="signup.php">Sign Up</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>


    <div class="container py-5">
        <div class="error-container">
            <h1 class="error-code">404</h1>
            <h2 class="error-message">Page Not Found</h2>
            <p class="lead">The page you are looking for might have been removed, had its name changed, or is
                temporarily unavailable.</p>
            <div class="mt-4">
                <a href="home.php" class="btn btn-primary btn-lg"><i class="fas fa-home mr-2"></i>Go to Homepage</a>
                <a href="food.php" class="btn btn-outline-primary btn-lg ml-2"><i
                        class="fas fa-shopping-bag mr-2"></i>Shop Pet Food</a>
            </div>
            <div class="mt-5">
                <img src="image/sad-pet.svg" alt="Sad Pet" class="img-fluid" style="max-width: 300px;">
            </div>
        </div>
    </div>


    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Pet World</h5>
                    <p>Your one-stop shop for quality pet food and supplies.</p>
                </div>
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="home.php" class="text-white">Home</a></li>
                        <li><a href="food.php" class="text-white">Pet Food</a></li>
                        <li><a href="cart.php" class="text-white">Cart</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contact Us</h5>
                    <address>
                        <p><i class="fas fa-map-marker-alt mr-2"></i> 123 Pet Street, Animal City</p>
                        <p><i class="fas fa-phone mr-2"></i> (123) 456-7890</p>
                        <p><i class="fas fa-envelope mr-2"></i> info@petworld.com</p>
                    </address>
                </div>
            </div>
            <div class="text-center mt-3">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Pet World. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>