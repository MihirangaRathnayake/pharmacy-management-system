# Pharmacy Management System

A comprehensive web-based Pharmacy Management System built with PHP and Tailwind CSS, designed to manage daily pharmacy operations with a focus on performance, accuracy, security, and customer convenience.

## Features

### 🏥 Core Functionality
- **Medicine Inventory Management**: Track stock levels, expiry dates, and suppliers with auto-alerts
- **Sales & Billing**: Generate detailed invoices with tax and discount calculations
- **Customer Records**: Store prescription history, personal info, and contact details
- **Prescription Upload**: Secure file upload with pharmacist verification
- **Shopping Cart & Checkout**: Multi-step checkout process
- **Reporting & Analytics**: Daily, weekly, and monthly sales reports

### 👥 User Management
- **Role-based Access Control**: Admin, Pharmacist, and Customer roles
- **Secure Authentication**: Registration, login, and logout functionality
- **Profile Management**: User profiles with image support

### 📊 Dashboard Features
- **Real-time Metrics**: Sales today, stock alerts, pending orders
- **Quick Actions**: Add medicine, new sale, upload prescription
- **Recent Activity**: Latest sales and stock alerts
- **Notifications**: Low stock and expiry alerts

### 🎨 UI/UX Design
- **Modern Interface**: Clean, minimal layout with intuitive navigation
- **Responsive Design**: Works on desktop, tablet, and mobile
- **Professional Typography**: Inter font for readability
- **Font Awesome Icons**: Clear visual cues throughout the interface
- **Consistent Color Palette**: Pharmacy green (#28a745) with gray accents

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **CSS Framework**: Tailwind CSS
- **Icons**: Font Awesome 6.4.0
- **Typography**: Inter Font Family

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Composer (optional, for dependencies)

### Setup Instructions

1. **Clone the Repository**
   ```bash
   git clone <repository-url>
   cd pharmacy-management-system
   ```

2. **Database Setup**
   - Create a MySQL database named `pharmacy_management`
   - Import the database schema:
   ```bash
   mysql -u your_username -p pharmacy_management < database/schema.sql
   ```

3. **Configuration**
   - Update database credentials in `config/database.php`:
   ```php
   private $host = 'localhost';
   private $db_name = 'pharmacy_management';
   private $username = 'your_username';
   private $password = 'your_password';
   ```

4. **File Permissions**
   ```bash
   chmod 755 uploads/
   chmod 755 uploads/prescriptions/
   ```

5. **Web Server Configuration**
   - Point your web server document root to the project directory
   - Ensure mod_rewrite is enabled (for Apache)

## Default Login Credentials

The system comes with pre-configured demo accounts:

- **Admin**: admin@pharmacy.com / admin123
- **Pharmacist**: pharmacist@pharmacy.com / pharma123
- **Customer**: customer@pharmacy.com / customer123

## Project Structure

```
pharmacy-management-system/
├── api/                    # API endpoints
│   ├── dashboard_stats.php
│   ├── search_medicines.php
│   ├── process_sale.php
│   └── ...
├── assets/                 # Static assets
│   ├── js/                # JavaScript files
│   └── css/               # Custom CSS files
├── auth/                  # Authentication pages
│   ├── login.php
│   ├── logout.php
│   └── register.php
├── config/                # Configuration files
│   └── database.php
├── database/              # Database files
│   └── schema.sql
├── includes/              # Shared PHP includes
│   ├── auth.php
│   └── navbar.php
├── modules/               # Feature modules
│   ├── inventory/         # Inventory management
│   ├── sales/            # Sales management
│   ├── customers/        # Customer management
│   ├── prescriptions/    # Prescription handling
│   └── reports/          # Reporting system
├── uploads/              # File uploads
│   └── prescriptions/    # Prescription images
├── index.php             # Dashboard homepage
└── README.md
```

## Key Features Breakdown

### Inventory Management
- Add, edit, and delete medicines
- Track stock levels with low stock alerts
- Monitor expiry dates with advance warnings
- Supplier management
- Category-based organization
- Barcode support

### Sales System
- Point-of-sale interface
- Real-time medicine search
- Shopping cart functionality
- Multiple payment methods
- Tax and discount calculations
- Invoice generation

### Customer Management
- Customer registration and profiles
- Prescription history tracking
- Loyalty points system
- Contact information management

### Prescription Handling
- Secure file upload (images/PDF)
- Pharmacist verification workflow
- Prescription to order conversion
- Digital prescription storage

### Reporting & Analytics
- Sales reports (daily, weekly, monthly)
- Inventory reports
- Customer analytics
- Profit margin analysis
- Export functionality

## Security Features

- **Password Hashing**: Secure password storage using PHP's password_hash()
- **SQL Injection Prevention**: Prepared statements throughout
- **Session Management**: Secure session handling
- **File Upload Security**: Validated file types and sizes
- **Role-based Access**: Proper authorization checks
- **Input Validation**: Server-side validation for all inputs

## Performance Optimizations

- **Database Indexing**: Optimized database queries with proper indexes
- **Lazy Loading**: Pagination for large datasets
- **AJAX Requests**: Asynchronous data loading
- **Caching**: Browser caching for static assets
- **Optimized Images**: Compressed images and icons

## Browser Support

- Chrome 70+
- Firefox 65+
- Safari 12+
- Edge 79+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/new-feature`)
3. Commit your changes (`git commit -am 'Add new feature'`)
4. Push to the branch (`git push origin feature/new-feature`)
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support and questions:
- Create an issue in the repository
- Email: support@pharmacare.com
- Documentation: [Project Wiki](wiki-url)

## Roadmap

### Upcoming Features
- [ ] Mobile app integration
- [ ] Barcode scanning
- [ ] SMS notifications
- [ ] Advanced analytics dashboard
- [ ] Multi-location support
- [ ] API for third-party integrations
- [ ] Automated reorder system
- [ ] Insurance claim processing

---

**Built with ❤️ for modern pharmacy management**