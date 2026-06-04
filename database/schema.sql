-- Wedding Service E-Commerce Database
-- Import in phpMyAdmin (XAMPP): create DB wedding_service, then import this file.

CREATE DATABASE IF NOT EXISTS wedding_service CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE wedding_service;

-- Users (customer, vendor, admin)
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role ENUM('customer', 'vendor', 'admin') NOT NULL DEFAULT 'customer',
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    email_verified_at DATETIME DEFAULT NULL,
    remember_token VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role (role),
    INDEX idx_users_email (email)
) ENGINE=InnoDB;

-- Vendor profiles
CREATE TABLE vendors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    business_name VARCHAR(150) NOT NULL,
    slug VARCHAR(160) NOT NULL UNIQUE,
    description TEXT,
    location VARCHAR(120) DEFAULT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    website VARCHAR(255) DEFAULT NULL,
    logo VARCHAR(255) DEFAULT NULL,
    rating_avg DECIMAL(3,2) NOT NULL DEFAULT 0,
    rating_count INT UNSIGNED NOT NULL DEFAULT 0,
    is_approved TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE service_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    icon VARCHAR(50) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE services (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(12,2) NOT NULL DEFAULT 0,
    duration_hours INT UNSIGNED DEFAULT NULL,
    location VARCHAR(120) DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    available_dates TEXT,
    package_details TEXT,
    portfolio_images TEXT,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    is_approved TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('draft', 'active', 'inactive') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES service_categories(id),
    INDEX idx_services_category (category_id),
    INDEX idx_services_vendor (vendor_id)
) ENGINE=InnoDB;

CREATE TABLE packages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(12,2) NOT NULL,
    duration_days INT UNSIGNED DEFAULT NULL,
    included_services TEXT,
    image VARCHAR(255) DEFAULT NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE product_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(12,2) NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    image VARCHAR(255) DEFAULT NULL,
    is_approved TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES product_categories(id)
) ENGINE=InnoDB;

CREATE TABLE bookings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    vendor_id INT UNSIGNED DEFAULT NULL,
    service_id INT UNSIGNED DEFAULT NULL,
    package_id INT UNSIGNED DEFAULT NULL,
    event_date DATE NOT NULL,
    guest_count INT UNSIGNED DEFAULT NULL,
    special_requests TEXT,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    deposit_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    remaining_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    status ENUM('pending', 'confirmed', 'in_progress', 'rejected', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    payment_status ENUM('unpaid', 'paid', 'refunded') NOT NULL DEFAULT 'unpaid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE SET NULL,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE vendor_availability (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT UNSIGNED NOT NULL,
    available_date DATE NOT NULL,
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    note VARCHAR(255) DEFAULT NULL,
    UNIQUE KEY uniq_vendor_date (vendor_id, available_date),
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE booking_timeline (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL,
    actor_user_id INT UNSIGNED DEFAULT NULL,
    event_type VARCHAR(80) NOT NULL,
    event_message VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (actor_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE quotations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    vendor_id INT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    details TEXT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    status ENUM('requested', 'sent', 'accepted', 'rejected', 'expired') NOT NULL DEFAULT 'requested',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE vendor_subscription_plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    monthly_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    feature_rank TINYINT UNSIGNED NOT NULL DEFAULT 1,
    max_services INT UNSIGNED DEFAULT NULL,
    max_products INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE vendor_subscriptions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT UNSIGNED NOT NULL,
    plan_id INT UNSIGNED NOT NULL,
    status ENUM('active', 'paused', 'cancelled') NOT NULL DEFAULT 'active',
    started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES vendor_subscription_plans(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    order_number VARCHAR(32) NOT NULL UNIQUE,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    discount DECIMAL(12,2) NOT NULL DEFAULT 0,
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
    payment_method ENUM('cod', 'bank_transfer', 'manual') NOT NULL DEFAULT 'cod',
    payment_status ENUM('unpaid', 'paid') NOT NULL DEFAULT 'unpaid',
    shipping_address TEXT,
    coupon_code VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    unit_price DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

CREATE TABLE payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    payable_type ENUM('booking', 'order') NOT NULL,
    payable_id INT UNSIGNED NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    method ENUM('cod', 'bank_transfer', 'manual', 'paypal', 'stripe') NOT NULL,
    status ENUM('pending', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    proof_image VARCHAR(255) DEFAULT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    reviewable_type ENUM('service', 'product', 'vendor') NOT NULL,
    reviewable_id INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    is_approved TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE wishlist (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    item_type ENUM('service', 'product') NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_wishlist (user_id, item_type, item_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE gallery (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT UNSIGNED DEFAULT NULL,
    category ENUM('wedding', 'decoration', 'dress', 'makeup', 'venue', 'other') NOT NULL DEFAULT 'wedding',
    title VARCHAR(150) DEFAULT NULL,
    image_path VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sender_id INT UNSIGNED NOT NULL,
    receiver_id INT UNSIGNED NOT NULL,
    subject VARCHAR(200) DEFAULT NULL,
    body TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'info',
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE blogs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    author_id INT UNSIGNED DEFAULT NULL,
    title VARCHAR(220) NOT NULL,
    slug VARCHAR(240) NOT NULL UNIQUE,
    excerpt TEXT,
    content LONGTEXT NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    published_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE contacts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    subject VARCHAR(200) DEFAULT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE site_settings (
    setting_key VARCHAR(80) PRIMARY KEY,
    setting_value TEXT
) ENGINE=InnoDB;

-- Seed: password for all demo users is "password" (bcrypt hash below)
INSERT INTO users (role, first_name, last_name, email, password_hash, phone, email_verified_at) VALUES
('admin', 'Site', 'Admin', 'admin@wedding.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+15550142099', NOW()),
('customer', 'Emma', 'Rose', 'customer@wedding.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+15550142001', NOW()),
('vendor', 'James', 'Studio', 'vendor@wedding.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+15550142002', NOW());

INSERT INTO vendors (user_id, business_name, slug, description, location, phone, rating_avg, rating_count, is_approved) VALUES
(3, 'Golden Lens Photography', 'golden-lens', 'Award-winning wedding photography with cinematic storytelling.', 'Bloom City', '+15550142002', 4.85, 42, 1);

INSERT INTO service_categories (name, slug, icon, sort_order) VALUES
('Photography', 'photography', 'camera', 1),
('Videography', 'videography', 'video', 2),
('Makeup Artist', 'makeup', 'sparkle', 3),
('Wedding Dress', 'dress', 'dress', 4),
('Decoration', 'decoration', 'flower', 5),
('Catering', 'catering', 'food', 6),
('Event Hall', 'event-hall', 'venue', 7),
('DJ & Music', 'dj', 'music', 8),
('Invitation Cards', 'invitation', 'card', 9),
('Car Rental', 'car', 'car', 10),
('Honeymoon', 'honeymoon', 'plane', 11);

INSERT INTO services (vendor_id, category_id, title, slug, description, price, duration_hours, location, is_featured, is_approved, status) VALUES
(1, 1, 'Full Day Wedding Photography', 'full-day-photography', '8 hours coverage, 400+ edited photos, online gallery.', 2800.00, 8, 'Bloom City', 1, 1, 'active'),
(1, 1, 'Engagement Session', 'engagement-session', '2-hour session at location of your choice.', 450.00, 2, 'Bloom City', 0, 1, 'active'),
(1, 2, 'Cinematic Wedding Film', 'wedding-film', 'Highlight film + full ceremony coverage.', 3200.00, 10, 'Bloom City', 1, 1, 'active');

INSERT INTO packages (vendor_id, name, slug, description, price, duration_days, included_services, is_featured, status) VALUES
(1, 'Silver Package', 'silver', 'Essential wedding services for intimate celebrations.', 5200.00, 1, 'Photography (4h), Makeup, Basic decoration', 0, 'active'),
(1, 'Gold Package', 'gold', 'Our most popular complete wedding package.', 9800.00, 2, 'Photography (8h), Videography, Makeup, Decoration, DJ', 1, 'active'),
(1, 'Platinum Package', 'platinum', 'Premium experience with top vendors.', 15800.00, 3, 'Full photography, film, styling, catering, venue styling', 1, 'active'),
(1, 'Luxury Wedding Package', 'luxury', 'White-glove planning and execution.', 28000.00, 5, 'Everything in Platinum plus honeymoon planning', 0, 'active');

INSERT INTO product_categories (name, slug) VALUES
('Wedding Dresses', 'dresses'),
('Rings', 'rings'),
('Flowers', 'flowers'),
('Gifts', 'gifts'),
('Accessories', 'accessories'),
('Invitation Cards', 'invitations');

INSERT INTO products (vendor_id, category_id, name, slug, description, price, stock, is_approved, is_active) VALUES
(1, 1, 'Ivory Lace A-Line Gown', 'ivory-lace-gown', 'Elegant lace with subtle train.', 1899.00, 5, 1, 1),
(1, 2, 'Rose Gold Band Set', 'rose-gold-bands', 'Matching his and hers bands.', 890.00, 12, 1, 1),
(1, 3, 'Bridal Bouquet — Blush', 'blush-bouquet', 'Fresh seasonal blush roses.', 185.00, 20, 1, 1),
(1, 4, 'Personalized Guest Favor Box', 'favor-box', 'Custom names and wedding date.', 4.50, 100, 1, 1),
(1, 6, 'Gold Foil Invitation Suite', 'gold-foil-invite', '50 cards with envelopes.', 320.00, 30, 1, 1);

INSERT INTO vendor_subscription_plans (name, monthly_price, feature_rank, max_services, max_products) VALUES
('Free Vendor', 0.00, 1, 10, 10),
('Premium Vendor', 39.00, 2, 100, 100),
('Featured Vendor', 99.00, 3, NULL, NULL);

INSERT INTO gallery (vendor_id, category, title, image_path, sort_order) VALUES
(1, 'wedding', 'Garden ceremony', 'assets/images/gallery-1.jpg', 1),
(1, 'wedding', 'First dance', 'assets/images/gallery-2.jpg', 2),
(NULL, 'decoration', 'Blush tablescape', 'assets/images/gallery-3.jpg', 3),
(NULL, 'makeup', 'Soft glam bridal', 'assets/images/gallery-4.jpg', 4),
(NULL, 'venue', 'Ballroom setup', 'assets/images/gallery-5.jpg', 5),
(NULL, 'dress', 'Lace detail', 'assets/images/gallery-6.jpg', 6);

INSERT INTO blogs (author_id, title, slug, excerpt, content, is_published) VALUES
(1, '10 Wedding Planning Tips for 2026', 'wedding-planning-tips-2026', 'Start early and stay organized.', '<p>Planning a wedding can feel overwhelming. Break tasks into monthly milestones, set a realistic budget buffer of 10%, and book your venue and photographer first.</p><p>Delegate tasks to your wedding party and use a shared planning hub to keep everyone aligned.</p>', 1),
(1, 'Budget-Friendly Decor Ideas', 'budget-decor-ideas', 'Beautiful celebrations without breaking the bank.', '<p>Focus on lighting and florals in high-impact areas: entrance, head table, and dance floor. Repurpose ceremony flowers for the reception.</p>', 1);

INSERT INTO site_settings (setting_key, setting_value) VALUES
('site_name', 'Lumière Weddings'),
('currency', 'USD'),
('bank_details', 'Account: Wedding Service — IBAN demo 000123456');
