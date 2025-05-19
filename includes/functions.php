<?php
require_once 'config.php';

function loginUser($email, $password) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['userID'];
        $_SESSION['is_admin'] = $user['isAdmin'];
        return true;
    }
    return false;
}

function registerUser($email, $password) {
    global $conn;
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    return $stmt->execute([$email, $hashedPassword]);
}

function getCartItems($userID) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT p.*, ci.quantity, b.name as brandName
        FROM cartItems ci
        JOIN carts c ON ci.cartID = c.cartID
        JOIN products p ON ci.productID = p.productID
        JOIN brands b ON p.brandID = b.brandID
        WHERE c.userID = ?
    ");
    $stmt->execute([$userID]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCartTotal($userID) {
    $items = getCartItems($userID);
    $total = 0;
    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

function createOrder($userID, $shippingAddress) {
    global $conn;
    
    $cartTotal = getCartTotal($userID);
    $cartItems = getCartItems($userID);
    
    try {
        $conn->beginTransaction();
        
        // Create order
        $stmt = $conn->prepare("
            INSERT INTO orders (userID, totalPrice, shippingAddress)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$userID, $cartTotal, $shippingAddress]);
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
        $stmt = $conn->prepare("
            DELETE ci FROM cartItems ci
            JOIN carts c ON ci.cartID = c.cartID
            WHERE c.userID = ?
        ");
        $stmt->execute([$userID]);
        
        $conn->commit();
        return $orderID;
    } catch (Exception $e) {
        $conn->rollBack();
        return false;
    }
}

function addToCart($userID, $productID, $quantity) {
    global $conn;
    
    // Get or create cart
    $stmt = $conn->prepare("SELECT cartID FROM carts WHERE userID = ?");
    $stmt->execute([$userID]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cart) {
        $stmt = $conn->prepare("INSERT INTO carts (userID) VALUES (?)");
        $stmt->execute([$userID]);
        $cartID = $conn->lastInsertId();
    } else {
        $cartID = $cart['cartID'];
    }
    
    // Check if item already in cart
    $stmt = $conn->prepare("
        SELECT cartItemID FROM cartItems 
        WHERE cartID = ? AND productID = ?
    ");
    $stmt->execute([$cartID, $productID]);
    $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingItem) {
        $stmt = $conn->prepare("
            UPDATE cartItems 
            SET quantity = quantity + ? 
            WHERE cartItemID = ?
        ");
        $stmt->execute([$quantity, $existingItem['cartItemID']]);
    } else {
        $stmt = $conn->prepare("
            INSERT INTO cartItems (cartID, productID, quantity)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$cartID, $productID, $quantity]);
    }
}

function getAllProducts($filters = []) {
    global $conn;
    
    $query = "
        SELECT p.*, b.name as brandName
        FROM products p
        JOIN brands b ON p.brandID = b.brandID
        WHERE 1=1
    ";
    $params = [];
    
    if (isset($filters['brand'])) {
        $query .= " AND p.brandID = ?";
        $params[] = $filters['brand'];
    }
    
    if (isset($filters['size'])) {
        $query .= " AND p.size = ?";
        $params[] = $filters['size'];
    }
    
    if (isset($filters['min_price'])) {
        $query .= " AND p.price >= ?";
        $params[] = $filters['min_price'];
    }
    
    if (isset($filters['max_price'])) {
        $query .= " AND p.price <= ?";
        $params[] = $filters['max_price'];
    }
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllBrands() {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM brands ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getOrderDetails($orderID) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT p.*, oi.quantity, b.name as brandName
        FROM orderItems oi
        JOIN products p ON oi.productID = p.productID
        JOIN brands b ON p.brandID = b.brandID
        WHERE oi.orderID = ?
    ");
    $stmt->execute([$orderID]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>