<?php 
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get featured products (latest 4)
$stmt = $conn->prepare("
    SELECT p.*, b.name as brandName
    FROM products p
    JOIN brands b ON p.brandID = b.brandID
    ORDER BY productID DESC LIMIT 4
");
$stmt->execute();
$featuredProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="jumbotron bg-light p-5 mb-4 rounded">
    <h1 class="display-4">Welcome to LocalFit</h1>
    <p class="lead">Discover and support local clothing brands in your community.</p>
    <hr class="my-4">
    <p>Browse our selection of unique, locally-produced fashion items all in one place.</p>
    <a class="btn btn-primary btn-lg" href="products.php" role="button">Shop Now</a>
</div>

<h2 class="mb-4">Featured Products</h2>

<div class="row">
    <?php foreach ($featuredProducts as $product): ?>
        <div class="col-md-3 mb-4">
            <div class="card h-100">
                <img src="<?= $product['imageURL'] ?>" class="card-img-top" alt="<?= $product['name'] ?>">
                <div class="card-body">
                    <h5 class="card-title"><?= $product['name'] ?></h5>
                    <p class="card-text text-muted">By <?= $product['brandName'] ?></p>
                    <p class="card-text">$<?= number_format($product['price'], 2) ?></p>
                    <a href="products.php?id=<?= $product['productID'] ?>" class="btn btn-outline-primary">View Details</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row mt-5">
    <div class="col-md-6">
        <h3>Support Local Brands</h3>
        <p>LocalFit is dedicated to connecting local clothing brands with customers who value unique, locally-produced fashion items. By shopping on LocalFit, you're directly supporting independent designers and local businesses.</p>
    </div>
    <div class="col-md-6">
        <h3>Discover Unique Fashion</h3>
        <p>Tired of seeing the same mass-produced clothing everywhere? Our marketplace features one-of-a-kind pieces created by passionate local designers who put care and creativity into everything they make.</p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>