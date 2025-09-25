# Coolpals PeakPH Website Structure

## Project Overview
This is an e-commerce website for camping gear and outdoor equipment built with PHP.

## Directory Structure

```
Coolpals_PeakPH/
├── admin/                          # Admin panel (Backend)
│   ├── index.php                   # Admin dashboard homepage
│   ├── dashboard.php               # Analytics dashboard
│   ├── mini-view.php               # Quick view component
│   ├── orders.php                  # Order management
│   ├── content/                    # Content management
│   │   ├── carousel.php            # Carousel management
│   │   └── carousel_data.php       # Carousel data handler
│   ├── inventory/                  # Inventory management
│   │   ├── inventory.php           # Main inventory page
│   │   ├── inventory_add.php       # Add new products
│   │   ├── inventory_edit.php      # Edit products
│   │   ├── inventory_update.php    # Update product handler
│   │   ├── inventory_delete.php    # Delete products
│   │   └── inventory_label.php     # Product labeling
│   ├── users/                      # User management
│   │   ├── index.php               # Users list page
│   │   ├── users.php               # User management
│   │   └── add-user.php            # Add new users
│   └── uploads/                    # Admin uploaded files
├── includes/                       # Shared includes
│   └── db.php                      # Database connection
├── Assets/                         # Static assets
│   ├── Carousel_Picts/             # Carousel images
│   ├── Gallery_Images/             # Product images
│   └── Main_Category/              # Category images
├── Css/                           # Stylesheets
│   ├── Global.css                  # Global styles
│   ├── admin.css                   # Admin panel styles
│   ├── landingcomponents.css       # Landing page styles
│   ├── prod.css                    # Product catalog styles
│   └── productview.css             # Product view styles
├── Js/                            # JavaScript files
│   ├── JavaScript.js               # Main JS
│   ├── admin.js                    # Admin panel JS
│   └── chatbot.js                  # Chatbot functionality
├── uploads/                        # User uploaded files
├── index.php                       # Homepage
├── ProductCatalog.php              # Product catalog page
├── cart.php                        # Shopping cart
├── add_to_cart.php                 # Add to cart handler
├── about.php                       # About page
├── login.php                       # Login handler
└── logout.php                      # Logout handler
```

## Access URLs

### User Side (Frontend)
- Homepage: `http://localhost/Coolpals_PeakPH/`
- Product Catalog: `http://localhost/Coolpals_PeakPH/ProductCatalog.php`
- Shopping Cart: `http://localhost/Coolpals_PeakPH/cart.php`
- About: `http://localhost/Coolpals_PeakPH/about.php`

### Admin Side (Backend)
- Admin Dashboard: `http://localhost/Coolpals_PeakPH/admin/`
- Analytics Dashboard: `http://localhost/Coolpals_PeakPH/admin/dashboard.php`
- Inventory Management: `http://localhost/Coolpals_PeakPH/admin/inventory/inventory.php`
- User Management: `http://localhost/Coolpals_PeakPH/admin/users/`
- Orders: `http://localhost/Coolpals_PeakPH/admin/orders.php`

## Features

### User Features
- Product browsing with category filters
- Shopping cart functionality
- Responsive design
- Product search and filtering

### Admin Features
- Complete inventory management
- User management
- Order tracking
- Content management (carousel, etc.)
- Dashboard analytics

## Setup Requirements
- XAMPP or similar PHP server
- MySQL database
- PHP 7.0 or higher

## Recent Changes
- Separated user-side and admin-side into distinct folder structures
- Moved all admin functionality to `/admin/` directory
- Centralized database connection in `/includes/` directory
- Updated all file paths and navigation links
- Maintained responsive design and cart functionality