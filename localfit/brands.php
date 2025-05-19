<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if viewing a specific brand
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $brandID = $_GET['id'];
    
    // Get brand details
    $stmt = $conn->prepare("SELECT * FROM brands WHERE brandID = ?");
    $stmt->execute([$brandID]);
    $brand = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$brand) {
        header("Location: brands.php");
        exit();
    }
    
    // Get brand products
    $stmt = $conn->prepare("
        SELECT * FROM products 
        WHERE brandID = ? 
        ORDER BY productID DESC
    ");
    $stmt->execute([$brandID]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row mb-5 align-items-center">
        <div class="col-md-3">
            <img src="<?= $brand['logoURL'] ?>" class="img-fluid" alt="<?= $brand['name'] ?>">
        </div>
        <div class="col-md-9">
            <h1><?= $brand['name'] ?></h1>
            <?php if (!empty($brand['socialLinks'])): ?>
                <p>Connect: <?= $brand['socialLinks'] ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <h2 class="mb-4">Products from <?= $brand['name'] ?></h2>
    
    <?php if (empty($products)): ?>
        <div class="alert alert-info">
            No products available from this brand yet.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="<?= $product['imageURL'] ?>" class="card-img-top" alt="<?= $product['name'] ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= $product['name'] ?></h5>
                            <p class="card-text">$<?= number_format($product['price'], 2) ?></p>
                            <p class="card-text small">Size: <?= $product['size'] ?></p>
                            <a href="products.php?id=<?= $product['productID'] ?>" class="btn btn-outline-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
} else {
    // Brands listing page
    // Get all brands
    $brands = getAllBrands();
    
    include 'includes/header.php';
?>

<h1 class="mb-4">Our Local Brands</h1>

<div class="row">
    <?php foreach ($brands as $brand): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <img src="<?= $brand['logoURL'] ?>" class="card-img-top" alt="<?= $brand['name'] ?>" style="height: 200px; object-fit: contain; padding: 20px;">
                <div class="card-body">
                    <h5 class="card-title"><?= $brand['name'] ?></h5>
                    <?php if (!empty($brand['socialLinks'])): ?>
                        <p class="card-text small">Connect: <?= $brand['socialLinks'] ?></p>
                    <?php endif; ?>
                    <a href="brands.php?id=<?= $brand['brandID'] ?>" class="btn btn-outline-primary">View Brand</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
}
include 'includes/footer.php';
?>