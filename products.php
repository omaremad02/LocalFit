<?php 
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if viewing a specific product
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $productID = $_GET['id'];
    
    // Get product details
    $stmt = $conn->prepare("
        SELECT p.*, b.name as brandName, b.logoURL as brandLogo, b.socialLinks
        FROM products p
        JOIN brands b ON p.brandID = b.brandID
        WHERE p.productID = ?
    ");
    $stmt->execute([$productID]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        header("Location: products.php");
        exit();
    }
    
    // Handle add to cart action
    if (isset($_POST['add_to_cart']) && isset($_SESSION['user_id'])) {
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        
        if ($quantity > 0) {
            addToCart($_SESSION['user_id'], $productID, $quantity);
            $_SESSION['success_message'] = "Product added to cart!";
            header("Location: products.php?id=" . $productID);
            exit();
        }
    }
    
    include 'includes/header.php';
    ?>
    
    <!-- Product Detail View -->
    <div class="container my-5">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success_message'] ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <img src="<?= $product['imageURL'] ?>" class="img-fluid" alt="<?= $product['name'] ?>">
            </div>
            <div class="col-md-6">
                <h2><?= $product['name'] ?></h2>
                <p class="text-muted">By <a href="brands.php?id=<?= $product['brandID'] ?>"><?= $product['brandName'] ?></a></p>
                <h4 class="my-3">$<?= number_format($product['price'], 2) ?></h4>
                <p>Size: <?= $product['size'] ?></p>
                <p><?= $product['description'] ?></p>
                
                <form action="" method="post">
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" id="quantity" name="quantity" class="form-control" value="1" min="1" style="width: 100px;">
                    </div>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary">Login to Purchase</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <div class="mt-5">
            <h3>About the Brand</h3>
            <div class="row align-items-center">
                <div class="col-md-2">
                    <img src="<?= $product['brandLogo'] ?>" class="img-fluid" alt="<?= $product['brandName'] ?>">
                </div>
                <div class="col-md-10">
                    <h4><?= $product['brandName'] ?></h4>
                    <?php if (!empty($product['socialLinks'])): ?>
                        <p>Connect: <?= $product['socialLinks'] ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
<?php } else {
    // Product listing page
    $filters = [];
    
    // Apply filters from GET parameters
    if (isset($_GET['brand']) && is_numeric($_GET['brand'])) {
        $filters['brand'] = (int)$_GET['brand'];
    }
    
    if (isset($_GET['size']) && !empty($_GET['size'])) {
        $filters['size'] = $_GET['size'];
    }
    
    if (isset($_GET['min_price']) && is_numeric($_GET['min_price'])) {
        $filters['min_price'] = (float)$_GET['min_price'];
    }
    
    if (isset($_GET['max_price']) && is_numeric($_GET['max_price'])) {
        $filters['max_price'] = (float)$_GET['max_price'];
    }
    
    // Get all products with filters
    $products = getAllProducts($filters);
    
    // Get all brands for filter
    $brands = getAllBrands();
    
    // Get unique sizes for filter
    $stmt = $conn->prepare("SELECT DISTINCT size FROM products ORDER BY size");
    $stmt->execute();
    $sizes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    include 'includes/header.php';
?>

<!-- Products Catalog -->
<h1 class="mb-4">Browse Products</h1>

<div class="row">
    <!-- Filters Sidebar -->
    <div class="col-md-3">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Filters</h5>
            </div>
            <div class="card-body">
                <form action="" method="get">
                    <div class="mb-3">
                        <label for="brand" class="form-label">Brand</label>
                        <select name="brand" id="brand" class="form-select">
                            <option value="">All Brands</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?= $brand['brandID'] ?>" <?= (isset($filters['brand']) && $filters['brand'] == $brand['brandID']) ? 'selected' : '' ?>>
                                    <?= $brand['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="size" class="form-label">Size</label>
                        <select name="size" id="size" class="form-select">
                            <option value="">All Sizes</option>
                            <?php foreach ($sizes as $size): ?>
                                <option value="<?= $size ?>" <?= (isset($filters['size']) && $filters['size'] == $size) ? 'selected' : '' ?>>
                                    <?= $size ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Price Range</label>
                        <div class="d-flex">
                            <input type="number" name="min_price" class="form-control me-2" placeholder="Min" value="<?= $filters['min_price'] ?? '' ?>">
                            <input type="number" name="max_price" class="form-control" placeholder="Max" value="<?= $filters['max_price'] ?? '' ?>">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="products.php" class="btn btn-outline-secondary">Clear</a>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Products Grid -->
    <div class="col-md-9">
        <?php if (empty($products)): ?>
            <div class="alert alert-info">No products found matching your criteria.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($products as $product): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="<?= $product['imageURL'] ?>" class="card-img-top" alt="<?= $product['name'] ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= $product['name'] ?></h5>
                                <p class="card-text text-muted">By <?= $product['brandName'] ?></p>
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
</div>

<?php } ?>

<?php include 'includes/footer.php'; ?>