# NeoBank — Core Banking System

A relational database system and PHP web interface for core banking operations, developed as an MSc Information Technology dissertation project at Glasgow Caledonian University (GCU), 2025–26.

**Supervisor:** Dr Ayub Ansari

---

## Project Overview

NeoBank is a web-based core banking application that demonstrates the design and implementation of a normalised relational database system for banking operations, integrated with a PHP front-end interface. The system supports customer account management, multi-type transaction processing with double-entry bookkeeping, branch and employee management, role-based access control, and a two-step transaction authorisation workflow backed by a comprehensive security layer.

---

## Technology Stack

| Layer | Technology |
|---|---|
| Database | MariaDB 10.4 / MySQL 8 (via XAMPP) |
| Backend | PHP 8 with PDO |
| Frontend | Bootstrap 5.3.3 (CDN) |
| Web Server | Apache (via XAMPP) |
| IDE | Visual Studio Code |
| Version Control | Git / GitHub |
| DB Design | draw.io (ERD), MySQL Workbench |

---

## Features

- **Customer Management:** Add, view, and edit customer records with contact details stored via a normalised CONTACT table
- **Account Management:** Open and manage customer accounts with auto-generated account numbers, balance tracking, and status management
- **Transaction Processing:** Double-entry bookkeeping across six transaction types with atomic posting using PDO database transactions
- **Branch Internal Accounts:** Each branch maintains three internal accounts (Cash, Accounts Payable, Accounts Receivable) to support the double-entry model
- **Employee Management:** Add and manage staff records linked to branches with contact details
- **Authentication:** Session-based login with bcrypt-hashed passwords and session fixation protection
- **Role-Based Access Control:** Six staff roles with differentiated privileges across all modules
- **Two-Step Transaction Workflow:** Tellers and Advisors initiate transactions (PENDING); Branch Managers and Admins authorise them (COMPLETED), with balances updating only on authorisation
- **Search, Sort and Filter:** All five modules support full-text search, column sorting, and status/type filtering
- **Transaction Detail View:** Full double-entry detail view showing both legs of every transaction
- **Activity Logging:** Real-time activity log tracking every page visit, login, logout, and transaction event
- **Brute Force Protection:** Login lockout after 5 failed attempts for 15 minutes, tracked in LOGIN_ATTEMPT table
- **CSRF Protection:** Token-based CSRF protection on every form submission
- **Session Timeout:** Automatic session expiry after 30 minutes of inactivity
- **Front Controller Routing:** Consistent URL structure via index.php with page= parameter

---

## Database Schema

The system uses eleven tables in `neobank_db`:

| Table | Description |
|---|---|
| `branch` | Branch locations, codes, and status |
| `customer` | Customer personal and identity details |
| `contact` | Centralised contact details for customers, branches, and employees |
| `employee` | Staff records linked to branches with status |
| `account` | Customer and internal branch accounts with category flag |
| `account_balance` | Running balance per account with total credits and debits |
| `transaction_history` | Full double-entry audit trail of all transaction legs |
| `account_status` | Account status change history |
| `modification_audit` | System-wide audit log of record changes |
| `user` | Login credentials and roles linked to employees |
| `login_attempt` | Tracks failed and successful login attempts for brute force protection |

---

## Transaction Types

| Type | Debit Side | Credit Side |
|---|---|---|
| Cash Deposit | Branch Cash Account | Customer Account |
| Cash Withdrawal | Customer Account | Branch Cash Account |
| Inward Transfer (External) | Branch Receivable Account | Customer Account |
| Outward Transfer (External) | Customer Account | Branch Payable Account |
| Internal Transfer (NeoBank) | Sender Customer Account | Receiver Customer Account |
| Bank Charge | Customer Account | Branch Cash Account |

---

## Installation and Setup

### Prerequisites

- XAMPP (Apache and MySQL/MariaDB)
- PHP 8 or above
- Git

### Steps

**1.** Clone the repository into your XAMPP htdocs folder:

```bash
cd C:/xampp/htdocs
git clone https://github.com/DAHSLEEK/neobank.git
```

**2.** Start Apache and MySQL in the XAMPP Control Panel.

**3.** Open phpMyAdmin and create the database:

```sql
CREATE DATABASE neobank_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**4.** Import the full database dump from the `/database/` folder. You can do this in phpMyAdmin by selecting `neobank_db`, clicking the **Import** tab, and uploading the file. Or via command line:

```bash
/c/xampp/mysql/bin/mysql -u root neobank_db < database/neobank_db_full.sql
```

**5.** Create the logs folder and an empty log file:

```bash
mkdir -p C:/xampp/htdocs/neobank/logs
touch C:/xampp/htdocs/neobank/logs/activity.log
touch C:/xampp/htdocs/neobank/logs/error.log
```

**6.** Visit the application in your browser:

```
http://localhost/neobank/
```

---

## Default Login Credentials

| Username | Password | Role |
|---|---|---|
| admin | admin123 | Admin |
| manager1 | password123 | Branch Manager |
| advisor1 | password123 | Customer Advisor |
| loans1 | password123 | Loans Officer |
| compliance1 | password123 | Compliance Officer |

> These credentials are for local development and demonstration purposes only. All passwords are stored as bcrypt hashes in the database.

---

## Viewing the Activity Log

The application writes a real-time activity log to `logs/activity.log`. To monitor it live via Git Bash or terminal:

```bash
tail -f /c/xampp/htdocs/neobank/logs/activity.log
```

Example log entries:

```
[2026-07-08 14:22:54] [INFO] User 'admin' (Admin) from 127.0.0.1: Visited page: customers
[2026-07-08 14:23:10] [INFO] User 'admin' (Admin) from 127.0.0.1: User logged out.
[2026-07-08 14:25:52] [WARN] User 'guest' (none) from 127.0.0.1: LOGIN FAILED for username 'admin' - 4 attempt(s) remaining
[2026-07-08 14:26:05] [INFO] User 'admin' (Admin) from 127.0.0.1: LOGIN SUCCESS for username 'admin'
```

---

## Project Structure

```
neobank/
  config/
    db.php               — PDO database connection
    auth.php             — Authentication, CSRF, session timeout, role hierarchy
    logger.php           — Activity logging helper functions
  database/
    neobank_db_full.sql  — Complete database dump (schema + data)
  includes/
    header.php           — Shared HTML header and Bootstrap 5 CDN
    footer.php           — Shared HTML footer
    navbar.php           — Navigation bar with role-based menu items
  pages/
    dashboard.php        — Landing dashboard after login
    customers.php        — Customer management module
    accounts.php         — Account management module
    transactions.php     — Transaction processing module
    branches.php         — Branch management module
    employees.php        — Employee management module
  assets/
    css/
      style.css          — Custom styles
  logs/
    activity.log         — Real-time activity log (git-ignored)
    error.log            — PHP error log (git-ignored)
  index.php              — Front controller
  login.php              — Login page with brute force protection
  logout.php             — Session destruction and redirect
```

---

## Role Hierarchy and Privileges

| Role | Customers | Accounts | Initiate Transactions | Authorise Transactions | Branches | Employees |
|---|---|---|---|---|---|---|
| Admin | View/Edit | View/Edit | Yes | Yes | View/Edit | View/Edit |
| Branch Manager | View/Edit | View/Edit | Yes | Yes | View Only | View Only |
| Loans Officer | View/Edit | View/Edit | Yes | No | No Access | No Access |
| Customer Advisor | View/Edit | View/Edit | Yes | No | No Access | No Access |
| Teller | View Only | View Only | Yes | No | No Access | No Access |
| Compliance Officer | View Only | View Only | No | No | View Only | View Only |

---

## Security Features

- All database queries use PDO prepared statements with `PDO::ATTR_EMULATE_PREPARES => false`, preventing SQL injection
- All output is sanitised with `htmlspecialchars()`, preventing XSS attacks
- Passwords are hashed using `password_hash()` with `PASSWORD_BCRYPT`
- Session fixation protection via `session_regenerate_id(true)` on every successful login
- CSRF tokens generated with `bin2hex(random_bytes(32))` and verified on every POST request
- Brute force lockout after 5 failed attempts for 15 minutes, tracked in the LOGIN_ATTEMPT table
- Session timeout after 30 minutes of inactivity with automatic redirect to login
- PHP errors suppressed from browser output and written to `logs/error.log`
- Real-time activity logging to `logs/activity.log` with timestamp, user, role, and IP address

---

## Academic Context

This project was developed as the practical implementation component of an MSc IT dissertation at GCU. The dissertation covers relational database design, normalisation (1NF, 2NF, 3NF), double-entry bookkeeping, SQL injection prevention, and web application security with PHP and PDO.

**Dissertation Title:** Design and Implementation of a Relational Database System for Core Banking Applications with a PHP Web Interface

**Supervisor:** Dr Ayub Ansari

**Programme:** MSc Information Technology, Glasgow Caledonian University

**Session:** 2025/26