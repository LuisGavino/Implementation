# Evidence – RBAC Laboratory Activity 2

Put the five screenshots in this folder with **exactly** these names. Take them yourself from the
running application – do not edit or mock them. Include the browser address bar in each one.

Prerequisite: `php artisan migrate`, then seed the demo accounts
(`php artisan db:seed --class=UsersTableSeeder` and `--class=BookingsTableSeeder`), then `php artisan serve`
and open http://127.0.0.1:8000.

| Account | Email | Password |
|---|---|---|
| Admin | samadmin@gmail.com | password |
| Customer 1 | customer1@example.com | password |
| Customer 2 | customer2@example.com | password |

## 01-admin-allowed.png
1. Log in as the **admin**.
2. Open `http://127.0.0.1:8000/admin/dashboard`.
3. Capture the dashboard with the URL visible.

## 02-customer-allowed.png
1. Log out. Log in as **Customer 1**.
2. Open `/wishlist` (or `/bookings`).
3. Capture the page with the URL visible.

## 03-role-denied.png
1. Still as **Customer 1**, type `http://127.0.0.1:8000/admin/dashboard` in the address bar
   (`/users` also works).
2. Capture the **403 Forbidden** page with the URL visible.

## 04-customer-ownership-protection.png
1. Log in as **Customer 2**, open `/bookings`, click *View* on the booking and note its ID in the URL
   (on a freshly seeded DB: Customer 1 = `/bookings/1`, Customer 2 = `/bookings/2`).
2. Log out, log in as **Customer 1**, open `/bookings/<Customer 2's id>`.
3. Capture the **403 Forbidden** page with the URL visible. (Optionally first capture that `/bookings/<own id>` works.)

## 05-restricted-action-denied.png
Browsers cannot send a DELETE/POST to a restricted route by typing a URL, so use one of:

* **Option A (easiest, browser console):** logged in as **Customer 1**, open any page of the site, press F12 →
  Console, paste and press Enter (the response status should be **403**):
  ```js
  fetch('/destinations/1', {
    method: 'DELETE',
    headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'text/html'}
  }).then(r => console.log('Status:', r.status));
  ```
  The `csrf-token` meta tag is on the admin layout and `/wishlist`'s layout; if it is missing on a page, use `/wishlist`.
  Capture the console showing `Status: 403` **and** the Network tab/URL if possible.
* **Option B:** loading `http://127.0.0.1:8000/destinations/create` as Customer 1 shows 403 (a restricted *create* action).
* Then, as proof the server refused it, open the admin destinations list as admin (or check the database)
  and show the destination still exists.

## Automated evidence (optional extra)
Run `php artisan test --filter=RbacTest` and screenshot the terminal output **only if it actually passes on your machine**.
