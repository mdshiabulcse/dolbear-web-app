Here is the technical documentation for dolbear website


# Dolbear Ecommerce - Technical Documentation

## 1. Executive Summary
Dolbear is a comprehensive, single-vendor ecommerce solution built with a Laravel backend and a hybrid frontend architecture. It features a robust Admin Panel rendered with Laravel Blade and a dynamic, customer-facing website built as a Vue.js Single Page Application (SPA). The system is designed for high performance and scalability, featuring real-time synchronization with an ERP system for customers, addresses, and orders.

## 2. Technology Stack

### Backend
*   **Framework:** Laravel 8.83.2 (PHP ^7.3|^8.0)
*   **Database:** MySQL
*   **Authentication:**
    *   **API:** Laravel Sanctum (v2.14) & JWT Auth (v1.0) for the Vue.js frontend.
    *   **Web:** Cartalyst Sentinel (v5.1) for the Blade-based Admin Panel.
*   **Real-time:** Pusher (PHP Server & JS Client)
*   **Image Processing:** Intervention Image

### Frontend
*   **Admin Panel:**
    *   **Technology:** Laravel Blade Templates (Server-side Rendering).
    *   **Styling:** Bootstrap 5, Custom CSS.
    *   **Scripting:** jQuery, Vanilla JS.
*   **Customer Website:**
    *   **Technology:** Vue.js 2.6.14 (SPA).
    *   **Communication:** REST API (consumes Laravel backend APIs).
    *   **State Management:** Vuex 4.1.0.
    *   **Routing:** Vue Router 3.4.3.
    *   **Build Tool:** Laravel Mix 6.0.41.

### Integrations
*   **Payment Gateway:** SSLCommerz.
*   **SMS Gateway:** Elitbuz.
*   **ERP System:** Custom integration for syncing Customers, Delivery Addresses, and Orders.
*   **Shipping:** Pathao (via Service).

## 3. System Architecture

### Backend Architecture
The backend follows a **Modular Monolith** approach with a strong separation of concerns using the **Repository Pattern**.

*   **Controllers:** Located in `app/Http/Controllers`.
    *   `Admin`: Handles requests for the Blade-based Admin Panel.
*   **Repositories:** Located in `app/Repositories`. Abstracts database interactions.
*   **Models:** Located in `app/Models`. Domain models representing the database schema.
*   **Routes:**
    *   `api.php`: RESTful API routes for the Vue.js frontend.
    *   `admin.php`: Routes for the Blade-based Admin Panel.
    *   `web.php`: Fallback routes for the SPA and other web endpoints.

### Frontend Architecture
*   **Admin Panel (Blade):** Traditional server-side rendered application. Views are located in `resources/views/admin`.
*   **Website (Vue.js):** A Single Page Application that runs in the browser.
    *   **Entry Point:** `resources/js/app.js`.
    *   **API Communication:** Uses `axios` to fetch data from the Laravel `api` routes.
    *   **Mount Point:** The Vue app is mounted within a host Blade file (e.g., `master.blade.php`).

## 4. Key Modules & Features

### User Management
*   **Roles:** Admin, Customer.
*   **Auth:**
    *   **Admin:** Session-based auth via Sentinel.
    *   **Customer:** Token-based auth (JWT) via API.

### ERP Synchronization
The system maintains data consistency with an external ERP system:
*   **Customers:** Synced to ERP upon registration or profile update.
*   **Addresses:** Delivery addresses are synced to ensure accurate shipping.
*   **Orders:** New orders are pushed to the ERP for processing and inventory management.

### Product Management
*   **Catalog:** Products, Categories, Brands, Attributes, Colors.
*   **Variants:** SKU generation based on attributes.
*   **Stock:** Inventory tracking per variant.

### Order Management
*   **Cart:** Persistent cart with coupons and shipping calculation.
*   **Checkout:** Multi-step checkout process via Vue.js.
*   **Order Processing:** Status workflow managed in Admin Panel.
*   **Invoicing:** PDF invoice generation.

### Marketing & Promotions
*   **Campaigns:** Seasonal sales and landing pages.
*   **Flash Deals:** Time-limited discounts.
*   **Coupons:** Fixed or percentage-based discounts.

### Logistics
*   **Shipping:** Zone-based shipping, Carrier integrations (Pathao).
*   **Pickup Hubs:** Local pickup options.

## 5. Database Schema Overview
The database supports the ecommerce operations with tables for users, products, orders, and configuration.
*   `users`: Stores both Admin and Customer records.
*   `products`: Central table for catalog items.
*   `orders`: Stores transactional data.

## 6. Setup & Deployment
*   **Requirements:** PHP 7.3+, MySQL 5.7+, Node.js, Composer.
*   **Installation:**
    1.  `composer install`
    2.  `unzip node_modules.zip`
    3.  `cp .env.example .env` (Configure DB, Redis, Pusher, Mail, SSLCommerz, Elitbuz)
    4.  `php artisan key:generate`
    5.  `php artisan migrate --seed`
    6.  `php artisan storage:link`
*   **Development:**
    *   `npm run watch` - Watches for file changes and rebuilds assets automatically.
    *   `php artisan serve` - Runs the Laravel development server.
*   **Queue:** database is recommended for queue processing (emails, notifications, ERP sync).
*   **Cron:** Required for scheduled tasks.

## 7. Security
*   **API Security:** `CheckApiKey` middleware ensures only authorized clients can access the API.
*   **Authentication:** JWT for stateless API authentication.
*   **Validation:** Strict server-side validation using Form Requests.

## 8. Git Workflow
*   **Development:**
    *   `git checkout final_v_3`
    *   `git pull origin final_v_3`
    *   `git checkout -b feature/your-feature`
    *   `git add .`
    *   `git commit -m "Your commit message"`
    *   `git push origin feature/your-feature`
    *   `git checkout final_v_3`
    *   `git pull origin final_v_3`
    *   `git merge feature/your-feature`
    *   `git push origin final_v_3`
    *   `git branch -d feature/your-feature`

    Current Latest Branch: final_v_3

## 9. Deployment

```bash
git pull origin final_v_3
Update database manually via phpMyAdmin if anything changed
copy public/images to root directory
npm run watch on local machine
zip  public
unzip public.zip into public_html directory in server
restore backup image to public_html/public/images