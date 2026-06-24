# Younic Home - Premium Hostel & Dormitory Management System

Younic Home is a state-of-the-art, fully responsive hostel management web application built with **Laravel 11**. It provides an end-to-end solution for hostel administrators to manage branches, rooms, residents, and financials, while giving residents a rich, real-time dashboard to manage their stay, pay rent, and request services.

---

## 🛠 Built With

* **Backend:** Laravel 11.x, PHP 8.2+
* **Frontend:** Laravel Blade, Tailwind CSS (Custom compiled via Vite), Vanilla JS
* **Database:** MySQL / SQLite
* **Real-time Engine:** Node.js, Express, Socket.io (via internal HTTP emit API)
* **Architecture Design:** MVC Pattern

---

## 🌟 Comprehensive Feature Set

### 🛡️ Admin Panel & Capabilities

1. **Admin Dashboard (`/admin/dashboard`)**
   - **Metrics Overview:** View real-time statistics including monthly revenue, total residents, available seats, and pending requests.
   - **Recent Activity:** Tracks and displays recent payments made by residents across all branches.

2. **Branch & Room Management (`/admin/rooms`)**
   - **Branches:** Create, view, and delete hostel branches/locations.
   - **Rooms:** Add rooms to specific branches. Define `room_number`, `room_type` (e.g., Single, 2-Seat), `capacity`, and most importantly, the **Daily Rent (৳)**.
   - **Dynamic Capacity:** Automatically tracks how many seats are occupied vs available.

3. **Resident Management (`/admin/users`)**
   - **User Roster:** View all registered users.
   - **Room Assignment Workflow:** Assign a user to a specific branch and room. 
   - **Booking Rules:** Admins specify the exact `booking_start_date` and `booking_end_date`. The system uses this to calculate exact per-day rent logic.
   - **Deposit Collection:** Record security deposits during room assignment.

4. **Request Center (`/admin/requests`)**
   - **Seat Changes:** Review requests from users who want to change their rooms. Displays exact financial breakdown (spent days, remaining balance, new room cost). Approve or reject with notes.
   - **Leaves:** Approve or reject dates users request to be away.
   - **Exits:** Process checkout requests. The system auto-generates a financial settlement (Deposit Refund vs Pending Dues).

5. **Announcements (`/admin/announcements`)**
   - **Push Notifications:** Broadcast announcements either globally to all residents or privately to specific users. Pushed in real-time via Socket.io.

### 👤 Resident (User) Panel & Capabilities

1. **Resident Dashboard (`/dashboard`)**
   - **Profile Management:** Update personal details.
   - **Current Status:** View assigned branch, room, seat number, and booking dates.
   - **Live Notifications:** Receive real-time alerts for payment approvals, announcements, and request statuses.

2. **Rent & Payments (`/rent`)**
   - **Dynamic Period Rent:** Rent is dynamically calculated strictly based on the booked period (`Total Days × Daily Rent`).
   - **Wallet Integration:** Displays current wallet balance.
   - **Payment Gateway Simulation:** Users can pay their dues using simulated gateways (bKash, Nagad, Visa). Payments automatically reflect in the admin dashboard and adjust the user's due status.

3. **Advanced Seat Change Workflows (`/seat-change`)**
   - **Interactive Selection:** Users select target branches and view only rooms with available seats.
   - **Real-time Financial Calculator:** Upon selecting a new room, the system calculates:
     - Cost of days already spent in the current room.
     - The remaining monetary value of their current booking.
     - The exact cost of the new room for the remaining days.
   - **Inline Resolution:** 
     - If the new room is more expensive, the user is prompted to pay the *exact difference inline* before submitting the request.
     - If it is cheaper, the system notes that the surplus will be credited to their wallet upon approval.

4. **Leave Application (`/leave`)**
   - Apply for official leaves by selecting start and end dates with a reason.

5. **Hostel Exit & Settlement (`/exit`)**
   - Users can file a 30-day notice to leave the hostel.
   - **Auto-Settlement:** Instantly displays how much of their security deposit will be refunded or how much rent they still owe based on real-time payment data.

---

## 🧠 Deep Dive: Core Logic Architectures

### Per-Day Rent Mechanics
Unlike legacy systems that rely on fractional monthly calculations, Younic Home strictly binds costs to the calendar day. 
- The `rooms` table natively stores `daily_rent`. 
- `PaymentController` derives total rent via `Carbon::diffInDays()` ensuring maximum billing accuracy regardless of the month's length.

### The Real-time Engine (No Redis Required)
Younic Home avoids heavy Redis dependencies by utilizing a lightweight internal HTTP proxy for WebSockets:
- **`server.js`**: Runs an Express app on port `6001` that exposes a POST `/emit` endpoint.
- **Laravel Implementation**: When a notification is generated (e.g., in `AdminController` or `RequestController`), Laravel sends an internal HTTP POST request containing the event payload to the Express server.
- **Socket.io**: The Express server catches this payload and immediately broadcasts it to the connected client browsers, ensuring instantaneous UI updates.

---

## 🗄️ Database Schema & Models

| Table / Model | Core Purpose | Key Attributes |
|---|---|---|
| `users` | Handles auth and resident state. | `role`, `room_id`, `balance`, `deposit`, `booking_start_date`, `booking_end_date` |
| `branches` | Physical hostel locations. | `name`, `address` |
| `rooms` | Specific rooms in a branch. | `branch_id`, `room_number`, `capacity`, `daily_rent` |
| `payments` | Centralized financial ledger. | `amount`, `payment_method`, `payment_type`, `status`, `transaction_id` |
| `seat_change_requests`| Complex state table for moves. | `spent_days`, `remaining_balance`, `new_room_cost`, `additional_needed`, `additional_paid` |
| `leave_applications` | Leave tracking. | `start_date`, `end_date`, `status` |
| `exit_requests` | Checkout financial snapshots. | `notice_date`, `exit_date`, `total_due`, `deposit_refund` |
| `notifications` | Alert system. | `title`, `message`, `type`, `is_read` |

---

## ⚙️ Installation & Local Setup

### Prerequisites
- PHP 8.2+
- Composer 2.x
- Node.js & npm
- MySQL / MariaDB (or SQLite)

### Step-by-Step Guide

**1. Clone & Install Dependencies**
```bash
git clone https://github.com/anik-paul-dev/younic-home
cd younic-home
composer install
npm install
```

**2. Environment Configuration**
```bash
cp .env.example .env
php artisan key:generate
```
Configure your database credentials inside the `.env` file (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

**3. Database Migrations & Seeding**
Run the migration with the seeder to populate the database with default Admin credentials, Branches, Rooms with accurate `daily_rent` values, and test users.
```bash
php artisan migrate:fresh --seed
```
*Note: Default Admin Login: `admin@younic.com` / `password`*
*Note: Default User Login: `test@example.com` / `password`*

**4. Build Frontend Assets**
Compile the Tailwind CSS and JavaScript assets:
```bash
npm run build
```

**5. Start the Application**
Because Younic Home utilizes a decoupled real-time engine, you must run three separate processes during local development:

*Terminal 1 (Laravel PHP Server):*
```bash
php artisan serve
```

*Terminal 2 (Vite Hot-Reloading for Frontend):*
```bash
npm run dev
```

*Terminal 3 (Real-time Socket.io Node Server):*
```bash
node server.js
```

Once all servers are running, navigate to `http://localhost:8000` to access Younic Home.
