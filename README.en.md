# HRIS — Human Resource Information System

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.3+-777BB4?style=flat&logo=php&logoColor=white" alt="PHP 8.3+">
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=flat&logo=laravel&logoColor=white" alt="Laravel 13">
  <img src="https://img.shields.io/badge/Sanctum-4-FF2D20?style=flat&logo=laravel&logoColor=white" alt="Laravel Sanctum 4">
  <img src="https://img.shields.io/badge/Tailwind_CSS-4-06B6D4?style=flat&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4">
  <img src="https://img.shields.io/badge/DaisyUI-5-5A0EF8?style=flat&logo=daisyui&logoColor=white" alt="DaisyUI 5">
  <img src="https://img.shields.io/badge/Vite-8-646CFF?style=flat&logo=vite&logoColor=white" alt="Vite 8">
  <img src="https://img.shields.io/badge/MySQL-8-4479A1?style=flat&logo=mysql&logoColor=white" alt="MySQL 8">
  <img src="https://img.shields.io/badge/PHPUnit-12-3776AB?style=flat&logo=php&logoColor=white" alt="PHPUnit 12">
  <img src="https://img.shields.io/badge/license-MIT-22C55E?style=flat" alt="License MIT">
</p>

<p align="center">
  <a href="README.md">🇮🇩 Bahasa Indonesia</a> · <b>🇬🇧 English</b>
</p>

A **Human Resource Information System (HRIS)** built on **Laravel 13** for managing employee data, GPS/geofence-based attendance, leave requests with multi-level approval workflows, work arrangements (WFH & business trips), timesheets, and daily activity logs — featuring both a web interface (Blade + Tailwind/DaisyUI) and a **REST API** (Sanctum) for mobile/external integration.

---

## Table of Contents

1. [Screenshots](#screenshots)
2. [Key Features](#key-features)
3. [Tech Stack](#tech-stack)
4. [Architecture & Project Structure](#architecture--project-structure)
5. [System Requirements](#system-requirements)
6. [Installation & Setup](#installation--setup)
7. [Running the Application](#running-the-application)
8. [Demo Accounts](#demo-accounts)
9. [Roles & Permissions](#roles--permissions)
10. [Feature Usage Guide](#feature-usage-guide)
11. [REST API Documentation](#rest-api-documentation)
12. [Database Schema](#database-schema)
13. [Testing](#testing)
14. [Troubleshooting](#troubleshooting)

---

## Screenshots

> Screenshots are stored in [`docs/screenshots/`](docs/screenshots/).

| Login | Dashboard |
|:-----:|:---------:|
| ![Login Page](docs/screenshots/login.png) | ![Dashboard](docs/screenshots/dashboard.png) |

| Attendance & Geofence | Leave Request |
|:---------------------:|:-------------:|
| ![Attendance](docs/screenshots/attendance.png) | ![Leave](docs/screenshots/leave.png) |

| Leave Approvals | Employee List |
|:---------------:|:-------------:|
| ![Leave Approvals](docs/screenshots/leave-approvals.png) | ![Employees](docs/screenshots/employees.png) |

| Timesheet |
|:---------:|
| ![Timesheet](docs/screenshots/timesheets.png) |

---

## Key Features

### 🏢 Organization Management
- **Multi-company (multi-tenant)** — all data is isolated per `company_id`.
- Manage **Companies**, **Departments**, and **Positions**.
- Configure **work Shifts** (start time, end time, lateness grace period).

### 👥 Employee Management
- Complete employee profiles (employee code, national ID, personal data, employment status, contact).
- **Manager–subordinate hierarchy** (self-referential manager).
- Storage of **addresses** and **employee documents** (ID card, contract, certificates).
- Profile photos & shift assignment.
- Employment statuses: *Permanent, Contract, Probation, Intern, Resigned*.

### 📍 Attendance & Geofence ⭐
- **Check-in / Check-out** with **live GPS location + photo** directly from the device.
- **Geofence validation**: *Office* mode attendance is only valid when inside the office radius (Haversine calculation). *WFH* and *Business Trip* modes skip the geofence.
- **Attendance modes**: Office, Work From Home (WFH), Business Trip.
- **Attendance statuses**: Present, Late, Leave, Absent, Holiday.
- Automatic **lateness** calculation (based on the shift's grace period) & **total working hours**.
- **Attendance report** with filters (employee, status, date range).
- Manage **attendance zones/locations** (coordinates + radius).

### 🌴 Leave Management ⭐
- **Leave types**: Annual, Sick, Maternity, Permission (with annual quotas).
- **Two-stage approval workflow**: Manager → HR (automatically routed straight to HR if the employee has no manager).
- **Leave balance** per type per year (entitled / used / remaining), automatically deducted upon HR approval.
- **Attachment** support (e.g. a doctor's note for sick leave).
- Automatic **notifications** to approvers & employees on status changes (including an optional WhatsApp channel).
- Statuses: *Pending Manager → Pending HR → Approved / Rejected / Cancelled*.

### 🏠 Work Arrangements
- Request **WFH** and **Business Trips** for specific dates.
- Single-stage approval (Manager/HR).
- Statuses: *Pending, Approved, Rejected, Cancelled*.

### 📝 Daily Work Logs & Timesheets
- **Daily Work Log** — record activities/tasks during WFH (task, description, start–end time).
- **Project-based Timesheet** (date, project name, task, hours, notes) with a *Draft → Submitted → Approved/Rejected* flow.

### 🔐 Security & Audit
- **Role-Based Access Control** via Spatie Permission (Super Admin, HR, Manager, Employee).
- Per-resource **Policies** for granular authorization.
- **Audit trail** via Spatie Activity Log (changes to User, Company, Department, Position, EmployeeProfile).
- **Soft deletes** on important entities.

---

## Tech Stack

| Category | Technology |
|----------|------------|
| Language | PHP 8.3+ |
| Framework | Laravel 13.8+ |
| API Authentication | Laravel Sanctum 4 |
| Authorization | Spatie Laravel Permission 8 |
| Audit Log | Spatie Laravel Activity Log 5 |
| Frontend | Blade + Tailwind CSS 4 + DaisyUI 5 (`corporate` theme) |
| Build Tool | Vite 8 |
| Database | MySQL |
| Queue / Cache / Session | Database driver |
| Testing | PHPUnit 12 |
| Code Style | Laravel Pint |

---

## Architecture & Project Structure

The application uses the **Service Layer + Repository + Policy + Form Request + Enum** pattern.

```
app/
├── Models/                  # 16 models (User, Company, EmployeeProfile, Attendance, LeaveRequest, etc.)
├── Enums/                   # Type-safe statuses & types (AttendanceMode, LeaveStatus, etc.)
├── Http/
│   ├── Controllers/Web/     # Web interface controllers (Blade)
│   ├── Controllers/Api/     # REST API controllers (JSON)
│   └── Requests/            # Form Request validation
├── Services/                # Business logic (AttendanceService, LeaveService, etc.)
├── Repositories/            # Data access abstraction (interface + implementation)
├── Policies/                # Per-resource authorization
└── Notifications/           # Leave notifications (+ WhatsApp channel)
database/
├── migrations/              # 26 schema migrations
├── seeders/                 # DatabaseSeeder, RolePermissionSeeder
└── factories/
resources/views/             # Blade templates + UI components
routes/
├── web.php                  # Web interface routes
├── api.php                  # REST API v1 routes
└── console.php
```

Typical request flow: **Route → Controller → Form Request (validation) → Service (business logic) → Repository/Model → Response (Blade/JSON)**.

---

## System Requirements

- PHP **8.3** or newer (with the standard Laravel extensions)
- Composer 2
- Node.js 18+ & npm
- MySQL 8 (or MariaDB)

---

## Installation & Setup

### Quick Way (automated)

A `setup` script is available in `composer.json` that runs install, key generation, migration, and frontend build:

```bash
composer run setup
```

> ⚠️ The `setup` script does **not** run the seeders. Run them manually afterwards (see step 6 below) so that roles, demo accounts, and master data are available.

### Manual Way (step by step)

```bash
# 1. Clone & enter the directory
git clone <repo-url> hris
cd hris

# 2. Install PHP & JS dependencies
composer install
npm install

# 3. Prepare the environment file
cp .env.example .env
php artisan key:generate

# 4. Configure the database in .env
#    DB_CONNECTION=mysql
#    DB_HOST=127.0.0.1
#    DB_PORT=3306
#    DB_DATABASE=hris
#    DB_USERNAME=root
#    DB_PASSWORD=
#    (create an empty database named `hris` first)

# 5. Run migrations
php artisan migrate

# 6. Run seeders (roles, demo accounts, master data)
php artisan db:seed

# 7. Create the storage symlink (to access attendance photos & documents)
php artisan storage:link

# 8. Build frontend assets
npm run build
```

---

## Running the Application

### Development Mode (all processes at once)

Runs the server, queue worker, log viewer (Pail), and Vite in parallel:

```bash
composer run dev
```

Then open **http://localhost:8000**.

### Manual Mode (separate terminals)

```bash
php artisan serve          # web server → http://localhost:8000
npm run dev                # Vite dev server (hot reload)
php artisan queue:listen   # queue worker (notifications, etc.)
```

> 💡 Since `QUEUE_CONNECTION=database`, leave notifications are processed via the queue. Make sure the queue worker is running so notifications get delivered.

---

## Demo Accounts

After `php artisan db:seed`, the following accounts are available (company: **HRIS Demo Company**):

| Role | Email | Password |
|------|-------|----------|
| Super Admin | `admin@hris.local` | `password` |
| Employee | `employee@hris.local` | `password` |

Seeded master data includes: departments (HR, IT, FIN, MKT), positions, a regular shift (08:00–17:00, 15-minute grace period), an office location (Head Office, 200 m radius), and 4 leave types.

---

## Roles & Permissions

| Role | Capabilities |
|------|--------------|
| **Super Admin** | Full access to the entire system and all permissions. |
| **HR** | Manage employees, departments, positions, user accounts; approve leave & timesheets (HR stage). |
| **Manager** | Team visibility; approve subordinates' leave/WFH requests (manager stage). |
| **Employee** | Self-service: attendance, request leave/WFH, fill timesheets & work logs. |

Permissions follow the `{resource}.{ability}` pattern, e.g. `employee.create`, `leave.approve`, `company.view`.

---

## Feature Usage Guide

### Login
Open `/login` and sign in with email & password. After logging in you are redirected to the **Dashboard**, which shows a summary (number of employees, departments, positions, today's attendance status, WFH/business trip counts).

### Managing Employees (HR / Admin)
1. Menu **Employees → Create**.
2. Fill in personal data, department, position, manager, and shift.
3. Save, then open the employee detail page to **upload documents** (ID card, contract, etc.).

### Attendance (Employee)
1. Open **Attendance → Me** (`/attendance/me`).
2. Choose a **mode** (Office / WFH / Business Trip).
3. Allow GPS location access — the map shows your position relative to the office zone.
4. Click **Check-in** (take a photo). For Office mode, your position must be within the radius of an active location.
5. When finished, click **Check-out**. The system calculates total working hours & lateness automatically.

> HR/Managers can view the full attendance log at `/attendance` with employee/status/date filters, and manage location zones at `/attendance-locations`.

### Requesting Leave (Employee)
1. Open **Leave → Me** (`/leave/me`) to view your balance & history.
2. Click **Request Leave**, pick a leave type, start–end dates, reason, and an attachment if required.
3. The status becomes *Pending Manager* (or directly *Pending HR* if there is no manager).
4. The request can be **cancelled** while it is not yet final.

### Approving Leave (Manager / HR)
1. Open **Leave Approvals** (`/leave/approvals`).
2. **Approve** or **Reject** (with a reason). After HR approval, the leave balance is automatically deducted and the employee is notified.

### Work Arrangements — WFH / Business Trip
1. Employee: **Work Arrangements → Me** (`/work-arrangements/me`) → request WFH/Business Trip for a specific date & reason.
2. Approver: **Work Arrangements → Approvals** → approve/reject.

### Timesheet
1. Open **Timesheets** → create an entry (date, project, task, hours, notes).
2. Click **Submit** to send it to the approver.
3. The approver performs **Approve / Reject**.

---

## REST API Documentation

Base URL: **`/api/v1`** — authentication uses a **Bearer Token (Sanctum)**.

### Authentication

```http
POST /api/v1/login          # body: { email, password } → returns a token
GET  /api/v1/me             # current authenticated user (token required)
POST /api/v1/logout         # revoke the active token
```

Login example:

```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Accept: application/json" \
  -d "email=admin@hris.local&password=password"
```

Use the token on subsequent requests:

```bash
curl http://localhost:8000/api/v1/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>"
```

### Main Endpoints

**Organization & Employees** (apiResource — index, store, show, update, destroy):

```http
GET|POST|PUT|DELETE  /api/v1/companies
GET|POST|PUT|DELETE  /api/v1/departments
GET|POST|PUT|DELETE  /api/v1/positions
GET|POST|PUT|DELETE  /api/v1/employees
POST                 /api/v1/employees/{id}/documents      # upload documents
```

**Attendance:**

```http
GET   /api/v1/attendance              # list attendance
GET   /api/v1/attendance/today        # today's attendance
POST  /api/v1/attendance/check-in     # body: mode, latitude, longitude, photo
POST  /api/v1/attendance/check-out
```

**Leave:**

```http
GET   /api/v1/leave-requests
POST  /api/v1/leave-requests
POST  /api/v1/leave-requests/{id}/approve
POST  /api/v1/leave-requests/{id}/reject
POST  /api/v1/leave-requests/{id}/cancel
GET   /api/v1/leave-balances          # current-year leave balance
```

**Work Arrangements (WFH / Business Trip):**

```http
GET   /api/v1/attendance-requests
POST  /api/v1/attendance-requests
PUT   /api/v1/attendance-requests/{id}/approve
PUT   /api/v1/attendance-requests/{id}/reject
PUT   /api/v1/attendance-requests/{id}/cancel
```

**Daily Work Log & Timesheet:**

```http
GET   /api/v1/daily-work-logs
POST  /api/v1/daily-work-logs

GET   /api/v1/timesheets
POST  /api/v1/timesheets
PUT   /api/v1/timesheets/{id}/submit
PUT   /api/v1/timesheets/{id}/approve
PUT   /api/v1/timesheets/{id}/reject
```

---

## Database Schema

Core entities and their relationships:

| Table | Description |
|-------|-------------|
| `companies` | Company/tenant (name, contact, logo, subscription plan). |
| `users` | Login accounts, linked to a company & roles. |
| `departments` / `positions` | Organization structure. |
| `shifts` | Work shifts (hours + grace period). |
| `employee_profiles` | Core employee data; relations to user, department, position, manager, shift. |
| `employee_addresses` / `employee_documents` | Employee addresses & documents. |
| `attendance_locations` | Attendance zones (coordinates + geofence radius). |
| `attendance` | Check-in/out records (GPS, photo, status, working hours, lateness). |
| `attendance_requests` | WFH / business trip requests. |
| `daily_work_logs` | Daily activity logs. |
| `timesheets` | Project timesheets with an approval flow. |
| `leave_types` | Leave types + annual quotas. |
| `leave_requests` | Leave requests with two-stage approval. |
| `leave_balances` | Leave balance per type per year. |
| Spatie tables | `roles`, `permissions`, `model_has_roles`, etc. |
| `activity_log` | Data-change audit trail. |

Statuses are encoded via **PHP Enums** (`AttendanceMode`, `AttendanceStatus`, `LeaveStatus`, `RequestStatus`, `EmploymentStatus`, `TimesheetStatus`, etc.) for type consistency.

---

## Testing

```bash
# Run the entire test suite
php artisan test --compact

# Run a single file
php artisan test --compact tests/Feature/ExampleTest.php

# Filter by test name
php artisan test --compact --filter=testName
```

Before committing, run the formatter:

```bash
vendor/bin/pint --dirty
```

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| `Unable to locate file in Vite manifest` | Run `npm run build` or `npm run dev`. |
| Attendance photos / documents not showing | Run `php artisan storage:link`. |
| Leave notifications not delivered | Make sure the queue worker is running: `php artisan queue:listen`. |
| Login fails / empty roles | Make sure `php artisan db:seed` has been run. |
| UI changes not appearing | Re-run `npm run dev` / `npm run build`. |

---

> Built with Laravel 13 · Tailwind CSS 4 · DaisyUI · Sanctum · Spatie Permission
