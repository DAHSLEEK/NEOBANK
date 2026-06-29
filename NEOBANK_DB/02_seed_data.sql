-- ============================================================
-- NeoBank Database Seed Data
-- Database: neobank_db
-- Created for MSc IT Dissertation, GCU 2025-26
-- Supervisor: Dr Ayub Ansari
-- ============================================================

USE neobank_db;

-- ------------------------------------------------------------
-- 1. BRANCH
-- ------------------------------------------------------------
INSERT INTO BRANCH (branch_name, branch_code) VALUES
('London Central',       'NEO-LDN-001'),
('Manchester Piccadilly','NEO-MAN-002'),
('Birmingham City',      'NEO-BHM-003'),
('Leeds Metropolitan',   'NEO-LDS-004'),
('Glasgow West End',     'NEO-GLA-005'),
('Edinburgh Royal Mile', 'NEO-EDI-006'),
('Bristol Harbourside',  'NEO-BST-007'),
('Liverpool One',        'NEO-LVP-008'),
('Sheffield Central',    'NEO-SHF-009'),
('Cardiff Bay',          'NEO-CDF-010'),
('Nottingham Castle',    'NEO-NGM-011'),
('Leicester Square',     'NEO-LCE-012'),
('Newcastle Quayside',   'NEO-NCL-013'),
('Southampton Docks',    'NEO-STH-014'),
('Reading Thames',       'NEO-RDG-015');

-- ------------------------------------------------------------
-- 2. CUSTOMER
-- ------------------------------------------------------------
INSERT INTO CUSTOMER (customer_name, date_of_birth, customer_type, gender, nationality, occupation, id_type, id_number) VALUES
('James Okafor',        '1985-03-12', 'Personal',   'Male',   'British',    'Software Engineer',   'Passport',         'GB123456A'),
('Amelia Thornton',     '1990-07-24', 'Personal',   'Female', 'British',    'Doctor',              'Driving Licence',  'THOR990724'),
('Mohammed Al-Rashid',  '1978-11-05', 'Business',   'Male',   'Saudi',      'Business Owner',      'Passport',         'SA987654B'),
('Priya Sharma',        '1993-01-30', 'Personal',   'Female', 'Indian',     'Accountant',          'Passport',         'IN456789C'),
('David Williams',      '1965-09-18', 'Personal',   'Male',   'British',    'Retired',             'Driving Licence',  'WILL650918'),
('Fatima Nwosu',        '1988-04-02', 'Personal',   'Female', 'Nigerian',   'Nurse',               'Passport',         'NG321654D'),
('Chen Wei',            '1995-12-15', 'Personal',   'Male',   'Chinese',    'Student',             'Passport',         'CN654321E'),
('Sarah Mitchell',      '1982-06-28', 'Business',   'Female', 'British',    'Consultant',          'Driving Licence',  'MITC820628'),
('Kwame Asante',        '1975-08-10', 'Business',   'Male',   'Ghanaian',   'Entrepreneur',        'Passport',         'GH147258F'),
('Elena Petrova',       '1999-02-20', 'Personal',   'Female', 'Romanian',   'Graphic Designer',    'Passport',         'RO258369G'),
('Thomas Hughes',       '1960-05-14', 'Personal',   'Male',   'British',    'Teacher',             'Driving Licence',  'HUGH600514'),
('Aisha Kamara',        '1997-10-08', 'Personal',   'Female', 'Sierra Leonean', 'Marketing Executive', 'Passport',     'SL369147H'),
('Luca Rossi',          '1986-03-25', 'Business',   'Male',   'Italian',    'Restaurant Owner',    'Passport',         'IT741852I'),
('Hannah Osei',         '1992-07-11', 'Personal',   'Female', 'Ghanaian',   'Pharmacist',          'Passport',         'GH852963J'),
('Robert Clarke',       '1970-12-03', 'Personal',   'Male',   'British',    'Civil Servant',       'Driving Licence',  'CLAR701203');

-- ------------------------------------------------------------
-- 3. EMPLOYEE
-- ------------------------------------------------------------
INSERT INTO EMPLOYEE (branch_id, full_name, role, hire_date) VALUES
(1,  'Sandra Obi',        'Branch Manager',       '2015-04-01'),
(1,  'Kevin Marsh',       'Customer Advisor',     '2018-09-15'),
(2,  'Diane Fletcher',    'Branch Manager',       '2013-06-10'),
(2,  'Omar Hassan',       'Loans Officer',        '2020-01-20'),
(3,  'Claire Jennings',   'Branch Manager',       '2016-03-07'),
(3,  'Paul Adeyemi',      'Customer Advisor',     '2021-05-12'),
(4,  'Rachel Tong',       'Branch Manager',       '2014-11-25'),
(4,  'James Boateng',     'Compliance Officer',   '2019-08-03'),
(5,  'Fiona MacLeod',     'Branch Manager',       '2017-02-14'),
(5,  'Stuart Campbell',   'Customer Advisor',     '2022-03-01'),
(6,  'Niall Ferguson',    'Branch Manager',       '2012-07-19'),
(7,  'Laura Simmons',     'Branch Manager',       '2018-10-22'),
(8,  'Tony Mwangi',       'Loans Officer',        '2020-06-30'),
(9,  'Gemma Harrison',    'Branch Manager',       '2015-09-09'),
(10, 'Dylan Price',       'Customer Advisor',     '2023-01-16');

-- ------------------------------------------------------------
-- 4. CONTACT
--    Each row belongs to exactly one of: customer, branch, or employee.
--    The other two FKs are NULL.
-- ------------------------------------------------------------
INSERT INTO CONTACT (customer_id, branch_id, employee_id, address, email, phone, mobile, postcode, country) VALUES
-- Customer contacts (customer_id set, others NULL)
(1,  NULL, NULL, '14 Baker Street, London',          'james.okafor@email.com',       '02071234567', '07911123456', 'W1U 3BW', 'United Kingdom'),
(2,  NULL, NULL, '22 Rose Avenue, Manchester',        'amelia.thornton@email.com',    '01612345678', '07922234567', 'M1 4BT',  'United Kingdom'),
(3,  NULL, NULL, '5 Crescent Road, Birmingham',       'mo.alrashid@business.com',     '01219876543', '07933345678', 'B1 1AA',  'United Kingdom'),
(4,  NULL, NULL, '88 Maple Close, Leeds',             'priya.sharma@email.com',       '01138765432', '07944456789', 'LS1 2AB', 'United Kingdom'),
(5,  NULL, NULL, '3 Oak Lane, Bristol',               'david.williams@email.com',     '01177654321', '07955567890', 'BS1 3CD', 'United Kingdom'),
-- Branch contacts (branch_id set, others NULL)
(NULL, 1,  NULL, '1 Canary Wharf, London',            'london.central@neobank.co.uk', '02079001000', NULL,          'E14 5AB', 'United Kingdom'),
(NULL, 2,  NULL, '10 Piccadilly Gardens, Manchester', 'manchester@neobank.co.uk',     '01619001000', NULL,          'M1 1RG',  'United Kingdom'),
(NULL, 3,  NULL, '20 Broad Street, Birmingham',       'birmingham@neobank.co.uk',     '01219001000', NULL,          'B1 2EA',  'United Kingdom'),
(NULL, 4,  NULL, '5 The Headrow, Leeds',              'leeds@neobank.co.uk',          '01139001000', NULL,          'LS1 6PU', 'United Kingdom'),
(NULL, 5,  NULL, '30 Sauchiehall Street, Glasgow',    'glasgow@neobank.co.uk',        '01419001000', NULL,          'G2 3AH',  'United Kingdom'),
-- Employee contacts (employee_id set, others NULL)
(NULL, NULL, 1,  '9 Elm Drive, London',               'sandra.obi@neobank.co.uk',     '02071119000', '07800111001', 'W1A 1AA', 'United Kingdom'),
(NULL, NULL, 2,  '17 Pine Road, London',              'kevin.marsh@neobank.co.uk',    '02071119001', '07800111002', 'W1B 2BB', 'United Kingdom'),
(NULL, NULL, 3,  '4 Birch Lane, Manchester',          'diane.fletcher@neobank.co.uk', '01619001001', '07800111003', 'M2 1CC', 'United Kingdom'),
(NULL, NULL, 4,  '6 Willow Way, Manchester',          'omar.hassan@neobank.co.uk',    '01619001002', '07800111004', 'M2 2DD', 'United Kingdom'),
(NULL, NULL, 5,  '11 Ash Grove, Birmingham',          'claire.jennings@neobank.co.uk','01219001001', '07800111005', 'B2 3EE', 'United Kingdom');

-- ------------------------------------------------------------
-- 5. ACCOUNT
-- ------------------------------------------------------------
INSERT INTO ACCOUNT (customer_id, branch_id, account_number, account_type, account_name, date_opened) VALUES
(1,  1,  'NEO-00100001', 'Current',  'James Okafor Current',        '2020-01-15'),
(1,  1,  'NEO-00100002', 'Savings',  'James Okafor Savings',        '2020-01-15'),
(2,  2,  'NEO-00200001', 'Current',  'Amelia Thornton Current',     '2019-06-10'),
(3,  3,  'NEO-00300001', 'Business', 'Al-Rashid Business Account',  '2018-03-22'),
(4,  4,  'NEO-00400001', 'Current',  'Priya Sharma Current',        '2021-09-05'),
(5,  1,  'NEO-00500001', 'Savings',  'David Williams Savings',      '2017-11-30'),
(6,  2,  'NEO-00600001', 'Current',  'Fatima Nwosu Current',        '2022-04-18'),
(7,  5,  'NEO-00700001', 'Savings',  'Chen Wei Savings',            '2023-01-09'),
(8,  3,  'NEO-00800001', 'Business', 'Mitchell Consulting Account', '2016-07-14'),
(9,  4,  'NEO-00900001', 'Business', 'Asante Enterprises Account',  '2015-02-28'),
(10, 6,  'NEO-01000001', 'Current',  'Elena Petrova Current',       '2023-08-20'),
(11, 7,  'NEO-01100001', 'Savings',  'Thomas Hughes Savings',       '2010-05-01'),
(12, 8,  'NEO-01200001', 'Current',  'Aisha Kamara Current',        '2022-11-11'),
(13, 9,  'NEO-01300001', 'Business', 'Rossi Restaurant Account',    '2019-04-03'),
(14, 10, 'NEO-01400001', 'Current',  'Hannah Osei Current',         '2021-07-25');

-- ------------------------------------------------------------
-- 6. ACCOUNT_BALANCE
-- ------------------------------------------------------------
INSERT INTO ACCOUNT_BALANCE (account_id, balance, currency, balance_date, total_credit, total_debit) VALUES
(1,  4500.00,   'GBP', '2026-06-28', 15000.00,  10500.00),
(2,  12000.00,  'GBP', '2026-06-28', 20000.00,  8000.00),
(3,  3200.50,   'GBP', '2026-06-28', 9500.00,   6299.50),
(4,  87500.00,  'GBP', '2026-06-28', 150000.00, 62500.00),
(5,  1800.75,   'GBP', '2026-06-28', 5000.00,   3199.25),
(6,  25000.00,  'GBP', '2026-06-28', 40000.00,  15000.00),
(7,  650.00,    'GBP', '2026-06-28', 2000.00,   1350.00),
(8,  320.25,    'GBP', '2026-06-28', 1500.00,   1179.75),
(9,  45000.00,  'GBP', '2026-06-28', 80000.00,  35000.00),
(10, 120000.00, 'GBP', '2026-06-28', 200000.00, 80000.00),
(11, 980.00,    'GBP', '2026-06-28', 3000.00,   2020.00),
(12, 15600.00,  'GBP', '2026-06-28', 30000.00,  14400.00),
(13, 2100.00,   'GBP', '2026-06-28', 6000.00,   3900.00),
(14, 8900.50,   'GBP', '2026-06-28', 18000.00,  9099.50),
(15, 500.00,    'GBP', '2026-06-28', 1200.00,   700.00);

-- ------------------------------------------------------------
-- 7. TRANSACTION_HISTORY
-- ------------------------------------------------------------
INSERT INTO TRANSACTION_HISTORY (account_id, transaction_type, amount, transaction_date, reference_number, transaction_category, transaction_narration, status) VALUES
(1,  'Credit', 3000.00,  '2026-06-01 09:15:00', 'TXN-20260601-0001', 'Salary',       'Monthly salary payment',         'COMPLETED'),
(1,  'Debit',  500.00,   '2026-06-03 14:22:00', 'TXN-20260603-0002', 'Utilities',    'Electricity bill payment',        'COMPLETED'),
(2,  'Credit', 1000.00,  '2026-06-05 10:00:00', 'TXN-20260605-0003', 'Transfer',     'Transfer to savings',             'COMPLETED'),
(3,  'Debit',  200.00,   '2026-06-07 16:45:00', 'TXN-20260607-0004', 'Groceries',    'Supermarket purchase',            'COMPLETED'),
(4,  'Credit', 50000.00, '2026-06-08 08:30:00', 'TXN-20260608-0005', 'Business',     'Client invoice payment received', 'COMPLETED'),
(5,  'Debit',  1200.00,  '2026-06-10 11:00:00', 'TXN-20260610-0006', 'Rent',         'Monthly rent payment',            'COMPLETED'),
(6,  'Credit', 2500.00,  '2026-06-12 09:00:00', 'TXN-20260612-0007', 'Salary',       'Monthly salary payment',          'COMPLETED'),
(7,  'Debit',  75.00,    '2026-06-13 13:30:00', 'TXN-20260613-0008', 'Subscription', 'Streaming service subscription',  'COMPLETED'),
(8,  'Credit', 300.00,   '2026-06-14 15:00:00', 'TXN-20260614-0009', 'Transfer',     'Internal transfer received',      'COMPLETED'),
(9,  'Credit', 15000.00, '2026-06-15 10:45:00', 'TXN-20260615-0010', 'Business',     'Wholesale supplier payment',      'COMPLETED'),
(10, 'Debit',  2000.00,  '2026-06-17 12:00:00', 'TXN-20260617-0011', 'Transfer',     'Overseas transfer',               'COMPLETED'),
(11, 'Credit', 500.00,   '2026-06-18 09:30:00', 'TXN-20260618-0012', 'Salary',       'Monthly salary payment',          'COMPLETED'),
(12, 'Debit',  150.00,   '2026-06-20 17:00:00', 'TXN-20260620-0013', 'Utilities',    'Gas bill payment',                'COMPLETED'),
(13, 'Credit', 3000.00,  '2026-06-22 08:00:00', 'TXN-20260622-0014', 'Business',     'Restaurant daily takings',        'COMPLETED'),
(14, 'Debit',  400.00,   '2026-06-25 14:15:00', 'TXN-20260625-0015', 'Shopping',     'Online retail purchase',          'PENDING');

-- ------------------------------------------------------------
-- 8. ACCOUNT_STATUS
-- ------------------------------------------------------------
INSERT INTO ACCOUNT_STATUS (account_id, status, status_date, changed_by) VALUES
(1,  'ACTIVE',    '2020-01-15 09:00:00', 1),
(2,  'ACTIVE',    '2020-01-15 09:05:00', 1),
(3,  'ACTIVE',    '2019-06-10 10:00:00', 3),
(4,  'ACTIVE',    '2018-03-22 11:00:00', 5),
(5,  'ACTIVE',    '2021-09-05 09:30:00', 7),
(6,  'ACTIVE',    '2017-11-30 10:00:00', 1),
(7,  'ACTIVE',    '2022-04-18 09:00:00', 3),
(8,  'ACTIVE',    '2023-01-09 10:00:00', 9),
(9,  'ACTIVE',    '2016-07-14 11:00:00', 5),
(10, 'ACTIVE',    '2015-02-28 09:00:00', 7),
(11, 'ACTIVE',    '2023-08-20 10:00:00', 11),
(12, 'ACTIVE',    '2010-05-01 09:00:00', 12),
(13, 'SUSPENDED', '2024-01-10 14:00:00', 4),
(14, 'ACTIVE',    '2019-04-03 09:00:00', 13),
(15, 'ACTIVE',    '2021-07-25 10:00:00', 14);

-- ------------------------------------------------------------
-- 9. MODIFICATION_AUDIT
-- ------------------------------------------------------------
INSERT INTO MODIFICATION_AUDIT (table_affected, record_id, employee_id, action_type, old_value, new_value) VALUES
('CUSTOMER',         1,  2,    'UPDATE', 'occupation: Developer',           'occupation: Software Engineer'),
('ACCOUNT',          3,  3,    'UPDATE', 'account_name: Amelia Thornton',   'account_name: Amelia Thornton Current'),
('ACCOUNT_STATUS',   13, 4,    'UPDATE', 'status: ACTIVE',                  'status: SUSPENDED'),
('CUSTOMER',         5,  1,    'UPDATE', 'nationality: UK',                 'nationality: British'),
('ACCOUNT_BALANCE',  4,  5,    'UPDATE', 'balance: 82000.00',               'balance: 87500.00'),
('EMPLOYEE',         6,  1,    'UPDATE', 'role: Advisor',                   'role: Customer Advisor'),
('ACCOUNT',          9,  7,    'UPDATE', 'account_type: Current',           'account_type: Business'),
('CUSTOMER',         8,  3,    'UPDATE', 'occupation: Self Employed',       'occupation: Consultant'),
('TRANSACTION_HISTORY', 15, 8, 'UPDATE', 'status: PENDING',                 'status: COMPLETED'),
('BRANCH',           6,  11,   'UPDATE', 'branch_name: Edinburgh',          'branch_name: Edinburgh Royal Mile'),
('CUSTOMER',         10, 2,    'UPDATE', 'nationality: Romanian',           'nationality: Romanian'),
('ACCOUNT_BALANCE',  1,  1,    'UPDATE', 'balance: 4000.00',                'balance: 4500.00'),
('EMPLOYEE',         14, 9,    'UPDATE', 'role: Advisor',                   'role: Branch Manager'),
('ACCOUNT',          14, 13,   'UPDATE', 'account_name: Rossi Account',     'account_name: Rossi Restaurant Account'),
('CUSTOMER',         15, 14,   'UPDATE', 'occupation: Government Worker',   'occupation: Civil Servant');
