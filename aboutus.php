<?php
session_start();
require_once 'config/database.php';
require_once 'utils/functions.php';


$database = new Database();
$db = $database->getConnection();


$cart_count = 0;
if (is_logged_in()) {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT COUNT(*) as count FROM cart_items WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $cart_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet World - About Us</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style type="text/css">
    .bg-gray-50 .bg-green-600.text-white.py-20.text-center {
        background-color: #060221;
        color: #FFFFFF;
    }

    .bg-gray-50 center h1 {
        font-size: 98px;
        color: green;
    }
    </style>
</head>

<body class="bg-gray-50">

    <nav class="bg-green-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-2"> <a href="home.php" class="text-xl font-bold">PET WORLD&nbsp;</a>
            </div>

            <div class="hidden md:flex space-x-6">
                <a href="home.php" class="hover:text-green-200"><b>Home</b></a>
                <a href="food.php" class="hover:text-green-200"><b>Food</b></a>
                <a href="aboutus.php" class="hover:text-green-200"><b>About Us</b></a>
                <a href="contactus.php" class="hover:text-green-200"><b>Contact Us</b></a>

            </div>

            <div class="flex items-center space-x-4">
                <a href="#" class="hover:text-green-200">
                    <i class="fas fa-search"></i>
                </a>
                <?php if (is_logged_in()): ?>
                <a href="my_account.php" class="hover:text-green-200">
                    <i class="fas fa-user"></i>
                </a>
                <a href="logout.php" class="hover:text-green-200">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
                <?php else: ?>
                <a href="login.php" class="hover:text-green-200">
                    <i class="fas fa-user"></i>
                </a>
                <?php endif; ?>
                <a href="cart.php" class="hover:text-green-200 relative">
                    <i class="fas fa-shopping-cart"></i>
                    <span
                        class="absolute -top-2 -right-2 bg-yellow-400 text-black text-xs rounded-full h-5 w-5 flex items-center justify-center"><?php echo $cart_count; ?></span>
                </a>
                <button class="md:hidden focus:outline-none" id="menuButton">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>
    </nav>


    <section class="bg-green-600 text-white py-20 text-center">
        <h2 class="text-4xl font-bold mb-4">Welcome to Pet World</h2>
        <p class="text-lg mb-6">Your one-stop shop for pets, food, toys, and more!</p>
        <a href="food.php" class="bg-yellow-400 text-black font-bold py-2 px-6 rounded hover:bg-yellow-500 transition"
            style="background-color: green">Shop Now</a>
    </section>

    <br>
    <center>
        <h1> <b>ABOUT US </b></h1>
        <br><br><br>
        <p>Hello Friends, Welcome to PetWorld!

            PetWorld World is your trusted one-stop shop for all your pet needs. Founded in 2015, we’ve been serving pet
            lovers around the globe for over 10 years. From nutritious pet foods to fun toys, reliable medicines, and
            even adorable pet fish, dogs, and cats — we have it all under one roof!

            We proudly carry all the top brands that your pets love and trust. Whether it's premium food for your dog,
            playful toys for your cat, or the perfect aquarium fish for your home, we've got something for every pet and
            every budget.

            At PetWorld, customer satisfaction is our #1 priority. Our experienced and caring staff are always ready to
            help you choose the best products and pets to suit your lifestyle. Whether you're a first-time pet owner or
            a long-time animal lover, we’re here to guide you every step of the way.

            We are passionate about pets, and we’re working hard to turn that passion into a thriving online pet supply
            destination. We hope you enjoy shopping with us as much as we enjoy serving you and your furry (or finned!)
            friends.

            Stay tuned — we’ll keep posting exciting updates, new arrivals, and special offers on our website. Thank you
            for your love and support!</p>
    </center>
    <br><br><br>
    <br><br><br>
    <br><br><br>
    <footer class="bg-gray-900 text-white pt-12 pb-6">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">


                <div>
                    <h3 class="text-xl font-bold mb-4 flex items-center">PET WORLD</h3>
                    <p class="text-gray-400">Connecting communities through local food trading since 2023.</p>
                </div>


                <div>
                    <h4 class="font-bold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="home.php" class="text-gray-400 hover:text-white">Home</a></li>
                        <li><a href="food.php" class="text-gray-400 hover:text-white">Pet Food</a></li>
                        <li><a href="aboutus.php" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li><a href="contactus.php" class="text-gray-400 hover:text-white">Contact Us</a></li>
                    </ul>
                </div>


                <div>
                    <h4 class="font-bold mb-4">Support</h4>
                    <ul class="space-y-2">
                        <li><a href="faq.php" class="text-gray-400 hover:text-white">FAQs</a></li>
                        <li><a href="safety.php" class="text-gray-400 hover:text-white">Safety Tips</a></li>
                        <li><a href="contactus.php" class="text-gray-400 hover:text-white">Contact Us</a></li>
                        <li><a href="privacy.php" class="text-gray-400 hover:text-white">Privacy Policy</a></li>
                    </ul>
                </div>


                <div>
                    <h4 class="font-bold mb-4">Connect With Us</h4>
                    <div class="flex space-x-4 mb-4">
                        <a href="#" class="text-gray-400 hover:text-white text-xl"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white text-xl"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white text-xl"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white text-xl"><i class="fab fa-pinterest"></i></a>
                    </div>
                    <p class="text-gray-400">Subscribe to our newsletter</p>
                    <div class="flex mt-2">
                        <input type="email" placeholder="Your email"
                            class="bg-gray-800 text-white px-3 py-2 rounded-l focus:outline-none w-full">
                        <button class="bg-green-600 text-white px-4 py-2 rounded-r hover:bg-green-700"><i
                                class="fas fa-paper-plane"></i></button>
                    </div>
                </div>

            </div>


            <div class="border-t border-gray-800 pt-6 text-center text-gray-400">
                <p>&copy; 2023 PET WORLD. All rights reserved.</p>
            </div>
        </div>
    </footer>


    <div class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden" id="mobileMenu">
        <div class="bg-green-600 h-full w-3/4 max-w-sm p-4">
            <div class="flex justify-between items-center mb-8">
                <div class="flex items-center space-x-2">
                    <span class="text-xl font-bold">PET WORLD</span>
                </div>
                <button id="closeMenu" class="text-2xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <nav class="space-y-4">
                <a href="home.php" class="block py-2 px-4 hover:bg-green-700 rounded">Home</a>
                <a href="food.php" class="block py-2 px-4 hover:bg-green-700 rounded">Pet Food</a>
                <a href="aboutus.php" class="block py-2 px-4 bg-green-700 rounded">About Us</a>
                <a href="contactus.php" class="block py-2 px-4 hover:bg-green-700 rounded">Contact Us</a>
                <?php if (is_logged_in()): ?>
                <a href="my_account.php" class="block py-2 px-4 hover:bg-green-700 rounded">My Account</a>
                <a href="logout.php" class="block py-2 px-4 hover:bg-green-700 rounded">Logout</a>
                <?php else: ?>
                <a href="login.php" class="block py-2 px-4 hover:bg-green-700 rounded">Login</a>
                <a href="signup.php" class="block py-2 px-4 hover:bg-green-700 rounded">Sign Up</a>
                <?php endif; ?>

            </nav>
        </div>
    </div>


    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenu = document.getElementById('mobileMenu');
        const menuButton = document.getElementById('menuButton');
        const closeButton = document.getElementById('closeMenu');

        if (menuButton) {
            menuButton.addEventListener('click', function() {
                mobileMenu.classList.remove('hidden');
            });
        }

        if (closeButton) {
            closeButton.addEventListener('click', function() {
                mobileMenu.classList.add('hidden');
            });
        }
    });
    </script>
</body>

</html>