# Laboratory Activity 2 – Role-Based Access Control (RBAC)
## Lakbay Pilipinas (Laravel 11)

> **Verification status (please read):** This implementation was written and statically reviewed
> (brace/directive balance, route and reference cross-checks) in an environment **without a PHP
> runtime**, so `php artisan migrate`, `php artisan test` and the five screenshots were **not
> executed by the author of these changes**. Everything below describes how the code is designed
> to behave and how *you* verify it. Run the commands in §10 and record the real results before
> submitting. Nothing in this document claims a passing test run.

---

## 1. Project Overview

Lakbay Pilipinas is a Laravel 11 (PHP 8.2) travel platform with destinations, categories, tags,
blog posts, wishlists, reviews and bookings. This activity adds **Role-Based Access Control** so that
every request is authorised on the **server** according to the user's role.

Exactly **two roles** exist: `admin` and `customer`. There is no Guest role: visitors who are not
logged in are simply redirected to the login page when they open a protected feature.

## 2. Selected Middleware Approach

**Option A – Dynamic Role Middleware.** One reusable middleware, `App\Http\Middleware\RoleMiddleware`,
registered under the alias `role`. The allowed role(s) are passed as middleware parameters:

```php
->middleware('role:admin')            // admins only
->middleware('role:customer,admin')   // either role
```

## 3. What is Role-Based Access Control?

RBAC gives every user a *role*, and permissions are attached to roles instead of to individual
users. A request is allowed only if the role of the logged-in user is permitted for that URL/action.
It follows the **principle of least privilege**: a customer gets only what a customer needs.
RBAC answers "*what kind of user are you?*" (role middleware). **Ownership checks** answer
"*is this record yours?*" (policies). This project uses both.

## 4. Admin Permissions

- Open the administrator dashboard (`/admin/dashboard`).
- Create, view, edit, delete (and restore) destinations; manage categories, tags and blog posts.
- Manage registered users: view all users and promote/demote roles (an admin cannot demote themselves).
- View and manage **all** bookings (change status, delete) at `/admin/bookings`.
- Everything a customer can do (browse, wishlist, own bookings).

## 5. Customer Permissions

- Browse destinations and the public site.
- Manage their **own** wishlist (`/wishlist`).
- Create and view their **own** bookings (`/bookings`, `/bookings/{id}`); cancel their own *pending* booking.
- Submit/delete their own reviews.

Customers **cannot**: open the admin dashboard, create/edit/delete destinations, manage users,
see or change other customers' bookings or wishlists, or assign themselves a role.

## 6. Middleware Implementation

`app/Http/Middleware/RoleMiddleware.php`

```php
public function handle(Request $request, Closure $next, string ...$roles): Response
{
    $user = $request->user();

    if (! $user) {                       // not logged in -> login page
        throw new AuthenticationException('Unauthenticated.', [], route('login'));
    }

    $allowedRoles = array_map('strtolower', array_filter(array_map('trim', $roles)));

    if (! in_array(strtolower((string) $user->role), $allowedRoles, true)) {
        abort(403, 'You do not have permission to access this resource.');   // wrong role
    }

    return $next($request);
}
```

The old single-purpose `VerifyIsAdmin` middleware (alias `admin`) was **replaced** by this one
(deleted, not duplicated). It redirected non-admins to `/home`, which was itself the admin dashboard.

## 7. Middleware Registration

Laravel 11 has no `Kernel.php` for HTTP middleware; aliases are registered in `bootstrap/app.php`:

```php
$middleware->alias([
    'role' => \App\Http\Middleware\RoleMiddleware::class,
    'verifyCategoriesCount' => \App\Http\Middleware\VerifyCategoriesCount::class,
]);
```

## 8. Protected Routes (`routes/web.php`)

| Group | Middleware | Routes |
|---|---|---|
| Post-login dispatcher | `auth` | `GET /home` → admin → `/admin/dashboard`, customer → `/` |
| Customer-facing | `auth`, `role:customer,admin` | `/wishlist*`, `/bookings*`, `reviews*`, `DELETE /cart/{id}/remove` |
| Admin only | `auth`, `role:admin` | `/admin/dashboard`, `/admin/bookings*`, `destinations*`, `trashed-destinations`, `restore-destinations`, `categories*`, `tags*`, `blog*`, `users*` (incl. `make-admin`, `make-customer`), `/send-email` |
| Public | – | home, packages, destination detail, blog, about, contact, login/register, public API |

Unauthenticated → redirect to `/login`. Authenticated with wrong role → **403 Forbidden**.

## 9. Security Measures

1. **Backend enforcement** – role checks are in route middleware; hiding links in Blade is only cosmetic.
2. **Defence in depth** – `DestinationsController` also calls `$this->authorize()` (existing `DestinationPolicy`)
   and the existing Form Requests still check `isAdmin()`.
3. **Role cannot be self-assigned** – `role` was removed from `User::$fillable`; registration sets
   `customer` explicitly in code and ignores any submitted `role`; the profile-update controller only
   writes `name` and `about`; only admins reach `make-admin` / `make-customer`, and `UserPolicy`
   stops an admin from changing their own role.
4. **Booking ownership** – `BookingPolicy::view` (owner or admin), `cancel` (owner and pending only),
   `update`/`delete`/`viewAny` (admin only). The customer list is `auth()->user()->bookings()`, so other
   people's rows are never queried.
5. **Wishlist ownership** – wishlist URLs contain a *destination* id, never a wishlist id, and every
   action runs through `auth()->user()->wishlist()`, so one user cannot read or alter another's wishlist.
6. **Server-decided values** – a new booking's owner (`user_id`), `status` and `total_price` are set by
   the server; submitted values are ignored. Admin status changes are validated with `Rule::in(Booking::STATUSES)`.
7. **Session fixation** – the custom login now calls `session()->regenerate()`.
8. **Least privilege on migration** – unknown role values are converted to `customer`.
9. Former public `DELETE /cart/{id}/remove` now requires login, and the demo `GET /send-email` route
   (which sends mail) is admin-only.

## 10. Testing Procedures

### 10.1 Setup / commands
```bash
composer install
cp .env.example .env          # then fill in your MySQL DB_* values
php artisan key:generate
php artisan migrate           # converts users.role: employee -> customer, keeps admins
php artisan storage:link
php artisan optimize:clear    # clears config/route/view/cache
php artisan serve
```
Seed **fresh** database: `php artisan migrate:fresh --seed`

Seed an **existing** database (do not run the whole seeder – the blog seeder is not idempotent):
```bash
php artisan db:seed --class=UsersTableSeeder
php artisan db:seed --class=BookingsTableSeeder
```
Promote your own account to admin if it is not the seeded one:
```bash
php artisan tinker --execute="App\User::where('email','you@example.com')->update(['role'=>'admin']);"
```

### 10.2 Test accounts (created by `UsersTableSeeder`, development only)
| Role | Email | Password |
|---|---|---|
| Admin | `samadmin@gmail.com` | `password` |
| Customer 1 | `customer1@example.com` | `password` |
| Customer 2 | `customer2@example.com` | `password` |

Log in at `/login`. (Change/remove these accounts before deploying anywhere public.)

### 10.3 Automated tests
```bash
php artisan test
# or: ./vendor/bin/phpunit
php artisan test --filter=RbacTest      # only the new RBAC suite
```
`tests/Feature/RbacTest.php` covers: guests redirected to login; registration always yields a customer;
no self role-escalation; admin can reach dashboard/users/bookings; customer gets 403 on every admin URL;
booking and wishlist ownership; booking tampering ignored; customer cannot create/update/delete destinations.
Two assertions in `tests/Feature/Auth/AdminAccessTest.php` were updated from "redirect to /home" to
"403 Forbidden" to match the new behaviour. **Record your actual result here:** `____ tests, ____ assertions, ____ failures`.

### 10.4 Manual test matrix
| # | As | Action | Expected |
|---|---|---|---|
| 1 | Guest | open `/admin/dashboard`, `/bookings`, `/wishlist` | redirect to `/login` |
| 2 | Admin | open `/admin/dashboard`, `/users`, `/admin/bookings` | 200 OK |
| 3 | Customer | open `/wishlist`, `/bookings` | 200 OK |
| 4 | Customer | open `/admin/dashboard`, `/users`, `/destinations/create` | 403 |
| 5 | Customer 1 | open `/bookings/{id of Customer 2's booking}` | 403 |
| 6 | Customer | submit delete/edit/create destination (see screenshot 5) | 403, data unchanged |

## 11. Screenshot Evidence Checklist

Save the files in the `evidence/` folder (see `evidence/README.md` for step-by-step capture instructions).
Include the **browser address bar** in every screenshot. Tick each only after you have captured it yourself.

- [ ] `01-admin-allowed.png` – logged in as admin, `/admin/dashboard` loads
- [ ] `02-customer-allowed.png` – logged in as customer, `/wishlist` (or `/bookings`) loads
- [ ] `03-role-denied.png` – customer opens `/admin/dashboard` (or `/users`) → 403 page
- [ ] `04-customer-ownership-protection.png` – Customer 1 opens `/bookings/{Customer 2's id}` → 403
- [ ] `05-restricted-action-denied.png` – customer attempts a destination create/edit/delete → 403

## 12. Implementation Justification

Dynamic role middleware was chosen because a single class (`RoleMiddleware`) serves every route and
the allowed roles are visible right where the route is declared, e.g. `role:admin`. This avoids a
separate middleware class per role and keeps the application easy to extend. Role checks decide *which
kind of user* may reach a URL, while Laravel policies decide *whether this specific record belongs to the user*,
so the two layers together stop both vertical (customer → admin) and horizontal (customer → customer)
access. Existing authentication, the `users.role` column and the existing destination policies were
reused, so the website's design and behaviour stay the same apart from the security fixes.

## 13. Reflection

Implementing RBAC showed that hiding an admin button is not security, because anyone can type the URL
or forge a request, so every check has to live on the server. I also learned that roles alone are not
enough: without ownership checks one customer could still read another customer's booking just by
changing the number in the URL. Auditing the project turned up weaknesses such as a mass-assignable
`role` field and an admin dashboard that any logged-in user could open, which reminded me to review existing
code and not only add new code. In the future I would add more automated tests earlier and keep
authorisation rules in policies from the start.

---

## Appendix A – Existing data and the database change

* Reused the existing `users.role` column. It was `ENUM('employee','admin')`, which cannot store `customer`.
* New migration `2026_11_13_000001_update_users_role_for_rbac.php`: widens the column to `VARCHAR(20)`
  (default `customer`) on MySQL/MariaDB/PostgreSQL, converts `employee → customer`, leaves `admin` untouched, and deletes nothing.
  `down()` restores the old enum.
* `2014_10_12_000000_create_users_table.php` now creates `role` as `VARCHAR(20) DEFAULT 'customer'`
  so fresh installs (and the SQLite test database) start with the correct column.
* Existing users keep logging in with the same credentials.

## Appendix B – Files

**Created:** `app/Http/Middleware/RoleMiddleware.php`, `app/Policies/BookingPolicy.php`,
`app/Http/Controllers/BookingController.php`, `app/Http/Controllers/AdminBookingController.php`,
`resources/views/bookings/index.blade.php`, `resources/views/bookings/show.blade.php`,
`resources/views/admin/bookings/index.blade.php`,
`database/migrations/2026_11_13_000001_update_users_role_for_rbac.php`,
`database/seeders/BookingsTableSeeder.php`, `tests/Feature/RbacTest.php`,
`evidence/README.md`, `README_RBAC_Laboratory_Activity_2.md`

**Modified:** `bootstrap/app.php`, `routes/web.php`, `app/User.php`, `app/Booking.php`,
`app/Policies/UserPolicy.php`, `app/Providers/AuthServiceProvider.php`,
`app/Http/Controllers/{HomeController,UsersController,DestinationsController}.php`,
`app/Http/Controllers/Auth/{RegisterController,LoginController}.php`,
`database/migrations/2014_10_12_000000_create_users_table.php`, `database/factories/UserFactory.php`,
`database/seeders/{UsersTableSeeder,DatabaseSeeder}.php`,
`resources/views/layouts/app.blade.php`, `resources/views/partials/navbar.blade.php`,
`resources/views/users/index.blade.php`, `tests/Feature/Auth/AdminAccessTest.php`

**Deleted:** `app/Http/Middleware/VerifyIsAdmin.php` (replaced by `RoleMiddleware`)

## Appendix C – Behaviour changes to be aware of

* The administrator dashboard moved from `/home` to `/admin/dashboard`. `/home` still exists and redirects by role
  (so login, registration and password-reset redirects keep working).
* "Dashboard" and "Edit Profile" menu items are shown only to admins. Profile editing was already
  admin-only (`users/profile`) in the original routes; customers were previously just bounced to the dashboard.
* The project had a `Booking` model, table and factory but **no booking routes/controllers/views**. A small
  booking feature (customer list/detail/create/cancel, admin list/status/delete) was added so ownership
  rules could be implemented and demonstrated. It reuses the existing front-end theme classes and Bootstrap; no CSS files were changed.
* Not changed (outside RBAC scope, worth knowing): the `carts` table has no owner column, so cart rows are
  shared; the legacy `stripe` POST route in `routes/api.php` is public; the `isVerified` middleware alias used in `web.php` is not registered in `bootstrap/app.php`.
