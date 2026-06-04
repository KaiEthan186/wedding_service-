-- Demo prototype data — run in phpMyAdmin on existing wedding_service database
-- Adds vendors, services, products, gallery images (Unsplash URLs)
USE wedding_service;

-- Images for existing records
UPDATE vendors SET logo = 'https://images.unsplash.com/photo-1519741497674-611481863552?w=600&q=80' WHERE slug = 'golden-lens';

UPDATE services SET image = 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=800&q=80' WHERE slug = 'full-day-photography';
UPDATE services SET image = 'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?w=800&q=80' WHERE slug = 'engagement-session';
UPDATE services SET image = 'https://images.unsplash.com/photo-1492691527719-9d1e072e638a?w=800&q=80' WHERE slug = 'wedding-film';

UPDATE products SET image = 'https://images.unsplash.com/photo-1594552072238-4b08548b0c69?w=800&q=80' WHERE slug = 'ivory-lace-gown';
UPDATE products SET image = 'https://images.unsplash.com/photo-1605100804763-247f67bf4017?w=800&q=80' WHERE slug = 'rose-gold-bands';
UPDATE products SET image = 'https://images.unsplash.com/photo-1490756845068-913cb9779757?w=800&q=80' WHERE slug = 'blush-bouquet';
UPDATE products SET image = 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=800&q=80' WHERE slug = 'favor-box';
UPDATE products SET image = 'https://images.unsplash.com/photo-1586075010923-2dd4570fb338?w=800&q=80' WHERE slug = 'gold-foil-invite';

-- Additional vendor accounts (password: password)
INSERT INTO users (role, first_name, last_name, email, password_hash, phone, email_verified_at) VALUES
('vendor', 'Sarah', 'Bloom', 'sarah@bloompetals.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+15550142010', NOW()),
('vendor', 'Mia', 'Chen', 'mia@glamstudio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+15550142011', NOW()),
('vendor', 'Robert', 'Hall', 'robert@crystalhall.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+15550142012', NOW()),
('vendor', 'Chef', 'Antonio', 'chef@sweettable.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+15550142013', NOW()),
('vendor', 'DJ', 'Marcus', 'dj@beatwave.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+15550142014', NOW());

INSERT INTO vendors (user_id, business_name, slug, description, location, phone, logo, rating_avg, rating_count, is_approved)
SELECT u.id, 'Bloom Petals Florals', 'bloom-petals', 'Luxury floral design for ceremonies and receptions.', 'Bloom City', '+15550142010',
  'https://images.unsplash.com/photo-1490756845068-913cb9779757?w=600&q=80', 4.72, 28, 1 FROM users u WHERE u.email = 'sarah@bloompetals.com'
UNION ALL SELECT u.id, 'Glam Studio Makeup', 'glam-studio', 'Bridal makeup and hair styling for all skin tones.', 'Bloom City', '+15550142011',
  'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?w=600&q=80', 4.91, 56, 1 FROM users u WHERE u.email = 'mia@glamstudio.com'
UNION ALL SELECT u.id, 'Crystal Events Hall', 'crystal-hall', 'Elegant ballroom venue with in-house coordination.', 'Riverside', '+15550142012',
  'https://images.unsplash.com/photo-1519167758481-83f550bb49b8?w=600&q=80', 4.68, 34, 1 FROM users u WHERE u.email = 'robert@crystalhall.com'
UNION ALL SELECT u.id, 'Sweet Table Catering', 'sweet-table', 'Farm-to-table wedding menus and dessert bars.', 'Bloom City', '+15550142013',
  'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=600&q=80', 4.80, 41, 1 FROM users u WHERE u.email = 'chef@sweettable.com'
UNION ALL SELECT u.id, 'BeatWave DJ & Music', 'beatwave-dj', 'Professional DJs, lighting, and dance-floor energy.', 'Metro Area', '+15550142014',
  'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=600&q=80', 4.77, 22, 1 FROM users u WHERE u.email = 'dj@beatwave.com';

INSERT INTO services (vendor_id, category_id, title, slug, description, price, duration_hours, location, image, is_featured, is_approved, status)
SELECT v.id, 5, 'Garden Ceremony Florals', 'garden-ceremony-florals', 'Arches, aisle petals, and bridal bouquet package.', 1850.00, 6, 'Bloom City',
  'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?w=800&q=80', 1, 1, 'active' FROM vendors v WHERE v.slug = 'bloom-petals'
UNION ALL SELECT v.id, 5, 'Reception Centerpieces', 'reception-centerpieces', '10 tables with seasonal floral centerpieces.', 980.00, 4, 'Bloom City',
  'https://images.unsplash.com/photo-1478146896981-b80fe463b330?w=800&q=80', 0, 1, 'active' FROM vendors v WHERE v.slug = 'bloom-petals'
UNION ALL SELECT v.id, 3, 'Bridal Glam Package', 'bridal-glam-package', 'Trial session plus wedding day makeup and hair.', 420.00, 3, 'Bloom City',
  'https://images.unsplash.com/photo-1487412947147-5cebf100ffc2?w=800&q=80', 1, 1, 'active' FROM vendors v WHERE v.slug = 'glam-studio'
UNION ALL SELECT v.id, 3, 'Bridesmaids Makeup', 'bridesmaids-makeup', 'Soft glam for up to 4 bridesmaids.', 280.00, 2, 'Bloom City',
  'https://images.unsplash.com/photo-1522335781263-6da2926b364e?w=800&q=80', 0, 1, 'active' FROM vendors v WHERE v.slug = 'glam-studio'
UNION ALL SELECT v.id, 7, 'Grand Ballroom Rental', 'grand-ballroom-rental', '500-guest ballroom with staging and bridal suite.', 6500.00, 12, 'Riverside',
  'https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=800&q=80', 1, 1, 'active' FROM vendors v WHERE v.slug = 'crystal-hall'
UNION ALL SELECT v.id, 7, 'Garden Terrace Venue', 'garden-terrace-venue', 'Outdoor terrace for intimate celebrations up to 120 guests.', 3800.00, 8, 'Riverside',
  'https://images.unsplash.com/photo-1465492120980-0b1273bca3bb?w=800&q=80', 0, 1, 'active' FROM vendors v WHERE v.slug = 'crystal-hall'
UNION ALL SELECT v.id, 6, 'Plated Dinner Service', 'plated-dinner-service', 'Three-course plated dinner for up to 150 guests.', 4200.00, 6, 'Bloom City',
  'https://images.unsplash.com/photo-1555244162-803834f70033?w=800&q=80', 1, 1, 'active' FROM vendors v WHERE v.slug = 'sweet-table'
UNION ALL SELECT v.id, 6, 'Dessert & Candy Bar', 'dessert-candy-bar', 'Custom dessert table with wedding cake coordination.', 890.00, 3, 'Bloom City',
  'https://images.unsplash.com/photo-1535254973040-607b684b0518?w=800&q=80', 0, 1, 'active' FROM vendors v WHERE v.slug = 'sweet-table'
UNION ALL SELECT v.id, 8, 'Reception DJ (5 hours)', 'reception-dj-5h', 'DJ, MC, dance-floor lighting, and curated playlist.', 1200.00, 5, 'Metro Area',
  'https://images.unsplash.com/photo-1571266028243-e68f8570c9e2?w=800&q=80', 1, 1, 'active' FROM vendors v WHERE v.slug = 'beatwave-dj'
UNION ALL SELECT v.id, 8, 'Ceremony Sound System', 'ceremony-sound', 'Wireless mics and speakers for vows and music.', 450.00, 2, 'Metro Area',
  'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=800&q=80', 0, 1, 'active' FROM vendors v WHERE v.slug = 'beatwave-dj';

INSERT INTO products (vendor_id, category_id, name, slug, description, price, stock, image, is_approved, is_active) VALUES
((SELECT id FROM vendors WHERE slug = 'golden-lens'), 1, 'Satin Mermaid Gown', 'satin-mermaid-gown', 'Modern mermaid silhouette with chapel train.', 2199.00, 4,
  'https://images.unsplash.com/photo-1566174053879-31528523f8ae?w=800&q=80', 1, 1),
((SELECT id FROM vendors WHERE slug = 'golden-lens'), 1, 'Off-Shoulder Tulle Dress', 'off-shoulder-tulle', 'Romantic tulle skirt with beaded bodice.', 1750.00, 6,
  'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=800&q=80', 1, 1),
((SELECT id FROM vendors WHERE slug = 'golden-lens'), 2, 'Vintage Diamond Solitaire', 'vintage-solitaire', 'Classic solitaire with pavé band.', 1450.00, 8,
  'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=800&q=80', 1, 1),
((SELECT id FROM vendors WHERE slug = 'golden-lens'), 2, 'Pearl Drop Earrings', 'pearl-drop-earrings', 'Bridal pearl drops in 14k gold.', 220.00, 25,
  'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=800&q=80', 1, 1),
((SELECT id FROM vendors WHERE slug = 'bloom-petals'), 3, 'White Rose Cascade Bouquet', 'white-rose-bouquet', 'Premium white roses with eucalyptus.', 245.00, 15,
  'https://images.unsplash.com/photo-1520854221256-17451af3e865?w=800&q=80', 1, 1),
((SELECT id FROM vendors WHERE slug = 'bloom-petals'), 3, 'Table Runner Greenery', 'table-runner-greenery', 'Greenery runner for head table (3m).', 95.00, 30,
  'https://images.unsplash.com/photo-1478146896981-b80fe463b330?w=800&q=80', 1, 1),
((SELECT id FROM vendors WHERE slug = 'glam-studio'), 4, 'Crystal Toast Flutes (Set of 2)', 'crystal-flutes', 'Engraved crystal champagne flutes.', 68.00, 40,
  'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?w=800&q=80', 1, 1),
((SELECT id FROM vendors WHERE slug = 'glam-studio'), 4, 'Monogrammed Napkins (50)', 'monogram-napkins', 'Custom embroidered linen napkins.', 185.00, 20,
  'https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=800&q=80', 1, 1),
((SELECT id FROM vendors WHERE slug = 'glam-studio'), 5, 'Bridal Hair Comb', 'bridal-hair-comb', 'Crystal hair comb with pearl accents.', 75.00, 35,
  'https://images.unsplash.com/photo-1617038260897-41a1f14a8ca0?w=800&q=80', 1, 1),
((SELECT id FROM vendors WHERE slug = 'glam-studio'), 5, 'Silk Veil — Cathedral', 'silk-veil-cathedral', 'Soft silk cathedral-length veil.', 160.00, 12,
  'https://images.unsplash.com/photo-1591604466377-1a63d39d2a9f?w=800&q=80', 1, 1),
((SELECT id FROM vendors WHERE slug = 'beatwave-dj'), 6, 'Minimalist Invitation Suite', 'minimal-invite-suite', '100 invitations with RSVP cards.', 280.00, 25,
  'https://images.unsplash.com/photo-1586075010923-2dd4570fb338?w=800&q=80', 1, 1),
((SELECT id FROM vendors WHERE slug = 'beatwave-dj'), 6, 'Save the Date Cards (50)', 'save-the-date-cards', 'Premium cardstock with gold foil date.', 145.00, 40,
  'https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=800&q=80', 1, 1);

DELETE FROM gallery;

INSERT INTO gallery (vendor_id, category, title, image_path, sort_order) VALUES
(1, 'wedding', 'Sunset first kiss', 'https://images.unsplash.com/photo-1519741497674-611481863552?w=800&q=80', 1),
(1, 'wedding', 'Candid dance floor', 'https://images.unsplash.com/photo-1465492120980-0b1273bca3bb?w=800&q=80', 2),
(1, 'wedding', 'Ring detail shot', 'https://images.unsplash.com/photo-1606216794074-735e2aa6c974?w=800&q=80', 3),
((SELECT id FROM vendors WHERE slug = 'bloom-petals'), 'decoration', 'Blush ceremony arch', 'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?w=800&q=80', 4),
((SELECT id FROM vendors WHERE slug = 'bloom-petals'), 'decoration', 'Reception tablescape', 'https://images.unsplash.com/photo-1478146896981-b80fe463b330?w=800&q=80', 5),
((SELECT id FROM vendors WHERE slug = 'glam-studio'), 'makeup', 'Soft glam bride', 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?w=800&q=80', 6),
((SELECT id FROM vendors WHERE slug = 'glam-studio'), 'makeup', 'Natural glow look', 'https://images.unsplash.com/photo-1487412947147-5cebf100ffc2?w=800&q=80', 7),
((SELECT id FROM vendors WHERE slug = 'crystal-hall'), 'venue', 'Ballroom reception', 'https://images.unsplash.com/photo-1519167758481-83f550bb49b8?w=800&q=80', 8),
((SELECT id FROM vendors WHERE slug = 'crystal-hall'), 'venue', 'Garden terrace vows', 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=800&q=80', 9),
(NULL, 'dress', 'Lace gown detail', 'https://images.unsplash.com/photo-1594552072238-4b08548b0c69?w=800&q=80', 10),
(NULL, 'dress', 'Bridal portrait', 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=800&q=80', 11),
(NULL, 'wedding', 'Outdoor ceremony', 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=800&q=80', 12),
(NULL, 'decoration', 'Candlelit aisle', 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=800&q=80', 13),
(NULL, 'makeup', 'Evening reception look', 'https://images.unsplash.com/photo-1522335781263-6da2926b364e?w=800&q=80', 14),
(NULL, 'venue', 'Head table styling', 'https://images.unsplash.com/photo-1555244162-803834f70033?w=800&q=80', 15),
(NULL, 'other', 'Wedding rings', 'https://images.unsplash.com/photo-1605100804763-247f67bf4017?w=800&q=80', 16);

UPDATE packages SET image = 'https://images.unsplash.com/photo-1465492120980-0b1273bca3bb?w=800&q=80' WHERE slug = 'silver';
UPDATE packages SET image = 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=800&q=80' WHERE slug = 'gold';
UPDATE packages SET image = 'https://images.unsplash.com/photo-1519741497674-611481863552?w=800&q=80' WHERE slug = 'platinum';
UPDATE packages SET image = 'https://images.unsplash.com/photo-1519167758481-83f550bb49b8?w=800&q=80' WHERE slug = 'luxury';
