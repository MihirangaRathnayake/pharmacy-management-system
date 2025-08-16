# 🏥 Pharmacy Management System - Setup Instructions

## 📋 Prerequisites
- XAMPP (Apache + MySQL + PHP)
- Web Browser (Chrome, Firefox, Safari, Edge)
- Text Editor (VS Code, Sublime Text, etc.)

## 🚀 Quick Setup (5 Minutes)

### Step 1: Install XAMPP
1. Download XAMPP from https://www.apachefriends.org/
2. Install XAMPP with default settings
3. Start XAMPP Control Panel

### Step 2: Start Services
1. In XAMPP Control Panel, click **Start** for:
   - ✅ Apache
   - ✅ MySQL
2. Both should show "Running" status

### Step 3: Setup Project
1. Copy the entire project folder to `C:\xampp\htdocs\pharmacy-management-system\`
2. Open browser and go to: `http://localhost/pharmacy-management-system/setup_database.php`
3. Wait for database setup to complete
4. You should see "✅ Database setup successful!" message

### Step 4: Access the System
- **Admin Dashboard**: `http://localhost/pharmacy-management-system/index.php`
- **Customer Website**: `http://localhost/pharmacy-management-system/customer/index.html`

## 🔐 Default Login Credentials

### Admin Panel
- **Admin**: admin@pharmacy.com / admin123
- **Pharmacist**: pharmacist@pharmacy.com / pharma123
- **Customer**: customer@pharmacy.com / customer123

### Customer Website
- **Customer**: customer@pharmacy.com / customer123
- Or register a new account

## 📁 Project Structure
```
pharmacy-management-system/
├── 📁 api/                    # API endpoints
├── 📁 assets/                 # Admin assets (JS, CSS)
├── 📁 auth/                   # Authentication pages
├── 📁 config/                 # Configuration files
├── 📁 customer/               # Customer website
├── 📁 database/               # Database schema
├── 📁 includes/               # Shared PHP files
├── 📁 modules/                # Admin modules
├── 📁 uploads/                # File uploads
├── 📄 index.php               # Admin dashboard
└── 📄 setup_database.php      # Database setup
```

## 🔧 Troubleshooting

### Database Connection Error
If you see "Connection error" or "Unknown database":
1. Make sure MySQL is running in XAMPP
2. Run `http://localhost/pharmacy-management-system/setup_database.php`
3. Check database credentials in `config/database.php`

### Apache Not Starting
1. Check if port 80 is free (close Skype, IIS, etc.)
2. Or change Apache port in XAMPP config
3. Restart XAMPP as Administrator

### File Upload Issues
1. Create `uploads/prescriptions/` folder
2. Set folder permissions to 755
3. Check PHP upload settings in `php.ini`

### API Errors
1. Make sure Apache mod_rewrite is enabled
2. Check `.htaccess` file exists
3. Verify API endpoints are accessible

## 🌟 Features Overview

### Admin Dashboard
- 📊 Real-time analytics
- 💊 Medicine inventory management
- 💰 Sales and billing system
- 👥 Customer management
- 📋 Prescription verification
- 📈 Reports and analytics

### Customer Website
- 🛒 Online shopping cart
- 🔍 Product search and filters
- 📱 Responsive design
- 🔐 User authentication
- 📋 Prescription upload
- 💳 Checkout system

## 🎯 Testing the System

### Test Admin Features
1. Login as admin: admin@pharmacy.com / admin123
2. Add a new medicine in Inventory
3. Create a new sale
4. View reports and analytics

### Test Customer Features
1. Visit customer website
2. Browse products and add to cart
3. Register/login as customer
4. Upload a prescription
5. Complete a purchase

### Test Integration
1. Add products in admin panel
2. Verify they appear on customer website
3. Make a purchase on customer site
4. Check sale appears in admin dashboard

## 🔒 Security Notes
- Change default passwords in production
- Update database credentials
- Enable HTTPS for production
- Set proper file permissions
- Regular security updates

## 📞 Support
If you encounter issues:
1. Check XAMPP error logs
2. Verify all services are running
3. Check browser console for JavaScript errors
4. Ensure database is properly set up

## 🚀 Going Live (Production)
1. Get web hosting with PHP/MySQL
2. Upload files via FTP
3. Create database and import schema
4. Update config/database.php with hosting credentials
5. Set up SSL certificate
6. Configure domain and DNS

---
**Happy Coding! 🎉**