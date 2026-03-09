# Pharmacy Management System - Client Website

A modern, secure, and user-friendly client-facing website for the Pharmacy Management System. Built with PHP 8.2, MySQL, and modern web technologies.

## 🚀 Features

### 🛒 E-Commerce Functionality
- **Product Catalog**: Browse medicines by category with search and filtering
- **Shopping Cart**: Session-based cart with quantity management
- **Secure Checkout**: Cash on Delivery (COD) payment system
- **Order Management**: Complete order history and tracking

### 👥 User Management
- **User Registration & Login**: Secure authentication system
- **Profile Management**: Update personal information and passwords
- **Order History**: View past orders and their status

### 🎨 Modern UI/UX
- **Responsive Design**: Mobile-first approach with Tailwind-inspired CSS
- **Inter Font**: Professional typography via Google Fonts
- **Font Awesome Icons**: Comprehensive icon system (v6.6.0)
- **Accessible**: WCAG compliant with proper ARIA labels and focus states

### 🔒 Security Features
- **CSRF Protection**: Token-based protection for all forms
- **Password Security**: bcrypt hashing with strong password requirements
- **SQL Injection Prevention**: PDO prepared statements throughout
- **Session Security**: Hardened session configuration
- **Input Validation**: Server-side validation and output escaping

## 🛠 Technology Stack

- **Backend**: PHP 8.2+ with PDO
- **Database**: MySQL 8.x
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Fonts**: Inter (Google Fonts)
- **Icons**: Font Awesome 6.6.0
- **Architecture**: MVC-inspired with clean separation of concerns

## 📁 Project Structure

```
pharmacy-client/
├── public/                 # Public web files
│   ├── index.php          # Homepage
│   ├── shop.php           # Product catalog
│   ├── product.php        # Product details
│   ├── cart.php           # Shopping cart
│   ├── checkout.php       # Checkout process
│   ├── login.php          # User login
│   ├── register.php       # User registration
│   ├── logout.php         # Logout handler
│   ├── profile.php        # User profile
│   ├── orders.php         # Order history
│   └── contact.php        # Contact page
├── actions/               # Form processing
│   ├── auth_login.php     # Login processing
│   ├── auth_register.php  # Registration processing
│   ├── add_to_cart.php    # Add to cart
│   ├── update_cart.php    # Update cart
│   └── place_order.php    # Order placement
├── partials/              # Reusable components
│   ├── head.php           # HTML head section
│   ├── header.php         # Site header
│   └── footer.php         # Site footer
├── core/                  # Core functionality
│   ├── config.php         # Configuration
│   ├── db.php             # Database layer
│   ├── csrf.php           # CSRF protection
│   ├── helpers.php        # Helper functions
│   ├── auth.php           # Authentication
│   └── init.php           # Application bootstrap
├── assets/                # Static assets
│   ├── css/
│   │   └── styles.css     # Main stylesheet
│   └── js/
│       └── app.js         # Main JavaScript
├── sql/
│   └── schema.sql         # Database schema
├── .htaccess              # Apache configuration
└── README.md              # This file
```

## 🚀 Installation

### Prerequisites
- PHP 8.2 or higher
- MySQL 8.x or higher
- Web server (Apache/Nginx)
- mod_rewrite enabled (for Apache)

### Quick Setup

1. **Clone/Download the project**
   ```bash
   git clone <repository-url>
   cd pharmacy-client
   ```

2. **Database Setup**
   - Create a MySQL database named `pharmacy_client`
   - Import the schema: `mysql -u username -p pharmacy_client < sql/schema.sql`

3. **Configuration**
   - Edit `core/config.php` with your database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'pharmacy_client');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

4. **Web Server Setup**
   - Point your web server document root to the project directory
   - Ensure `.htaccess` is enabled (Apache) or configure URL rewriting (Nginx)

5. **Permissions**
   - Ensure web server has read access to all files
   - Set appropriate permissions for uploads directory (if added later)

### Sample Data

The schema includes sample data:
- **3 Categories**: Pain Relief, Cold & Flu, Vitamins & Supplements
- **12 Products**: Mix of OTC and prescription medicines
- **3 Sample Users**: For testing (password: `password`)
- **Sample Orders**: For demonstration

### Test Accounts

Default test accounts (password: `password`):
- `john@example.com`
- `jane@example.com`
- `mike@example.com`

## 🎨 Design System

### Colors
- **Primary**: #0ea5e9 (Sky Blue)
- **Accent**: #10b981 (Emerald Green)
- **Text**: #0f172a (Slate)
- **Background**: #f8fafc (Gray)

### Typography
- **Font Family**: Inter (Google Fonts)
- **Weights**: 400 (Regular), 600 (Semibold), 700 (Bold)

### Components
- **Buttons**: Primary, secondary, and accent variants
- **Forms**: Consistent styling with validation states
- **Cards**: Product cards, info cards with hover effects
- **Navigation**: Responsive header with mobile menu

## 🔧 Configuration

### Environment Settings
Edit `core/config.php` to customize:
- Database connection
- Site name and URL
- Session lifetime
- File upload settings
- Pagination limits

### Security Settings
- CSRF token lifetime
- Session configuration
- Password requirements
- File upload restrictions

## 📱 Responsive Design

The website is fully responsive with breakpoints:
- **Mobile**: < 640px
- **Tablet**: 640px - 1024px
- **Desktop**: > 1024px

## 🔒 Security Features

### Authentication
- Secure password hashing (bcrypt)
- Session regeneration on login
- Strong password requirements
- Account lockout protection (configurable)

### Data Protection
- CSRF tokens on all forms
- SQL injection prevention (PDO)
- XSS protection (output escaping)
- Input validation and sanitization

### Headers
- X-Content-Type-Options: nosniff
- X-XSS-Protection: 1; mode=block
- X-Frame-Options: DENY
- Content Security Policy

## 🚀 Performance

### Optimizations
- Database indexing
- Query optimization
- Browser caching headers
- Gzip compression
- Lazy loading for images
- Minified assets

### Caching
- Browser caching for static assets
- Database query optimization
- Session-based cart storage

## 🧪 Testing

### Manual Testing Checklist
- [ ] User registration and login
- [ ] Product browsing and search
- [ ] Cart functionality
- [ ] Checkout process
- [ ] Order management
- [ ] Profile updates
- [ ] Responsive design
- [ ] Security features

### Browser Support
- Chrome 70+
- Firefox 65+
- Safari 12+
- Edge 79+
- Mobile browsers

## 🔧 Customization

### Branding
- Update `SITE_NAME` in `core/config.php`
- Modify colors in `assets/css/styles.css`
- Replace logo and favicon
- Update contact information

### Features
- Add payment gateways
- Implement email notifications
- Add product reviews
- Integrate with inventory system
- Add prescription upload

## 📈 SEO Features

- Semantic HTML structure
- Meta tags and descriptions
- Open Graph tags
- Clean URLs
- Sitemap ready
- Schema markup ready

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

For support and questions:
- Check the documentation
- Review the code comments
- Create an issue in the repository

## 🗺 Roadmap

### Upcoming Features
- [ ] Email notifications
- [ ] SMS alerts
- [ ] Payment gateway integration
- [ ] Prescription upload
- [ ] Product reviews and ratings
- [ ] Wishlist functionality
- [ ] Advanced search filters
- [ ] Multi-language support
- [ ] API endpoints
- [ ] Mobile app integration

---

**Built with ❤️ for modern pharmacy management**