-- Run once on existing databases (phpMyAdmin or mysql CLI)
USE wedding_service;

ALTER TABLE bookings
  MODIFY status ENUM('pending', 'confirmed', 'in_progress', 'rejected', 'completed', 'cancelled')
  NOT NULL DEFAULT 'pending';
