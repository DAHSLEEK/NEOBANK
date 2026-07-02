<?php
require_once __DIR__ . '/../config/db.php';
$pdo = getDBConnection();

$message = '';

// Helper function: post one leg of a double-entry transaction
function postLeg(PDO $pdo, int $accountId, string $type, float $amount, string $reference, string $category, string $narration): void {
    $stmt = $pdo->prepare("
        INSERT INTO TRANSACTION_HISTORY
            (account_id, transaction_type, amount, transaction_date, reference_number,
             transaction_category, transaction_narration, status)
        VALUES (?, ?, ?, NOW(), ?, ?, ?, 'COMPLETED')
    ");
    $stmt->execute([$accountId, $type, $amount, $reference, $category, $narration]);

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

// Helper function: auto-generate unique reference number
function generateReference(PDO $pdo): string {
    $datePart = date('Ymd');
    $unique   = strtoupper(substr(uniqid(), -6));
    return 'TXN-' . $datePart . '-' . $unique;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_type = $_POST['transaction_type'];
    $account_id       = $_POST['account_id'];
    $amount           = (float) $_POST['amount'];
    $category         = $_POST['transaction_category'];
    $narration        = trim($_POST['transaction_narration']);

    // Fetch the customer account details and balance
    $accStmt = $pdo->prepare("
        SELECT a.account_id, a.branch_id, ab.balance
        FROM ACCOUNT a
        LEFT JOIN ACCOUNT_BALANCE ab ON ab.account_id = a.account_id
        WHERE a.account_id = ?
    ");
    $accStmt->execute([$account_id]);
    $customerAcc = $accStmt->fetch();

    $branchId = $customerAcc['branch_id'];
    $reference = generateReference($pdo);
    $error = '';

    try {
        $pdo->beginTransaction();

        switch ($transaction_type) {

            case 'Cash Deposit':
                // Debit branch CASH account, Credit customer account
                $cashAcc = $pdo->prepare("SELECT account_id FROM ACCOUNT WHERE branch_id = ? AND account_category = 'INTERNAL-CASH'");
                $cashAcc->execute([$branchId]);
                $branchCashId = $cashAcc->fetchColumn();

                postLeg($pdo, $branchCashId, 'Debit',  $amount, $reference, $category, 'Cash deposit - ' . $narration);
                postLeg($pdo, $account_id,   'Credit', $amount, $reference, $category, 'Cash deposit - ' . $narration);
                break;

            case 'Cash Withdrawal':
                // Check sufficient funds
                if ($amount > $customerAcc['balance']) {
                    throw new Exception("Insufficient funds. Current balance is £" . number_format($customerAcc['balance'], 2));
                }
                // Debit customer account, Credit branch CASH account
                $cashAcc = $pdo->prepare("SELECT account_id FROM ACCOUNT WHERE branch_id = ? AND account_category = 'INTERNAL-CASH'");
                $cashAcc->execute([$branchId]);
                $branchCashId = $cashAcc->fetchColumn();

                postLeg($pdo, $account_id,   'Debit',  $amount, $reference, $category, 'Cash withdrawal - ' . $narration);
                postLeg($pdo, $branchCashId, 'Credit', $amount, $reference, $category, 'Cash withdrawal - ' . $narration);
                break;

            case 'Inward Transfer':
                // External bank sending money in: Debit branch RECEIVABLE, Credit customer
                $recAcc = $pdo->prepare("SELECT account_id FROM ACCOUNT WHERE branch_id = ? AND account_category = 'INTERNAL-RECEIVABLE'");
                $recAcc->execute([$branchId]);
                $branchRecId = $recAcc->fetchColumn();

                postLeg($pdo, $branchRecId, 'Debit',  $amount, $reference, $category, 'Inward transfer - ' . $narration);
                postLeg($pdo, $account_id,  'Credit', $amount, $reference, $category, 'Inward transfer - ' . $narration);
                break;

            case 'Outward Transfer':
                // Customer sending money out to external bank: Debit customer, Credit branch PAYABLE
                if ($amount > $customerAcc['balance']) {
                    throw new Exception("Insufficient funds. Current balance is £" . number_format($customerAcc['balance'], 2));
                }
                $payAcc = $pdo->prepare("SELECT account_id FROM ACCOUNT WHERE branch_id = ? AND account_category = 'INTERNAL-PAYABLE'");
                $payAcc->execute([$branchId]);
                $branchPayId = $payAcc->fetchColumn();

                postLeg($pdo, $account_id,  'Debit',  $amount, $reference, $category, 'Outward transfer - ' . $narration);
                postLeg($pdo, $branchPayId, 'Credit', $amount, $reference, $category, 'Outward transfer - ' . $narration);
                break;

            case 'Internal Transfer':
                // NeoBank to NeoBank: Debit sender, Credit receiver directly
                $receiver_account_id = $_POST['receiver_account_id'] ?? null;
                if (!$receiver_account_id) {
                    throw new Exception("Please select a receiver account for internal transfers.");
                }
                if ($receiver_account_id == $account_id) {
                    throw new Exception("Sender and receiver accounts cannot be the same.");
                }
                if ($amount > $customerAcc['balance']) {
                    throw new Exception("Insufficient funds. Current balance is £" . number_format($customerAcc['balance'], 2));
                }

                postLeg($pdo, $account_id,          'Debit',  $amount, $reference, $category, 'Internal transfer out - ' . $narration);
                postLeg($pdo, $receiver_account_id, 'Credit', $amount, $reference, $category, 'Internal transfer in - '  . $narration);
                break;

            case 'Bank Charge':
                // Debit customer account, Credit branch CASH account
                if ($amount > $customerAcc['balance']) {
                    throw new Exception("Insufficient funds. Current balance is £" . number_format($customerAcc['balance'], 2));
                }
                $cashAcc = $pdo->prepare("SELECT account_id FROM ACCOUNT WHERE branch_id = ? AND account_category = 'INTERNAL-CASH'");
                $cashAcc->execute([$branchId]);
                $branchCashId = $cashAcc->fetchColumn();

                postLeg($pdo, $account_id,   'Debit',  $amount, $reference, $category, 'Bank charge - ' . $narration);
                postLeg($pdo, $branchCashId, 'Credit', $amount, $reference, $category, 'Bank charge - ' . $narration);
                break;
        }

        $pdo->commit();
        $message = "Transaction posted successfully. Reference: " . $reference;

    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error: " . $e->getMessage();
    }
}

// Customer accounts only for dropdowns
$customerAccounts = $pdo->query("
    SELECT a.account_id, a.account_number, a.account_type, c.customer_name, ab.balance
    FROM ACCOUNT a
    LEFT JOIN CUSTOMER c ON c.customer_id = a.customer_id
    LEFT JOIN ACCOUNT_BALANCE ab ON ab.account_id = a.account_id
    WHERE a.account_category = 'CUSTOMER'
    ORDER BY a.account_number
")->fetchAll();

// Fetch all transactions with account and customer info
$transactions = $pdo->query("
    SELECT th.*, a.account_number, a.account_category,
        COALESCE(c.customer_name, a.account_name) AS display_name
    FROM TRANSACTION_HISTORY th
    LEFT JOIN ACCOUNT a ON a.account_id = th.account_id
    LEFT JOIN CUSTOMER c ON c.customer_id = a.customer_id
    ORDER BY th.transaction_id DESC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<h1>Transaction Management</h1>

<?php if ($message): ?>
    <div class="alert <?= str_starts_with($message, 'Error:') ? 'alert-danger' : 'alert-success' ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- New Transaction Form -->
<div class="card mb-4">
    <div class="card-header">Record New Transaction</div>
    <div class="card-body">
        <form method="POST">
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

                <!-- Receiver account: only shown for Internal Transfer -->
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
                <div class="col-md-4">
                    <label class="form-label">Reference Number</label>
                    <input type="text" class="form-control" value="Auto-generated on save" disabled>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Narration</label>
                    <input type="text" name="transaction_narration" class="form-control"
                           placeholder="Brief description of transaction">
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Post Transaction</button>
        </form>
    </div>
</div>

<!-- Show/hide receiver field based on transaction type -->
<script>
    document.getElementById('transaction_type').addEventListener('change', function () {
        const receiverField = document.getElementById('receiver_field');
        receiverField.style.display = this.value === 'Internal Transfer' ? 'block' : 'none';
    });
</script>

<!-- Transaction List -->
<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Reference</th>
            <th>Account</th>
            <th>Name</th>
            <th>Type</th>
            <th>Amount</th>
            <th>Category</th>
            <th>Narration</th>
            <th>Date</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
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
            <td><?= htmlspecialchars($txn['transaction_date']) ?></td>
            <td>
                <span class="badge bg-secondary"><?= htmlspecialchars($txn['status']) ?></span>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>