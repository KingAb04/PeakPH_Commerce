# 🚀 PeakPH Commerce Database Setup Guide

## Quick Setup Instructions

### 1. **Start XAMPP Services**
- Start Apache and MySQL in XAMPP Control Panel
- Make sure both services are running (green lights)

### 2. **Access phpMyAdmin**
- Open browser and go to: `http://localhost/phpmyadmin`
- Login with default credentials (usually no password needed)

### 3. **Run Database Setup**
**Option A: Using phpMyAdmin GUI**
1. Click on "SQL" tab in phpMyAdmin
2. Copy and paste the entire content from `database_setup.sql`
3. Click "Go" to execute

**Option B: Import SQL File**
1. Click "Import" tab in phpMyAdmin
2. Choose `database_setup.sql` file
3. Click "Go"

### 4. **Verify Setup**
After running the script, you should see:
- ✅ Database `peakph_db` created
- ✅ 10 tables created successfully
- ✅ Sample data inserted

### 5. **Test Admin Login**
- Go to: `http://localhost/PeakPH_Commerce/`
- Click Login button
- Use credentials: `admin@peakph.com` / `12345`

---

## 📋 Database Tables Created

| Table | Purpose |
|-------|---------|
| `inventory` | Product management and stock tracking |
| `users` | User accounts and admin management |
| `products` | Product catalog compatibility |
| `audit_trail` | System activity logging |
| `orders` | Customer order management |
| `order_items` | Order details and line items |
| `carousel` | Homepage banner/slider management |
| `bestsellers` | Featured bestseller products |
| `new_arrivals` | New arrival product showcase |

## 🛠️ Sample Data Included

### Products Added:
- Professional Camping Tent (₱2,500)
- Portable Cooking Set (₱750)
- Hiking Backpack Pro (₱1,800)
- Travel Boots Waterproof (₱1,200)
- Survival Kit Complete (₱950)
- Camping Stove Portable (₱650)

### Admin User:
- Email: `admin@peakph.com`
- Password: `12345`

## 🔧 Troubleshooting

**Connection Issues:**
- Make sure XAMPP MySQL is running
- Check `includes/db.php` has correct database name: `peakph_db`

**Import Errors:**
- Try running the SQL script in smaller chunks
- Check MySQL error log in XAMPP

**Login Issues:**
- Verify admin user was created in `users` table
- Check session configuration in PHP

---

## 🚀 Ready to Go!
After setup completion, your PeakPH Commerce website should be fully functional with:
- Working admin panel
- Product inventory management
- Shopping cart functionality
- Sample products to test with