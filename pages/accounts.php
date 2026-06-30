<?php
require_once __DIR__ . '/../config/db.php';
$pdo = getDBConnection();

$editAccount = null;
$message = '';

// Handle Add / Update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_id     = $_POST['account_id'] ?? null;
    $customer_id    = $_POST['customer_id'];
    $branch_id      = $_POST['branch_id'];
    if (!$account_id) {
        // Auto-generate account number: NEO- followed by 8-digit sequential number
        // Loop ensures the generated number does not already exist, even in edge cases
        do {
            $maxStmt = $pdo->query("SELECT MAX(account_id) AS max_id FROM ACCOUNT");
            $nextId = ($maxStmt->fetch()['max_id'] ?? 0) + 1;
            $candidate_number = 'NEO-' . str_pad($nextId, 8, '0', STR_PAD_LEFT);

            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM ACCOUNT WHERE account_number = ?");
            $checkStmt->execute([$candidate_number]);
            $exists = $checkStmt->fetchColumn() > 0;
        } while ($exists);

        $account_number = $candidate_number;
    } else {
        $account_number = trim($_POST['account_number']);
    }
    $account_type   = $_POST['account_type'];
    $account_name   = trim($_POST['account_name']);
    $date_opened    = $_POST['date_opened'];
    $opening_balance = $_POST['opening_balance'];

    if ($account_id) {
        // UPDATE existing account
        $stmt = $pdo->prepare("
            UPDATE ACCOUNT SET customer_id = ?, branch_id = ?, account_number = ?,
                account_type = ?, account_name = ?, date_opened = ?
            WHERE account_id = ?
        ");
        $stmt->execute([$customer_id, $branch_id, $account_number, $account_type,
                         $account_name, $date_opened, $account_id]);
        $message = "Account updated successfully.";
    } else {
        // INSERT new account
        $stmt = $pdo->prepare("
            INSERT INTO ACCOUNT (customer_id, branch_id, account_number, account_type, account_name, date_opened)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$customer_id, $branch_id, $account_number, $account_type, $account_name, $date_opened]);
        $newAccountId = $pdo->lastInsertId();

        // Create the initial balance record for this account
        $stmt = $pdo->prepare("
            INSERT INTO ACCOUNT_BALANCE (account_id, balance, currency, balance_date, total_credit, total_debit)
            VALUES (?, ?, 'GBP', CURDATE(), ?, 0.00)
        ");
        $stmt->execute([$newAccountId, $opening_balance, $opening_balance]);

        // Create the initial status record for this account
        $stmt = $pdo->prepare("
            INSERT INTO ACCOUNT_STATUS (account_id, status, status_date, changed_by)
            VALUES (?, 'ACTIVE', NOW(), NULL)
        ");
        $stmt->execute([$newAccountId]);

        $message = "Account opened successfully.";
    }
}

// Handle Edit link click
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM ACCOUNT WHERE account_id = ?");
    $stmt->execute([$_GET['edit']]);
    $editAccount = $stmt->fetch();
}

// Dropdown data
$customers = $pdo->query("SELECT customer_id, customer_name FROM CUSTOMER ORDER BY customer_name")->fetchAll();
$branches  = $pdo->query("SELECT branch_id, branch_name FROM BRANCH ORDER BY branch_name")->fetchAll();

// Fetch all accounts with customer name, branch name, balance, and current status
$accounts = $pdo->query("
    SELECT a.*, c.customer_name, b.branch_name, bal.balance,
        (SELECT status FROM ACCOUNT_STATUS WHERE account_id = a.account_id ORDER BY status_date DESC LIMIT 1) AS current_status
    FROM ACCOUNT a
    LEFT JOIN CUSTOMER c ON c.customer_id = a.customer_id
    LEFT JOIN BRANCH b ON b.branch_id = a.branch_id
    LEFT JOIN ACCOUNT_BALANCE bal ON bal.account_id = a.account_id
    ORDER BY a.account_id DESC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<h1>Account Management</h1>

<?php if ($message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

    <div class="mb-3">
    <a href="customers.php" class="btn btn-outline-primary">+ New Customer</a>
    </div>

<!-- Add / Edit Form -->
<div class="card mb-4">
    <div class="card-header">
        <?= $editAccount ? 'Edit Account' : 'Open New Account' ?>
    </div>
    <div class="card-body">
        <form method="POST">
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
                        <option value="Current" <?= ($editAccount['account_type'] ?? '') === 'Current' ? 'selected' : '' ?>>Current</option>
                        <option value="Savings" <?= ($editAccount['account_type'] ?? '') === 'Savings' ? 'selected' : '' ?>>Savings</option>
                        <option value="Business" <?= ($editAccount['account_type'] ?? '') === 'Business' ? 'selected' : '' ?>>Business</option>
                    </select>
                </div>

                <?php if ($editAccount): ?>
                <div class="col-md-4">
                    <label class="form-label">Account Number</label>
                    <input type="text" name="account_number" class="form-control" required
                           value="<?= htmlspecialchars($editAccount['account_number']) ?>">
                </div>
                <?php else: ?>
                <div class="col-md-4">
                    <label class="form-label">Account Number</label>
                    <input type="text" class="form-control" value="Auto-generated on save" disabled>
                </div>
                <?php endif; ?>
                <div class="col-md-4">
                    <label class="form-label">Account Name</label>
                    <input type="text" name="account_name" class="form-control"
                           value="<?= htmlspecialchars($editAccount['account_name'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date Opened</label>
                    <input type="date" name="date_opened" class="form-control" required
                           value="<?= htmlspecialchars($editAccount['date_opened'] ?? '') ?>">
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
                <a href="accounts.php" class="btn btn-secondary mt-3">Cancel</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Account List -->
<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Account No.</th>
            <th>Customer</th>
            <th>Branch</th>
            <th>Type</th>
            <th>Balance</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($accounts as $acc): ?>
        <tr>
            <td><?= htmlspecialchars($acc['account_id']) ?></td>
            <td><?= htmlspecialchars($acc['account_number']) ?></td>
            <td><?= htmlspecialchars($acc['customer_name'] ?? '-') ?></td>
            <td><?= htmlspecialchars($acc['branch_name'] ?? '-') ?></td>
            <td><?= htmlspecialchars($acc['account_type']) ?></td>
            <td>&pound;<?= number_format($acc['balance'] ?? 0, 2) ?></td>
            <td>
                <?php
                    $status = $acc['current_status'] ?? 'UNKNOWN';
                    $badgeClass = $status === 'ACTIVE' ? 'bg-success' : ($status === 'SUSPENDED' ? 'bg-danger' : 'bg-secondary');
                ?>
                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($status) ?></span>
            </td>
            <td>
                <a href="?edit=<?= $acc['account_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>