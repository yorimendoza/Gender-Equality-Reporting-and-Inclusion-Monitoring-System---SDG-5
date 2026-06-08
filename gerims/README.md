# GERIMS — Gender Equality Reporting & Inclusion Monitoring System
### SDG 5: Gender Equality | IT223 Advanced Database System | BSIT 2D

---

## 📋 OVERVIEW

GERIMS is a PHP + MySQL web application aligned with **UN Sustainable Development Goal 5 (Gender Equality)**. It provides a safe, confidential platform for users to report gender-related concerns, and enables administrators to monitor, manage, and respond to those reports.

---

## 🗄️ DATABASE — 10 Tables

| # | Table | Purpose |
|---|-------|---------|
| 1 | `users` | Stores all registered users and admins with roles |
| 2 | `categories` | Report categories (Discrimination, Harassment, Bias, etc.) |
| 3 | `reports` | Main report submissions from users |
| 4 | `report_status_logs` | Tracks every status change with timestamps |
| 5 | `admin_responses` | Admin replies to reports (visible or internal) |
| 6 | `feedbacks` | User feedback and ratings on the system |
| 7 | `notifications` | In-app notifications for users and admins |
| 8 | `policies` | Institutional gender equality policy documents |
| 9 | `audit_logs` | Tracks all CRUD actions for accountability |
| 10 | `announcements` | Admin-posted announcements shown to users |

---

## ⚙️ SETUP INSTRUCTIONS (XAMPP)

### Step 1 — Place the project
Copy the `gerims/` folder into:
```
C:\xampp\htdocs\gerims\
```

### Step 2 — Import the database
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click **New** → enter database name: `gerims_db` → click **Create**
3. Click the `gerims_db` database → go to the **Import** tab
4. Click **Choose File** → select `gerims/database.sql` → click **Go**

### Step 3 — Configure the database (if needed)
Open `gerims/includes/config.php` and verify:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');        // change if your MySQL has a password
define('DB_NAME', 'gerims_db');
define('SITE_URL', 'http://localhost/gerims');
```

### Step 4 — Start XAMPP
- Start **Apache** and **MySQL** in XAMPP Control Panel

### Step 5 — Open the system
Go to: `http://localhost/gerims`

---

## 🔐 DEFAULT CREDENTIALS

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@gerims.edu | password |

> Register new user accounts through the registration page.

---

## 📁 FILE STRUCTURE

```
gerims/
├── index.php                  ← Entry point (redirects)
├── login.php                  ← Login page
├── register.php               ← User registration
├── logout.php                 ← Session destroy
├── dashboard.php              ← Role-based dashboard
├── submit_report.php          ← Submit gender report (users)
├── my_reports.php             ← User's own report list
├── view_report.php            ← Single report view + timeline
├── feedback.php               ← Submit feedback form
├── policies.php               ← View published policies
├── profile.php                ← Update profile + password
├── notifications.php          ← All notifications
├── mark_notif_read.php        ← AJAX: mark read
├── database.sql               ← Full DB schema + seed data
│
├── includes/
│   ├── config.php             ← DB connection, helpers, session
│   ├── header.php             ← Navbar + HTML head
│   └── footer.php             ← Footer + JS includes
│
├── admin/
│   ├── reports.php            ← View/filter all reports
│   ├── edit_report.php        ← Update status + respond
│   ├── view_report.php        ← Redirects to edit_report
│   ├── delete_report.php      ← Delete report handler
│   ├── users.php              ← Manage users (CRUD)
│   ├── policies.php           ← Manage policies (CRUD)
│   ├── announcements.php      ← Manage announcements (CRUD)
│   ├── feedback.php           ← View all user feedback
│   └── delete_feedback.php    ← Delete feedback handler
│
├── css/
│   └── style.css              ← Custom styles
│
├── js/
│   └── main.js                ← jQuery interactions
│
└── uploads/
    └── reports/               ← Uploaded report attachments
```

---

## ✅ SYSTEM FUNCTIONALITIES

### User Features
- **Register / Login** — Secure authentication with hashed passwords
- **Submit Report** — Report gender concerns with category, priority, anonymous option, and attachment
- **Track Report Status** — View status timeline (Pending → Under Review → Resolved)
- **View Admin Responses** — See replies from admins on their reports
- **Submit Feedback** — Rate and comment on the system or policies
- **View Policies** — Read published institutional gender equality policies
- **Profile Management** — Update name, course, year level, contact info
- **Change Password** — Secure password update
- **Notifications** — Real-time in-app alerts for status changes and responses

### Admin Features
- **Dashboard** — Stats overview: total reports, pending, resolved, users
- **Manage Reports** — Filter by status, category, priority, search by keyword
- **Update Report Status** — Change status with remarks, auto-notifies user
- **Respond to Reports** — Add visible or internal responses
- **Manage Users** — Edit profiles, reset passwords, activate/deactivate, delete
- **Manage Policies** — Create, edit, publish/unpublish, delete policies
- **Post Announcements** — Broadcast messages to users, with expiry control
- **View Feedback** — See all user ratings and comments
- **Audit Logs** — Every action is logged with user, action, and timestamp

---

## 🛡️ SQL OPERATIONS USED

- **CREATE** — INSERT INTO (reports, users, feedback, notifications, etc.)
- **READ** — SELECT with JOIN (reports + users + categories), filtering, search
- **UPDATE** — UPDATE (report status, user profiles, policy publish toggle)
- **DELETE** — DELETE (reports, users cascade, feedback)
- **Advanced** — FOREIGN KEYS, CASCADE, ON DELETE SET NULL, NOT is_active, subqueries, COUNT(), AVG(), GROUP BY, ENUM, TIMESTAMP auto-update

---

## 👨‍💻 PROPONENTS

- Galigao, Rex
- Bonior, Anthony Gian A.
- Mendoza, Yori D.

**Course:** IT223 – Advanced Database System | BSIT 2D  
**Faculty:** Eduardo L. Catoc Jr.  
**Institution:** Davao del Norte State College  
**Date:** April 2026
