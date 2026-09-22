# Nyuwi-Creation-Project

> E-commerce web application for selling handcrafted bouquets, accessories, and bags with customer and admin roles. Built with **Laravel 11**, **Vue 3** (Inertia.js), and **Tailwind CSS**.

## Tech Stack
- **Backend:** Laravel 11 (PHP 8.2+)
- **Frontend:** Vue 3 (Composition API) via Inertia.js
- **Styling:** Tailwind CSS v3
- **Database:** MySQL 8.0
- **Containerization:** Docker & Docker Compose

---

## Getting Started

### 1. Clone & Install Dependencies
```sh
# Install PHP & JS dependencies
composer install
npm install
```

### 2. Environment Configuration
```sh
cp .env.example .env
php artisan key:generate
```

Configure your database credentials in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nyuwicreation
DB_USERNAME=root
DB_PASSWORD=
```
*(If using Docker, these defaults already match the container settings).*

---

## Running the Application

### Option A: Using Docker (Recommended)
```sh
# 1. Start Docker containers (Nginx, PHP-FPM, MySQL)
docker compose up -d

# 2. Run Vite dev server on host
npm run dev
```
- Web Application: [http://localhost:8080](http://localhost:8080)
- Vite HMR: [http://localhost:5173](http://localhost:5173)

### Option B: Local PHP Setup
```sh
# Terminal 1: Start Laravel development server
php artisan serve

# Terminal 2: Start Vite development server
npm run dev
```
- Web Application: [http://localhost:8000](http://localhost:8000)

---

## Database Migrations & Seeders

### 1. Run Migrations
```sh
# Local:
php artisan migrate

# Or with Docker:
docker compose exec app php artisan migrate
```

### 2. Run Seeders
Running the main seeder populates categories, sample products, store profile, Indonesian regions (`azishapidin/indoregion`), and default user accounts:
```sh
# Local:
php artisan db:seed

# Or with Docker:
docker compose exec app php artisan db:seed
```

### Default Seeded Accounts
| Role | Email | Password |
|---|---|---|
| **Admin** | `admin@gmail.com` | `admin123` |
| **Customer** | `customer@example.com` | `password123` |

---

## Creating an Admin Account via CLI

You can create a new admin account anytime using the custom `create:admin` Artisan command:

### Interactive Mode:
```sh
# Local:
php artisan create:admin

# Or with Docker:
docker compose exec app php artisan create:admin
```
You will be prompted to enter the admin name, email, and password (minimum 8 characters).

### With Options (Non-Interactive):
```sh
# Local:
php artisan create:admin --name="Admin Name" --email="admin@example.com" --password="password123"

# Or with Docker:
docker compose exec app php artisan create:admin --name="Admin Name" --email="admin@example.com" --password="password123"
```

---

## Running Tests

```sh
# Run all tests inside Docker:
docker compose exec app php artisan test

# Run specific test suite:
docker compose exec app php artisan test tests/Feature/Auth/
```

---


## Roadmap
1. <a href="https://www.canva.com/design/DAGSG8cxYVE/yMOnO7jKfWhBQHEnEhlt8g/edit?utm_content=DAGSG8cxYVE&utm_campaign=designshare&utm_medium=link2&utm_source=sharebutton" target="_blank">Canva version</a>
2. <a href="https://roadmap.sh/r/web-nyuwi-creation" target="_blank">Roadmap.sh version</a>

## Use Case
1. <a href="https://drive.google.com/file/d/15PaOeT-oRpbwP1lbECQqsf3kyr_L_nOL/view?usp=drive_link" target="_blank">Draw.io</a>

## Activity Diagram
1.  <a href="https://drive.google.com/file/d/1jc5FSPUwBjqCjqDlbbEsTwZjUvwDuUkE/view?usp=drive_link" target="_blank">Draw.io</a>

## Design Web
1. <a href="https://balsamiq.cloud/sbak1wr/p6v12f5" target="_blank">Wireframe</a>
2. <a href="https://www.figma.com/design/VzUMdbuxABTrmvBDSxVonj/Nyuwi-Creation-eCommerce-Website-%7C-Web-Page-Design?node-id=1-1948&node-type=frame&t=Mzahc1Ep71ObW3mm-0">UI/UX Design</a>

## Assets Web
1. <a href="https://drive.google.com/drive/folders/12AQRHeQ3g5vjdaLDCS5dijrI1PG3Euly" target="_blank">Google Drive</a>

## Client Instagram
1. <a href="https://www.instagram.com/nyuwi.creation?igsh=MTU3YjhxZnI3dnljdw==" target="_blank">Instagram</a>
