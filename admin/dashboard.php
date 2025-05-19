<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include '../includes/header.php';
?>

<h1>Admin Dashboard</h1>

<div class="dashboard-links">
    <a href="manage_products.php" class="dashboard-card">
        <h3>Manage Products</h3>
        <p>Add, edit, or remove products</p>
    </a>
    <a href="manage_brands.php" class="dashboard-card">
        <h3>Manage Brands</h3>
        <p>Add, edit, or remove brands</p>
    </a>
    <a href="manage_orders.php" class="dashboard-card">
        <h3>View Orders</h3>
        <p>View and manage customer orders</p>
    </a>
</div>

<?php include '../includes/footer.php'; ?>