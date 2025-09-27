-- =====================================================
-- PeakPH Commerce Database Setup Script
-- =====================================================

-- Create the database
CREATE DATABASE IF NOT EXISTS peakph_db;
USE peakph_db;

-- =====================================================
-- 1. INVENTORY TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS inventory (
  id int(11) NOT NULL AUTO_INCREMENT,
  product_name varchar(255) NOT NULL,
  price decimal(10,2) NOT NULL,
  stock int(11) NOT NULL,
  tag varchar(50) DEFAULT NULL,
  image varchar(255) DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  label varchar(50) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 2. USERS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
  id int(11) NOT NULL AUTO_INCREMENT,
  username varchar(100) NOT NULL,
  email varchar(150) NOT NULL UNIQUE,
  password varchar(255) NOT NULL,
  role enum('User','Admin') NOT NULL DEFAULT 'User',
  status enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 3. PRODUCTS TABLE (for catalog compatibility)
-- =====================================================
CREATE TABLE IF NOT EXISTS products (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  price decimal(10,2) NOT NULL,
  image_path varchar(255) DEFAULT NULL,
  category varchar(100) DEFAULT NULL,
  description text DEFAULT NULL,
  stock int(11) DEFAULT 0,
  status enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 4. AUDIT TRAIL TABLE (for tracking changes)
-- =====================================================
CREATE TABLE IF NOT EXISTS audit_trail (
  id int(11) NOT NULL AUTO_INCREMENT,
  table_name varchar(100) NOT NULL,
  record_id int(11) NOT NULL,
  action enum('INSERT','UPDATE','DELETE') NOT NULL,
  old_values text DEFAULT NULL,
  new_values text DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  user_email varchar(150) DEFAULT NULL,
  timestamp timestamp NOT NULL DEFAULT current_timestamp(),
  ip_address varchar(45) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_table_record (table_name, record_id),
  KEY idx_timestamp (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 5. ORDERS TABLE (for order management)
-- =====================================================
CREATE TABLE IF NOT EXISTS orders (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) DEFAULT NULL,
  customer_name varchar(255) NOT NULL,
  customer_email varchar(150) NOT NULL,
  customer_phone varchar(20) DEFAULT NULL,
  total_amount decimal(10,2) NOT NULL,
  status enum('Pending','Processing','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
  shipping_address text NOT NULL,
  order_date timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 6. ORDER ITEMS TABLE (for order details)
-- =====================================================
CREATE TABLE IF NOT EXISTS order_items (
  id int(11) NOT NULL AUTO_INCREMENT,
  order_id int(11) NOT NULL,
  product_id int(11) NOT NULL,
  product_name varchar(255) NOT NULL,
  quantity int(11) NOT NULL,
  price decimal(10,2) NOT NULL,
  total decimal(10,2) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES inventory(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 7. CONTENT MANAGEMENT TABLES
-- =====================================================

-- Carousel/Banner Management
CREATE TABLE IF NOT EXISTS carousel (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(255) NOT NULL,
  description text DEFAULT NULL,
  image_path varchar(255) NOT NULL,
  link_url varchar(255) DEFAULT NULL,
  is_active tinyint(1) NOT NULL DEFAULT 1,
  display_order int(11) DEFAULT 0,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Best Sellers Configuration
CREATE TABLE IF NOT EXISTS bestsellers (
  id int(11) NOT NULL AUTO_INCREMENT,
  product_id int(11) NOT NULL,
  display_order int(11) DEFAULT 0,
  is_active tinyint(1) NOT NULL DEFAULT 1,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  FOREIGN KEY (product_id) REFERENCES inventory(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- New Arrivals Configuration
CREATE TABLE IF NOT EXISTS new_arrivals (
  id int(11) NOT NULL AUTO_INCREMENT,
  product_id int(11) NOT NULL,
  display_order int(11) DEFAULT 0,
  is_active tinyint(1) NOT NULL DEFAULT 1,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  FOREIGN KEY (product_id) REFERENCES inventory(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 8. INSERT SAMPLE DATA
-- =====================================================

-- Sample Admin User
INSERT INTO users (username, email, password, role, status) VALUES 
('admin', 'admin@peakph.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'Active');

-- Sample Inventory Data
INSERT INTO inventory (product_name, price, stock, tag, image, label) VALUES 
('Professional Camping Tent', 2500.00, 25, 'tent', 'Assets/Gallery_Images/TentSample.jpg', 'Featured'),
('Portable Cooking Set', 750.00, 40, 'cooking', 'Assets/Gallery_Images/CookingGearSample.png', 'Popular'),
('Hiking Backpack Pro', 1800.00, 15, 'equipment', 'Assets/Gallery_Images/HikingBackpackSample.png', 'New'),
('Travel Boots Waterproof', 1200.00, 30, 'equipment', 'Assets/Gallery_Images/TravelBootsSample.png', 'Bestseller'),
('Survival Kit Complete', 950.00, 20, 'equipment', 'Assets/Gallery_Images/Survival Kit Sample.png', 'Essential'),
('Camping Stove Portable', 650.00, 35, 'cooking', 'Assets/Gallery_Images/Camping Stove Sample.png', 'Compact');

-- Sample Products for Catalog Compatibility
INSERT INTO products (name, price, image_path, category, description, stock, status) VALUES 
('Professional Camping Tent', 2500.00, 'Assets/Gallery_Images/TentSample.jpg', 'tent', 'Professional grade camping tent for extreme weather conditions', 25, 'Active'),
('Portable Cooking Set', 750.00, 'Assets/Gallery_Images/CookingGearSample.png', 'cooking', 'Complete portable cooking set for outdoor adventures', 40, 'Active'),
('Hiking Backpack Pro', 1800.00, 'Assets/Gallery_Images/HikingBackpackSample.png', 'equipment', 'Professional hiking backpack with advanced features', 15, 'Active');

-- Sample Carousel Data
INSERT INTO carousel (title, description, image_path, is_active, display_order) VALUES 
('Welcome to PeakPH', 'Your ultimate destination for camping gear', 'Assets/Carousel_Picts/DeaksV2.png', 1, 1),
('Best Deals Available', 'Check out our amazing deals on camping equipment', 'Assets/Carousel_Picts/Deals.png', 1, 2),
('Special Vouchers', 'Get exclusive vouchers for your next adventure', 'Assets/Carousel_Picts/Vouchers.png', 1, 3);

-- =====================================================
-- 9. CREATE INDEXES FOR PERFORMANCE
-- =====================================================
CREATE INDEX idx_inventory_tag ON inventory(tag);
CREATE INDEX idx_inventory_stock ON inventory(stock);
CREATE INDEX idx_inventory_created ON inventory(created_at);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_products_category ON products(category);
CREATE INDEX idx_products_status ON products(status);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_date ON orders(order_date);

-- =====================================================
-- 10. SHOW SETUP COMPLETION
-- =====================================================
SELECT 'Database setup completed successfully!' as Status;
SELECT 'Tables created:' as Info;
SHOW TABLES;