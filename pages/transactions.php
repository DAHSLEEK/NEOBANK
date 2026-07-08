<?php
require_once __DIR__ . '/../config/db.php';
$pdo = getDBConnection();

$message = '';

function postLeg(PDO $pdo, int $accountId, string $type, float $amount, string $reference, string $category, string $narration, int $initiatedBy, ?string $counterpartyName = null): void {
    $stmt = $pdo->prepare("
        INSERT INTO TRANSACTION_HISTORY
            (account_id, transaction_type, amount, transaction_date, reference_number,
             transaction_category, transaction_narration, counterparty_name, status, initiated_by)
        VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, 'PENDING', ?)
    ");
    $stmt->execute([$accountId, $type, $amount, $reference, $category, $narration, $counterpartyName, $initiatedBy]);
}

function updateBalance(PDO $pdo, int $accountId, string $type, float $amount): void {
    $balStmt = $pdo->prepare("SELECT balance_id, balance, total_credit, total_debit FROM ACCOUNT_BALANCE WHERE account_id = ?");
    $balStmt->execute([$accountId]);
    $bal = $balStmt->fetch();

    if ($type === 'Credit') {
        $newBalance     = $bal['balance'] + $amount;
        $newTotalCredit = $bal['total_credit'] + $amount;
        $newTotalDebit  = $bal['total_debit'];
    } else {
        $newBalance     = $bal['balance'] - $amount;
        $newTotalCredit = $bal['total_credit'];
        $newTotalDebit  = $bal['total_debit'] + $amount;
    }

    $updStmt = $pdo->prepare("
        UPDATE ACCOUNT_BALANCE
        SET balance = ?, total_credit = ?, total_debit = ?, balance_date = CURDATE()
        WHERE balance_id = ?
    ");
    $updStmt->execute([$newBalance, $newTotalCredit, $newTotalDebit, $bal['balance_id']]);
}

function generateReference(PDO $pdo): string {
    $datePart = date('Ymd');
    $unique   = strtoupper(substr(uniqid(), -6));
    return 'TXN-' . $datePart . '-' . $unique;
}

// Handle rejection
if (isset($_GET['reject']) && hasRole('Branch Manager')) {
    $reference = $_GET['reject'];
    $stmt = $pdo->prepare("
        UPDATE TRANSACTION_HISTORY
        SET status = 'REJECTED', authorised_by = ?
        WHERE reference_number = ? AND status = 'PENDING'
    ");
    $stmt->execute([$_SESSION['user_id'], $reference]);
    $message = "Transaction {$reference} has been rejected.";
}

// Handle authorisation
if (isset($_GET['authorise']) && hasRole('Branch Manager')) {
    $reference   = $_GET['authorise'];
    $legs        = $pdo->prepare("SELECT * FROM TRANSACTION_HISTORY WHERE reference_number = ? AND status = 'PENDING'");
    $legs->execute([$reference]);
    $pendingLegs = $legs->fetchAll();

    if ($pendingLegs) {
        try {
            $pdo->beginTransaction();
            foreach ($pendingLegs as $leg) {
                if ($leg['transaction_type'] === 'Debit') {
                    $accChk = $pdo->prepare("
                        SELECT a.account_category, ab.balance
                        FROM ACCOUNT a
                        JOIN ACCOUNT_BALANCE ab ON ab.account_id = a.account_id
                        WHERE a.account_id = ?
                    ");
                    $accChk->execute([$leg['account_id']]);
                    $accInfo = $accChk->fetch();
                    if ($accInfo['account_category'] === 'CUSTOMER' && $leg['amount'] > $accInfo['balance']) {
                        throw new Exception("Insufficient funds on account ID " . $leg['account_id'] . " at time of authorisation.");
                    }
                }
                updateBalance($pdo, $leg['account_id'], $leg['transaction_type'], $leg['amount']);
                $upd = $pdo->prepare("
                    UPDATE TRANSACTION_HISTORY SET status = 'COMPLETED', authorised_by = ?
                    WHERE transaction_id = ?
                ");
                $upd->execute([$_SESSION['user_id'], $leg['transaction_id']]);
            }
            $pdo->commit();
            $message = "Transaction {$reference} authorised successfully.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Error: " . $e->getMessage();
        }
    } else {
        $message = "Error: No pending transaction found for reference {$reference}.";
    }
}

// Handle new transaction submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $transaction_type = $_POST['transaction_type'];
    $account_id       = (int) $_POST['account_id'];
    $amount           = (float) $_POST['amount'];
    $category         = $_POST['transaction_category'];
    $narration        = trim($_POST['transaction_narration']);
    $counterpartyName = trim($_POST['counterparty_name'] ?? '');
    $initiatedBy      = $_SESSION['user_id'];

    $accStmt = $pdo->prepare("
        SELECT a.account_id, a.branch_id, ab.balance
        FROM ACCOUNT a
        LEFT JOIN ACCOUNT_BALANCE ab ON ab.account_id = a.account_id
        WHERE a.account_id = ?
    ");
    $accStmt->execute([$account_id]);
    $customerAcc = $accStmt->fetch();
    $branchId    = $customerAcc['branch_id'];
    $reference   = generateReference($pdo);

    try {
        $pdo->beginTransaction();

        switch ($transaction_type) {
            case 'Cash Deposit':
                $cashAcc = $pdo->prepare("SELECT account_id FROM ACCOUNT WHERE branch_id = ? AND account_category = 'INTERNAL-CASH'");
                $cashAcc->execute([$branchId]);
                $branchCashId = $cashAcc->fetchColumn();
                postLeg($pdo, $branchCashId, 'Debit',  $amount, $reference, $category, 'Cash deposit - ' . $narration, $initiatedBy);
                postLeg($pdo, $account_id,   'Credit', $amount, $reference, $category, 'Cash deposit - ' . $narration, $initiatedBy);
                break;

            case 'Cash Withdrawal':
                $cashAcc = $pdo->prepare("SELECT account_id FROM ACCOUNT WHERE branch_id = ? AND account_category = 'INTERNAL-CASH'");
                $cashAcc->execute([$branchId]);
                $branchCashId = $cashAcc->fetchColumn();
                postLeg($pdo, $account_id,   'Debit',  $amount, $reference, $category, 'Cash withdrawal - ' . $narration, $initiatedBy);
                postLeg($pdo, $branchCashId, 'Credit', $amount, $reference, $category, 'Cash withdrawal - ' . $narration, $initiatedBy);
                break;

            case 'Inward Transfer':
                $recAcc = $pdo->prepare("SELECT account_id FROM ACCOUNT WHERE branch_id = ? AND account_category = 'INTERNAL-RECEIVABLE'");
                $recAcc->execute([$branchId]);
                $branchRecId = $recAcc->fetchColumn();
                postLeg($pdo, $branchRecId, 'Debit',  $amount, $reference, $category, 'Inward transfer - ' . $narration, $initiatedBy, $counterpartyName);
                postLeg($pdo, $account_id,  'Credit', $amount, $reference, $category, 'Inward transfer - ' . $narration, $initiatedBy, $counterpartyName);
                break;

            case 'Outward Transfer':
                $payAcc = $pdo->prepare("SELECT account_id FROM ACCOUNT WHERE branch_id = ? AND account_category = 'INTERNAL-PAYABLE'");
                $payAcc->execute([$branchId]);
                $branchPayId = $payAcc->fetchColumn();
                postLeg($pdo, $account_id,  'Debit',  $amount, $reference, $category, 'Outward transfer - ' . $narration, $initiatedBy, $counterpartyName);
                postLeg($pdo, $branchPayId, 'Credit', $amount, $reference, $category, 'Outward transfer - ' . $narration, $initiatedBy, $counterpartyName);
                break;

            case 'Internal Transfer':
                $receiver_account_id = (int) ($_POST['receiver_account_id'] ?? 0);
                if (!$receiver_account_id) throw new Exception("Please select a receiver account for internal transfers.");
                if ($receiver_account_id === $account_id) throw new Exception("Sender and receiver accounts cannot be the same.");
                postLeg($pdo, $account_id,          'Debit',  $amount, $reference, $category, 'Internal transfer out - ' . $narration, $initiatedBy);
                postLeg($pdo, $receiver_account_id, 'Credit', $amount, $reference, $category, 'Internal transfer in - '  . $narration, $initiatedBy);
                break;

            case 'Bank Charge':
                $cashAcc = $pdo->prepare("SELECT account_id FROM ACCOUNT WHERE branch_id = ? AND account_category = 'INTERNAL-CASH'");
                $cashAcc->execute([$branchId]);
                $branchCashId = $cashAcc->fetchColumn();
                postLeg($pdo, $account_id,   'Debit',  $amount, $reference, $category, 'Bank charge - ' . $narration, $initiatedBy);
                postLeg($pdo, $branchCashId, 'Credit', $amount, $reference, $category, 'Bank charge - ' . $narration, $initiatedBy);
                break;
        }

        $pdo->commit();
        $message = "Transaction initiated successfully. Reference: {$reference}. Awaiting authorisation.";

    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error: " . $e->getMessage();
    }
}

// Transaction detail view
$detailTxn = null;
if (isset($_GET['detail'])) {
    $ref = $_GET['detail'];
    $detailStmt = $pdo->prepare("
        SELECT th.*,
            a.account_number, a.account_category, a.account_name,
            COALESCE(c.customer_name, a.account_name) AS display_name,
            u1.username AS initiated_by_user,
            u2.username AS authorised_by_user
        FROM TRANSACTION_HISTORY th
        LEFT JOIN ACCOUNT a  ON a.account_id  = th.account_id
        LEFT JOIN CUSTOMER c ON c.customer_id = a.customer_id
        LEFT JOIN USER u1    ON u1.user_id    = th.initiated_by
        LEFT JOIN USER u2    ON u2.user_id    = th.authorised_by
        WHERE th.reference_number = ?
        ORDER BY th.transaction_id ASC
    ");
    $detailStmt->execute([$ref]);
    $detailTxn = $detailStmt->fetchAll();
}

// Search and sort
$search       = trim($_GET['search'] ?? '');
$dateFrom     = $_GET['date_from'] ?? '';
$dateTo       = $_GET['date_to'] ?? '';
$filterStatus = $_GET['filter_status'] ?? '';
$sortCol      = $_GET['sort'] ?? 'transaction_id';
$sortDir      = $_GET['dir'] ?? 'desc';

$allowedSorts = ['transaction_id', 'reference_number', 'amount', 'transaction_date', 'status'];
if (!in_array($sortCol, $allowedSorts)) $sortCol = 'transaction_id';
$sortDir = $sortDir === 'asc' ? 'asc' : 'desc';
$nextDir = $sortDir === 'asc' ? 'desc' : 'asc';

$whereParts = ["th.status IN ('COMPLETED', 'REJECTED')"];
$params     = [];

if ($search !== '') {
    $whereParts[] = "(th.reference_number LIKE ? OR a.account_number LIKE ? OR c.customer_name LIKE ? OR th.transaction_narration LIKE ? OR th.counterparty_name LIKE ?)";
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like, $like, $like);
}
if ($filterStatus !== '') {
    $whereParts[] = "th.status = ?";
    $params[]     = $filterStatus;
}
if ($dateFrom !== '') {
    $whereParts[] = "DATE(th.transaction_date) >= ?";
    $params[]     = $dateFrom;
}
if ($dateTo !== '') {
    $whereParts[] = "DATE(th.transaction_date) <= ?";
    $params[]     = $dateTo;
}

$whereSQL = 'WHERE ' . implode(' AND ', $whereParts);

$txnStmt = $pdo->prepare("
    SELECT th.*, a.account_number, a.account_category,
        COALESCE(c.customer_name, a.account_name) AS display_name,
        u1.username AS initiated_by_user,
        u2.username AS authorised_by_user
    FROM TRANSACTION_HISTORY th
    LEFT JOIN ACCOUNT a  ON a.account_id  = th.account_id
    LEFT JOIN CUSTOMER c ON c.customer_id = a.customer_id
    LEFT JOIN USER u1    ON u1.user_id    = th.initiated_by
    LEFT JOIN USER u2    ON u2.user_id    = th.authorised_by
    {$whereSQL}
    ORDER BY th.{$sortCol} {$sortDir}
");
$txnStmt->execute($params);
$transactions = $txnStmt->fetchAll();

$customerAccounts = $pdo->query("
    SELECT a.account_id, a.account_number, a.account_type, c.customer_name, ab.balance
    FROM ACCOUNT a
    LEFT JOIN CUSTOMER c ON c.customer_id = a.customer_id
    LEFT JOIN ACCOUNT_BALANCE ab ON ab.account_id = a.account_id
    WHERE a.account_category = 'CUSTOMER'
    ORDER BY a.account_number
")->fetchAll();

$pendingTransactions = $pdo->query("
    SELECT th.reference_number, th.transaction_date,
        MAX(th.transaction_category) AS transaction_category,
        SUM(CASE WHEN th.transaction_type = 'Debit' THEN th.amount ELSE 0 END) AS debit_amount,
        MAX(CASE WHEN a.account_category = 'CUSTOMER' THEN c.customer_name END) AS customer_name,
        MAX(CASE WHEN a.account_category = 'CUSTOMER' THEN a.account_number END) AS account_number,
        u.username AS initiated_by
    FROM TRANSACTION_HISTORY th
    LEFT JOIN ACCOUNT a  ON a.account_id  = th.account_id
    LEFT JOIN CUSTOMER c ON c.customer_id = a.customer_id
    LEFT JOIN USER u     ON u.user_id     = th.initiated_by
    WHERE th.status = 'PENDING'
    GROUP BY th.reference_number, th.transaction_date, u.username
    ORDER BY th.transaction_date DESC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';

function sortLink(string $col, string $label, string $currentCol, string $nextDir, string $search, string $filterStatus, string $dateFrom, string $dateTo): string {
    $arrow  = $currentCol === $col ? ' &#8597;' : '';
    $params = http_build_query(['page' => 'transactions', 'sort' => $col, 'dir' => $nextDir, 'search' => $search, 'filter_status' => $filterStatus, 'date_from' => $dateFrom, 'date_to' => $dateTo]);
    return "<a href='/neobank/?{$params}' class='text-decoration-none text-dark'>{$label}{$arrow}</a>";
}
?>

<h1>Transaction Management</h1>

<?php if ($message): ?>
    <div class="alert <?= str_starts_with($message, 'Error:') ? 'alert-danger' : 'alert-success' ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- Transaction Detail -->
<?php if ($detailTxn): ?>
<div class="card mb-4 border-info">
    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
        <span>Transaction Detail: <?= htmlspecialchars($_GET['detail']) ?></span>
        <a href="/neobank/?page=transactions" class="btn btn-sm btn-light">Close</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered mb-0">
            <thead>
                <tr>
                    <th>Leg</th><th>Account</th><th>Name</th><th>Type</th>
                    <th>Amount</th><th>Narration</th><th>Counterparty</th>
                    <th>Date</th><th>Status</th><th>Initiated By</th><th>Authorised By</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detailTxn as $i => $leg): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($leg['account_number'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($leg['display_name'] ?? '-') ?></td>
                    <td>
                        <span class="badge <?= $leg['transaction_type'] === 'Credit' ? 'bg-success' : 'bg-danger' ?>">
                            <?= htmlspecialchars($leg['transaction_type']) ?>
                        </span>
                    </td>
                    <td>&pound;<?= number_format($leg['amount'], 2) ?></td>
                    <td><?= htmlspecialchars($leg['transaction_narration'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($leg['counterparty_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($leg['transaction_date']) ?></td>
                    <td>
                        <?php
                            $badgeClass = match($leg['status']) {
                                'COMPLETED' => 'bg-success',
                                'REJECTED'  => 'bg-danger',
                                'PENDING'   => 'bg-warning text-dark',
                                default     => 'bg-secondary'
                            };
                        ?>
                        <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($leg['status']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($leg['initiated_by_user'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($leg['authorised_by_user'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- New Transaction Form -->
<?php if (hasRole('Teller')): ?>
<div class="card mb-4">
    <div class="card-header">Record New Transaction</div>
    <div class="card-body">
        <form method="POST" action="/neobank/?page=transactions">
            <?= csrfField() ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Transaction Type</label>
                    <select name="transaction_type" class="form-control" id="transaction_type" required>
                        <option value="">Select type</option>
                        <option value="Cash Deposit">Cash Deposit</option>
                        <option value="Cash Withdrawal">Cash Withdrawal</option>
                        <option value="Inward Transfer">Inward Transfer (External)</option>
                        <option value="Outward Transfer">Outward Transfer (External)</option>
                        <option value="Internal Transfer">Internal Transfer (NeoBank)</option>
                        <option value="Bank Charge">Bank Charge</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Customer Account</label>
                    <select name="account_id" class="form-control" required>
                        <option value="">Select account</option>
                        <?php foreach ($customerAccounts as $acc): ?>
                            <option value="<?= $acc['account_id'] ?>">
                                <?= htmlspecialchars($acc['account_number']) ?>
                                - <?= htmlspecialchars($acc['customer_name']) ?>
                                (£<?= number_format($acc['balance'] ?? 0, 2) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Amount (GBP)</label>
                    <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required>
                </div>
                <div class="col-md-4" id="receiver_field" style="display:none;">
                    <label class="form-label">Receiver Account (NeoBank)</label>
                    <select name="receiver_account_id" class="form-control">
                        <option value="">Select receiver account</option>
                        <?php foreach ($customerAccounts as $acc): ?>
                            <option value="<?= $acc['account_id'] ?>">
                                <?= htmlspecialchars($acc['account_number']) ?>
                                - <?= htmlspecialchars($acc['customer_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4" id="counterparty_field" style="display:none;">
                    <label class="form-label" id="counterparty_label">Counterparty Name</label>
                    <input type="text" name="counterparty_name" class="form-control" placeholder="Enter name">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Category</label>
                    <select name="transaction_category" class="form-control">
                        <option value="Salary">Salary</option>
                        <option value="Transfer">Transfer</option>
                        <option value="Utilities">Utilities</option>
                        <option value="Rent">Rent</option>
                        <option value="Groceries">Groceries</option>
                        <option value="Shopping">Shopping</option>
                        <option value="Business">Business</option>
                        <option value="Subscription">Subscription</option>
                        <option value="Bank Charge">Bank Charge</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Narration</label>
                    <input type="text" name="transaction_narration" class="form-control"
                           placeholder="Brief description of transaction">
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Initiate Transaction</button>
        </form>
    </div>
</div>

<script>
    document.getElementById('transaction_type').addEventListener('change', function () {
        const val = this.value;
        document.getElementById('receiver_field').style.display = val === 'Internal Transfer' ? 'block' : 'none';
        const counterpartyField = document.getElementById('counterparty_field');
        const counterpartyLabel = document.getElementById('counterparty_label');
        if (val === 'Inward Transfer') {
            counterpartyLabel.textContent = 'Sender Name';
            counterpartyField.style.display = 'block';
        } else if (val === 'Outward Transfer') {
            counterpartyLabel.textContent = 'Beneficiary Name';
            counterpartyField.style.display = 'block';
        } else {
            counterpartyField.style.display = 'none';
        }
    });
</script>
<?php endif; ?>

<!-- Pending Transactions -->
<?php if (hasRole('Branch Manager') && count($pendingTransactions) > 0): ?>
<div class="card mb-4 border-warning">
    <div class="card-header bg-warning text-dark">Pending Transactions Awaiting Authorisation</div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Reference</th><th>Customer</th><th>Account</th>
                    <th>Amount</th><th>Category</th><th>Initiated By</th><th>Date</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingTransactions as $ptxn): ?>
                <tr>
                    <td>
                        <a href="/neobank/?page=transactions&detail=<?= urlencode($ptxn['reference_number']) ?>">
                            <?= htmlspecialchars($ptxn['reference_number']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($ptxn['customer_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($ptxn['account_number'] ?? '-') ?></td>
                    <td>&pound;<?= number_format($ptxn['debit_amount'], 2) ?></td>
                    <td><?= htmlspecialchars($ptxn['transaction_category']) ?></td>
                    <td><?= htmlspecialchars($ptxn['initiated_by']) ?></td>
                    <td><?= htmlspecialchars($ptxn['transaction_date']) ?></td>
                    <td>
                        <a href="/neobank/?page=transactions&authorise=<?= urlencode($ptxn['reference_number']) ?>"
                           class="btn btn-sm btn-success me-1"
                           onclick="return confirm('Authorise transaction <?= htmlspecialchars($ptxn['reference_number']) ?>?')">
                            Authorise
                        </a>
                        <a href="/neobank/?page=transactions&reject=<?= urlencode($ptxn['reference_number']) ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Reject transaction <?= htmlspecialchars($ptxn['reference_number']) ?>? This cannot be undone.')">
                            Reject
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Search and Filter -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="/neobank/" class="row g-2 align-items-end">
            <input type="hidden" name="page" value="transactions">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control"
                       placeholder="Reference, account, customer, narration..."
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="filter_status" class="form-control">
                    <option value="">All</option>
                    <option value="COMPLETED" <?= $filterStatus === 'COMPLETED' ? 'selected' : '' ?>>Completed</option>
                    <option value="REJECTED"  <?= $filterStatus === 'REJECTED'  ? 'selected' : '' ?>>Rejected</option>
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
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
            <div class="col-md-1">
                <a href="/neobank/?page=transactions" class="btn btn-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Transactions List -->
<h5 class="mb-3">Transactions <span class="badge bg-secondary"><?= count($transactions) ?> results</span></h5>
<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th><?= sortLink('transaction_id',   'ID',        $sortCol, $nextDir, $search, $filterStatus, $dateFrom, $dateTo) ?></th>
            <th><?= sortLink('reference_number', 'Reference', $sortCol, $nextDir, $search, $filterStatus, $dateFrom, $dateTo) ?></th>
            <th>Account</th>
            <th>Name</th>
            <th>Type</th>
            <th><?= sortLink('amount',           'Amount',    $sortCol, $nextDir, $search, $filterStatus, $dateFrom, $dateTo) ?></th>
            <th>Category</th>
            <th>Narration</th>
            <th>Counterparty</th>
            <th><?= sortLink('transaction_date', 'Date',      $sortCol, $nextDir, $search, $filterStatus, $dateFrom, $dateTo) ?></th>
            <th>Initiated By</th>
            <th>Authorised By</th>
            <th><?= sortLink('status',           'Status',    $sortCol, $nextDir, $search, $filterStatus, $dateFrom, $dateTo) ?></th>
            <th>Detail</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($transactions) === 0): ?>
        <tr><td colspan="14" class="text-center text-muted">No transactions found.</td></tr>
        <?php endif; ?>
        <?php foreach ($transactions as $txn): ?>
        <tr>
            <td><?= htmlspecialchars($txn['transaction_id']) ?></td>
            <td><?= htmlspecialchars($txn['reference_number']) ?></td>
            <td><?= htmlspecialchars($txn['account_number'] ?? '-') ?></td>
            <td><?= htmlspecialchars($txn['display_name'] ?? '-') ?></td>
            <td>
                <span class="badge <?= $txn['transaction_type'] === 'Credit' ? 'bg-success' : 'bg-danger' ?>">
                    <?= htmlspecialchars($txn['transaction_type']) ?>
                </span>
            </td>
            <td>&pound;<?= number_format($txn['amount'], 2) ?></td>
            <td><?= htmlspecialchars($txn['transaction_category'] ?? '-') ?></td>
            <td><?= htmlspecialchars($txn['transaction_narration'] ?? '-') ?></td>
            <td><?= htmlspecialchars($txn['counterparty_name'] ?? '-') ?></td>
            <td><?= htmlspecialchars($txn['transaction_date']) ?></td>
            <td><?= htmlspecialchars($txn['initiated_by_user'] ?? '-') ?></td>
            <td><?= htmlspecialchars($txn['authorised_by_user'] ?? '-') ?></td>
            <td>
                <?php
                    $badgeClass = match($txn['status']) {
                        'COMPLETED' => 'bg-success',
                        'REJECTED'  => 'bg-danger',
                        default     => 'bg-secondary'
                    };
                ?>
                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($txn['status']) ?></span>
            </td>
            <td>
                <a href="/neobank/?page=transactions&detail=<?= urlencode($txn['reference_number']) ?>"
                   class="btn btn-sm btn-info text-white">View</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
