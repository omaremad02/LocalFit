<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get cart items
$cartItems = getCartItems($_SESSION['user_id']);
$cartTotal = getCartTotal($_SESSION['user_id']);

// Redirect if cart is empty
if (empty($cartItems)) {
    header("Location: cart.php");
    exit();
}

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Basic validation
    if (empty($_POST['shipping_address'])) {
        $errors[] = "Shipping address is required";
    }
    
    // If no errors, process the order
    if (empty($errors)) {
        $shippingAddress = $_POST['shipping_address'];
        
        $orderID = createOrder($_SESSION['user_id'], $shippingAddress);
        
        if ($orderID) {
            // Redirect to order confirmation
            $_SESSION['order_id'] = $orderID;
            header("Location: order_confirmation.php");
            exit();
        } else {
            $errors[] = "Order processing failed. Please try again.";
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
                <li><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Shipping Information</h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="shipping_address" class="form-label">Shipping Address</label>
                        <textarea id="shipping_address" name="shipping_address" class="form-control" rows="3" required></textarea>
                    </div>
                    
                    <h5 class="mt-4 mb-3">Payment Information</h5>
                    <div class="alert alert-info">
                        <p class="mb-0">This is a demo application. No actual payment processing will occur.</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="card_number" class="form-label">Card Number</label>
                            <input type="text" id="card_number" class="form-control" placeholder="1234 5678 9012 3456">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="expiry" class="form-label">Expiry</label>
                            <input type="text" id="expiry" class="form-control" placeholder="MM/YY">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="cvv" class="form-label">CVV</label>
                            <input type="text" id="cvv" class="form-control" placeholder="123">
                        </div>
                    </div>
                    
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary">Place Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Order Summary</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <div>
                                <?= $item['name'] ?> x <?= $item['quantity'] ?>
                                <div class="text-muted small">Size: <?= $item['size'] ?></div>
                            </div>
                            <div>$<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between mb-2">
                    <div>Subtotal</div>
                    <div>$<?= number_format($cartTotal, 2) ?></div>
                </div>
                
                <div class="d-flex justify-content-between mb-2">
                    <div>Shipping</div>
                    <div>$0.00</div>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between mb-0">
                    <div><strong>Total</strong></div>
                    <div><strong>$<?= number_format($cartTotal, 2) ?></strong></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>