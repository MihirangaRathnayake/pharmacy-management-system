# 🚀 Quick Start Guide - Pharmacy Management System

## ⚡ 5-Minute Setup

### Step 1: Start XAMPP
1. Open XAMPP Control Panel
2. Click **Start** for Apache and MySQL
3. Both should show "Running" status

### Step 2: Install Database
1. Go to: `http://localhost/pharmacy-management-system/install.php`
2. Click "Continue Installation"
3. Wait for "Installation Complete!" message

### Step 3: Access System
- **Admin Dashboard**: `http://localhost/pharmacy-management-system/index.php`
- **Customer Website**: `http://localhost/pharmacy-management-system/customer/index.html`

## 🔐 Login Credentials
- **Admin**: admin@pharmacy.com / admin123
- **Pharmacist**: pharmacist@pharmacy.com / pharma123
- **Customer**: customer@pharmacy.com / customer123

## ⚠️ Common Issues & Quick Fixes

### "GD Extension Missing"
**This is NOT critical!** The system works fine without it.
- GD is only used for image resizing
- Images will be stored as-is without resizing
- All other features work normally

**To enable GD (optional):**
1. Open `C:\xampp\php\php.ini`
2. Find `;extension=gd`
3. Remove the `;` to make it `extension=gd`
4. Restart Apache in XAMPP

### "Database Connection Error"
1. Make sure MySQL is running in XAMPP
2. Run: `http://localhost/pharmacy-management-system/install.php`

### "Apache Won't Start"
1. Close Skype (uses port 80)
2. Or change Apache port to 8080 in XAMPP config

### "File Not Found Errors"
1. Make sure project is in: `C:\xampp\htdocs\pharmacy-management-system\`
2. Check folder name is exactly `pharmacy-management-system`

## 🧪 Test Your Installation

Visit: `http://localhost/pharmacy-management-system/test_connection.php`

This will show you:
- ✅ What's working
- ❌ What needs fixing
- ⚠️ What's optional

## 🎯 What Each Part Does

### Admin Dashboard (`index.php`)
- Manage medicines and inventory
- Process sales and billing
- View customer records
- Generate reports
- Verify prescriptions

### Customer Website (`customer/index.html`)
- Browse and search medicines
- Add items to shopping cart
- Upload prescriptions
- Create customer account
- Place orders

## 🔧 System Requirements

### Required (Must Have)
- ✅ PHP 7.4+
- ✅ MySQL 5.7+
- ✅ Apache Web Server
- ✅ PDO Extension
- ✅ PDO MySQL Extension

### Optional (Nice to Have)
- ⚠️ GD Extension (for image resizing)
- ⚠️ FileInfo Extension (for file type detection)

## 🚨 Emergency Reset

If something goes wrong:

1. **Reset Database**:
   ```
   http://localhost/pharmacy-management-system/install.php
   ```

2. **Check System**:
   ```
   http://localhost/pharmacy-management-system/test_connection.php
   ```

3. **Start Fresh**:
   - Delete the `pharmacy_management` database in phpMyAdmin
   - Run install.php again

## 📱 Mobile Access

The system works on mobile devices too!
- Responsive design
- Touch-friendly interface
- Works on phones and tablets

## 🎉 You're Ready!

Once you see "✅ All systems working" in the test page, you can:

1. **Login as Admin** and add some medicines
2. **Visit Customer Site** and browse products
3. **Test the Shopping Cart** functionality
4. **Upload a Prescription** (any image file)
5. **Generate Reports** in the admin panel

**Need Help?** Check `test_connection.php` first - it shows exactly what's working and what isn't!