# 📦 Inventory Management System

A full-stack **Inventory Management System** built with **Vue.js** on the frontend and **PHP (Laravel)** on the backend. Designed to help businesses efficiently track products, manage stock levels, and streamline inventory operations through a clean, responsive interface.

---

## 🚀 Features

- **Product Management** — Add, edit, view, and delete products with details like name, price, quantity, and category
- **Category Management** — Organise products into categories for easier navigation and filtering
- **Stock Tracking** — Monitor real-time stock levels and get visibility into inventory changes
- **Supplier Management** — Maintain supplier records and associate them with products
- **Search & Filter** — Quickly find products by name, category, or other attributes
- **RESTful API** — PHP backend exposes a clean REST API consumed by the Vue.js SPA
- **Responsive UI** — Works across desktop and mobile browsers

---

## 🛠️ Tech Stack

| Layer      | Technology            |
|------------|-----------------------|
| Frontend   | Vue.js, JavaScript, HTML/CSS |
| Backend    | PHP (Laravel)         |
| Database   | MySQL                 |
| API Style  | RESTful               |

---

## 📁 Project Structure

```
InventoryManagementSystem/
├── backend/               # PHP/Laravel REST API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/   # API Controllers
│   │   │   └── Middleware/
│   │   └── Models/            # Eloquent models
│   ├── database/
│   │   └── migrations/        # Database migrations
│   ├── routes/
│   │   └── api.php            # API route definitions
│   ├── .env.example
│   └── composer.json
│
├── frontend/              # Vue.js Single Page Application
│   ├── src/
│   │   ├── components/        # Reusable Vue components
│   │   ├── views/             # Page-level views
│   │   ├── router/            # Vue Router configuration
│   │   ├── store/             # State management (Vuex/Pinia)
│   │   └── services/          # API service layer
│   ├── public/
│   └── package.json
│
└── package-lock.json
```

---

## ⚙️ Prerequisites

Make sure you have the following installed before setting up the project:

- [Node.js](https://nodejs.org/) (v16+) and npm
- [PHP](https://www.php.net/) (v8.0+)
- [Composer](https://getcomposer.org/)
- [MySQL](https://www.mysql.com/) (v8.0+)
- A local server environment such as [XAMPP](https://www.apachefriends.org/), [Laragon](https://laragon.org/), or Laravel's built-in dev server

---

## 🔧 Installation & Setup

### 1. Clone the Repository

```bash
git clone https://github.com/Mohamedarshathsaleem/InventoryManagementSystem.git
cd InventoryManagementSystem
```

---

### 2. Backend Setup (PHP / Laravel)

```bash
cd backend
```

**Install PHP dependencies:**

```bash
composer install
```

**Create the environment file:**

```bash
cp .env.example .env
```

**Configure your database in `.env`:**

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_db
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password
```

**Generate the application key:**

```bash
php artisan key:generate
```

**Run database migrations:**

```bash
php artisan migrate
```

**(Optional) Seed the database with sample data:**

```bash
php artisan db:seed
```

**Start the backend server:**

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`.

---

### 3. Frontend Setup (Vue.js)

Open a new terminal:

```bash
cd frontend
```

**Install Node dependencies:**

```bash
npm install
```

**Create the environment file (if applicable):**

```bash
cp .env.example .env
```

Update the API base URL in `.env` to point to your backend:

```env
VITE_API_BASE_URL=http://localhost:8000/api
```

**Start the development server:**

```bash
npm run dev
```

The frontend will be available at `http://localhost:5173` (or as shown in your terminal).

---

## 🏗️ Building for Production

**Frontend:**

```bash
cd frontend
npm run build
```

The compiled assets will be placed in the `frontend/dist/` directory.

**Backend:**

Make sure to set `APP_ENV=production` and `APP_DEBUG=false` in your `.env`, then optimise the application:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📡 API Endpoints

The backend exposes a RESTful API. Below are the main endpoint groups:

| Method | Endpoint              | Description              |
|--------|-----------------------|--------------------------|
| GET    | `/api/products`       | List all products         |
| POST   | `/api/products`       | Create a new product      |
| GET    | `/api/products/{id}`  | Get a single product      |
| PUT    | `/api/products/{id}`  | Update a product          |
| DELETE | `/api/products/{id}`  | Delete a product          |
| GET    | `/api/categories`     | List all categories       |
| POST   | `/api/categories`     | Create a new category     |
| GET    | `/api/suppliers`      | List all suppliers        |
| POST   | `/api/suppliers`      | Create a new supplier     |

> **Note:** Refer to the `routes/api.php` file in the backend for the full and most up-to-date list of routes.

---
