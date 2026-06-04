# Lumière Weddings — Wedding Service Platform

PHP + MySQL (XAMPP) full-stack wedding marketplace: services, booking, e-commerce shop, gallery, and role-based dashboards.

## Requirements

- XAMPP (Apache + MySQL + PHP 8+)
- phpMyAdmin (optional, for importing SQL)

## Setup

1. Copy the `wedding` folder to `C:\xampp\htdocs\wedding` (or keep in your project and point Apache document root).
2. Start **Apache** and **MySQL** in XAMPP Control Panel.
3. Open phpMyAdmin → **Import** → select `database/schema.sql`.
4. Edit `config.php` if your MySQL user/password differs from `root` / empty password.
5. Visit: `http://localhost/wedding/`
6. **Optional — richer demo content:** Import `database/seed_demo_data.sql` in phpMyAdmin to add more vendors, services, shop products, and gallery photos.

## Demo accounts

| Role     | Email               | Password   |
|----------|---------------------|------------|
| Admin    | admin@wedding.com   | password   |
| Customer | customer@wedding.com| password   |
| Vendor   | vendor@wedding.com  | password   |

## Project structure

```
wedding/
├── admin/           Admin dashboard, users, vendors, services, reports
├── customer/        Customer bookings, orders, wishlist, messages
├── vendor/          Vendor services, bookings, portfolio
├── includes/        header, footer, db, auth, functions
├── database/        schema.sql
├── css/             wedding.css
├── js/              wedding.js
├── index.php        Home
├── services.php     Service listing + filters
├── shop.php         Products + cart
├── booking.php      Book services/packages
└── login.php        Authentication
```

## Features implemented

- **3 roles**: Customer, Vendor, Admin with separate dashboards
- **Authentication**: Register, login, logout, forgot password (demo)
- **Services**: Categories, search/filter, service details
- **Packages**: Silver, Gold, Platinum, Luxury tiers
- **Booking**: Date, guests, special requests, payment method
- **Shop**: Cart, checkout, coupon `WEDDING10` (10% off), COD / bank / manual payment
- **Gallery**: Category filters
- **Blog**, **Contact** (saved to database)
- **Wishlist**, **Messages** (customer ↔ vendor)
- **Notifications** on register, order, booking
- **Admin**: Approve vendors/services, user management, reports

## Development phases (reference)

1. Setup — database + folder structure ✓  
2. Frontend — home, nav, responsive ✓  
3. Authentication ✓  
4. Service management ✓  
5. Booking ✓  
6. Shop / cart / checkout ✓  
7. Admin panel ✓  
8. Reviews, notifications, search ✓ (basic)

## Optional next steps

- Upload real images to `assets/images/`
- Email verification & SMTP for forgot password
- PayPal / Stripe integration
- Availability calendar (prevent double booking)
- Burmese language (i18n)
# wedding_service-
