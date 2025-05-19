<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
 
    $price = $_POST['price'] ?? '';
    $size = $_POST['size'] ?? '';
    $imageURL = $_POST['imageURL'] ?? '';
    $brandID = $_POST['brandID'] ?? '';
    $productID = $_POST['productID'] ?? null;
    
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Product name is required";
    }
    if (empty($price) || !is_numeric($price)) {
        $errors[] = "Valid price is required";
    }
    if (empty($brandID)) {
        $errors[] = "Brand is required";
    }
    
    if (empty($errors)) {
        if ($productID) {
            // Update product
            $stmt = $conn->prepare("
                UPDATE products SET name = ?, description = ?, price = ?, size = ?, imageURL = ?, brandID = ?
                WHERE productID = ?
            ");
            $stmt->execute([$name, $description, $price, $size, $imageURL, $brandID, $productID]);
        } else {
            // Create product
            $stmt = $conn->prepare("
                INSERT INTO products (name, description, price, size, imageURL, brandID)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $description, $price, $size, $imageURL, $brandID]);
        }
        header("Location: manage_products.php");
        exit();
    }
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM products WHERE productID = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: manage_products.php");
    exit();
}

// Get all products
$stmt = $conn->prepare("
    SELECT p.*, b.name as brandName
    FROM products p
    JOIN brands b ON p.brandID = b.brandID
");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all brands
$brands = getAllBrands();

// Get product for editing
$editProduct = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE productID = ?");
    $stmt->execute([$_GET['edit']]);
    $editProduct = $stmt->fetch(PDO::FETCH_ASSOC);
}

include '../includes/header.php';
?>

<h1 class="mb-4">Manage Products</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><?php echo $editProduct ? 'Edit Product' : 'Add New Product'; ?></h5>
    </div>
    <div class="card-body">
        <form method="post">
            <?php if ($editProduct): ?>
                <input type="hidden" name="productID" value="<?= $editProduct['productID'] ?>">
            <?php endif; ?>
            <div class="mb-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?= htmlspecialchars($editProduct['price'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label for="size" class="form-label">Size</label>
                <input type="text" class="form-control" id="size" name="size" value="<?= htmlspecialchars($editProduct['size'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="imageURL" class="form-label">Image URL</label>
                <input type="text" class="form-control" id="imageURL" name="imageURL" value="<?= htmlspecialchars($editProduct['imageURL'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="brandID" class="form-label">Brand</label>
                <select class="form-select" id="brandID" name="brandID" required>
                    <option value="">Select Brand</option>
                    <?php foreach ($brands as $brand): ?>
                        <option value="<?= $brand['brandID'] ?>" <?= ($editProduct && $editProduct['brandID'] == $brand['brandID']) ? 'selected' : '' ?>>
                            <?= $brand['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo $editProduct ? 'Update Product' : 'Add Product'; ?></button>
            <?php if ($editProduct): ?>
                <a href="manage_products.php" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Product List</h5>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Brand</th>
                    <th>Price</th>
                    <th>Size</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= htmlspecialchars($product['brandName']) ?></td>
                        <td>$<?= number_format($product['price'], 2) ?></td>
                        <td><?= htmlspecialchars($product['size'] ?? '') ?></td>
                        <td><img src="<?= $product['imageURL'] ?>" alt="<?= $product['name'] ?>" style="max-width: 50px;"></td>
                        <td>
                            <a href="?edit=<?= $product['productID'] ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="?delete=<?= $product['productID'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>