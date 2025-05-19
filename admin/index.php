<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php");
    exit();
}

// Get some stats
$stmt = $conn->query("SELECT COUNT(*) FROM users");
$totalUsers = $stmt->fetchColumn();

$stmt = $conn->query("SELECT COUNT(*) FROM products");
$totalProducts = $stmt->fetchColumn();

$stmt = $conn->query("SELECT COUNT(*) FROM orders");
$totalOrders = $stmt->fetchColumn();

include '../includes/header.php';
?>

<h1 class="mb-4">Admin Dashboard</h1>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Total Users</h5>
                <p class="card-text display-4"><?php echo $totalUsers; ?></p>
                <a href="manage_users.php" class="btn btn-primary">Manage Users</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Total Products</h5>
                <p class="card-text display-4"><?php echo $totalProducts; ?></p>
                <a href="manage_products.php" class="btn btn-primary">Manage Products</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Total Orders</h5>
                <p class="card-text display-4"><?php echo $totalOrders; ?></p>
                <a href="manage_orders.php" class="btn btn-primary">Manage Orders</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>