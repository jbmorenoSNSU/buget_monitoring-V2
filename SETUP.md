# Personal Budget Monitoring System

## Prerequisites
- PHP 8.3+
- Composer
- Node.js & npm
- MySQL Server (e.g., WampServer)

## Setup Instructions

1. **Environment Setup**
   Ensure your `.env` file is configured for your MySQL database. By default, it expects:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=budget_monitoring
   DB_USERNAME=root
   DB_PASSWORD=
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Database Migration and Seeding**
   The application comes with comprehensive seeders to populate realistic data (Accounts, Categories, Transactions, Budget Goals, and Recurring Transactions).
   ```bash
   php artisan migrate:fresh --seed
   ```

4. **Build Frontend Assets**
   Compile the Vue components and Tailwind CSS:
   ```bash
   npm run build
   # Or for development: npm run dev
   ```

5. **Schedule Jobs (Optional)**
   To handle recurring transactions automatically, ensure the Laravel scheduler runs daily.
   You can manually run it via:
   ```bash
   php artisan recurring:generate
   ```

6. **Serve the Application**
   If you are using WampServer, navigate to your virtual host.
   Otherwise, use the built-in server:
   ```bash
   php artisan serve
   ```
   Access the application at `http://localhost:8000`.

## Architecture Highlights
- **Laravel 13** backend with thin controllers delegating to a dedicated `Services` layer for business logic and atomicity (`DB::transaction`).
- **Vue 3 Composition API** frontend utilizing `Inertia.js` for an SPA experience without an API routing layer.
- **Tailwind CSS v4** styling following modern aesthetic principles (colors, spacing, and micro-interactions).
- **PDF & Excel Reports** using `barryvdh/laravel-dompdf` and `maatwebsite/excel`.
