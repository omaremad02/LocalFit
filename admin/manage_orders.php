<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php");
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['orderID']) && isset($_POST['status'])) {
    $orderID = $_POST['orderID'];
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE orderID = ?");
    $stmt->execute([$status, $orderID]);
    header("Location: manage_orders.php");
    exit();
}

// Get all orders
$stmt = $conn->prepare("
    SELECT o.*, u.email
    FROM orders o
    JOIN users u ON o.userID = u.userID
    ORDER BY o.orderDate DESC
");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<h1 class="mb-4">Manage Orders</h1>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Order List</h5>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>User</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?= $order['orderID'] ?></td>
                        <td><?= htmlspecialchars($order['email']) ?></td>
                        <td><?= date("M d, Y", strtotime($order['orderDate'])) ?></td>
                        <td>$<?= number_format($order['totalPrice'], 2) ?></td>
                        <td>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="orderID" value="<?= $order['orderID'] ?>">
                                <select name="status" onchange="this.form.submit()">
                                    <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                    <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                    <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                </select>
                            </form>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary view-order" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#orderDetailModal" 
                                    data-order-id="<?= $order['orderID'] ?>">
                                View Details
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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
    document.querySelectorAll('.view-order').forEach(button => {
        button.addEventListener('click', function() {
            const orderID = this.getAttribute('data-order-id');
            const contentArea = document.getElementById('orderDetailContent');
            
            fetch('../includes/get_order_details.php?order_id=' + orderID)
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

<?php include '../includes/footer.php'; ?>