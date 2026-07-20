<?php
require_once __DIR__ . '/../config/db.php';
$pdo = getDBConnection();

$report      = $_GET['report'] ?? '';
$dateFrom    = $_GET['date_from'] ?? date('Y-m-01'); // first day of current month
$dateTo      = $_GET['date_to']   ?? date('Y-m-d');  // today
$accountId   = $_GET['account_id'] ?? '';
$branchId    = $_GET['branch_id']  ?? '';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Reports</h1>
    <?php if ($report): ?>
        <button onclick="window.print()" class="btn btn-outline-secondary">
            &#128438; Print / Export
        </button>
    <?php endif; ?>
</div>

<!-- Report Selection Tabs -->
<div class="card mb-4 no-print">
    <div class="card-header"><strong>Select Report</strong></div>
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-2">
                <a href="/neobank/?page=reports&report=account_statement"
                   class="btn w-100 <?= $report === 'account_statement' ? 'btn-primary' : 'btn-outline-primary' ?>">
                    Account Statement
                </a>
            </div>
            <div class="col-md-2">
                <a href="/neobank/?page=reports&report=customer_report"
                   class="btn w-100 <?= $report === 'customer_report' ? 'btn-primary' : 'btn-outline-primary' ?>">
                    Customer Report
                </a>
            </div>
            <div class="col-md-2">
                <a href="/neobank/?page=reports&report=branch_report"
                   class="btn w-100 <?= $report === 'branch_report' ? 'btn-primary' : 'btn-outline-primary' ?>">
                    Branch Report
                </a>
            </div>
            <div class="col-md-2">
                <a href="/neobank/?page=reports&report=employee_report"
                   class="btn w-100 <?= $report === 'employee_report' ? 'btn-primary' : 'btn-outline-primary' ?>">
                    Employee Report
                </a>
            </div>
            <div class="col-md-2">
                <a href="/neobank/?page=reports&report=transaction_summary"
                   class="btn w-100 <?= $report === 'transaction_summary' ? 'btn-primary' : 'btn-outline-primary' ?>">
                    Transaction Summary
                </a>
            </div>
            <?php if (hasRole('Admin')): ?>
            <div class="col-md-2">
                <a href="/neobank/?page=reports&report=audit_trail"
                   class="btn w-100 <?= $report === 'audit_trail' ? 'btn-primary' : 'btn-outline-primary' ?>">
                    Audit Trail
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!$report): ?>
    <div class="alert alert-info">Select a report from the menu above to get started.</div>

<?php elseif ($report === 'account_statement'): ?>
    <!-- ============================================================
         ACCOUNT STATEMENT
    ============================================================ -->
    <div class="card mb-3 no-print">
        <div class="card-body">
            <form method="GET" action="/neobank/" class="row g-2 align-items-end">
                <input type="hidden" name="page" value="reports">
                <input type="hidden" name="report" value="account_statement">
                <div class="col-md-4">
                    <label class="form-label">Account</label>
                    <select name="account_id" class="form-control" required>
                        <option value="">Select account...</option>
                        <?php
                        $accList = $pdo->query("
                            SELECT a.account_id, a.account_number, a.account_type,
                                   a.account_category, c.customer_name
                            FROM ACCOUNT a
                            LEFT JOIN CUSTOMER c ON c.customer_id = a.customer_id
                            WHERE a.account_category = 'CUSTOMER'
                            ORDER BY a.account_number
                        ")->fetchAll();
                        foreach ($accList as $acc):
                        ?>
                            <option value="<?= $acc['account_id'] ?>"
                                <?= $accountId == $acc['account_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($acc['account_number']) ?>
                                (<?= htmlspecialchars($acc['customer_name'] ?? 'Unknown') ?>
                                - <?= htmlspecialchars($acc['account_type']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($dateFrom) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($dateTo) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Generate</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($accountId): ?>
    <?php
        // Fetch account details
        $accStmt = $pdo->prepare("
            SELECT a.*, c.customer_name, b.branch_name,
                (SELECT status FROM ACCOUNT_STATUS WHERE account_id = a.account_id ORDER BY status_date DESC LIMIT 1) AS current_status,
                (SELECT balance FROM ACCOUNT_BALANCE WHERE account_id = a.account_id ORDER BY balance_date DESC, balance_id DESC LIMIT 1) AS current_balance
            FROM ACCOUNT a
            LEFT JOIN CUSTOMER c ON c.customer_id = a.customer_id
            LEFT JOIN BRANCH b ON b.branch_id = a.branch_id
            WHERE a.account_id = ?
        ");
        $accStmt->execute([$accountId]);
        $accDetail = $accStmt->fetch();

        // Fetch transactions in date range
        $txStmt = $pdo->prepare("
            SELECT th.*, u.username AS initiated_by_username
            FROM TRANSACTION_HISTORY th
            LEFT JOIN USER u ON u.user_id = th.initiated_by
            WHERE th.account_id = ?
              AND th.status = 'COMPLETED'
              AND DATE(th.transaction_date) BETWEEN ? AND ?
            ORDER BY th.transaction_date ASC, th.transaction_id ASC
        ");
        $txStmt->execute([$accountId, $dateFrom, $dateTo]);
        $transactions = $txStmt->fetchAll();

        // Opening balance: most recent balance_date before date_from
        $openingStmt = $pdo->prepare("
            SELECT balance FROM ACCOUNT_BALANCE
            WHERE account_id = ?
              AND balance_date < ?
            ORDER BY balance_date DESC, balance_id DESC
            LIMIT 1
        ");
        $openingStmt->execute([$accountId, $dateFrom]);
        $openingRow     = $openingStmt->fetch();
        $openingBalance = $openingRow ? $openingRow['balance'] : 0.00;
    ?>

    <!-- Print header -->
    <div class="print-header" style="display:none;">
        <h2>NeoBank</h2>
        <p>Account Statement | Generated: <?= date('d M Y H:i') ?></p>
        <hr>
    </div>

    <div class="card mb-3">
        <div class="card-header"><strong>Account Statement</strong> &mdash; <?= htmlspecialchars($dateFrom) ?> to <?= htmlspecialchars($dateTo) ?></div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr><td><strong>Account Number:</strong></td><td><?= htmlspecialchars($accDetail['account_number']) ?></td></tr>
                        <tr><td><strong>Account Type:</strong></td><td><?= htmlspecialchars($accDetail['account_type']) ?></td></tr>
                        <tr><td><strong>Customer Name:</strong></td><td><?= htmlspecialchars($accDetail['customer_name'] ?? 'N/A') ?></td></tr>
                        <tr><td><strong>Branch:</strong></td><td><?= htmlspecialchars($accDetail['branch_name']) ?></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr><td><strong>Date Opened:</strong></td><td><?= htmlspecialchars($accDetail['date_opened']) ?></td></tr>
                        <tr><td><strong>Status:</strong></td><td><span class="badge bg-<?= $accDetail['current_status'] === 'ACTIVE' ? 'success' : 'secondary' ?>"><?= htmlspecialchars($accDetail['current_status']) ?></span></td></tr>
                        <tr><td><strong>Opening Balance:</strong></td><td>£<?= number_format($openingBalance, 2) ?></td></tr>
                        <tr><td><strong>Current Balance:</strong></td><td><strong>£<?= number_format($accDetail['current_balance'], 2) ?></strong></td></tr>
                    </table>
                </div>
            </div>

            <?php if (count($transactions) === 0): ?>
                <div class="alert alert-info">No completed transactions found for this period.</div>
            <?php else: ?>
            <?php
                $runningBalance = $openingBalance;
            ?>
            <table class="table table-striped table-bordered table-sm">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Type</th>
                        <th>Narration</th>
                        <th>Debit (£)</th>
                        <th>Credit (£)</th>
                        <th>Balance (£)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="table-secondary">
                        <td colspan="6"><strong>Opening Balance</strong></td>
                        <td><strong><?= number_format($openingBalance, 2) ?></strong></td>
                    </tr>
                    <?php foreach ($transactions as $tx):
                        $isDebit  = in_array($tx['transaction_category'], ['Debit']);
                        // Determine debit/credit from transaction_category
                        $debitAmt  = $tx['transaction_category'] === 'Debit'  ? $tx['amount'] : '';
                        $creditAmt = $tx['transaction_category'] === 'Credit' ? $tx['amount'] : '';
                        if ($tx['transaction_category'] === 'Debit') {
                            $runningBalance -= $tx['amount'];
                        } else {
                            $runningBalance += $tx['amount'];
                        }
                    ?>
                    <tr>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($tx['transaction_date']))) ?></td>
                        <td><?= htmlspecialchars($tx['reference_number']) ?></td>
                        <td><?= htmlspecialchars($tx['transaction_type']) ?></td>
                        <td><?= htmlspecialchars($tx['transaction_narration'] ?? $tx['counterparty_name'] ?? '-') ?></td>
                        <td class="text-danger"><?= $debitAmt  ? number_format($debitAmt, 2)  : '-' ?></td>
                        <td class="text-success"><?= $creditAmt ? number_format($creditAmt, 2) : '-' ?></td>
                        <td><?= number_format($runningBalance, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="table-secondary">
                        <td colspan="6"><strong>Closing Balance</strong></td>
                        <td><strong><?= number_format($runningBalance, 2) ?></strong></td>
                    </tr>
                </tbody>
            </table>
            <p class="text-muted small mt-2">Total transactions in period: <?= count($transactions) ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

<?php elseif ($report === 'customer_report'): ?>
    <!-- ============================================================
         CUSTOMER REPORT
    ============================================================ -->
    <div class="card">
        <div class="card-header"><strong>Customer Report</strong> &mdash; Generated: <?= date('d M Y H:i') ?></div>
        <div class="card-body">
        <?php
            $custReport = $pdo->query("
                SELECT c.customer_id, c.customer_name, c.customer_type, c.nationality,
                       co.email, co.phone,
                       COUNT(DISTINCT a.account_id) AS account_count,
                       COALESCE(SUM(
                           (SELECT balance FROM ACCOUNT_BALANCE
                            WHERE account_id = a.account_id
                            ORDER BY balance_date DESC, balance_id DESC LIMIT 1)
                       ), 0) AS total_balance
                FROM CUSTOMER c
                LEFT JOIN CONTACT co ON co.customer_id = c.customer_id
                LEFT JOIN ACCOUNT a  ON a.customer_id  = c.customer_id
                                     AND a.account_category = 'CUSTOMER'
                GROUP BY c.customer_id
                ORDER BY c.customer_name ASC
            ")->fetchAll();
        ?>
        <p class="text-muted">Total customers: <strong><?= count($custReport) ?></strong></p>
        <table class="table table-striped table-bordered table-sm">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Nationality</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Accounts</th>
                    <th>Total Balance (£)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($custReport) === 0): ?>
                <tr><td colspan="8" class="text-center text-muted">No customers found.</td></tr>
                <?php endif; ?>
                <?php foreach ($custReport as $cr): ?>
                <tr>
                    <td><?= htmlspecialchars($cr['customer_id']) ?></td>
                    <td><?= htmlspecialchars($cr['customer_name']) ?></td>
                    <td><?= htmlspecialchars($cr['customer_type']) ?></td>
                    <td><?= htmlspecialchars($cr['nationality'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($cr['email'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($cr['phone'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($cr['account_count']) ?></td>
                    <td><?= number_format($cr['total_balance'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-secondary">
                <tr>
                    <td colspan="7"><strong>Total</strong></td>
                    <td><strong>£<?= number_format(array_sum(array_column($custReport, 'total_balance')), 2) ?></strong></td>
                </tr>
            </tfoot>
        </table>
        </div>
    </div>

<?php elseif ($report === 'branch_report'): ?>
    <!-- ============================================================
         BRANCH REPORT
    ============================================================ -->
    <div class="card">
        <div class="card-header"><strong>Branch Report</strong> &mdash; Generated: <?= date('d M Y H:i') ?></div>
        <div class="card-body">
        <?php
            $branchReport = $pdo->query("
                SELECT b.branch_id, b.branch_name, b.branch_code, b.status,
                       COUNT(DISTINCT e.employee_id) AS employee_count,
                       COUNT(DISTINCT CASE WHEN a.account_category = 'CUSTOMER' THEN a.account_id END) AS customer_account_count,
                       COALESCE(SUM(
                           CASE WHEN a.account_category = 'CUSTOMER' THEN
                               (SELECT balance FROM ACCOUNT_BALANCE
                                WHERE account_id = a.account_id
                                ORDER BY balance_date DESC, balance_id DESC LIMIT 1)
                           ELSE 0 END
                       ), 0) AS total_deposits
                FROM BRANCH b
                LEFT JOIN EMPLOYEE e ON e.branch_id = b.branch_id
                LEFT JOIN ACCOUNT  a ON a.branch_id  = b.branch_id
                GROUP BY b.branch_id
                ORDER BY b.branch_name ASC
            ")->fetchAll();
        ?>
        <p class="text-muted">Total branches: <strong><?= count($branchReport) ?></strong></p>
        <table class="table table-striped table-bordered table-sm">
            <thead class="table-dark">
                <tr>
                    <th>Branch Name</th>
                    <th>Branch Code</th>
                    <th>Status</th>
                    <th>Employees</th>
                    <th>Customer Accounts</th>
                    <th>Total Deposits (£)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($branchReport as $br): ?>
                <tr>
                    <td><?= htmlspecialchars($br['branch_name']) ?></td>
                    <td><?= htmlspecialchars($br['branch_code']) ?></td>
                    <td><span class="badge bg-<?= $br['status'] === 'ACTIVE' ? 'success' : 'secondary' ?>"><?= htmlspecialchars($br['status']) ?></span></td>
                    <td><?= htmlspecialchars($br['employee_count']) ?></td>
                    <td><?= htmlspecialchars($br['customer_account_count']) ?></td>
                    <td><?= number_format($br['total_deposits'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-secondary">
                <tr>
                    <td colspan="5"><strong>Total</strong></td>
                    <td><strong>£<?= number_format(array_sum(array_column($branchReport, 'total_deposits')), 2) ?></strong></td>
                </tr>
            </tfoot>
        </table>
        </div>
    </div>

<?php elseif ($report === 'employee_report'): ?>
    <!-- ============================================================
         EMPLOYEE REPORT
    ============================================================ -->
    <div class="card">
        <div class="card-header"><strong>Employee Report</strong> &mdash; Generated: <?= date('d M Y H:i') ?></div>
        <div class="card-body">
        <?php
            $empReport = $pdo->query("
                SELECT e.employee_id, e.full_name, e.job_title, e.hire_date, e.status,
                       b.branch_name, co.email, co.phone,
                       u.username, u.role AS system_role
                FROM EMPLOYEE e
                LEFT JOIN BRANCH  b  ON b.branch_id   = e.branch_id
                LEFT JOIN CONTACT co ON co.employee_id = e.employee_id
                LEFT JOIN USER    u  ON u.employee_id  = e.employee_id
                ORDER BY b.branch_name ASC, e.full_name ASC
            ")->fetchAll();
        ?>
        <p class="text-muted">Total employees: <strong><?= count($empReport) ?></strong></p>
        <table class="table table-striped table-bordered table-sm">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Branch</th>
                    <th>Job Title</th>
                    <th>System Role</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Hire Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($empReport as $er): ?>
                <tr>
                    <td><?= htmlspecialchars($er['employee_id']) ?></td>
                    <td><?= htmlspecialchars($er['full_name']) ?></td>
                    <td><?= htmlspecialchars($er['branch_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($er['job_title']) ?></td>
                    <td><?= htmlspecialchars($er['system_role'] ?? 'No System Access') ?></td>
                    <td><?= htmlspecialchars($er['username'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($er['email'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($er['phone'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($er['hire_date']) ?></td>
                    <td><span class="badge bg-<?= $er['status'] === 'ACTIVE' ? 'success' : 'secondary' ?>"><?= htmlspecialchars($er['status']) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

<?php elseif ($report === 'transaction_summary'): ?>
    <!-- ============================================================
         TRANSACTION SUMMARY
    ============================================================ -->
    <div class="card mb-3 no-print">
        <div class="card-body">
            <form method="GET" action="/neobank/" class="row g-2 align-items-end">
                <input type="hidden" name="page" value="reports">
                <input type="hidden" name="report" value="transaction_summary">
                <div class="col-md-3">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($dateFrom) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($dateTo) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Branch (optional)</label>
                    <select name="branch_id" class="form-control">
                        <option value="">All Branches</option>
                        <?php
                        $brList = $pdo->query("SELECT branch_id, branch_name FROM BRANCH ORDER BY branch_name")->fetchAll();
                        foreach ($brList as $br):
                        ?>
                            <option value="<?= $br['branch_id'] ?>" <?= $branchId == $br['branch_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($br['branch_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Generate</button>
                </div>
            </form>
        </div>
    </div>

    <?php
        $branchFilter = $branchId ? "AND a.branch_id = ?" : "";
        $txSumParams  = [$dateFrom, $dateTo];
        if ($branchId) $txSumParams[] = $branchId;

        // Summary by transaction type - count each unique reference once (one leg per transaction)
        // We use the Credit leg only to avoid double-counting, falling back to any leg if needed
        $txSummary = $pdo->prepare("
            SELECT th.transaction_type,
                   COUNT(DISTINCT th.reference_number) AS transaction_count,
                   SUM(th.amount) AS total_volume
            FROM TRANSACTION_HISTORY th
            JOIN ACCOUNT a ON a.account_id = th.account_id
            WHERE th.status = 'COMPLETED'
              AND DATE(th.transaction_date) BETWEEN ? AND ?
              AND th.transaction_category = 'Credit'
              AND a.account_category = 'CUSTOMER'
              $branchFilter
            GROUP BY th.transaction_type
            ORDER BY transaction_count DESC
        ");
        $txSummary->execute($txSumParams);
        $summaryRows = $txSummary->fetchAll();

        // If no results using CUSTOMER Credit legs, fall back to all completed transactions
        if (count($summaryRows) === 0) {
            $txSummaryFallback = $pdo->prepare("
                SELECT th.transaction_type,
                       COUNT(DISTINCT th.reference_number) AS transaction_count,
                       SUM(th.amount) AS total_volume
                FROM TRANSACTION_HISTORY th
                JOIN ACCOUNT a ON a.account_id = th.account_id
                WHERE th.status = 'COMPLETED'
                  AND DATE(th.transaction_date) BETWEEN ? AND ?
                  $branchFilter
                GROUP BY th.transaction_type
                ORDER BY transaction_count DESC
            ");
            $txSummaryFallback->execute($txSumParams);
            $summaryRows = $txSummaryFallback->fetchAll();
        }

        // Daily volume - count each unique reference once using MIN(transaction_id) per reference
        $dailyStmt = $pdo->prepare("
            SELECT DATE(th.transaction_date) AS txn_date,
                   COUNT(DISTINCT th.reference_number) AS txn_count,
                   SUM(th.amount) AS total_volume
            FROM TRANSACTION_HISTORY th
            JOIN ACCOUNT a ON a.account_id = th.account_id
            WHERE th.status = 'COMPLETED'
              AND DATE(th.transaction_date) BETWEEN ? AND ?
              AND th.transaction_id = (
                  SELECT MIN(th2.transaction_id)
                  FROM TRANSACTION_HISTORY th2
                  WHERE th2.reference_number = th.reference_number
              )
              $branchFilter
            GROUP BY DATE(th.transaction_date)
            ORDER BY txn_date ASC
        ");
        $dailyStmt->execute($txSumParams);
        $dailyRows = $dailyStmt->fetchAll();
    ?>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><strong>Summary by Transaction Type</strong></div>
                <div class="card-body">
                    <p class="text-muted small">Period: <?= htmlspecialchars($dateFrom) ?> to <?= htmlspecialchars($dateTo) ?></p>
                    <table class="table table-striped table-bordered table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>Transaction Type</th>
                                <th>Count</th>
                                <th>Total Volume (£)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($summaryRows) === 0): ?>
                            <tr><td colspan="3" class="text-center text-muted">No completed transactions in this period.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($summaryRows as $sr): ?>
                            <tr>
                                <td><?= htmlspecialchars($sr['transaction_type']) ?></td>
                                <td><?= htmlspecialchars($sr['transaction_count']) ?></td>
                                <td><?= number_format($sr['total_volume'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <td><strong>Total</strong></td>
                                <td><strong><?= array_sum(array_column($summaryRows, 'transaction_count')) ?></strong></td>
                                <td><strong>£<?= number_format(array_sum(array_column($summaryRows, 'total_volume')), 2) ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><strong>Daily Transaction Volume</strong></div>
                <div class="card-body">
                    <table class="table table-striped table-bordered table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>Date</th>
                                <th>Transactions</th>
                                <th>Total Volume (£)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($dailyRows) === 0): ?>
                            <tr><td colspan="3" class="text-center text-muted">No data for this period.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($dailyRows as $dr): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($dr['txn_date']))) ?></td>
                                <td><?= htmlspecialchars($dr['txn_count']) ?></td>
                                <td><?= number_format($dr['total_volume'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($report === 'audit_trail' && hasRole('Admin')): ?>
    <!-- ============================================================
         AUDIT TRAIL REPORT (Admin only)
    ============================================================ -->
    <div class="card mb-3 no-print">
        <div class="card-body">
            <form method="GET" action="/neobank/" class="row g-2 align-items-end">
                <input type="hidden" name="page" value="reports">
                <input type="hidden" name="report" value="audit_trail">
                <div class="col-md-3">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($dateFrom) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($dateTo) ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Generate</button>
                </div>
            </form>
        </div>
    </div>

    <?php
        $auditStmt = $pdo->prepare("
            SELECT ma.*, e.full_name AS employee_name
            FROM MODIFICATION_AUDIT ma
            LEFT JOIN EMPLOYEE e ON e.employee_id = ma.employee_id
            WHERE DATE(ma.time_created) BETWEEN ? AND ?
            ORDER BY ma.time_created DESC
            LIMIT 200
        ");
        $auditStmt->execute([$dateFrom, $dateTo]);
        $auditRows = $auditStmt->fetchAll();
    ?>

    <div class="card">
        <div class="card-header"><strong>Audit Trail Report</strong> &mdash; <?= htmlspecialchars($dateFrom) ?> to <?= htmlspecialchars($dateTo) ?></div>
        <div class="card-body">
            <p class="text-muted small">Showing up to 200 most recent records.</p>
            <table class="table table-striped table-bordered table-sm">
                <thead class="table-dark">
                    <tr>
                        <th>Date/Time</th>
                        <th>Table</th>
                        <th>Record ID</th>
                        <th>Action</th>
                        <th>Performed By</th>
                        <th>Old Value</th>
                        <th>New Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($auditRows) === 0): ?>
                    <tr><td colspan="7" class="text-center text-muted">No audit records found for this period.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($auditRows as $ar): ?>
                    <tr>
                        <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($ar['time_created']))) ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($ar['table_affected']) ?></span></td>
                        <td><?= htmlspecialchars($ar['record_id']) ?></td>
                        <td>
                            <?php
                                $actionColour = match($ar['action_type']) {
                                    'INSERT'        => 'bg-success',
                                    'UPDATE'        => 'bg-warning text-dark',
                                    'STATUS_CHANGE' => 'bg-info text-dark',
                                    default         => 'bg-secondary',
                                };
                            ?>
                            <span class="badge <?= $actionColour ?>"><?= htmlspecialchars($ar['action_type']) ?></span>
                        </td>
                        <td><?= htmlspecialchars($ar['employee_name'] ?? 'System') ?></td>
                        <td><small class="text-muted"><?= htmlspecialchars($ar['old_value'] ?? '-') ?></small></td>
                        <td><small><?= htmlspecialchars($ar['new_value'] ?? '-') ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php endif; ?>

<!-- Print styles -->
<style>
@media print {
    .no-print { display: none !important; }
    .navbar   { display: none !important; }
    .print-header { display: block !important; }
    body { font-size: 11px; }
    .badge { border: 1px solid #ccc; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>