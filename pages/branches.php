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

    if ($branch_id) {
        $stmt = $pdo->prepare("
            UPDATE BRANCH SET branch_name = ?, branch_code = ?
            WHERE branch_id = ?
        ");
        $stmt->execute([$branch_name, $branch_code, $branch_id]);
        $message = "Branch updated successfully.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO BRANCH (branch_name, branch_code)
            VALUES (?, ?)
        ");
        $stmt->execute([$branch_name, $branch_code]);
        $message = "Branch added successfully.";
    }
}

// Handle Edit link click
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM BRANCH WHERE branch_id = ?");
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
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
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
            <td><?= htmlspecialchars($branch['time_created']) ?></td>
            <td>
                <a href="?edit=<?= $branch['branch_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>