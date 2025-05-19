<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
// session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user's cart
$userID = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT cartID FROM carts WHERE userID = ?");
$stmt->execute([$userID]);
$cart = $stmt->fetch(PDO::FETCH_ASSOC);

$cartItems = [];
$total = 0;
if ($cart) {
    $stmt = $conn->prepare("
        SELECT ci.cartItemID, ci.productID, ci.quantity, p.name, p.price, p.size, p.imageURL, b.name AS brandName
        FROM cartItems ci
        JOIN products p ON ci.productID = p.productID
        JOIN brands b ON p.brandID = b.brandID
        WHERE ci.cartID = ?
    ");
    $stmt->execute([$cart['cartID']]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate total
    foreach ($cartItems as $item) {
        $total += $item['price'] * $item['quantity'];
    }
}

// Handle quantity update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $cartItemID => $quantity) {
        $quantity = (int)$quantity;
        if ($quantity <= 0) {
            // Remove item if quantity is 0
            $stmt = $conn->prepare("DELETE FROM cartItems WHERE cartItemID = ?");
            $stmt->execute([$cartItemID]);
        } else {
            // Update quantity
            $stmt = $conn->prepare("UPDATE cartItems SET quantity = ? WHERE cartItemID = ?");
            $stmt->execute([$quantity, $cartItemID]);
        }
    }
    header("Location: cart.php");
    exit();
}

// Handle remove item
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $stmt = $conn->prepare("DELETE FROM cartItems WHERE cartItemID = ?");
    $stmt->execute([$_GET['remove']]);
    header("Location: cart.php");
    exit();
}

include 'includes/header.php';
?>

<h1 class="mb-4">Your Cart</h1>

<?php if (empty($cartItems)): ?>
    <div class="alert alert-info">Your cart is empty.</div>
<?php else: ?>
    <form method="post">
        <input type="hidden" name="update_cart" value="1">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartItems as $item): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <?php if ($item['imageURL']): ?>
                                    <img src="<?= htmlspecialchars($item['imageURL']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width: 50px; height: 50px; object-fit: contain; margin-right: 10px;">
                                <?php endif; ?>
                                <div>
                                    <?= htmlspecialchars($item['name']) ?><br>
                                    <small>Size: <?= htmlspecialchars($item['size'] ?? 'N/A') ?><br>
                                    Brand: <?= htmlspecialchars($item['brandName']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td>$<?= number_format($item['price'], 2) ?></td>
                        <td>
                            <input type="number" name="quantity[<?= $item['cartItemID'] ?>]" value="<?= $item['quantity'] ?>" min="0" class="form-control" style="width: 80px;">
                        </td>
                        <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                        <td>
                            <a href="?remove=<?= $item['cartItemID'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remove this item?')">Remove</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">Update Cart</button>
            <h4>Total: $<?= number_format($total, 2) ?></h4>
        </div>
    </form>
    <a href="checkout.php" class="btn btn-success mt-3">Proceed to Checkout</a>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>