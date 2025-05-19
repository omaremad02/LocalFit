<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if we have an order ID in the session
if (!isset($_SESSION['order_id'])) {
    header("Location: products.php");
    exit();
}

$orderID = $_SESSION['order_id'];

// Get order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE orderID = ? AND userID = ?");
$stmt->execute([$orderID, $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: user_dashboard.php");
    exit();
}

// Get order items
$orderItems = getOrderDetails($orderID);

// Clear the order ID from session
unset($_SESSION['order_id']);

include 'includes/header.php';
?>

<div class="text-center mb-5">
    <h1 class="mb-3">Thank You for Your Order!</h1>
    <p class="lead">Your order has been placed successfully.</p>
</div>

<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">Order Confirmation</h5>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h6>Order Details</h6>
                <p>Order Number: #<?= $orderID ?></p>
                <p>Date: <?= date("F j, Y, g:i a", strtotime($order['orderDate'])) ?></p>
                <p>Status: <?= ucfirst($order['status']) ?></p>
            </div>
            <div class="col-md-6">
                <h6>Shipping Address</h6>
                <p><?= nl2br(htmlspecialchars($order['shippingAddress'])) ?></p>
            </div>
        </div>
        
        <h6>Order Summary</h6>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Brand</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?= $item['imageURL'] ?>" alt="<?= $item['name'] ?>" style="width: 60px; height: 60px; object-fit: cover;" class="me-3">
                                    <div>
                                        <?= $item['name'] ?>
                                        <div class="small text-muted">Size: <?= $item['size'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= $item['brandName'] ?></td>
                            <td>$<?= number_format($item['price'], 2) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Order Total:</th>
                        <th>$<?= number_format($order['totalPrice'], 2) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="text-center mb-5">
    <p>An email confirmation has been sent to your registered email address.</p>
    <div class="mt-4">
        <a href="products.php" class="btn btn-primary me-2">Continue Shopping</a>
        <a href="user_dashboard.php" class="btn btn-outline-primary">View Your Orders</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>