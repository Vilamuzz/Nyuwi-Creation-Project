# AGENTS.md — Project Constitution

> This file defines the non-negotiable rules for any AI agent working in this repo.
> Read this fully before writing any code.

## 1. Project Identity
- **Name:** Nyuwi Creation
- **One-line description:** E-commerce web application for selling products with admin and customer roles
- **Primary user:** Admin (manages products, orders, store settings) and Customer (browses, purchases products)
- **Current phase:** MVP / Active Development

## 2. Tech Stack (Exact Versions)
- **Backend:** Laravel 11 (PHP 8.2+)
- **Frontend:** Vue 3 via Inertia.js
- **Styling:** Tailwind CSS v3 + DaisyUI v4
- **Database:** MySQL (InnoDB)
- **Auth:** Laravel Sanctum + Laravel Breeze
- **Build tool:** Vite
- **Package managers:** Composer (PHP) + npm (Vue/JS)
- **Node version:** 20.x
- **Key packages:**
  - `inertiajs/inertia-laravel` + `@inertiajs/vue3` — SPA-like experience
  - `laravel/sanctum` — API token authentication
  - `laravel/breeze` — Auth scaffolding
  - `azishapidin/indoregion` — Indonesian region data
  - `intervention/image` — Image manipulation
  - `daisyui` — UI component library
  - `lucide-vue-next` — Icons

## 3. Folder Structure (Respect This)
```
├── app/
│   ├── Http/
│   │   ├── Controllers/           # Web controllers (Inertia)
│   │   │   ├── API/              # API controllers (JSON responses)
│   │   │   ├── Auth/             # Authentication controllers (Breeze)
│   │   │   └── [Feature]Controller.php
│   │   ├── Middleware/            # Custom middleware (EnsureAdmin, EnsureCustomer, etc.)
│   │   ├── Requests/             # Form request validation classes
│   │   └── Resources/            # API resources (transformers)
│   ├── Models/                   # Eloquent models
│   └── Providers/                # Service providers
├── database/
│   ├── migrations/               # Database migrations (timestamp命名)
│   └── seeders/                  # Database seeders
├── public/                       # Public assets
├── resources/
│   ├── js/
│   │   ├── Components/           # Reusable Vue components
│   │   ├── Layouts/              # Layout components (admin, customer, guest)
│   │   └── Pages/                # Page components (mirrors routes)
│   │       ├── Admin/            # Admin pages
│   │       └── Customer/         # Customer pages
│   └── css/                      # Stylesheets
├── routes/
│   ├── web.php                   # Web routes (Inertia pages)
│   ├── api.php                   # API routes (JSON)
│   └── auth.php                  # Auth routes (Breeze)
├── tests/
│   ├── Feature/                  # Feature tests
│   └── Unit/                     # Unit tests
├── docker/                       # Docker configuration
└── docs/                         # Project documentation
```

## 4. Iron Rules (Never Break These)
1. **Never** modify files in `/database/migrations` directly. Create new migrations for schema changes.
2. **Never** use `any` in TypeScript. Use proper types or Zod validation.
3. **Never** commit secrets. Use `.env` and reference `env()` or `config()`.
4. **Never** install a new dependency without asking first.
5. **Never** delete or rename existing files without explicit approval.
6. **Always** write a test for any new controller method or model relationship.
7. **Always** use server components by default; add `"use client"` only when needed (Vue 3 Composition API).
8. **Always** run `php artisan test` and `npm run build` after changes.
9. **Always** use form request validation for all input (create Request classes in `app/Http/Requests/`).
10. **Always** follow Laravel naming conventions (PascalCase models, camelCase methods, snake_case columns).

## 5. Code Style
### PHP (Laravel)
- Use PHP 8.2+ features (enums, readonly properties, named arguments)
- Prefer named exports over default exports
- Use Eloquent relationships for all database queries
- Use API Resources for JSON responses
- Validate all input via Form Request classes
- Use Route::controller() for grouping routes

### Vue 3 (Inertia)
- Use Composition API (`<script setup>`)
- Use kebab-case for component files, PascalCase for component names
- Use Tailwind CSS classes (utility-first)
- Use DaisyUI components for consistent UI
- No comments explaining *what* code does; only *why* if non-obvious

## 6. Workflow Protocol
When given a task:
1. **Read** the relevant files and `/docs/specs/[feature].md` if it exists.
2. **Plan** — output a bullet list of files to change and why. Do NOT write code yet.
3. **Wait** for my approval on the plan.
4. **Implement** in small, verifiable steps.
5. **Verify** — run typecheck + tests, report results.
6. **Log** — append a summary to `/docs/CHANGELOG.md`.

## 7. When You're Unsure
- Ask, don't guess. A clarifying question costs 10 seconds; a wrong refactor costs an hour.
- If a task requires breaking an iron rule, stop and explain why.

## 8. Out of Scope (Do Not Touch)
- CI/CD config (`.github/`)
- Deployment configs (`vercel.json`, `Dockerfile`)
- Environment files (`.env*`)
- Vendor packages (unless upgrading with approval)
- Database seeders (unless modifying schema)

## 9. Key Patterns to Follow

### Controller Structure
```php
// Web Controller (Inertia)
class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return Inertia::render('Admin/Products', [
            'products' => ProductResource::collection($products),
        ]);
    }
}

// API Controller (JSON)
class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return response()->json([
            'data' => ProductResource::collection($products),
        ]);
    }
}
```

### Model Relationships
```php
// Always define relationships in models
class Order extends Model
{
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### Form Validation
```php
// Create Request classes for validation
class StoreOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }
}
```

## 10. Testing Guidelines
- **Feature tests** in `tests/Feature/` — test HTTP endpoints and authentication
- **Unit tests** in `tests/Unit/` — test model logic and helpers
- Use `php artisan test` to run all tests
- Use `php artisan test --filter=TestName` to run specific tests
- Write tests for:
  - Authentication flows (login, register, logout)
  - CRUD operations for each resource
  - Authorization (admin vs customer access)
  - Form validation rules

## 11. Database Guidelines
- Use migrations for all schema changes
- Use seeders for dev/test data
- Follow Laravel naming conventions:
  - Tables: plural, snake_case (e.g., `order_items`)
  - Columns: snake_case (e.g., `created_at`)
  - Foreign keys: `model_id` (e.g., `user_id`)
- Always use soft deletes for important data
- Use timestamps (`created_at`, `updated_at`) on all models

---

> **Remember:** This is an e-commerce application. Security, data integrity, and user experience are critical.
> Always validate input, sanitize output, and handle errors gracefully.
