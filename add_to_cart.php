<?php
session_start();
include 'includes/functions.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $quantity = max(1, $_POST['quantity'] ?? 1);
    addToCart($_POST['product_id'], $quantity);
    header("Location: cart.php");
    exit();
}

header("Location: products.php");
exit();
?>