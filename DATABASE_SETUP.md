# 🗄️ Database Setup Instructions

## For New PC Installation

**Use this file ONLY:** `database/schema.sql`

This is the **complete database schema** with everything you need:
- ✅ All tables (users, medicines, sales, customers, etc.)
- ✅ Password reset tokens table (updated structure)
- ✅ Default admin user
- ✅ Sample categories, suppliers, and medicines
- ✅ All indexes and foreign keys

## Installation Steps

### Step 1: Create Database

Open phpMyAdmin or MySQL command line and run:

```sql
CREATE DATABASE IF NOT EXISTS pharmacy_management;
```

### Step 2: Import Schema

**Option A: Using phpMyAdmin**
1. Open phpMyAdmin
2. Select `pharmacy_management` database
3. Click "Import" tab
4. Choose file: `database/schema.sql`
5. Click "Go"

**Option B: Using MySQL Command Line**
```bash
mysql -u root -p pharmacy_management < database/schema.sql
```

### Step 3: Default Login Credentials

After importing, you can login with these accounts:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@pharmacy.com | password |
| Pharmacist | pharmacist@pharmacy.com | password |
| Customer | customer@pharmacy.com | password |

**⚠️ IMPORTANT:** Change these passwords immediately after first login!

## File Structure

```
database/
├── schema.sql                          ← USE THIS FILE
├── migrations/
│   ├── add_sample_notifications.sql    ← Optional: Adds sample notifications
│   └── add_user_preferences.sql        ← Optional: Adds theme preferences

```

## Optional Migrations

After importing the main schema, you can optionally run these migrations:

### Add Sample Notifications (Optional)
```bash
mysql -u root -p pharmacy_management < database/migrations/add_sample_notifications.sql
```

### Add User Preferences (Optional)
```bash
mysql -u root -p pharmacy_management < database/migrations/add_user_preferences.sql
```

## What's Included in Main Schema?

### Core Tables
✅ `users` - System users (admin, pharmacist, customer)
✅ `categories` - Medicine categories
✅ `suppliers` - Supplier information
✅ `medicines` - Medicine inventory
✅ `customers` - Customer profiles
✅ `sales` - Sales transactions
✅ `sale_items` - Sale line items
✅ `prescriptions` - Prescription uploads
✅ `prescription_items` - Prescription medicines

### Additional Tables
✅ `stock_movements` - Inventory tracking
✅ `notifications` - System notifications
✅ `settings` - System settings
✅ `password_reset_tokens` - Password reset (with email link)
✅ `user_preferences` - User theme preferences

### Sample Data
✅ 3 default users (admin, pharmacist, customer)
✅ 8 medicine categories
✅ 3 suppliers
✅ 5 sample medicines
✅ Default system settings

## Database Configuration

Update your database connection in: `config/config.php`

Or create `.env` file:
```env
DB_HOST=localhost
DB_NAME=pharmacy_management
DB_USER=root
DB_PASS=
```

## Troubleshooting

### Error: "Table already exists"
- Drop the database and recreate it:
```sql
DROP DATABASE pharmacy_management;
CREATE DATABASE pharmacy_management;
```

### Error: "Cannot add foreign key constraint"
- Make sure you're importing the complete schema.sql file
- The file creates tables in the correct order

### Error: "Access denied"
- Check your MySQL username and password
- Make sure MySQL service is running

## Need Help?

- Check if XAMPP/MySQL is running
- Verify database name in `config/config.php`
- Check PHP error logs for detailed errors

---

**✨ That's it! Your database is ready to use.**

Run the application at: `http://localhost/pharmacy-management-system/admin/auth/login.php`
