# 🛍️ Kunj E-commerce Website

A **full-featured e-commerce platform** built with PHP, MySQL, JavaScript, and Bootstrap. This project includes a complete customer-facing store and a powerful admin panel for managing products, orders, users, and coupons.

---

## 🌐 Live Demo

Experience the live application:

### 👤 **Regular User Demo**

**[Launch Customer Store →](https://ecommerce.kunjdeveloper.me)**

> **Demo Credentials:**
>
> - **Email:** `lodycawaxe@mailinator.com`
> - **Password:** `1234`

### 🔐 **Admin Panel Demo**

**[Launch Admin Dashboard →](https://ecommerce.kunjdeveloper.me/admin)**

> **Admin Credentials:**
>
> - **Email:** `jelexycet@mailinator.com`
> - **Password:** `1234`

---

## ✨ Features Overview

### 🛒 Customer-Facing Features

#### **1. User Authentication & Profile Management**

- Complete registration system with email and password
- **🔐 Enterprise-grade security** with bcrypt/Argon2 password hashing
- Secure login/logout functionality
- **Modern session management** with HTTP-only cookies and CSRF protection
- Session management with auto-logout after inactivity (30 minutes)
- **Automatic password migration** from legacy MD5 to secure hashes
- User dashboard with profile management
- Update personal details, addresses, and contact information
- Profile picture upload and management

#### **2. Product Browsing & Discovery**

- Dynamic product catalog with grid layout
- Advanced search and filtering capabilities
- Filter by category, brand, price range, and color
- Product pagination for easy navigation
- Product details page with:
  - High-quality product images
  - Detailed descriptions
  - Stock availability
  - Brand information
  - Price display with discounts
  - Product ratings

#### **3. Shopping Cart System**

- Add products to cart with quantity selection
- Real-time cart value updates
- Modify product quantities in cart
- Remove items from cart
- Cart persistence across sessions
- Cart icon with live item count
- Local storage integration for guest users

#### **4. Wishlist Functionality**

- Save favorite products for later
- Quick access to wishlist items
- Move items from wishlist to cart
- Remove items from wishlist
- Wishlist icon with item count

#### **5. Product Comparison**

- Compare up to 4 products side-by-side
- Category-based comparison (only compare products from the same category)
- Compare button with live product count
- Visual comparison of:
  - Product images
  - Product names and descriptions
  - Prices and discounts
  - Stock availability
  - Brand information
  - Product specifications
  - Colors and features
- Remove individual products from comparison
- Clear all comparisons at once
- Persistent comparison list using local storage
- Responsive comparison view with offcanvas panel
- Add to cart directly from comparison view

#### **6. Checkout & Payment**

- Multi-step checkout process
- Shipping address management
- Billing address (separate or same as shipping)
- Contact number validation
- Payment method selection
- Order summary with itemized costs
- Coupon code application for discounts
- Real-time total calculation
- Order confirmation page

#### **7. Order Management**

- View complete order history
- Track order status (Pending, Processing, Shipped, Delivered)
- Order details with:
  - Order ID and date
  - Product list with quantities
  - Shipping information
  - Payment details
  - Total amount
- **Order Return System:**
  - Request returns within 30 days
  - Select return reason (Defective, Wrong Item, Changed Mind, etc.)
  - Add return description
  - Track return status
  - Refund amount calculation

#### **8. User Dashboard**

- Personal information overview
- Quick access to:
  - My Orders
  - My Addresses
  - Account Details
- Statistics and recent activity

#### **9. Additional Features**

- Responsive design (mobile, tablet, desktop)
- Toast notifications for user actions
- Form validation with error messages
- About page
- Contact page
- Modern UI/UX with Bootstrap 5
- Font Awesome icons

---

### 🔧 Admin Panel Features

#### **1. Admin Dashboard**

- **Overview statistics:**
  - Total revenue
  - Total orders
  - Total products
  - Total users
- Recent orders summary
- Quick access navigation
- Analytics and insights

#### **2. Product Management**

- **Add new products** with:
  - Product name and description
  - Category and brand
  - Price and discount percentage
  - Stock quantity
  - Color and specifications
  - Product image upload
  - Form validation
- **View all products** in a data table
- **Edit existing products**:
  - Update all product details
  - Change product images
  - Modify pricing and stock
- **Delete products** with confirmation
- **Product state management** (Active/Inactive)
- **Search and filter** products
- **Pagination** for product listing

#### **3. Order Management**

- View all customer orders
- **Order listing** with:
  - Order ID and date
  - Customer details
  - Order status
  - Total amount
  - Payment method
- **View order details:**
  - Complete product list
  - Customer information
  - Shipping address
  - Payment details
- **Edit order status:**
  - Pending
  - Processing
  - Shipped
  - Delivered
  - Cancelled
- **Delete orders** (with caution)
- **Delete individual order items**
- **Order filtering and search**
- **Export order data**

#### **4. User Management**

- View all registered users
- User information display:
  - Username and email
  - Registration date
  - Contact details
  - Profile picture
- User activity tracking
- Search users by name or email

#### **5. Coupon Management**

- **Create discount coupons:**
  - Coupon code generation
  - Discount type (percentage or fixed amount)
  - Discount value
  - Expiry date
  - Usage limit
  - Minimum order value
- **View all active coupons**
- **Edit coupon details**
- **Delete/Deactivate coupons**
- **Coupon usage tracking**

#### **6. Admin Authentication**

- Separate admin login system
- Secure session management
- Admin profile with image
- Auto-logout after inactivity
- Admin-only access protection

#### **7. Admin UI Features**

- Responsive sidebar navigation
- Mobile-friendly offcanvas menu
- Profile dropdown
- Toast notifications
- Data tables with sorting
- Modal dialogs for confirmations
- Form validation

---

## � Security Features

This e-commerce platform implements enterprise-grade security practices to protect user data and prevent common vulnerabilities:

### **Password Security**

- **Modern hashing algorithms**:
  - Argon2id (preferred) - Winner of Password Hashing Competition
  - Argon2i (fallback) - Memory-hard algorithm
  - Bcrypt (fallback) - Industry-standard with cost factor 12
- **Automatic salting** - Unique salt for each password
- **Automatic password migration** - Legacy MD5 passwords automatically upgraded to secure hashes on next login
- **Password rehashing** - Automatically upgrades to stronger algorithms when available

### **Session Security**

- **HTTP-only cookies** - Prevents JavaScript access to session cookies
- **SameSite=Strict** - Prevents CSRF attacks via cross-site requests
- **Session timeout** - Automatic logout after 30 minutes of inactivity
- **Session regeneration** - New session IDs every 30 minutes
- **User-Agent validation** - Detects session hijacking attempts
- **IP address validation** - Additional session security layer

### **Additional Security Measures**

- **Security headers**:
  - X-Frame-Options: DENY (prevents clickjacking)
  - X-Content-Type-Options: nosniff (prevents MIME sniffing)
  - X-XSS-Protection: 1; mode=block
  - Referrer-Policy: strict-origin-when-cross-origin
  - Permissions-Policy (restricts browser features)
- **CSRF protection** - Token generation and validation ready
- **Rate limiting** - Helper functions to prevent brute force attacks
- **Input sanitization** - XSS and injection attack prevention

---

## �🚀 Quick Start

### **Local Development**

1. **Clone or download** this repository to your `htdocs` folder (XAMPP/WAMP)

   ```bash
   cd C:\xampp\htdocs
   git clone <repository-url> E-commerce-Project-FullStack
   ```

2. **Import database**:
   - Open phpMyAdmin (`http://localhost/phpmyadmin`)
   - Create a new database (e.g., `kunj_ecommerce`)
   - Import the SQL file included in the project

3. **Configure database connection**:
   - Edit `config.php` if needed (auto-detects localhost)

4. **Access the site**:
   - Customer Store: `http://localhost/E-commerce-Project-FullStack/`
   - Admin Panel: `http://localhost/E-commerce-Project-FullStack/admin/`

5. **Start shopping!** 🎉

### **Production Deployment**

For production deployment instructions, see [`DEPLOYMENT_CHECKLIST.md`](DEPLOYMENT_CHECKLIST.md)

---

## 📁 Project Structure

```
E-commerce-Project-FullStack/
│
├── 📄 *.php                         # Core application pages
│   ├── config.php & conn.php        # Configuration & database connection
│   ├── index.php                    # Homepage
│   ├── product.php, show-product.php # Product pages
│   ├── checkout.php, add-to-cart.php # Shopping & checkout
│   ├── signin.php, signup.php       # Authentication
│   ├── my-*.php                     # User dashboard pages
│   ├── return-*.php                 # Order return system
│   └── header/footer components     # Reusable UI components
│
├── 📁 admin/                        # � Admin Panel
│   ├── *.php                        # Admin pages (dashboard, products, orders, users, coupons)
│   └── � assets/
│       ├── 📁 api/                  # Admin API endpoints
│       ├── 📁 css/                  # Admin styles
│       ├── 📁 js/                   # Admin JavaScript
│       └── 📁 images/               # Admin uploads & resources
│
├── 📁 assets/                       # 🎨 Customer-Facing Assets
│   ├── 📁 api/                      # Backend API endpoints
│   │   ├── Cart & Wishlist APIs
│   │   ├── Product & Checkout APIs
│   │   ├── User & Order APIs
│   │   └── Coupon & Return APIs
│   │
│   ├── 📁 css/                      # Stylesheets
│   │   ├── bootstrap.min.css
│   │   ├── style.css
│   │   └── responsive.css
│   │
│   ├── 📁 js/                       # JavaScript Modules
│   │   ├── Cart & Wishlist logic
│   │   ├── Product display & filters
│   │   ├── Product comparison (compare.js)
│   │   ├── Form validation
│   │   ├── Order & return management
│   │   └── Utility functions
│   │
│   ├── 📁 images/                   # Images & Graphics
│   │   ├── 📁 icons/                # Icon assets
│   │   ├── 📁 resources/            # Static images
│   │   ├── 📁 uploads/              # Product images
│   │   └── 📁 user-sign-up-uploads/ # User avatars
│   │
│   └── 📁 webfonts/                 # Font files (Font Awesome)
│
├── 📖 README.md                     # Project documentation
└── 📋 DEPLOYMENT_CHECKLIST.md       # Deployment guide
```

### **Key Directories Explained**

| Directory          | Purpose                                                             |
| ------------------ | ------------------------------------------------------------------- |
| **Root (/)**       | Main customer-facing PHP pages and application logic                |
| **admin/**         | Complete admin panel with product, order, user & coupon management  |
| **assets/api/**    | RESTful API endpoints for cart, wishlist, checkout, orders, returns |
| **assets/css/**    | Bootstrap framework + custom responsive styles                      |
| **assets/js/**     | Modular JavaScript for dynamic features (cart, filters, validation) |
| **assets/images/** | Product uploads, user avatars, icons, and static resources          |
| **admin/assets/**  | Separate asset structure for admin panel functionality              |

---

## 🛠️ Technology Stack

### **Frontend**

- **HTML5** - Semantic markup
- **CSS3** - Modern styling with Flexbox & Grid
- **Bootstrap 5** - Responsive framework
- **JavaScript (ES6+)** - Modern JS with modules
- **jQuery** - DOM manipulation and AJAX
- **Font Awesome** - Icon library
- **Owl Carousel** - Product carousels

### **Backend**

- **PHP 7.4+** - Server-side scripting with modern features
- **MySQL** - Relational database
- **PDO/MySQLi** - Database connectivity with prepared statements

### **Security**

- **bcrypt/Argon2** - Modern password hashing (cost factor 12 / 64MB memory)
- **Prepared statements** - SQL injection prevention
- **Session security** - HTTP-only cookies, SameSite=Strict, 30-min timeout
- **Security headers** - X-Frame-Options, CSP-ready, XSS protection
- **CSRF protection** - Token-based validation (ready to implement)
- **Rate limiting** - Brute force attack prevention
- **Input sanitization** - XSS and injection protection
- **Automatic password migration** - Legacy MD5 to modern hashes

### **Libraries & Plugins**

- **jQuery Validation** - Form validation
- **Bootstrap Bundle** - Bootstrap + Popper.js
- **AJAX** - Asynchronous data loading
- **Owl Carousel** - Product carousels and sliders
- CSRF protection
- Input validation and sanitization
- XSS prevention with `htmlspecialchars()`

---

## 💾 Database Structure

The application uses MySQL with the following main tables:

### **User Tables**

- `user_sign_in` - Customer accounts
- `admin_sign_in` - Admin accounts
- `user_details` - Extended user information

### **Product Tables**

- `products` - Product catalog
- `product_categories` - Categories
- `product_brands` - Brands

### **Order Tables**

- `orders` - Order headers
- `order_items` - Order line items
- `return_products` - Product returns

### **Other Tables**

- `coupons` - Discount coupons
- `cart` - Shopping cart items
- `wishlist` - Saved products

---

## 🎨 Key Features Explained

### **1. Real-time Cart Updates**

The application uses a combination of local storage and database synchronization to provide:

- Instant cart updates without page refresh
- Persistent cart across sessions
- Cart badge updates in real-time
- Seamless experience for logged-in and guest users

### **2. Advanced Product Filtering**

Users can filter products by:

- **Category** (Laptop, Mobile, Tablet, etc.)
- **Brand** (Apple, Samsung, Dell, etc.)
- **Price Range** (Custom slider)
- **Color** (Multiple selections)
- Search by product name
- Combinations of all filters

### **3. Coupon System**

Admins can create flexible coupons with:

- Percentage or fixed amount discounts
- Expiry dates
- Minimum order requirements
- Usage limits
- Auto-validation at checkout

### **4. Product Comparison System**

Users can compare products with:

- **Maximum 4 products** at a time for optimal comparison
- **Category restriction** - Only products from the same category can be compared
- **Real-time comparison view** using offcanvas panel
- **Detailed comparison** including:
  - Product images and names
  - Prices and discount percentages
  - Stock availability status
  - Brand and category information
  - Product descriptions
  - Color and specifications
- **Persistent storage** - Comparison list saved in local storage
- **Easy management** - Remove individual products or clear all
- **Visual indicators** - Active comparison icon on product cards
- **Responsive design** - Optimized for mobile, tablet, and desktop
- **Direct cart actions** - Add to cart from comparison view

### **5. Order Return Workflow**

Customers can:

1. View eligible orders (within 30 days)
2. Select items to return
3. Choose return reason
4. Provide description
5. Submit return request
6. Track return status
7. Receive refund confirmation

### **6. Responsive Design**

- Mobile-first approach
- Breakpoints for all device sizes
- Touch-friendly interface
- Optimized images
- Collapsible navigation
- Offcanvas sidebars for mobile

---

## 🔐 Security Best Practices

1. **SQL Injection Prevention**
   - All database queries use prepared statements
   - Parameter binding for user inputs

2. **XSS Protection**
   - Output escaping with `htmlspecialchars()`
   - Content Security Policy headers

3. **Session Security**
   - Secure session configuration
   - Session timeout (auto-logout)
   - Session ID regeneration

4. **Password Security**
   - Passwords hashed with strong algorithms
   - Password strength requirements

5. **Access Control**
   - Admin panel protected with authentication
   - User session validation on each page
   - Separate admin and user sessions

---

## 📱 Responsive Breakpoints

- **Mobile:** < 576px
- **Tablet:** 576px - 768px
- **Desktop:** 768px - 992px
- **Large Desktop:** 992px - 1200px
- **Extra Large:** > 1200px

---

## 🚀 Installation Guide

### **Prerequisites**

- XAMPP/WAMP/LAMP (PHP 7.4+ and MySQL 5.7+)
- Web browser (Chrome, Firefox, Safari, Edge)
- Text editor (VS Code, Sublime, PHPStorm)

### **Step-by-Step Installation**

1. **Download/Clone the project**

   ```bash
   git clone <repository-url>
   cd E-commerce-Project-FullStack
   ```

2. **Move to web server directory**

   ```bash
   # For XAMPP
   move E-commerce-Project-FullStack C:\xampp\htdocs\

   # For WAMP
   move E-commerce-Project-FullStack C:\wamp64\www\
   ```

3. **Create database**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Click "New" to create database
   - Name it `kunj_ecommerce`
   - Set collation to `utf8mb4_general_ci`

4. **Import database**
   - Select the `kunj_ecommerce` database
   - Click "Import" tab
   - Choose the SQL file from project
   - Click "Go" to import

5. **Configure connection** (optional for localhost)
   - Open `config.php`
   - Verify these settings:

   ```php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $dbname = "kunj_ecommerce";
   ```

6. **Start Apache and MySQL**
   - Open XAMPP Control Panel
   - Start Apache
   - Start MySQL

7. **Access the website**
   - Customer: `http://localhost/E-commerce-Project-FullStack/`
   - Admin: `http://localhost/E-commerce-Project-FullStack/admin/`

8. **Create test accounts or use demo credentials** (see top of README)

---

## 🎯 Usage Guide

### **For Customers**

1. **Browse Products**
   - Visit homepage or products page
   - Use filters to narrow down choices
   - Click on product for details

2. **Add to Cart/Wishlist**
   - Select quantity
   - Click "Add to Cart" or heart icon
   - View cart icon for item count

3. **Checkout**
   - Click cart icon
   - Review items
   - Proceed to checkout
   - Fill shipping details
   - Apply coupon (optional)
   - Place order

4. **Compare Products**
   - Browse products
   - Click the compare icon (code-compare) on product cards
   - Add up to 4 products from the same category
   - Click "Compare Products" button at bottom of page
   - View side-by-side comparison
   - Add products to cart from comparison
   - Remove products or clear all comparisons
   - Close comparison panel when done

5. **Track Orders**
   - Go to "My Orders"
   - Click order to view details
   - Track shipment status

6. **Return Items**
   - Go to "My Orders"
   - Click "Return Order"
   - Select items and reason
   - Submit return request

### **For Admins**

1. **Login to Admin Panel**
   - Visit `/admin/`
   - Enter admin credentials
   - Access dashboard

2. **Manage Products**
   - Click "Products" in sidebar
   - Add new products with form
   - Edit/delete existing products
   - Toggle product status

3. **Process Orders**
   - Click "Order Listing"
   - View all orders
   - Update order status
   - View order details
   - Manage order items

4. **Create Coupons**
   - Click "Coupons"
   - Add coupon code and details
   - Set discount and validity
   - Track coupon usage

5. **Manage Users**
   - Click "Users"
   - View all registered users
   - Search and filter users

---

## 🐛 Troubleshooting

### **Common Issues**

**Database Connection Error**

- Check XAMPP/WAMP is running
- Verify MySQL service is active
- Check `config.php` credentials
- Ensure database name matches

**Images Not Loading**

- Check file paths in database
- Verify images exist in upload folders
- Check folder permissions

**Session Errors**

- Clear browser cookies
- Check PHP session configuration
- Ensure session_start() is called

**Admin Can't Login**

- Verify admin account exists in `admin_sign_in` table
- Check password hash
- Clear browser cache

**Products Not Displaying**

- Check product status is "active"
- Verify stock > 0
- Check API endpoints are accessible

---

## 📄 License

This project is open-source and available for educational purposes.

---

## 👨‍💻 Developer

**Kunj Developer**

- Portfolio Website: [kunjdeveloper.me](https://kunjdeveloper.me)
- Live Demo: [ecommerce.kunjdeveloper.me](https://ecommerce.kunjdeveloper.me)

---

## 🙏 Acknowledgments

- Bootstrap team for the amazing framework
- Font Awesome for the icon library
- jQuery team for the powerful library
- PHP community for excellent documentation

---

## 📞 Support

For issues, questions, or suggestions:

1. Check the troubleshooting section
2. Review the code documentation
3. Test with demo credentials
4. Check browser console for errors

---

## 🔄 Version History

**v1.0.0** - Initial Release

- Complete e-commerce functionality
- Admin panel
- User authentication
- Order management
- Return system
- Coupon support

---

**Made by Kunj Developer**
│ └── ...
└── assets/
├── api/ # API endpoints
├── css/ # Stylesheets
├── js/ # JavaScript files
└── images/ # Images and uploads

```

## 🔧 Configuration

All base URLs and database settings are now managed in `config.php`. This file:

- ✅ Auto-detects localhost vs. production
- ✅ Supports HTTPS
- ✅ Works with root or subdirectory deployment
- ✅ No code changes needed when switching environments

## 📚 Documentation

- **[DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)** - Quick deployment guide
- **[DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)** - Comprehensive deployment instructions
- **[BASE_URL_CHANGES.md](BASE_URL_CHANGES.md)** - Technical details about base URL changes
- **[.htaccess.example](.htaccess.example)** - Apache configuration template

## 🛠️ Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Frameworks**: Bootstrap 5
- **Libraries**: jQuery, Owl Carousel

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled (for Apache)

## 🔐 Security

- Database credentials are stored in `config.php`
- Passwords should be changed for production
- HTTPS is recommended for production
- See `.htaccess.example` for additional security settings

## 🌐 Admin Panel

Access the admin panel at: `/admin/`

Default admin credentials (change these!):

- Check the database for admin user details

## 🤝 Contributing

This is a learning project. Feel free to fork and modify for your own use.

## 📝 License

This project is for educational purposes.

## 🐛 Troubleshooting

**Q: Images not loading?**
A: Check the base URL in `config.php` matches your directory structure

**Q: Database connection error?**
A: Verify credentials in `config.php`

**Q: CSS/JS not working?**
A: Clear browser cache and verify base URLs

For more troubleshooting help, see [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)

## 📞 Support

If you encounter issues:

1. Check the documentation files
2. Verify your `config.php` settings
3. Check server error logs
4. Ensure PHP version compatibility

---

**Last Updated**: December 6, 2025
**Author**: Kunj
```
