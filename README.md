# RestoSystem-API 🚀

The robust backend engine powering the RestoPOS system. Built with **Laravel 11**, this API handles everything from real-time sales processing to inventory management.

## ✨ Core Functionalities

-   **Order Processing**: Specialized endpoints for Buffet check-ins and Alacarte sales increments.
-   **Inventory & Stock Control**: Real-time stock mutation tracking, supplier management, and automated recipe-to-stock deductions.
-   **Kitchen Display System (KDS) Backend**: Integrated with **Laravel Reverb** for high-speed WebSocket notifications between floors and the kitchen.
-   **Reservation System**: Intelligent booking logic with deposit handling and automated table occupation.
-   **Comprehensive Reporting**: SQL-optimized queries for sales reports, employee performance, and stock cards.
-   **Role-Based Access Control**: Secure authentication via **Laravel Sanctum** with granular permissions for Admin, Cashier, and Waiters.

## 🚀 Tech Stack

-   **Framework**: [Laravel 11](https://laravel.com/)
-   **Authentication**: [Sanctum](https://laravel.com/docs/sanctum)
-   **Real-time Engine**: [Laravel Reverb](https://laravel.com/docs/reverb) (WebSockets)
-   **Database**: MySQL
-   **Asset Management**: Vite

## 📦 Setup & Installation

```bash
# Clone the repository
git clone your-repo-url

# Install Composer dependencies
composer install

# Install NPM dependencies
npm install

# Setup environment
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations and seeders
php artisan migrate:fresh --seed
```

## 🎙️ Real-time Implementation

RestoSystem-API uses **Laravel Reverb** to notify KDS stations. Ensure your Reverb server is running for real-time order updates:

```bash
php artisan reverb:start
```

## 🛠️ Key Controllers

-   `SalesController`: Manages complex transaction logic and alacarte increments.
-   `ReservationController`: Handles the transition from booking to active sale.
-   `KitchenController`: Powers the KDS status updates.

---
Robust backend architecture for professional hospitality.
