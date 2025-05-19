-- Create database
CREATE DATABASE IF NOT EXISTS localfit;
USE localfit;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    userID INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    isAdmin BOOLEAN DEFAULT FALSE,
    INDEX idx_email (email)
);

-- Brands table
CREATE TABLE IF NOT EXISTS brands (
    brandID INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    logoURL VARCHAR(255),
    socialLinks TEXT,
    INDEX idx_name (name)
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    productID INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price FLOAT NOT NULL,
    size VARCHAR(50),
    imageURL VARCHAR(255),
    brandID INT,
    FOREIGN KEY (brandID) REFERENCES brands(brandID),
    INDEX idx_brandID (brandID),
    INDEX idx_price (price)
);

-- Cart table
CREATE TABLE IF NOT EXISTS carts (
    cartID INT AUTO_INCREMENT PRIMARY KEY,
    userID INT,
    FOREIGN KEY (userID) REFERENCES users(userID),
    INDEX idx_userID (userID)
);

-- CartItems table
CREATE TABLE IF NOT EXISTS cartItems (
    cartItemID INT AUTO_INCREMENT PRIMARY KEY,
    cartID INT,
    productID INT,
    quantity INT DEFAULT 1,
    FOREIGN KEY (cartID) REFERENCES carts(cartID),
    FOREIGN KEY (productID) REFERENCES products(productID),
    INDEX idx_cartID (cartID),
    INDEX idx_productID (productID)
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    orderID INT AUTO_INCREMENT PRIMARY KEY,
    userID INT,
    totalPrice FLOAT NOT NULL,
    shippingAddress TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    orderDate DATETIME DEFAULT CURRENT_TIMESTAMP
    FOREIGN KEY (userID) REFERENCES users(userID),
    INDEX idx_userID (userID),
    INDEX idx_orderDate (orderDate)
);

-- OrderItems table
CREATE TABLE IF NOT EXISTS orderItems (
    orderItemID INT AUTO_INCREMENT PRIMARY KEY,
    orderID INT,
    productID INT,
    quantity INT,
    FOREIGN KEY (orderID) REFERENCES orders(orderID),
    FOREIGN KEY (productID) REFERENCES products(productID),
    INDEX idx_orderID (orderID),
    INDEX idx_productID (productID)
);

-- Insert sample admin user
INSERT INTO users (email, password, isAdmin) VALUES 
('admin@localfit.com', '$2y$10$6AOa/bFbBQuPvYhZgL3N/.8vl3iJZKI5uYDN2dXjEG5fpPgUTXJvi', TRUE),
('user@localfit.com', '$2y$10$7BOa/cGcCRvQwZiKh4MO/..wl4jKZLI6vZEN3eYkFH6gpQiVUYJwj', FALSE);

-- Insert sample brands
INSERT INTO brands (name, logoURL, socialLinks) VALUES 
('Local Threads', 'assets/images/placeholder.jpg', 'Twitter: @LocalThreads, Instagram: @local_threads'),
('Urban Local', 'assets/images/placeholder.jpg', 'Twitter: @UrbanLocal, Instagram: @urban_local'),
('Community Fashion', 'assets/images/placeholder.jpg', 'Twitter: @CommFashion, Instagram: @community_fashion'),
('EcoWear', 'assets/images/placeholder.jpg', 'Twitter: @EcoWear, Instagram: @ecowear_designs');

-- Insert sample products
INSERT INTO products (name, description, price, size, imageURL, brandID) VALUES 
('Classic T-Shirt', 'Locally made cotton t-shirt', 29.99, 'M', 'assets/images/placeholder.jpg', 1),
('Denim Jeans', 'Handcrafted denim jeans', 79.99, '32', 'assets/images/placeholder.jpg', 1),
('Summer Dress', 'Light summer dress made locally', 49.99, 'S', 'assets/images/placeholder.jpg', 2),
('Knit Sweater', 'Warm knit sweater for winter', 59.99, 'L', 'assets/images/placeholder.jpg', 3),
('Eco T-Shirt', 'Sustainable organic cotton t-shirt', 34.99, 'M', 'assets/images/placeholder.jpg', 4),
('Casual Shorts', 'Comfortable locally made shorts', 39.99, 'M', 'assets/images/placeholder.jpg', 2),
('Winter Jacket', 'Warm locally crafted jacket', 99.99, 'L', 'assets/images/placeholder.jpg', 3);