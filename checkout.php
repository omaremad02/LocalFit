<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
session_start();

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
$errors = [];
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

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shippingAddress = trim($_POST['shipping_address'] ?? '');
    
    // Validate input
    if (empty($shippingAddress)) {
        $errors[] = "Shipping address is required";
    }
    
    // Process order if no errors
    if (empty($errors) && !empty($cartItems)) {
        try {
            $conn->beginTransaction();
            
            // Create order
            $stmt = $conn->prepare("
                INSERT INTO orders (userID, totalPrice, shippingAddress, status, orderDate)
                VALUES (?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([$userID, $total, $shippingAddress]);
            $orderID = $conn->lastInsertId();
            
            // Add order items
            $stmt = $conn->prepare("
                INSERT INTO orderItems (orderID, productID, quantity)
                VALUES (?, ?, ?)
            ");
            foreach ($cartItems as $item) {
                $stmt->execute([$orderID, $item['productID'], $item['quantity']]);
            }
            
            // Clear cart
            $stmt = $conn->prepare("DELETE FROM cartItems WHERE cartID = ?");
            $stmt->execute([$cart['cartID']]);
            
            $conn->commit();
            header("Location: order_confirmation.php?orderID=$orderID");
            exit();
        } catch (Exception $e) {
            $conn->rollBack();
            $errors[] = "Checkout failed: " . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<h1 class="mb-4">Checkout</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (empty($cartItems)): ?>
    <div class="alert alert-info">Your cart is empty. <a href="products.php">Shop now</a>.</div>
<?php else: ?>
    <div class="row">
        <div class="col-md-6">
            <h3>Order Summary</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
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
                            <td><?= $item['quantity'] ?></td>
                            <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <h4>Total: $<?= number_format($total, 2) ?></h4>
        </div>
        <div class="col-md-6">
            <h3>Shipping Information</h3>
            <form method="post">
                <div class="mb-3">
                    <label for="shipping_address" class="form-label">Shipping Address</label>
                    <textarea class="form-control" id="shipping_address" name="shipping_address" rows="4" required><?= isset($_POST['shipping_address']) ? htmlspecialchars($_POST['shipping_address']) : '' ?></textarea>
                </div>
                <button type="submit" class="btn btn-success">Place Order</button>
                <a href="cart.php" class="btn btn-secondary">Back to Cart</a>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>