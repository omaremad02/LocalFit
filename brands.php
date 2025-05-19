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
        SELECT p.*, b.name as brandName
        FROM products p
        JOIN brands b ON p.brandID = b.brandID
        WHERE p.brandID = ?
    ");
    $stmt->execute([$brandID]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    include 'includes/header.php';
?>

    <div class="container my-5">
        <div class="row align-items-center mb-4">
            <div class="col-md-2">
                <img src="<?= $brand['logoURL'] ?>" class="img-fluid" alt="<?= $brand['name'] ?>">
            </div>
            <div class="col-md-10">
                <h2><?= htmlspecialchars($brand['name']) ?></h2>
                <?php if (!empty($brand['socialLinks'])): ?>
                    <p>Connect: <?= htmlspecialchars($brand['socialLinks']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <h3>Products by <?= htmlspecialchars($brand['name']) ?></h3>
        <div class="row">
            <?php if (empty($products)): ?>
                <div class="alert alert-info">No products available for this brand.</div>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="<?= $product['imageURL'] ?>" class="card-img-top" alt="<?= $product['name'] ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                                <p class="card-text">$<?= number_format($product['price'], 2) ?></p>
                                <p class="card-text small">Size: <?= htmlspecialchars($product['size'] ?? '') ?></p>
                                <a href="products.php?id=<?= $product['productID'] ?>" class="btn btn-outline-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

<?php } else {
    // List all brands
    $brands = getAllBrands();
    
    include 'includes/header.php';
?>

    <h1 class="mb-4">Browse Brands</h1>
    
    <div class="row">
        <?php if (empty($brands)): ?>
            <div class="alert alert-info">No brands available.</div>
        <?php else: ?>
            <?php foreach ($brands as $brand): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="<?= $brand['logoURL'] ?>" class="card-img-top" alt="<?= $brand['name'] ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($brand['name']) ?></h5>
                            <?php if (!empty($brand['socialLinks'])): ?>
                                <p class="card-text small">Connect: <?= htmlspecialchars($brand['socialLinks']) ?></p>
                            <?php endif; ?>
                            <a href="brands.php?id=<?= $brand['brandID'] ?>" class="btn btn-outline-primary">View Brand</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

<?php } ?>

<?php include 'includes/footer.php'; ?>