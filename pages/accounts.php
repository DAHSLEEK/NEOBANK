<?php
require_once __DIR__ . '/../config/db.php';
$pdo = getDBConnection();

$editAccount = null;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $account_id      = $_POST['account_id'] ?? null;
    $customer_id     = $_POST['customer_id'];
    $branch_id       = $_POST['branch_id'];
    $account_type    = $_POST['account_type'];
    $account_name    = trim($_POST['account_name']);
    $opening_balance = $_POST['opening_balance'] ?? '0.00';
    $date_opened     = date('Y-m-d');

    if (!$account_id) {
        do {
            $maxStmt = $pdo->query("
                SELECT MAX(CAST(SUBSTRING(account_number, 5) AS UNSIGNED)) AS max_num
                FROM ACCOUNT
            ");
            $nextId = ($maxStmt->fetch()['max_num'] ?? 0) + 1;
            $candidate_number = 'NEO-' . str_pad($nextId, 8, '0', STR_PAD_LEFT);
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM ACCOUNT WHERE account_number = ?");
            $checkStmt->execute([$candidate_number]);
            $exists = $checkStmt->fetchColumn() > 0;
        } while ($exists);
        $account_number = $candidate_number;
    }

    if ($account_id) {
        // Fetch existing values for audit
        $oldStmt = $pdo->prepare("SELECT customer_id, branch_id, account_type, account_name FROM ACCOUNT WHERE account_id = ?");
        $oldStmt->execute([$account_id]);
        $oldData = $oldStmt->fetch();

        $stmt = $pdo->prepare("
            UPDATE ACCOUNT SET customer_id = ?, branch_id = ?,
                account_type = ?, account_name = ?
            WHERE account_id = ?
        ");
        $stmt->execute([$customer_id, $branch_id, $account_type, $account_name, $account_id]);

        auditModification($pdo, 'ACCOUNT', (int)$account_id, 'UPDATE', $oldData, [
            'customer_id'  => $customer_id,
            'branch_id'    => $branch_id,
            'account_type' => $account_type,
            'account_name' => $account_name,
        ]);
        $message = "Account updated successfully.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO ACCOUNT (customer_id, branch_id, account_number, account_type, account_name, date_opened)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$customer_id, $branch_id, $account_number, $account_type, $account_name, $date_opened]);
        $newAccountId = $pdo->lastInsertId();

        $stmt = $pdo->prepare("
            INSERT INTO ACCOUNT_BALANCE (account_id, balance, currency, balance_date, total_credit, total_debit)
            VALUES (?, ?, 'GBP', CURDATE(), ?, 0.00)
        ");
        $stmt->execute([$newAccountId, $opening_balance, $opening_balance]);

        $stmt = $pdo->prepare("
            INSERT INTO ACCOUNT_STATUS (account_id, status, status_date, changed_by)
            VALUES (?, 'ACTIVE', NOW(), NULL)
        ");
        $stmt->execute([$newAccountId]);
        auditModification($pdo, 'ACCOUNT', (int)$newAccountId, 'INSERT', null, [
            'account_number'   => $account_number,
            'account_type'     => $account_type,
            'account_name'     => $account_name,
            'opening_balance'  => $opening_balance,
        ]);
        $message = "Account opened successfully.";
    }
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM ACCOUNT WHERE account_id = ?");
    $stmt->execute([$_GET['edit']]);
    $editAccount = $stmt->fetch();
}

$customers = $pdo->query("SELECT customer_id, customer_name FROM CUSTOMER ORDER BY customer_name")->fetchAll();
$branches  = $pdo->query("SELECT branch_id, branch_name FROM BRANCH ORDER BY branch_name")->fetchAll();

$search       = trim($_GET['search'] ?? '');
$filterType   = $_GET['filter_type'] ?? '';
$filterStatus = $_GET['filter_status'] ?? '';
$sortCol      = $_GET['sort'] ?? 'account_id';
$sortDir      = $_GET['dir'] ?? 'desc';

$allowedSorts = ['account_id', 'account_number', 'customer_name', 'account_type', 'balance', 'date_opened'];
if (!in_array($sortCol, $allowedSorts)) $sortCol = 'account_id';
$sortDir = $sortDir === 'asc' ? 'asc' : 'desc';
$nextDir = $sortDir === 'asc' ? 'desc' : 'asc';

$whereParts = ["a.account_category = 'CUSTOMER'"];
$params     = [];

if ($search !== '') {
    $whereParts[] = "(a.account_number LIKE ? OR c.customer_name LIKE ? OR a.account_name LIKE ?)";
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like);
}
if ($filterType !== '') {
    $whereParts[] = "a.account_type = ?";
    $params[]     = $filterType;
}
if ($filterStatus !== '') {
    $whereParts[] = "(SELECT status FROM ACCOUNT_STATUS WHERE account_id = a.account_id ORDER BY status_date DESC LIMIT 1) = ?";
    $params[]     = $filterStatus;
}

$whereSQL = 'WHERE ' . implode(' AND ', $whereParts);
$sortExpression = match($sortCol) {
    'customer_name' => 'c.customer_name',
    'balance'       => 'bal.balance',
    default         => "a.{$sortCol}"
};

$accStmt = $pdo->prepare("
    SELECT a.*, c.customer_name, b.branch_name, bal.balance,
        (SELECT status FROM ACCOUNT_STATUS WHERE account_id = a.account_id ORDER BY status_date DESC LIMIT 1) AS current_status
    FROM ACCOUNT a
    LEFT JOIN CUSTOMER c ON c.customer_id = a.customer_id
    LEFT JOIN BRANCH b ON b.branch_id = a.branch_id
    LEFT JOIN ACCOUNT_BALANCE bal ON bal.account_id = a.account_id
    {$whereSQL}
    ORDER BY {$sortExpression} {$sortDir}
");
$accStmt->execute($params);
$accounts = $accStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';

function sortLink(string $col, string $label, string $currentCol, string $nextDir, string $search, string $filterType, string $filterStatus): string {
    $arrow  = $currentCol === $col ? ' &#8597;' : '';
    $params = http_build_query(['page' => 'accounts', 'sort' => $col, 'dir' => $nextDir, 'search' => $search, 'filter_type' => $filterType, 'filter_status' => $filterStatus]);
    return "<a href='/neobank/?{$params}' class='text-decoration-none text-dark'>{$label}{$arrow}</a>";
}
?>

<h1>Account Management</h1>

<?php if ($message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="mb-3">
    <a href="/neobank/?page=customers" class="btn btn-outline-primary">+ New Customer</a>
</div>

<div class="card mb-4">
    <div class="card-header"><?= $editAccount ? 'Edit Account' : 'Open New Account' ?></div>
    <div class="card-body">
        <form method="POST" action="/neobank/?page=accounts">
            <?= csrfField() ?>
            <?php if ($editAccount): ?>
                <input type="hidden" name="account_id" value="<?= htmlspecialchars($editAccount['account_id']) ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Customer</label>
                    <select name="customer_id" class="form-control" required>
                        <option value="">Select customer</option>
                        <?php foreach ($customers as $cust): ?>
                            <option value="<?= $cust['customer_id'] ?>"
                                <?= ($editAccount['customer_id'] ?? '') == $cust['customer_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cust['customer_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-control" required>
                        <option value="">Select branch</option>
                        <?php foreach ($branches as $br): ?>
                            <option value="<?= $br['branch_id'] ?>"
                                <?= ($editAccount['branch_id'] ?? '') == $br['branch_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($br['branch_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Account Type</label>
                    <select name="account_type" class="form-control" required>
                        <option value="Current"  <?= ($editAccount['account_type'] ?? '') === 'Current'  ? 'selected' : '' ?>>Current</option>
                        <option value="Savings"  <?= ($editAccount['account_type'] ?? '') === 'Savings'  ? 'selected' : '' ?>>Savings</option>
                        <option value="Business" <?= ($editAccount['account_type'] ?? '') === 'Business' ? 'selected' : '' ?>>Business</option>
                    </select>
                </div>
                <?php if ($editAccount): ?>
                <div class="col-md-4">
                    <label class="form-label">Account Number</label>
                    <input type="text" class="form-control"
                           value="<?= htmlspecialchars($editAccount['account_number']) ?>" disabled>
                </div>
                <?php endif; ?>
                <div class="col-md-4">
                    <label class="form-label">Account Name</label>
                    <input type="text" name="account_name" class="form-control"
                           value="<?= htmlspecialchars($editAccount['account_name'] ?? '') ?>">
                </div>
                <?php if (!$editAccount): ?>
                <div class="col-md-4">
                    <label class="form-label">Opening Balance (GBP)</label>
                    <input type="number" step="0.01" name="opening_balance" class="form-control" required value="0.00">
                </div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary mt-3">
                <?= $editAccount ? 'Update Account' : 'Open Account' ?>
            </button>
            <?php if ($editAccount): ?>
                <a href="/neobank/?page=accounts" class="btn btn-secondary mt-3">Cancel</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="/neobank/" class="row g-2 align-items-end">
            <input type="hidden" name="page" value="accounts">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control"
                       placeholder="Account number, customer name, account name..."
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Account Type</label>
                <select name="filter_type" class="form-control">
                    <option value="">All Types</option>
                    <option value="Current"  <?= $filterType === 'Current'  ? 'selected' : '' ?>>Current</option>
                    <option value="Savings"  <?= $filterType === 'Savings'  ? 'selected' : '' ?>>Savings</option>
                    <option value="Business" <?= $filterType === 'Business' ? 'selected' : '' ?>>Business</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="filter_status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="ACTIVE"    <?= $filterStatus === 'ACTIVE'    ? 'selected' : '' ?>>Active</option>
                    <option value="SUSPENDED" <?= $filterStatus === 'SUSPENDED' ? 'selected' : '' ?>>Suspended</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
            <div class="col-md-2">
                <a href="/neobank/?page=accounts" class="btn btn-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<h5 class="mb-3">Accounts <span class="badge bg-secondary"><?= count($accounts) ?> results</span></h5>
<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th><?= sortLink('account_id',     'ID',          $sortCol, $nextDir, $search, $filterType, $filterStatus) ?></th>
            <th><?= sortLink('account_number', 'Account No.', $sortCol, $nextDir, $search, $filterType, $filterStatus) ?></th>
            <th><?= sortLink('customer_name',  'Customer',    $sortCol, $nextDir, $search, $filterType, $filterStatus) ?></th>
            <th>Branch</th>
            <th><?= sortLink('account_type',   'Type',        $sortCol, $nextDir, $search, $filterType, $filterStatus) ?></th>
            <th><?= sortLink('balance',        'Balance',     $sortCol, $nextDir, $search, $filterType, $filterStatus) ?></th>
            <th><?= sortLink('date_opened',    'Date Opened', $sortCol, $nextDir, $search, $filterType, $filterStatus) ?></th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($accounts) === 0): ?>
        <tr><td colspan="9" class="text-center text-muted">No accounts found.</td></tr>
        <?php endif; ?>
        <?php foreach ($accounts as $acc): ?>
        <tr>
            <td><?= htmlspecialchars($acc['account_id']) ?></td>
            <td><?= htmlspecialchars($acc['account_number']) ?></td>
            <td><?= htmlspecialchars($acc['customer_name'] ?? '-') ?></td>
            <td><?= htmlspecialchars($acc['branch_name'] ?? '-') ?></td>
            <td><?= htmlspecialchars($acc['account_type']) ?></td>
            <td>&pound;<?= number_format($acc['balance'] ?? 0, 2) ?></td>
            <td><?= htmlspecialchars($acc['date_opened']) ?></td>
            <td>
                <?php
                    $status = $acc['current_status'] ?? 'UNKNOWN';
                    $badgeClass = match($status) {
                        'ACTIVE'    => 'bg-success',
                        'SUSPENDED' => 'bg-danger',
                        default     => 'bg-secondary'
                    };
                ?>
                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($status) ?></span>
            </td>
            <td>
                <a href="/neobank/?page=accounts&edit=<?= $acc['account_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
