-- Run on existing wedding_service databases.
USE wedding_service;

-- Vendor-owned products.
ALTER TABLE products
  ADD COLUMN vendor_id INT UNSIGNED NULL AFTER id,
  ADD COLUMN is_approved TINYINT(1) NOT NULL DEFAULT 0 AFTER image;

UPDATE products
SET vendor_id = (
  SELECT id FROM vendors ORDER BY id ASC LIMIT 1
)
WHERE vendor_id IS NULL;

UPDATE products SET is_approved = 1 WHERE is_approved = 0;

ALTER TABLE products
  MODIFY vendor_id INT UNSIGNED NOT NULL,
  ADD CONSTRAINT fk_products_vendor FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE;

-- Vendor-owned packages.
ALTER TABLE packages
  ADD COLUMN vendor_id INT UNSIGNED NULL AFTER id;

UPDATE packages
SET vendor_id = (
  SELECT id FROM vendors ORDER BY id ASC LIMIT 1
)
WHERE vendor_id IS NULL;

ALTER TABLE packages
  MODIFY vendor_id INT UNSIGNED NOT NULL,
  ADD CONSTRAINT fk_packages_vendor FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE;

-- Deposit-ready bookings.
ALTER TABLE bookings
  ADD COLUMN deposit_amount DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER total_amount,
  ADD COLUMN remaining_amount DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER deposit_amount;

UPDATE bookings
SET remaining_amount = total_amount - deposit_amount
WHERE remaining_amount = 0;

-- Availability calendar.
CREATE TABLE IF NOT EXISTS vendor_availability (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  vendor_id INT UNSIGNED NOT NULL,
  available_date DATE NOT NULL,
  is_available TINYINT(1) NOT NULL DEFAULT 1,
  note VARCHAR(255) DEFAULT NULL,
  UNIQUE KEY uniq_vendor_date (vendor_id, available_date),
  FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE
) ENGINE=InnoDB;

ALTER TABLE services
  ADD COLUMN available_dates TEXT NULL AFTER image,
  ADD COLUMN package_details TEXT NULL AFTER available_dates,
  ADD COLUMN portfolio_images TEXT NULL AFTER package_details;

-- Event timeline per booking.
CREATE TABLE IF NOT EXISTS booking_timeline (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id INT UNSIGNED NOT NULL,
  actor_user_id INT UNSIGNED DEFAULT NULL,
  event_type VARCHAR(80) NOT NULL,
  event_message VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  FOREIGN KEY (actor_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Quotations between customer and vendor.
CREATE TABLE IF NOT EXISTS quotations (
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

-- Vendor subscription plans.
CREATE TABLE IF NOT EXISTS vendor_subscription_plans (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  monthly_price DECIMAL(12,2) NOT NULL DEFAULT 0,
  feature_rank TINYINT UNSIGNED NOT NULL DEFAULT 1,
  max_services INT UNSIGNED DEFAULT NULL,
  max_products INT UNSIGNED DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vendor_subscriptions (
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

INSERT IGNORE INTO vendor_subscription_plans (name, monthly_price, feature_rank, max_services, max_products) VALUES
('Free Vendor', 0.00, 1, 10, 10),
('Premium Vendor', 39.00, 2, 100, 100),
('Featured Vendor', 99.00, 3, NULL, NULL);
