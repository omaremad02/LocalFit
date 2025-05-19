<?php
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    echo json_encode(['error' => 'Invalid order ID']);
    exit();
}

$orderID = (int)$_GET['order_id'];
$userID = $_SESSION['user_id'];

// Verify order belongs to user
$stmt = $conn->prepare("SELECT * FROM orders WHERE orderID = ? AND userID = ?");
$stmt->execute([$orderID, $userID]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo json_encode(['error' => 'Order not found']);
    exit();
}

// Get order items
$items = getOrderDetails($orderID);

echo json_encode([
    'order' => $order,
    'items' => $items
]);
?>