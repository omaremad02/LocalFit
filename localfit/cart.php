<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle cart actions
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'remove' && isset($_POST['cart_item_id'])) {
        $cartItemID = (int)$_POST['cart_item_id'];
        removeFromCart($cartItemID);
        $_SESSION['success_message'] = "Item removed from cart";
        header("Location: cart.php");
        exit();
    }
    
    if ($action === 'update_quantity' && isset($_POST['cart_item_id']) && isset($_POST['quantity'])) {
        $cartItemID = (int)$_POST['cart_item_id'];
        $quantity = (int)$_POST['quantity'];
        
        if ($quantity > 0) {
            // Update the quantity
            $conn->prepare("UPDATE cartItems SET quantity = ? WHERE cartItemID = ?")->execute([$quantity, $cartItemID]);
            $_SESSION['success_message'] = "Cart updated successfully";
        } else {
            // Remove item if quantity is 0
            removeFromCart($cartItemID);
            $_SESSION['success_message'] = "Item removed from cart";
        }
        
        header("Location: cart.php");
        exit();
    }
}

// Get cart items
$cartItems = getCartItems($_SESSION['user_id']);
$cartTotal = getCartTotal($_SESSION['user_id']);

include 'includes/header.php';
?>

<h1 class="mb-4">Your Shopping Cart</h1>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success">
        <?= $_SESSION['success_message'] ?>
        <?php unset($_SESSION['success_message']); ?>
    </div>
<?php endif; ?>

<?php if (empty($cartItems)): ?>
    <div class="alert alert-info">
        Your cart is empty. <a href="products.php">Continue shopping</a>.
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Brand</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?= $item['imageURL'] ?>" alt="<?= $item['name'] ?>" style="width: 60px; height: 60px; object-fit: cover;" class="me-3">
                                        <div>
                                            <a href="products.php?id=<?= $item['productID'] ?>"><?= $item['name'] ?></a>
                                            <div class="small text-muted">Size: <?= $item['size'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= $item['brandName'] ?></td>
                                <td>$<?= number_format($item['price'], 2) ?></td>
                                <td>
                                    <form method="post" class="d-flex align-items-center">
                                        <input type="hidden" name="action" value="update_quantity">
                                        <input type="hidden" name="cart_item_id" value="<?= $item['cartItemID'] ?>">
                                        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="0" class="form-control form-control-sm" style="width: 60px;">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary ms-2">Update</button>
                                    </form>
                                </td>
                                <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="cart_item_id" value="<?= $item['cartItemID'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Cart Total:</th>
                            <th>$<?= number_format($cartTotal, 2) ?></th>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-between">
        <a href="products.php" class="btn btn-outline-primary">Continue Shopping</a>
        <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>