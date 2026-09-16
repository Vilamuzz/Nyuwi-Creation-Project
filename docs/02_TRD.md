# TRD — Technical Requirements

## Architecture
- **Monolithic Laravel 11 application** with Inertia.js for SPA-like experience
- Server-side rendering via Inertia + Vue 3 (no separate backend/frontend)
- Single web controller layer — controllers return Inertia responses for page rendering and redirects for mutations
- Inertia partial reloads handle dynamic data such as cart updates, regions, shipping, and wishlist changes
- Role-based middleware (`admin`, `customer`) for route protection
- MySQL database with Indonesian region data (azishapidin/indoregion package)

## Stack Decisions (and Why)

| Choice | Reason |
|--------|--------|
| Laravel 11 | Mature PHP framework, excellent ecosystem, built-in auth scaffolding, Eloquent ORM |
| Vue 3 (Composition API) | Lightweight, excellent Inertia integration, reactive UI without full SPA complexity |
| Inertia.js | SPA-like experience without API layer duplication, seamless Laravel ↔ Vue data flow |
| MySQL | Widely supported, excellent Laravel integration, suitable for e-commerce scale |
| Tailwind CSS v3 + DaisyUI v4 | Utility-first styling with pre-built components for rapid UI development |
| Laravel Breeze | Opinionated auth scaffolding with Vue/Inertia preset, reduces auth boilerplate |

| Vite | Fast HMR, native ESM support, excellent Laravel integration via laravel-vite-plugin |
| Intervention/Image | Image upload processing and manipulation for product images |

## Constraints
- **Deployment target:** Traditional hosting (cPanel/VPS) or Laravel Vapor
- **Budget:** Minimal hosting costs (shared hosting compatible)
- **Browser support:** Modern browsers (Chrome, Firefox, Safari, Edge — last 2 versions)
- **Mobile:** Responsive web design (no native app)
- **Database:** MySQL 5.7+ / 8.0+ (InnoDB engine required)
- **PHP version:** 8.2+ (required for Laravel 11)
- **Node version:** 20.x (LTS)

## Non-Functional Requirements
- Page load < 2s on 3G connection
- All form inputs validated server-side via Laravel Form Requests
- Inertia responses provide page props; validation failures use Laravel error bags and mutations use redirects with flash messages
- Image uploads processed server-side (max 5MB per image)
- Database queries optimized with eager loading (prevent N+1)
- CSRF protection on all web routes
- XSS prevention via Vue template escaping and backend sanitization
- Session-based auth with secure cookie configuration and CSRF protection

## Dependencies (Approved List)

### PHP Packages (Composer)
Only these may be installed without asking:
- `laravel/framework` (core)
- `inertiajs/inertia-laravel` — Inertia server adapter

- `laravel/breeze` — Auth scaffolding
- `azishapidin/indoregion` — Indonesian region data
- `intervention/image` — Image processing
- `tightenco/ziggy` — Laravel routes in JavaScript
- `laravel/tinker` — REPL for debugging

### JavaScript Packages (npm)
Only these may be installed without asking:
- `@inertiajs/vue3` — Inertia Vue adapter
- `vue` — Core Vue framework
- `tailwindcss` + `@tailwindcss/forms` — CSS framework
- `daisyui` — Tailwind component library

- `lucide-vue-next` — Icon library
- `laravel-vite-plugin` — Vite integration

### Dev Dependencies
- `laravel/pint` — Code formatting
- `barryvdh/laravel-debugbar` — Debug toolbar
- `phpunit/phpunit` — Testing framework
- `fakerphp/faker` — Test data generation

## Explicitly Rejected
- **No React** — Vue 3 chosen for Inertia integration and lighter footprint
- **No Next.js/Nuxt** — Laravel handles routing and server logic; Inertia provides SPA UX
- **No separate API layer** — Inertia web routes provide the application data flow
- **No Redux/Pinia state management** — Use Vue 3 Composition API reactivity + Inertia shared data
- **No Tailwind UI / Headless UI** — DaisyUI provides sufficient component library
- **No MongoDB** — Relational data model required for e-commerce (orders, inventory)
- **No Redis caching (v1)** — Add only if performance profiling shows need
- **No WebSocket/real-time (v1)** — Polling or page refresh sufficient for order status updates

## Inertia Data Flow
- Initial page data is supplied through page-specific Inertia props.
- Mutations use Inertia `useForm` or `router` requests to named web routes.
- Controllers validate with Form Requests, apply authorization and transactions, then redirect with flash messages.
- Region selectors and shipping recalculation use partial reloads instead of JSON API endpoints.
- Shared props remain lightweight: authenticated user, flash messages, store branding, and small navigation counters.