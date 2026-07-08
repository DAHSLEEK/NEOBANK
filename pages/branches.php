<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('Compliance Officer');
require_once __DIR__ . '/../config/db.php';
$pdo = getDBConnection();

$editBranch = null;
$message = '';

// Handle Add / Update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $branch_id   = $_POST['branch_id'] ?? null;
    $branch_name = trim($_POST['branch_name']);
    $branch_code = trim($_POST['branch_code']);
    $email       = trim($_POST['email'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $address     = trim($_POST['address'] ?? '');
    $postcode    = trim($_POST['postcode'] ?? '');
    $country     = trim($_POST['country'] ?? 'United Kingdom');

    if ($branch_id) {
        $stmt = $pdo->prepare("
            UPDATE BRANCH SET branch_name = ?, branch_code = ?
            WHERE branch_id = ?
        ");
        $stmt->execute([$branch_name, $branch_code, $branch_id]);

        // Update or insert CONTACT row for this branch
        $check = $pdo->prepare("SELECT contact_id FROM CONTACT WHERE branch_id = ?");
        $check->execute([$branch_id]);
        if ($check->fetch()) {
            $stmt = $pdo->prepare("
                UPDATE CONTACT SET email = ?, phone = ?, address = ?, postcode = ?, country = ?
                WHERE branch_id = ?
            ");
            $stmt->execute([$email, $phone, $address, $postcode, $country, $branch_id]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO CONTACT (branch_id, email, phone, address, postcode, country)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$branch_id, $email, $phone, $address, $postcode, $country]);
        }
        $message = "Branch updated successfully.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO BRANCH (branch_name, branch_code)
            VALUES (?, ?)
        ");
        $stmt->execute([$branch_name, $branch_code]);
        $newBranchId = $pdo->lastInsertId();

        $stmt = $pdo->prepare("
            INSERT INTO CONTACT (branch_id, email, phone, address, postcode, country)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$newBranchId, $email, $phone, $address, $postcode, $country]);

        $message = "Branch added successfully.";
    }
}
// Handle status toggle
if (isset($_GET['toggle_status'])) {
    $toggle_id = (int) $_GET['toggle_status'];
    $current = $pdo->prepare("SELECT status FROM BRANCH WHERE branch_id = ?");
    $current->execute([$toggle_id]);
    $currentStatus = $current->fetchColumn();
    $newStatus = $currentStatus === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';

    $pdo->prepare("UPDATE BRANCH SET status = ? WHERE branch_id = ?")->execute([$newStatus, $toggle_id]);
    $message = "Branch status updated to {$newStatus}.";
}
// Handle Edit link click
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("
        SELECT b.*, co.email, co.phone, co.address, co.postcode, co.country
        FROM BRANCH b
        LEFT JOIN CONTACT co ON co.branch_id = b.branch_id
        WHERE b.branch_id = ?
    ");
    $stmt->execute([$_GET['edit']]);
    $editBranch = $stmt->fetch();
}

// Fetch all branches with employee count and account count
$branches = $pdo->query("
    SELECT b.*,
        COUNT(DISTINCT e.employee_id) AS employee_count,
        COUNT(DISTINCT a.account_id) AS account_count
    FROM BRANCH b
    LEFT JOIN EMPLOYEE e ON e.branch_id = b.branch_id
    LEFT JOIN ACCOUNT a ON a.branch_id = b.branch_id AND a.account_category = 'CUSTOMER'
    GROUP BY b.branch_id
    ORDER BY b.branch_id ASC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<h1>Branch Management</h1>

<?php if ($message): ?>
    <div class="alert <?= str_starts_with($message, 'Error:') ? 'alert-danger' : 'alert-success' ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- Add / Edit Form -->
<div class="card mb-4">
    <div class="card-header">
        <?= $editBranch ? 'Edit Branch' : 'Add New Branch' ?>
    </div>
    <div class="card-body">
        <form method="POST">
            <?php if ($editBranch): ?>
                <input type="hidden" name="branch_id" value="<?= htmlspecialchars($editBranch['branch_id']) ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Branch Name</label>
                    <input type="text" name="branch_name" class="form-control" required
                           value="<?= htmlspecialchars($editBranch['branch_name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Branch Code</label>
                    <input type="text" name="branch_code" class="form-control" required
                           value="<?= htmlspecialchars($editBranch['branch_code'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($editBranch['email'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control"
                           value="<?= htmlspecialchars($editBranch['phone'] ?? '') ?>">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control"
                           value="<?= htmlspecialchars($editBranch['address'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Postcode</label>
                    <input type="text" name="postcode" class="form-control"
                           value="<?= htmlspecialchars($editBranch['postcode'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Country</label>
                    <input type="text" name="country" class="form-control"
                           value="<?= htmlspecialchars($editBranch['country'] ?? 'United Kingdom') ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">
                <?= $editBranch ? 'Update Branch' : 'Add Branch' ?>
            </button>
            <?php if ($editBranch): ?>
                <a href="branches.php" class="btn btn-secondary mt-3">Cancel</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Branch List -->
<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Branch Name</th>
            <th>Branch Code</th>
            <th>Employees</th>
            <th>Customer Accounts</th>
            <th>Status</th>
            <th>Date Created</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($branches as $branch): ?>
        <tr>
            <td><?= htmlspecialchars($branch['branch_id']) ?></td>
            <td><?= htmlspecialchars($branch['branch_name']) ?></td>
            <td><?= htmlspecialchars($branch['branch_code']) ?></td>
            <td><?= htmlspecialchars($branch['employee_count']) ?></td>
            <td><?= htmlspecialchars($branch['account_count']) ?></td>
            <td>
                <?php
                    $branchStatus = $branch['status'] ?? 'ACTIVE';
                    $branchBadge  = $branchStatus === 'ACTIVE' ? 'bg-success' : 'bg-secondary';
                ?>
                <span class="badge <?= $branchBadge ?>"><?= htmlspecialchars($branchStatus) ?></span>
            </td>
            <td><?= htmlspecialchars($branch['time_created']) ?></td>
            <td>
                <a href="?edit=<?= $branch['branch_id'] ?>" class="btn btn-sm btn-warning me-1">Edit</a>
                <a href="?toggle_status=<?= $branch['branch_id'] ?>"
                   class="btn btn-sm <?= $branch['status'] === 'ACTIVE' ? 'btn-secondary' : 'btn-success' ?>"
                   onclick="return confirm('<?= $branch['status'] === 'ACTIVE' ? 'Deactivate' : 'Activate' ?> this branch?')">
                    <?= $branch['status'] === 'ACTIVE' ? 'Deactivate' : 'Activate' ?>
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>