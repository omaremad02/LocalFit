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
    $logoURL = $_POST['logoURL'] ?? '';
    $socialLinks = $_POST['socialLinks'] ?? '';
    $brandID = $_POST['brandID'] ?? null;
    
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Brand name is required";
    }
    
    if (empty($errors)) {
        if ($brandID) {
            // Update brand
            $stmt = $conn->prepare("
                UPDATE brands SET name = ?, logoURL = ?, socialLinks = ?
                WHERE brandID = ?
            ");
            $stmt->execute([$name, $logoURL, $socialLinks, $brandID]);
        } else {
            // Create brand
            $stmt = $conn->prepare("
                INSERT INTO brands (name, logoURL, socialLinks)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$name, $logoURL, $socialLinks]);
        }
        header("Location: manage_brands.php");
        exit();
    }
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM brands WHERE brandID = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: manage_brands.php");
    exit();
}

// Get all brands
$brands = getAllBrands();

// Get brand for editing
$editBrand = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM brands WHERE brandID = ?");
    $stmt->execute([$_GET['edit']]);
    $editBrand = $stmt->fetch(PDO::FETCH_ASSOC);
}

include '../includes/header.php';
?>

<h1 class="mb-4">Manage Brands</h1>

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
        <h5 class="mb-0"><?php echo $editBrand ? 'Edit Brand' : 'Add New Brand'; ?></h5>
    </div>
    <div class="card-body">
        <form method="post">
            <?php if ($editBrand): ?>
                <input type="hidden" name="brandID" value="<?= $editBrand['brandID'] ?>">
            <?php endif; ?>
            <div class="mb-3">
                <label for="name" class="form-label">Brand Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($editBrand['name'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label for="logoURL" class="form-label">Logo URL</label>
                <input type="text" class="form-control" id="logoURL" name="logoURL" value="<?= htmlspecialchars($editBrand['logoURL'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="socialLinks" class="form-label">Social Links</label>
                <textarea class="form-control" id="socialLinks" name="socialLinks" rows="3"><?= htmlspecialchars($editBrand['socialLinks'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo $editBrand ? 'Update Brand' : 'Add Brand'; ?></button>
            <?php if ($editBrand): ?>
                <a href="manage_brands.php" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Brand List</h5>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Logo</th>
                    <th>Social Links</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($brands as $brand): ?>
                    <tr>
                        <td><?= htmlspecialchars($brand['name']) ?></td>
                        <td><img src="<?= $brand['logoURL'] ?>" alt="<?= $brand['name'] ?>" style="max-width: 50px;"></td>
                        <td><?= htmlspecialchars($brand['socialLinks'] ?? '') ?></td>
                        <td>
                            <a href="?edit=<?= $brand['brandID'] ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="?delete=<?= $brand['brandID'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>