# 📦 Inventory Management System

[![CI](https://github.com/Mohamedarshathsaleem/InventoryManagementSystem/actions/workflows/ci.yml/badge.svg)](https://github.com/Mohamedarshathsaleem/InventoryManagementSystem/actions/workflows/ci.yml)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)
![Slim](https://img.shields.io/badge/Slim-4-719E40)
![Vue](https://img.shields.io/badge/Vue-3-4FC08D?logo=vuedotjs&logoColor=white)

**A Vue 3 single-page app on a PHP Slim 4 REST API, with JWT authentication and role-based access to product management.**

<!-- Add a screenshot or GIF of the dashboard here, e.g. docs/dashboard.png -->
<!-- ![Dashboard](docs/dashboard.png) -->

---

## ✨ Features

- **JWT authentication.** Register and log in; the API issues a signed HS256 token that expires after 1 hour.
- **Role-based access.** Staff can view and add products; only **admins** can edit or delete them. The check is enforced in API middleware, not just hidden in the UI.
- **Product CRUD.** Create, list, update (partial updates) and delete products with name, price and stock.
- **Secure password storage.** bcrypt via `password_hash`, with automatic upgrade of any legacy plain-text accounts on first login.
- **Tested.** PHPUnit suite against an in-memory SQLite database, run on every push by GitHub Actions.

## 🛠️ Tech stack

| Layer | Technology |
|---|---|
| Frontend | Vue 3, Vue Router 4, Fetch API |
| Backend | PHP 8, Slim 4, PSR-7, firebase/php-jwt |
| Database | MySQL 8 (PDO, prepared statements) |
| Testing / CI | PHPUnit 9, GitHub Actions |

## 🏗️ Architecture

```
Vue SPA (localhost:8080)
   │  fetch + Authorization: Bearer <JWT>
   ▼
Slim 4 API (localhost:8000)
   ├── /api/auth/register, /api/auth/login   → AuthController   (public)
   └── /api/products[/{id}]                  → ProductController (JwtMiddleware: admin-only PUT/DELETE)
   ▼
MySQL (users, products)
```

### API

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/api/auth/register` | – | Create a staff account |
| POST | `/api/auth/login` | – | Returns `{ token, user }` |
| GET | `/api/products` | JWT | List products |
| POST | `/api/products` | JWT | Add a product |
| PUT | `/api/products/{id}` | JWT · admin | Update name / price / stock |
| DELETE | `/api/products/{id}` | JWT · admin | Delete a product |

## 👤 My role

This was a 3-person course project. I:

- set up the project structure, Slim backend and Vue frontend
- built the **dashboard** and **product management** (frontend views and the product update endpoint)
- built the **registration flow** and redesigned the login/registration UI
- later hardened the codebase: bcrypt password hashing, server-side role assignment, secrets moved to environment variables, a shared DB connection (fixing `lastInsertId`), the PHPUnit suite and CI

## 🚀 Running locally

**Prerequisites:** PHP 8.1+, Composer, Node.js 18+, MySQL 8.

```bash
git clone https://github.com/Mohamedarshathsaleem/InventoryManagementSystem.git
cd InventoryManagementSystem

# 1. Database
mysql -u root -p < database/schema.sql

# 2. Backend (http://localhost:8000)
cd backend
composer install
export JWT_SECRET="$(openssl rand -hex 32)"   # see backend/.env.example for all settings
export DB_PASSWORD="your-mysql-password"
php -S localhost:8000 -t public

# 3. Frontend (http://localhost:8080), in a second terminal
cd frontend
npm install
npm run serve
```

To make an admin, register normally and then run:

```sql
UPDATE users SET role = 'admin' WHERE username = 'your-username';
```

### Tests

```bash
cd backend && vendor/bin/phpunit
```

## 🧭 Roadmap

- Supplier, order and stock-movement modules (the frontend routes are already in place)
- Move role checks from path matching into per-route middleware
- Refresh tokens and logout/blacklisting
- Docker Compose for one-command setup

## 📄 License

[MIT](LICENSE)
