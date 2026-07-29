# NeoBank

A core banking web application built with PHP and MariaDB, developed as part of an MSc dissertation project at Glasgow Caledonian University.

**Live URL (local):** http://localhost/neobank/

---

## Stack

| Component | Technology |
|---|---|
| Database | MariaDB 10.4 (via XAMPP) |
| Backend | PHP 8 with PDO |
| Frontend | Bootstrap 5.3.3 |
| Web Server | Apache (via XAMPP) |
| Version Control | Git / GitHub |

---

## Features

- Customer management with contact details
- Account management with auto-generated account numbers (NEO-XXXXXXXX)
- Double-entry transaction engine supporting 6 transaction types
- Two-step maker-checker transaction workflow (PENDING → COMPLETED/REJECTED)
- Six-role access control system
- Branch and employee management with soft deactivation
- Reports module with 6 report types
- Three-layer audit trail
- Append-only balance history
- Brute force login protection
- Real-time activity logging
- CSRF, session fixation, and XSS protection

---

## Transaction Types

| Type | Debit Side | Credit Side |
|---|---|---|
| Cash Deposit | Branch INTERNAL-CASH | Customer Account |
| Cash Withdrawal | Customer Account | Branch INTERNAL-CASH |
| Inward Transfer | Branch INTERNAL-RECEIVABLE | Customer Account |
| Outward Transfer | Customer Account | Branch INTERNAL-PAYABLE |
| Internal Transfer | Sender Account | Receiver Account |
| Bank Charge | Customer Account | Branch INTERNAL-CASH |

---

## Reports

- Account Statement (with running balance)
- Customer Report
- Branch Report
- Employee Report
- Transaction Summary (by type and daily volume)
- Audit Trail (Admin only)

---

## Role Permissions

| Role | Customers | Accounts | Initiate Txn | Authorise Txn | Branches | Employees | Reports |
|---|---|---|---|---|---|---|---|
| Admin | View/Edit | View/Edit | No | Yes | View/Edit | View/Edit | All |
| Branch Manager | View/Edit | View/Edit | No | Yes | View Only | View Only | All |
| Loans Officer | View/Edit | View/Edit | Yes | No | No | No | Limited |
| Customer Advisor | View/Edit | View/Edit | Yes | No | No | No | Limited |
| Teller | View Only | View Only | Yes | No | No | No | No |
| Compliance Officer | View Only | View Only | No | No | View Only | View Only | All excl. Audit |

---

## Installation

### Requirements

- XAMPP (Apache + MariaDB + PHP 8)
- Git

### Steps

**1. Clone the repository**

```bash
cd /c/xampp/htdocs
git clone https://github.com/DAHSLEEK/neobank.git
```

**2. Start XAMPP**

Open XAMPP Control Panel and start both Apache and MySQL.

**3. Create the database**

Go to http://localhost/phpmyadmin and create a new database:

```
Name: neobank_db
Collation: utf8mb4_unicode_ci
```

**4. Import the database**

In phpMyAdmin, select `neobank_db`, click the Import tab, and upload:

```
database/neobank_db_full.sql
```

Or via command line:

```bash
/c/xampp/mysql/bin/mysql -u root neobank_db < database/neobank_db_full.sql
```

**5. Create the logs folder**

```bash
mkdir -p /c/xampp/htdocs/neobank/logs
touch /c/xampp/htdocs/neobank/logs/activity.log
touch /c/xampp/htdocs/neobank/logs/error.log
```

**6. Open the app**

```
http://localhost/neobank/
```

---

## Login Credentials

| Username | Password | Role |
|---|---|---|
| admin | admin123 | Admin |
| manager1 | password123 | Branch Manager |
| advisor1 | password123 | Customer Advisor |
| loans1 | password123 | Loans Officer |
| compliance1 | password123 | Compliance Officer |
| teller1 | password | Teller |

> All passwords are stored as bcrypt hashes. These credentials are for local development only.

---

## Project Structure

```
neobank/
  config/
    db.php            — PDO connection
    auth.php          — Authentication, CSRF, RBAC, session timeout
    logger.php        — Activity logging and audit helper
  database/
    neobank_db_full.sql — Full schema and seed data
  includes/
    header.php
    footer.php
    navbar.php
  pages/
    dashboard.php
    customers.php
    accounts.php
    transactions.php
    branches.php
    employees.php
    reports.php
  assets/css/style.css
  logs/
    activity.log      — git-ignored
    error.log         — git-ignored
  index.php           — Front controller
  login.php
  logout.php
```

---

## Activity Log

Monitor the log in real time:

```bash
tail -f /c/xampp/htdocs/neobank/logs/activity.log
```

Useful filters:

```bash
# Failed logins only
grep "LOGIN FAILED" /c/xampp/htdocs/neobank/logs/activity.log

# Transactions only
grep "Transaction" /c/xampp/htdocs/neobank/logs/activity.log

# Warnings and errors
grep -E "WARN|ERROR" /c/xampp/htdocs/neobank/logs/activity.log
```

---

## Database Schema

11 tables in `neobank_db`:

| Table | Description |
|---|---|
| branch | Branch locations and codes |
| customer | Customer personal and identity details |
| contact | Centralised contact store for customers, branches, and employees |
| employee | Staff records with job title and branch assignment |
| account | Customer and internal branch accounts |
| account_balance | Append-only balance history per account |
| transaction_history | Double-entry transaction ledger |
| account_status | Account status change history |
| modification_audit | Before/after audit log for all data changes |
| user | Authentication credentials and system roles |
| login | Login attempt tracking for brute force protection |

---

## Licence

Submitted for academic assessment at Glasgow Caledonian University. All rights reserved.