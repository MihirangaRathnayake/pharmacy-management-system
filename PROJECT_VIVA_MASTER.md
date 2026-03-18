# Pharmacy Management System - Viva Master Guide

_Auto-generated on 2026-03-17 02:15:39_

## 1) Elevator Pitch (What to Say in Viva)
- This is a PHP + MySQL pharmacy management system with role-based admin operations for auth, inventory, suppliers, customers, prescriptions, sales, reports, and settings.
- Core flow: maintain medicine stock -> perform sales -> reduce inventory -> generate invoice -> monitor KPIs and alerts.
- Data integrity uses relational schema, foreign keys, PDO prepared statements, and transactions for critical writes.

## 2) High-Level Architecture
- Entry points: index.php, admin/index.php, admin/auth/*.php, admin/modules/*, admin/api/*.php
- Bootstrap chain: admin/bootstrap.php -> config/config.php -> config/database.php -> auth/helpers
- Shared UI components live in admin/includes and static assets in admin/assets

## 3) Application Flow (End-to-End)
1. User opens index.php and gets redirected to admin/.
2. admin/index.php verifies DB setup, admin existence, and login state.
3. Unauthenticated users are redirected to admin/auth/login.php.
4. On success, dashboard loads KPIs: today sales, low stock, pending prescriptions, customer count.
5. User works through modules: inventory, suppliers, customers, prescriptions, sales, reports, settings.
6. Sales flow inserts sales + sale_items and updates medicines stock atomically.
7. Prescription flow uploads file, links/creates customer, and stores pending prescription record.

## 4) Module Map
- admin/modules/customers
- admin/modules/inventory
- admin/modules/prescriptions
- admin/modules/profile
- admin/modules/reports
- admin/modules/sales
- admin/modules/settings
- admin/modules/suppliers

## 5) Auth + API Surfaces
### Auth Pages
- admin/auth/forgot_password.php
- admin/auth/login.php
- admin/auth/logout.php
- admin/auth/register.php
- admin/auth/reset_password.php
- admin/auth/verify_code.php

### API Endpoints
- admin/api/notifications.php
- admin/api/profile_actions.php

## 6) Database Map
- users: Authentication users and roles
- categories: Medicine grouping
- suppliers: Supplier master data
- medicines: Medicine master, stock and pricing
- customers: Customer profile and medical context
- sales: Sales headers (invoice-level)
- sale_items: Line items per sale
- prescriptions: Prescription upload and verification status
- prescription_items: Prescription medicine lines
- stock_movements: Inventory movement audit trail
- notifications: In-app alerts
- settings: System-wide settings
- password_reset_tokens: Password reset workflow
- user_preferences: Theme and notification preferences

## 7) Critical Redirects
- index.php -> admin/
- admin\modules\customers\view_customer.php -> index.php
- admin\modules\customers\view_customer.php -> index.php
- admin\index.php -> auth/login.php?message=database_error
- admin\index.php -> auth/login.php?message=no_admin
- admin\index.php -> auth/login.php
- admin\index.php -> auth/login.php?message=access_denied
- admin\modules\suppliers\view_supplier.php -> index.php
- admin\modules\suppliers\view_supplier.php -> index.php
- admin\modules\suppliers\edit_supplier.php -> index.php
- admin\modules\suppliers\edit_supplier.php -> index.php
- admin\includes\header.php ->  
- admin\includes\auth.php -> auth/register.php
- admin\includes\auth.php -> auth/login.php
- admin\includes\auth.php -> /pharmacy-management-system/index.php?error=access_denied
- admin\bootstrap.php -> $url
- admin\modules\settings\index.php -> ../../auth/login.php
- admin\auth\verify_code.php -> ../index.php
- admin\auth\verify_code.php -> forgot_password.php
- admin\auth\verify_code.php -> reset_password.php?token=
- admin\auth\reset_password.php -> ../index.php
- admin\auth\register.php -> login.php?message=admin_exists
- admin\auth\logout.php -> login.php?message=logout
- admin\auth\login.php -> register.php
- admin\auth\login.php -> ../index.php
- admin\auth\login.php -> ../index.php
- admin\auth\forgot_password.php -> ../index.php
- admin\modules\sales\invoice.php -> ../../auth/login.php
- admin\modules\sales\invoice.php -> index.php
- admin\modules\sales\invoice.php -> index.php?error=Invoice not found
- admin\modules\sales\invoice.php -> index.php?error=Error loading invoice
- admin\modules\reports\index.php -> ../../auth/login.php
- admin\modules\inventory\view_medicine.php -> index.php
- admin\modules\inventory\view_medicine.php -> index.php
- admin\modules\inventory\edit_medicine.php -> index.php
- admin\modules\inventory\edit_medicine.php -> index.php
- admin\modules\inventory\add_medicine.php -> ../../auth/login.php

## 8) Transaction-Sensitive Files
- admin\modules\customers\add_customer.php
- admin\modules\sales\process_sale.php
- admin\modules\prescriptions\upload.php

## 9) Top Viva Questions with Model Answers
1. Q: Why use a bootstrap file?
   A: It centralizes config, DB connection, auth, and shared utilities so all modules load consistent context.
2. Q: How is authentication handled?
   A: Session-based login with password hashing/verification and active-user checks from DB.
3. Q: Why split sales and sale_items tables?
   A: Header-detail normalization allows one invoice with multiple medicine lines.
4. Q: How is stock kept accurate?
   A: Stock is reduced during sale processing inside a transaction, avoiding partial writes.
5. Q: How are prescriptions processed?
   A: Upload and validate file, create/find customer, save prescription with pending status for verification.
6. Q: What security controls are present?
   A: PDO prepared statements, hashed passwords, session auth, and role checks.
7. Q: One limitation and improvement?
   A: Add stricter CSRF coverage and stronger module-level authorization in all state-changing routes.
8. Q: Why foreign keys matter here?
   A: They enforce data integrity between users, customers, medicines, sales, and prescriptions.
9. Q: How would you scale this app?
   A: Add pagination, query/index tuning, caching, and service/API separation as traffic grows.
10. Q: Why transactions are critical in pharmacy sales?
    A: Sales, line items, and stock updates must succeed together or fail together to keep financial and inventory accuracy.

## 10) 60-Second Viva Script
- This project is a modular PHP/MySQL pharmacy system with authenticated admin workflows.
- The app boots through admin/bootstrap.php, connects to DB, and protects routes using session checks.
- Main business operations are medicine inventory, supplier/customer handling, prescription intake, and POS billing.
- Sales are transaction-safe and automatically update stock quantities to keep inventory synchronized.
- Dashboard and reports provide actionable KPIs for daily operations and decision making.

## 11) Quick Revision Checklist
- Explain login/session lifecycle clearly.
- Explain sales transaction and stock deduction flow.
- Explain prescription status lifecycle.
- Explain key table relationships and foreign keys.
- Explain one limitation and one future enhancement.

## 12) Snapshot Stats
- PHP files scanned: 54
- Modules detected: 8
- API endpoints detected: 2
- DB tables parsed: 14

## 13) Detailed Runtime Flows
### A) Login + Dashboard Flow
1. `index.php` redirects to `admin/`.
2. `admin/index.php` includes `admin/bootstrap.php`.
3. Bootstrap loads `config/config.php`, `config/database.php`, `admin/includes/auth.php`.
4. `admin/index.php` checks database connection and admin availability.
5. If not logged in, redirect to `admin/auth/login.php`.
6. `login.php` validates email/password using `loginUser()` and `password_verify()`.
7. Session keys set: `user_id`, `user_email`, `user_name`, `user_role`, `login_time`.
8. Dashboard loads KPIs from `sales`, `medicines`, `prescriptions`, `customers`.

### B) Sales (POS) Flow
1. Open `admin/modules/sales/new_sale.php`.
2. Search medicines through `search_medicine.php?q=...` (name/generic/barcode).
3. Build cart in frontend and send JSON to `process_sale.php`.
4. Backend starts transaction.
5. Insert into `sales`.
6. Insert each row into `sale_items`.
7. Decrement `medicines.stock_quantity` for each sold item.
8. Commit transaction and return invoice URL.
9. Invoice rendered by `invoice.php?id=...`.

### C) Prescription Upload Flow
1. Open `admin/modules/prescriptions/upload.php`.
2. Validate fields and uploaded file extension (`jpg`, `jpeg`, `png`, `pdf`).
3. Create customer if phone not already existing.
4. Insert prescription with status `pending`.
5. Commit transaction and show success message.

### D) Customer Add Flow
1. `admin/modules/customers/add_customer.php` validates required fields.
2. Checks duplicate active phone/email.
3. Starts transaction.
4. Inserts customer with generated customer code.
5. If email exists, creates linked user account with role `customer`.
6. Commits transaction.

### E) Delete / Deactivate Strategy
1. Customer delete endpoint checks sales history.
2. If history exists: mark customer `inactive`.
3. If no history: hard delete row.
4. Medicine delete endpoint checks `sale_items`.
5. If sold before: mark `discontinued`.
6. If never sold: hard delete.
7. Supplier delete endpoint checks linked medicines.
8. If linked: mark `inactive`; else hard delete.

### F) Notification Engine Flow
1. Client hits `admin/api/notifications.php?action=fetch`.
2. API returns latest 20 notifications (user-specific + global).
3. Mark single read with `action=mark_read`.
4. Mark all read with `action=mark_all_read`.
5. `action=generate` creates smart alerts for low stock, expiry, daily sales, pending prescriptions.

## 14) Business Rules Captured in Code
- Selling price must be greater than purchase price when adding medicine.
- Medicine barcode uniqueness is validated in edit flow.
- Stock values are validated as non-negative in edit flow.
- Sales tax is fixed at 18 percent in `process_sale.php`.
- Customer phone is mandatory in customer add flow.
- Active customer phone/email are checked for duplicates.
- Profile image upload max size is 5MB.
- Profile image MIME types allowed: `image/jpeg`, `image/jpg`, `image/png`, `image/gif`.
- Prescription file extensions allowed: `jpg`, `jpeg`, `png`, `pdf`.
- New password complexity in `profile_actions.php`: min 8 with upper/lower/number.
- Login and registration use hashed passwords (`password_hash`, `password_verify`).

## 15) Authorization and Roles
- Roles in schema: `admin`, `pharmacist`, `customer`.
- Runtime auth is session based with `requireLogin()`.
- `requireRole()` helper exists but module-level strict role gating is limited.
- Most admin module pages depend on login state, not fine-grained role checks.
- APIs (`notifications`, `profile_actions`, sales process) reject unauthenticated access.

## 16) Data Model Relationships (Viva-Ready)
- `customers.user_id -> users.id`
- `sales.customer_id -> customers.id`
- `sales.user_id -> users.id`
- `sale_items.sale_id -> sales.id`
- `sale_items.medicine_id -> medicines.id`
- `medicines.category_id -> categories.id`
- `medicines.supplier_id -> suppliers.id`
- `prescriptions.customer_id -> customers.id`
- `prescriptions.verified_by -> users.id`
- `prescription_items.prescription_id -> prescriptions.id`
- `prescription_items.medicine_id -> medicines.id`
- `stock_movements.medicine_id -> medicines.id`
- `stock_movements.created_by -> users.id`
- `notifications.user_id -> users.id` (nullable for global alerts)
- `user_preferences.user_id -> users.id` (unique per user)

## 17) Reporting and Analytics Logic
- Reports calculate daily, monthly, yearly sales totals.
- Customer metrics include total active customers and new customers this month.
- Inventory metrics include low stock and expiring soon counts.
- Supplier analytics include medicine count, stock volume, stock value.
- Monthly sales trend is grouped by month for charts.
- Category sales and top-selling medicines are computed from `sale_items` + `sales`.
- Payment method distribution is aggregated from sales records.

## 18) API Contract Summary
### `admin/modules/sales/search_medicine.php`
- Method: GET
- Input: `q`
- Output: `{ success, medicines[] }`
- Purpose: live medicine search for POS

### `admin/modules/sales/process_sale.php`
- Method: POST JSON
- Input: `items[]`, optional `customer_id`, `discount_amount`, `payment_method`, `notes`
- Output: `{ success, sale_id, invoice_number, total_amount, invoice_url }`
- Purpose: transactional sale write + stock update

### `admin/api/notifications.php`
- Method: GET/POST depending on action
- Actions: `fetch`, `mark_read`, `mark_all_read`, `generate`
- Output: JSON success/error payloads

### `admin/api/profile_actions.php`
- Method: POST
- Actions: `upload_profile_image`, `update_profile`, `update_preferences`, `change_password`
- Output: JSON success/error payloads

## 19) Password Reset Flow Notes
- `forgot_password.php` generates token and stores in `password_reset_tokens`.
- `reset_password.php` validates token + expiry + used flag.
- `verify_code.php` expects `verification_code` and `verified` columns.
- If your DB was created from current `schema.sql` only, those two columns are not present.
- For viva: mention token-based reset exists, and 2-step code verification is partially integrated and needs schema alignment.

## 20) Architecture Strengths to Say in Viva
- Clear modular separation by domain (`inventory`, `sales`, `customers`, `suppliers`, `reports`, `settings`).
- Transaction use for critical writes.
- Uses prepared statements with PDO.
- Business-friendly soft delete strategy to preserve historical consistency.
- Dashboard and reports driven by real SQL aggregation, not mock data only.

## 21) Known Gaps and Honest Improvement Points
- `customers/index.php` links to `edit_customer.php`, but that file is missing.
- `theme_helper.php` returns `/pharmacy-management-system/assets/css/theme.css`, but file is missing.
- `verify_code.php` requires DB columns not present in base schema (`verification_code`, `verified`).
- `reset_admin.php` references `start_here.php` and `router.php`, but those files are missing.
- CSRF protection is not consistently visible in forms/endpoints.
- Some state-changing endpoints rely on session auth only without role-level checks.
- Some destructive behavior still allows hard delete when no history exists.
- Default customer password (`customer123`) in add-customer flow is weak for production.

## 22) 8-Minute Viva Demo Script
1. Show architecture folders (`config`, `admin/modules`, `admin/api`, `database`).
2. Explain bootstrap path and auth gate.
3. Log in and show dashboard KPI cards.
4. Open inventory and show stock/expiry alert logic.
5. Create a new sale with medicine search and complete checkout.
6. Open generated invoice and explain header-detail table design.
7. Show reports page charts and describe SQL aggregation.
8. Open notifications and explain auto-generation rules.
9. Show settings/profile update and preference persistence.
10. Finish with one limitation and one roadmap item.

## 23) Advanced Viva Questions (High Probability)
1. Q: Why do you use transactions in sales but not in every module?
   A: Sales updates multiple tables with strong consistency requirements; simpler single-row updates can be atomic per query.
2. Q: Why soft-delete customers/suppliers/medicines?
   A: Historical invoices and analytics must remain valid even when entities become inactive.
3. Q: Why keep both `sale_date` and `created_at`?
   A: `sale_date` represents business event time; `created_at` tracks row creation metadata.
4. Q: How can overselling happen and how would you prevent it?
   A: Current flow decrements stock without explicit row lock/check; add stock validation with `SELECT ... FOR UPDATE` and reject insufficient stock.
5. Q: What indexes matter most for this app?
   A: Invoice, sale date, medicine name/barcode, customer code/phone, token expiry.
6. Q: How would you make reports faster at scale?
   A: Add summary tables/materialized views, cache common periods, optimize joins/indexes.
7. Q: Where does role authorization need improvement?
   A: Add explicit role guards per sensitive route (delete/update/report admin-only actions).
8. Q: How would you productionize file uploads?
   A: Strong MIME validation, antivirus scan, random path, private storage, signed retrieval URLs.
9. Q: What happens if DB is down during login?
   A: Login fails and app redirects with database error handling path.
10. Q: Which part best shows maintainability?
    A: Module-based separation plus centralized bootstrap/auth/helpers.

## 24) Test Scenarios You Should Be Ready to Explain
- Successful login and failed login.
- Add medicine with invalid pricing (should fail).
- Sale with multiple items and invoice generation.
- Stock deduction after sale.
- Delete medicine with history (should mark discontinued).
- Delete customer with history (should mark inactive).
- Supplier with linked medicines should deactivate, not delete.
- Upload invalid profile image type (should fail).
- Password change with weak password (API should reject).
- Report numbers changing after adding sample data.

## 25) Quick Command Set for Viva Preparation
- Regenerate guide: `powershell -NoProfile -ExecutionPolicy Bypass -File .\\generate-viva-guide.ps1`
- Count PHP files: `rg --files -g \"*.php\" | measure`
- Find transactions: `rg -n \"beginTransaction|commit\\(|rollBack\\(\" admin`
- Find redirects: `rg -n \"header\\('Location:\" admin index.php`
- Find auth checks: `rg -n \"requireLogin\\(|isLoggedIn\\(\" admin`

## 26) Long Forms and Glossary (Viva Terminology)
- API: Application Programming Interface. A defined way for frontend and backend to communicate.
- UI: User Interface. The visual screens and controls users interact with.
- UX: User Experience. Overall ease and quality of user interaction.
- POS: Point of Sale. Billing/checkout workflow for medicine sales.
- KPI: Key Performance Indicator. Measurable business metric (sales, low stock count, etc.).
- DB: Database. Structured storage for application data.
- DBMS: Database Management System. Software used to manage databases (MySQL here).
- RDBMS: Relational Database Management System. Database using tables and relations.
- SQL: Structured Query Language. Language for querying and managing relational data.
- PDO: PHP Data Objects. PHP extension for database access with prepared statements.
- CRUD: Create, Read, Update, Delete. Four core data operations.
- JSON: JavaScript Object Notation. Data format used for API payloads.
- AJAX: Asynchronous JavaScript and XML. Browser technique for background requests.
- HTTP: HyperText Transfer Protocol. Web communication protocol.
- HTTPS: HyperText Transfer Protocol Secure. Encrypted HTTP communication.
- URI/URL: Uniform Resource Identifier / Uniform Resource Locator. Web resource addressing.
- HTML: HyperText Markup Language. Structure of web pages.
- CSS: Cascading Style Sheets. Styling language for web pages.
- JS: JavaScript. Client-side programming language for interactivity.
- PHP: PHP Hypertext Preprocessor. Server-side scripting language used in this project.
- MIME: Multipurpose Internet Mail Extensions. Standard for identifying file content types.
- SMTP: Simple Mail Transfer Protocol. Protocol for sending emails.
- OTP: One-Time Password. Single-use verification code for authentication/recovery.
- CSRF: Cross-Site Request Forgery. Attack tricking authenticated users into unwanted actions.
- XSS: Cross-Site Scripting. Injection attack using malicious scripts in web pages.
- SQL Injection: Attack that manipulates SQL queries via unsafe input handling.
- Session: Server-side user state storage for logged-in users.
- Session Timeout: Time limit after which inactive session becomes invalid.
- Authentication: Verifying user identity (login flow).
- Authorization: Granting permissions after identity verification (role checks).
- RBAC: Role-Based Access Control. Access control based on assigned roles.
- Hashing: One-way transformation used for secure password storage.
- `password_hash`: PHP function to create secure password hash.
- `password_verify`: PHP function to verify password against hash.
- Transaction: Group of DB operations executed as one unit.
- Atomicity: Property that all operations in a transaction succeed or all fail.
- Rollback: Revert transaction changes when an error occurs.
- Commit: Permanently apply successful transaction changes.
- Concurrency: Handling multiple simultaneous users/requests.
- Race Condition: Bug caused by concurrent operations in unexpected order.
- FK: Foreign Key. Field linking one table to another table's primary key.
- PK: Primary Key. Unique identifier for each table row.
- Index: Data structure to speed up query lookups.
- Normalization: Organizing schema to reduce data duplication and anomalies.
- ERD: Entity-Relationship Diagram. Visual model of tables and relationships.
- Soft Delete: Marking record inactive/discontinued instead of hard deletion.
- Hard Delete: Physically removing a row from database table.
- Migration: Versioned database schema update script.
- Seed Data: Initial/sample data inserted for development/testing.
- Environment Variable: Externalized config value (DB host, SMTP config, etc.).
- Bootstrap (app): Initial loading stage for config, DB, auth, and helpers.
- Middleware-like Check: Reusable pre-check logic such as `requireLogin()`.
- Validation: Ensuring input data meets rules before processing.
- Sanitization: Cleaning user input to reduce security risks.
- Escaping: Encoding output to safely display dynamic content.
- Pagination: Splitting long record lists into multiple pages.
- Aggregation Query: SQL query summarizing data (SUM, COUNT, GROUP BY).
- Dashboard: Central screen showing real-time operational metrics.
- Notification System: Alert mechanism for low stock, expiry, and pending items.
- File Upload Pipeline: Validation, storage, and DB reference creation for uploaded files.
- Walk-in Customer: Sale made without a registered customer account.
- Invoice Number: Unique identifier for each sale transaction.
- Low Stock Threshold: Minimum stock level used to trigger alerts.
- Expiry Alert Window: Time period (e.g., 30 days) to flag near-expiry medicines.

### Fast Viva Terminology Lines (Speak This Way)
- "This system uses RBAC with session-based authentication and module-level authorization checks."
- "Critical sales writes are transaction-protected to maintain atomicity and data consistency."
- "Schema design follows normalized relational modeling with PK/FK integrity."
- "Operational KPIs are generated using SQL aggregation and exposed in the dashboard/reports."
- "Soft-delete strategy preserves historical accuracy for invoices and analytics."

