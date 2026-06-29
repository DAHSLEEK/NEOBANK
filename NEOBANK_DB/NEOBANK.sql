-- ============================================================
-- NeoBank Database
-- Database: novabank_db
-- Created for MSc IT Dissertation, GCU 2025-26
-- Supervisor: Dr Ayub Ansari
-- ============================================================

CREATE DATABASE IF NOT EXISTS neobank_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE neobank_db;

-- ------------------------------------------------------------
-- 1. BRANCH (no FK dependencies)
-- ------------------------------------------------------------
CREATE TABLE BRANCH (
    branch_id     INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    branch_name   VARCHAR(100)  NOT NULL,
    branch_code   VARCHAR(20)   NOT NULL UNIQUE,
    time_created  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `current_time` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
                                ON UPDATE CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- 2. CUSTOMER (no FK dependencies)
-- ------------------------------------------------------------
CREATE TABLE CUSTOMER (
    customer_id    INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    customer_name  VARCHAR(150)  NOT NULL,
    date_of_birth  DATE          NOT NULL,
    customer_type  VARCHAR(50)   NOT NULL,
    gender         VARCHAR(20),
    nationality    VARCHAR(100),
    occupation     VARCHAR(100),
    id_type        VARCHAR(50)   NOT NULL,
    id_number      VARCHAR(100)  NOT NULL,
    time_created   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- 3. CONTACT (nullable FKs — provisional CONTACT table design)
--    employee_id FK is added via ALTER TABLE after EMPLOYEE is created.
-- ------------------------------------------------------------
CREATE TABLE CONTACT (
    contact_id    INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    customer_id   INT UNSIGNED  NULL,
    branch_id     INT UNSIGNED  NULL,
    employee_id   INT UNSIGNED  NULL,
    address       VARCHAR(255),
    email         VARCHAR(150),
    phone         VARCHAR(30),
    mobile        VARCHAR(30),
    postcode      VARCHAR(20),
    country       VARCHAR(100),
    CONSTRAINT fk_contact_customer
        FOREIGN KEY (customer_id) REFERENCES CUSTOMER(customer_id),
    CONSTRAINT fk_contact_branch
        FOREIGN KEY (branch_id)   REFERENCES BRANCH(branch_id)
);

-- ------------------------------------------------------------
-- 4. EMPLOYEE (references BRANCH)
-- ------------------------------------------------------------
CREATE TABLE EMPLOYEE (
    employee_id   INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    branch_id     INT UNSIGNED  NOT NULL,
    full_name     VARCHAR(150)  NOT NULL,
    role          VARCHAR(100)  NOT NULL,
    hire_date     DATE          NOT NULL,
    time_created  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `current_time` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
                                ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_employee_branch
        FOREIGN KEY (branch_id) REFERENCES BRANCH(branch_id)
);

-- Add EMPLOYEE FK to CONTACT now that EMPLOYEE table exists
ALTER TABLE CONTACT
    ADD CONSTRAINT fk_contact_employee
        FOREIGN KEY (employee_id) REFERENCES EMPLOYEE(employee_id);

-- ------------------------------------------------------------
-- 5. ACCOUNT (references CUSTOMER and BRANCH)
-- ------------------------------------------------------------
CREATE TABLE ACCOUNT (
    account_id      INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    customer_id     INT UNSIGNED  NOT NULL,
    branch_id       INT UNSIGNED  NOT NULL,
    account_number  VARCHAR(30)   NOT NULL UNIQUE,
    account_type    VARCHAR(50)   NOT NULL,
    account_name    VARCHAR(150),
    date_opened     DATE          NOT NULL,
    time_created    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `current_time`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
                                  ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_account_customer
        FOREIGN KEY (customer_id) REFERENCES CUSTOMER(customer_id),
    CONSTRAINT fk_account_branch
        FOREIGN KEY (branch_id)   REFERENCES BRANCH(branch_id)
);

-- ------------------------------------------------------------
-- 6. ACCOUNT_BALANCE (references ACCOUNT)
--    NOTE: balance, total_credit, and total_debit are denormalised
--    derived fields retained by design decision for performance.
--    Justification documented in Design Report.
-- ------------------------------------------------------------
CREATE TABLE ACCOUNT_BALANCE (
    balance_id    INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
    account_id    INT UNSIGNED   NOT NULL,
    balance       DECIMAL(15,2)  NOT NULL DEFAULT 0.00,
    currency      VARCHAR(10)    NOT NULL DEFAULT 'GBP',
    balance_date  DATE           NOT NULL,
    total_credit  DECIMAL(15,2)  NOT NULL DEFAULT 0.00,
    total_debit   DECIMAL(15,2)  NOT NULL DEFAULT 0.00,
    time_created  DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `current_time` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
                                 ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_balance_account
        FOREIGN KEY (account_id) REFERENCES ACCOUNT(account_id)
);

-- ------------------------------------------------------------
-- 7. TRANSACTION_HISTORY (references ACCOUNT)
-- ------------------------------------------------------------
CREATE TABLE TRANSACTION_HISTORY (
    transaction_id        INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
    account_id            INT UNSIGNED   NOT NULL,
    transaction_type      VARCHAR(50)    NOT NULL,
    amount                DECIMAL(15,2)  NOT NULL,
    transaction_date      DATETIME       NOT NULL,
    reference_number      VARCHAR(100)   NOT NULL UNIQUE,
    transaction_category  VARCHAR(100),
    transaction_narration VARCHAR(255),
    status                VARCHAR(50)    NOT NULL DEFAULT 'PENDING',
    time_created          DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_transaction_account
        FOREIGN KEY (account_id) REFERENCES ACCOUNT(account_id)
);

-- ------------------------------------------------------------
-- 8. ACCOUNT_STATUS (references ACCOUNT and EMPLOYEE)
--    changed_by is nullable: some status changes may be system-triggered.
-- ------------------------------------------------------------
CREATE TABLE ACCOUNT_STATUS (
    status_id     INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    account_id    INT UNSIGNED  NOT NULL,
    status        VARCHAR(50)   NOT NULL,
    status_date   DATETIME      NOT NULL,
    changed_by    INT UNSIGNED  NULL,
    time_created  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `current_time` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
                                ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_accstatus_account
        FOREIGN KEY (account_id) REFERENCES ACCOUNT(account_id),
    CONSTRAINT fk_accstatus_employee
        FOREIGN KEY (changed_by) REFERENCES EMPLOYEE(employee_id)
);

-- ------------------------------------------------------------
-- 9. MODIFICATION_AUDIT (references EMPLOYEE)
--    employee_id is nullable: some audit events may be system-triggered.
-- ------------------------------------------------------------
CREATE TABLE MODIFICATION_AUDIT (
    audit_id        INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    table_affected  VARCHAR(100)  NOT NULL,
    record_id       INT UNSIGNED  NOT NULL,
    employee_id     INT UNSIGNED  NULL,
    action_type     VARCHAR(50)   NOT NULL,
    old_value       TEXT,
    new_value       TEXT,
    time_created    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `current_time`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
                                  ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_employee
        FOREIGN KEY (employee_id) REFERENCES EMPLOYEE(employee_id)
);
