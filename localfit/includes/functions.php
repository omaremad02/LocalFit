<?php
require_once 'db.php';

// User registration function
function registerUser($email, $password) {
    global $conn;
    
    // Check if user already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        return false;
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    $result = $stmt->execute([$email, $hashedPassword]);
    
    if ($result) {
        // Create a cart for the new user
        $userID = $conn->lastInsertId();
        $stmt = $conn->prepare("INSERT INTO carts (userID) VALUES (?)");
        $stmt->execute([$userID]);
        
        return $userID;
    }
    
    return false;
}

// User login function
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

// Get user's cart ID
function getUserCartID($userID) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT cartID FROM carts WHERE userID = ?");
    $stmt->execute([$userID]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cart) {
        return $cart['cartID'];
    } else {
        // Create a cart if not exists
        $stmt = $conn->prepare("INSERT INTO carts (userID) VALUES (?)");
        $stmt->execute([$userID]);
        return $conn->lastInsertId();
    }
}

// Add product to cart
function addToCart($userID, $productID, $quantity) {
    global $conn;
    
    $cartID = getUserCartID($userID);
    
    // Check if product already in cart
    $stmt = $conn->prepare("SELECT * FROM cartItems WHERE cartID = ? AND productID = ?");
    $stmt->execute([$cartID, $productID]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($item) {
        // Update quantity
        $newQuantity = $item['quantity'] + $quantity;
        $stmt = $conn->prepare("UPDATE cartItems SET quantity = ? WHERE cartItemID = ?");
        return $stmt->execute([$newQuantity, $item['cartItemID']]);
    } else {
        // Add new item
        $stmt = $conn->prepare("INSERT INTO cartItems (cartID, productID, quantity) VALUES (?, ?, ?)");
        return $stmt->execute([$cartID, $productID, $quantity]);
    }
}

// Get cart items
function getCartItems($userID) {
    global $conn;
    
    $cartID = getUserCartID($userID);
    
    $stmt = $conn->prepare("
        SELECT ci.cartItemID, ci.quantity, p.*, b.name as brandName
        FROM cartItems ci
        JOIN products p ON ci.productID = p.productID
        JOIN brands b ON p.brandID = b.brandID
        WHERE ci.cartID = ?
    ");
    $stmt->execute([$cartID]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Calculate cart total
function getCartTotal($userID) {
    $items = getCartItems($userID);
    $total = 0;
    
    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    return $total;
}

// Remove item from cart
function removeFromCart($cartItemID) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM cartItems WHERE cartItemID = ?");
    return $stmt->execute([$cartItemID]);
}

// Create order from cart
function createOrder($userID, $shippingAddress) {
    global $conn;
    
    $total = getCartTotal($userID);
    $cartItems = getCartItems($userID);
    
    if (count($cartItems) === 0) {
        return false;
    }
    
    try {
        $conn->beginTransaction();
        
        // Create order
        $stmt = $conn->prepare("INSERT INTO orders (userID, totalPrice, shippingAddress) VALUES (?, ?, ?)");
        $stmt->execute([$userID, $total, $shippingAddress]);
        $orderID = $conn->lastInsertId();
        
        // Add order items
        foreach ($cartItems as $item) {
            $stmt = $conn->prepare("INSERT INTO orderItems (orderID, productID, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$orderID, $item['productID'], $item['quantity']]);
        }
        
        // Clear cart
        $cartID = getUserCartID($userID);
        $stmt = $conn->prepare("DELETE FROM cartItems WHERE cartID = ?");
        $stmt->execute([$cartID]);
        
        $conn->commit();
        return $orderID;
    } catch (Exception $e) {
        $conn->rollBack();
        return false;
    }
}

// Get all products
function getAllProducts($filters = []) {
    global $conn;
    
    $where = [];
    $params = [];
    
    $query = "
        SELECT p.*, b.name as brandName
        FROM products p
        JOIN brands b ON p.brandID = b.brandID
    ";
    
    // Apply filters
    if (!empty($filters)) {
        if (isset($filters['brand']) && $filters['brand'] > 0) {
            $where[] = "p.brandID = ?";
            $params[] = $filters['brand'];
        }
        
        if (isset($filters['size']) && !empty($filters['size'])) {
            $where[] = "p.size = ?";
            $params[] = $filters['size'];
        }
        
        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $where[] = "p.price >= ?";
            $params[] = $filters['min_price'];
        }
        
        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $where[] = "p.price <= ?";
            $params[] = $filters['max_price'];
        }
    }
    
    if (!empty($where)) {
        $query .= " WHERE " . implode(" AND ", $where);
    }
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get all brands
function getAllBrands() {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM brands");
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get user orders
function getUserOrders($userID) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM orders WHERE userID = ? ORDER BY orderDate DESC");
    $stmt->execute([$userID]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get order details
function getOrderDetails($orderID) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT oi.quantity, p.*, b.name as brandName
        FROM orderItems oi
        JOIN products p ON oi.productID = p.productID
        JOIN brands b ON p.brandID = b.brandID
        WHERE oi.orderID = ?
    ");
    $stmt->execute([$orderID]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Admin functions
function addBrand($name, $logoURL, $socialLinks) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO brands (name, logoURL, socialLinks) VALUES (?, ?, ?)");
    return $stmt->execute([$name, $logoURL, $socialLinks]);
}

function updateBrand($brandID, $name, $logoURL, $socialLinks) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE brands SET name = ?, logoURL = ?, socialLinks = ? WHERE brandID = ?");
    return $stmt->execute([$name, $logoURL, $socialLinks, $brandID]);
}

function deleteBrand($brandID) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM brands WHERE brandID = ?");
    return $stmt->execute([$brandID]);
}

function addProduct($name, $description, $price, $size, $imageURL, $brandID) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, size, imageURL, brandID) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$name, $description, $price, $size, $imageURL, $brandID]);
}

function updateProduct($productID, $name, $description, $price, $size, $imageURL, $brandID) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, size = ?, imageURL = ?, brandID = ? WHERE productID = ?");
    return $stmt->execute([$name, $description, $price, $size, $imageURL, $brandID, $productID]);
}

function deleteProduct($productID) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM products WHERE productID = ?");
    return $stmt->execute([$productID]);
}

function getAllUsers() {
    global $conn;
    
    $stmt = $conn->prepare("SELECT userID, email, isAdmin FROM users");
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllOrders() {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT o.*, u.email as userEmail
        FROM orders o
        JOIN users u ON o.userID = u.userID
        ORDER BY orderDate DESC
    ");
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateOrderStatus($orderID, $status) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE orderID = ?");
    return $stmt->execute([$status, $orderID]);
}
?>