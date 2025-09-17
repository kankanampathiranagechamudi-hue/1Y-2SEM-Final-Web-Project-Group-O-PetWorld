<?php
/**
 * Utility Functions
 * 
 * This file contains common utility functions used throughout the Pet World website
 */

/**
 * Clean input data to prevent XSS attacks
 * 
 * @param string $data The input data to clean
 * @return string The cleaned data
 */
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Redirect to a specific page
 * 
 * @param string $location The URL to redirect to
 * @return void
 */
function redirect($location) {
    header("Location: {$location}");
    exit;
}

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is an admin
 * 
 * @return bool True if user is an admin, false otherwise
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Display error message
 * 
 * @param string $message The error message to display
 * @return string HTML for the error message
 */
function display_error($message) {
    return "<div class='alert alert-danger'>{$message}</div>";
}

/**
 * Display success message
 * 
 * @param string $message The success message to display
 * @return string HTML for the success message
 */
function display_success($message) {
    return "<div class='alert alert-success'>{$message}</div>";
}

/**
 * Format price with currency symbol
 * 
 * @param float $price The price to format
 * @return string The formatted price
 */
function format_price($price) {
    return '$' . number_format($price, 2);
}

/**
 * Generate a random string
 * 
 * @param int $length The length of the random string
 * @return string The random string
 */
function generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
?>