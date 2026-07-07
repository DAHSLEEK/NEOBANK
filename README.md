# NeoBank — Core Banking System

A relational database system and PHP web interface for core banking operations, developed as an MSc Information Technology dissertation project at Glasgow Caledonian University (GCU), 2025–26.

**Supervisor:** Dr Ayub Ansari

---

## Project Overview

NeoBank is a web-based core banking application that demonstrates the design and implementation of a normalised relational database system for banking operations, integrated with a PHP front-end interface. The system supports customer account management, multi-type transaction processing with double-entry bookkeeping, branch and employee management, and role-based access control with a two-step transaction authorisation workflow.

---

## Technology Stack

| Layer | Technology |
|---|---|
| Database | MySQL 8 (via XAMPP) |
| Backend | PHP 8 with PDO |
| Frontend | Bootstrap 5 |
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
- **Employee Management:** Add and manage staff records linked to branches
- **Authentication:** Session-based login with bcrypt-hashed passwords
- **Role-Based Access Control:** Six staff roles with differentiated privileges across all modules
- **Two-Step Transaction Workflow:** Tellers and Advisors initiate transactions (PENDING); Branch Managers and Admins authorise them (COMPLETED), with balances updating only on authorisation

---

## Database Schema

The system uses nine core tables in `neobank_db`:

| Table | Description |
|---|---|
| `BRANCH` | Branch locations and codes |
| `CUSTOMER` | Customer personal and identity details |
| `CONTACT` | Centralised contact details for customers, branches, and employees |
| `EMPLOYEE` | Staff records linked to branches |
| `ACCOUNT` | Customer and internal branch accounts |
| `ACCOUNT_BALANCE` | Running balance per account |
| `TRANSACTION_HISTORY` | Full audit trail of all transaction legs |
| `ACCOUNT_STATUS` | Account status change history |
| `MODIFICATION_AUDIT` | System-wide audit log of record changes |
| `USER` | Login credentials and roles linked to employees |

---

## Transaction Types

| Type | Debit Side | Credit Side |
|---|---|---|
| Cash Deposit | Branch Cash Account | Customer Account |
| Cash Withdrawal | Customer Account | Branch Cash Account |
| Inward Transfer | Branch Receivable Account | Customer Account |
| Outward Transfer | Customer Account | Branch Payable Account |
| Internal Transfer (NeoBank) | Sender Customer Account | Receiver Customer Account |
| Bank Charge | Customer Account | Branch Cash Account |

---

## Installation and Setup

### Prerequisites
- XAMPP (Apache and MySQL)
- PHP 8 or above
- Git

### Steps

1. Clone the repository into your XAMPP htdocs folder:
```bash
cd C:/xampp/htdocs
git clone https://github.com/your-username/neobank.git
```

2. Start Apache and MySQL in the XAMPP Control Panel.

3. Open phpMyAdmin and create the database:
```sql
CREATE DATABASE neobank_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

4. Run the SQL scripts in order from the `/database/` folder:
```
01_create_tables.sql
02_seed_data.sql
```

5. Visit the application in your browser:
```
http://localhost/neobank/index.php
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

> These credentials are for local development and demonstration purposes only.

---

## Project Structure

```
neobank/
  config/
    db.php               — PDO database connection
  database/
    01_create_tables.sql — Full DDL for all tables
    02_seed_data.sql     — Sample data (15 rows per table)
  includes/
    header.php           — Shared HTML header and Bootstrap CDN
    footer.php           — Shared HTML footer
    navbar.php           — Navigation bar
  pages/
    customers.php        — Customer management module
    accounts.php         — Account management module
    transactions.php     — Transaction processing module
    branches.php         — Branch management module
    employees.php        — Employee management module
  assets/
    css/
      style.css          — Custom styles
  index.php              — Homepage
```

---

## Academic Context

This project was developed as the practical implementation component of an MSc IT dissertation at GCU. The dissertation covers relational database design, normalisation (1NF, 2NF, 3NF), SQL security (prepared statements, injection prevention), and web application development with PHP and PDO.

**Dissertation Title:** Design and Implementation of a Relational Database System for Core Banking Applications with a PHP Web Interface

---

## Security Features

- All database queries use PDO prepared statements with `PDO::ATTR_EMULATE_PREPARES => false`, preventing SQL injection
- All output is sanitised with `htmlspecialchars()`, preventing XSS attacks
- Passwords are hashed using `password_hash()` with `PASSWORD_BCRYPT`
- Session-based authentication protects all pages
- Role-based access control restricts functionality by staff role
