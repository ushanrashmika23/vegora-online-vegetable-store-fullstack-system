# Vegi - E-Commerce Platform

A modern, feature-rich e-commerce platform built with PHP and MySQL, designed for selling vegetables and grocery products with a comprehensive admin panel.

## 🎯 Features

### Customer Features
- **Product Browsing** - Browse products by category with detailed descriptions and reviews
- **Shopping Cart** - Add/remove items, manage quantities, persistent cart storage
- **Secure Checkout** - Complete order placement with validation
- **User Accounts** - Register, login, view order history, manage profile
- **Product Reviews** - Leave and view customer reviews with ratings
- **Coupons** - Apply discount codes at checkout (percentage or fixed amount)
- **Order Tracking** - View order status (Placed, Packed, Shipped, Delivered)
- **Invoice Download** - Generate and download PDF invoices for orders

### Admin Panel Features
- **Dashboard** - KPIs, sales charts, recent orders, top products at a glance
- **Product Management** - Create, edit, delete products; manage inventory and stock levels
- **Category Management** - Organize products into categories
- **Order Management** - View, filter, and manage customer orders with status tracking
- **User Management** - View all users, manage roles and accounts
- **Coupon Management** - Create and manage discount codes with usage statistics
- **Organized Navigation** - Sidebar categorized into Overview, Catalog, Sales, Customers, and Promotions sections

## 💻 Technology Stack

- **Backend**: PHP 8.x with PDO (MySQL)
- **Frontend**: Bootstrap 5.3.0, HTML5, CSS3, JavaScript
- **Database**: MySQL/MariaDB
- **Charts**: Chart.js
- **Icons**: Font Awesome 6.4.0
- **Architecture**: MVC (Model-View-Controller) pattern
- **PDF Generation**: TCPDF (via InvoicePdfService)

## 📋 Requirements

- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Web server (Apache, Nginx, etc.)
- Composer (optional, for package management)

## 🚀 Installation & Setup

### 1. Database Setup

Import the provided SQL schema:

```bash
mysql -u root -p vegi_db < database.sql
```

This will create:
- All required tables (users, products, orders, cart, reviews, coupons, etc.)
- Foreign key relationships
- Indexes for performance
- Seed data (categories, sample products, admin user, coupons)

**Default Admin Credentials:**
- Email: `admin@vegi.com`
- Password: `admin123`

### 2. Configuration

Update database connection in `config/db.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'vegi_db');
```

### 3. File Permissions

Ensure the following directories are writable:
- `assets/invoices/` - For PDF invoice storage
- `assets/images/` - For product images

```bash
chmod 755 assets/invoices
chmod 755 assets/images
chmod 755 tmp_migrate.php
```

### 4. Running the Application

Start your local server:

```bash
# If using PHP built-in server
php -S localhost:8000

# Or use XAMPP/WAMP/LAMP
# Access via http://localhost/vegi
```

**Access Points:**
- **Customer Portal**: `http://localhost:8000/` or `/index.php`
- **Admin Panel**: `http://localhost:8000/admin/` (login required with admin account)
- **Shop**: `http://localhost:8000/shop.php`

## 📁 Project Structure

```
vegi/
├── admin/                    # Admin panel interface
│   ├── includes/
│   │   ├── auth.php         # Auth middleware
│   │   ├── header.php       # Shared layout & CSS utilities
│   │   ├── sidebar.php      # Navigation sidebar
│   │   └── topbar.php       # Top navigation
│   ├── index.php            # Dashboard
│   ├── products.php         # Product management
│   ├── categories.php       # Category management
│   ├── orders.php           # Order management
│   ├── users.php            # User management
│   ├── coupons.php          # Coupon management
│   ├── add_product.php      # Product creation form
│   ├── edit_product.php     # Product editing form
│   └── ...
├── assets/                  # Static files
│   ├── css/style.css        # Main stylesheet
│   ├── js/cart.js           # Cart functionality
│   ├── images/              # Product images
│   └── invoices/            # Generated PDF invoices
├── config/
│   └── db.php               # Database configuration
├── controllers/             # Business logic
│   ├── authController.php   # Authentication & authorization
│   ├── productController.php# Product operations
│   ├── cartController.php   # Cart management
│   ├── orderController.php  # Order processing
│   ├── reviewController.php # Review management
│   └── userController.php   # User operations
├── includes/
│   ├── navbar.php           # Customer navbar
│   └── footer.php           # Customer footer
├── models/                  # Data models
│   ├── User.php
│   ├── Product.php
│   ├── Cart.php
│   ├── Order.php
│   └── ...
├── services/
│   └── InvoicePdfService.php# PDF invoice generation
├── index.php                # Home page
├── shop.php                 # Product listing/search
├── product.php              # Product detail page
├── cart.php                 # Shopping cart
├── checkout.php             # Checkout process
├── checkout_success.php     # Order confirmation
├── login.php                # Customer login
├── register.php             # Customer registration
├── orders.php               # Customer order history
├── about.php                # About page
├── database.sql             # Database schema & seed data
└── README.md                # This file
```

## 🔐 Security Features

- **Password Hashing**: Uses `password_hash()` for secure password storage
- **SQL Injection Prevention**: Prepared statements via PDO
- **Session Management**: Secure PHP sessions with authentication checks
- **CSRF Protection**: Token-based validation (implement if needed)
- **Input Validation**: Server-side validation on all forms
- **Role-Based Access**: Admin functions protected by role checks

## 📊 Database Schema

### Core Tables
- **users** - Customer and admin accounts
- **product_categories** - Product categorization
- **products** - Product inventory with stock tracking
- **cart** - User shopping carts
- **orders** - Customer orders with status tracking
- **order_items** - Individual items in orders
- **product_reviews** - Customer reviews and ratings
- **coupons** - Discount codes (percentage or fixed amount)
- **user_coupons** - Coupon usage tracking

### Key Features
- Foreign key constraints for data integrity
- Unique constraints on cart items and reviews
- Indexes for query performance
- Enum types for status values
- Timestamps for audit trails

## 🎨 UI/UX Highlights

### Admin Panel Styling
- **Badge Chip System** - Consistent, color-coded badges across all pages
  - Primary (blue) - Placed orders, Admin users
  - Success (green) - Delivered orders, Active coupons
  - Info (cyan) - Packed orders
  - Warning (yellow) - Shipped orders
  - Danger (red) - Cancelled orders, Disabled coupons
  - Dark/Neutral - Other statuses

- **Unified Filter Controls** - Consistent sizing and alignment
  - 44px height for all inputs, selects, and buttons
  - Responsive grid layout with proper spacing
  - Standard reset/apply button patterns

- **Organized Navigation** - Sidebar categorized into logical sections
  - Overview (Dashboard)
  - Catalog (Products, Categories)
  - Sales (Orders)
  - Customers (Users)
  - Promotions (Coupons)

## 🛠️ Common Operations

### Adding a Product
1. Go to Admin → Products
2. Click "Add Product"
3. Fill in product details, upload image, set category and price
4. Click "Add Product"

### Creating a Coupon
1. Go to Admin → Promotions → Coupons
2. Click "Create Coupon"
3. Set code, discount type (fixed/percentage), amount
4. Toggle active status
5. Save

### Processing an Order
1. Go to Admin → Orders
2. Click on order to view details
3. Update order status (Placed → Packed → Shipped → Delivered)
4. Customer receives notifications for status updates

### Generating Invoice
1. Visit order completion page or customer account page
2. Click "Download Invoice" button
3. PDF generated using TCPDF service

## 🔄 Admin Migration Scripts

The project includes migration utilities:
- `migrate_product_categories.php` - Import/update product categories
- `migrate_product_pricing.php` - Bulk price updates
- `migrate_product_reviews.php` - Import review data
- `migrate_coupons.php` - Coupon management

Run via: `php migrate_*.php`

## 🐛 Troubleshooting

**Database Connection Failed**
- Check credentials in `config/db.php`
- Verify MySQL is running
- Ensure database `vegi_db` exists

**Admin Pages Show No Styling**
- Clear browser cache (`Ctrl+Shift+Del`)
- Check `admin/includes/header.php` is included properly
- Verify Bootstrap CSS loaded from CDN

**Orders Not Appearing**
- Check `orders` table in database
- Verify user auth session is active
- Check order timestamps match filter dates

**Upload Issues**
- Ensure `assets/invoices/` and `assets/images/` directories exist and are writable
- Check PHP `upload_max_filesize` configuration
- Verify image file extensions are allowed (jpg, png, gif)

## 📝 Development Notes

### Code Style
- MVC architecture keeps models, controllers, and views separated
- Utility-first CSS approach with reusable classes
- PDO prepared statements for all database queries
- Bootstrap components for consistent UI

### Adding New Features
1. Create model class in `/models`
2. Create controller logic in `/controllers`
3. Create view template in `/` or `/admin`
4. Include auth/header where needed
5. Update sidebar navigation if adding admin feature

### Testing Locally
- Use XAMPP/WAMP/LAMP for development
- Always test admin features with different user roles
- Verify responsive design on mobile devices
- Test checkout flow end-to-end

## 📞 Support

For issues or feature requests, please document:
- Steps to reproduce
- Current behavior vs expected behavior
- PHP version and database system
- Browser version (for frontend issues)

## 📄 License

This project is provided as-is for educational and commercial use.

---

**Last Updated**: April 2026
**PHP Version**: 8.0+
**Database**: MySQL 5.7+ / MariaDB 10.2+
