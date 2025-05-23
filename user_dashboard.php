<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Get user information
$userID = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE userID = ?");
$stmt->execute([$userID]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get user orders
$stmt = $conn->prepare("
    SELECT * FROM orders 
    WHERE userID = ? 
    ORDER BY orderDate DESC
");
$stmt->execute([$userID]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<h1 class="mb-4">My Account</h1>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Account Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <a href="#" class="btn btn-outline-primary btn-sm">Change Password</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Order History</h5>
            </div>
            <div class="card-body">
                <?php if (empty($orders)): ?>
                    <p>You haven't placed any orders yet.</p>
                    <a href="products.php" class="btn btn-primary">Start Shopping</a>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?= $order['orderID'] ?></td>
                                        <td><?= date("M d, Y", strtotime($order['orderDate'])) ?></td>
                                        <td>
                                            <span class="badge <?= $order['status'] === 'completed' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                                <?= ucfirst($order['status']) ?>
                                            </span>
                                        </td>
                                        <td>$<?= number_format($order['totalPrice'], 2) ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary view-order" 
                                                    data-bs-toggle="modal" data-bs-target="#orderDetailModal" 
                                                    data-order-id="<?= $order['orderID'] ?>">
                                                View Details
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Order Detail Modal -->
<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-labelledby="orderDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailModalLabel">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="orderDetailContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle order detail modal
    document.querySelectorAll('.view-order').forEach(button => {
        button.addEventListener('click', function() {
            const orderID = this.getAttribute('data-order-id');
            const contentArea = document.getElementById('orderDetailContent');
            
            // Fetch order details via AJAX
            fetch('includes/get_order_details.php?order_id=' + orderID)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        contentArea.innerHTML = '<p class="text-danger">' + data.error + '</p>';
                        return;
                    }
                    
                    let html = `
                        <h6>Order #${orderID}</h6>
                        <p><strong>Shipping Address:</strong> ${data.order.shippingAddress}</p>
                        <p><strong>Order Date:</strong> ${new Date(data.order.orderDate).toLocaleString()}</p>
                        <p><strong>Status:</strong> ${data.order.status.charAt(0).toUpperCase() + data.order.status.slice(1)}</p>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Brand</th>
                                    <th>Size</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    
                    data.items.forEach(item => {
                        html += `
                            <tr>
                                <td>${item.name}</td>
                                <td>${item.brandName}</td>
                                <td>${item.size}</td>
                                <td>${item.quantity}</td>
                                <td>$${parseFloat(item.price).toFixed(2)}</td>
                                <td>$${(item.price * item.quantity).toFixed(2)}</td>
                            </tr>
                        `;
                    });
                    
                    html += `
                            <tr>
                                <td colspan="5" class="text-end"><strong>Total:</strong></td>
                                <td><strong>$${parseFloat(data.order.totalPrice).toFixed(2)}</strong></td>
                            </tr>
                        </tbody>
                        </table>
                    `;
                    
                    contentArea.innerHTML = html;
                })
                .catch(error => {
                    contentArea.innerHTML = '<p class="text-danger">Error loading order details.</p>';
                });
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>