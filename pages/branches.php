<?php
require_once __DIR__ . '/../config/db.php';
$pdo = getDBConnection();

$editBranch = null;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $branch_id   = $_POST['branch_id'] ?? null;
    $branch_name = trim($_POST['branch_name']);
    $branch_code = trim($_POST['branch_code']);
    $email       = trim($_POST['email'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $address     = trim($_POST['address'] ?? '');
    $postcode    = trim($_POST['postcode'] ?? '');
    $country     = trim($_POST['country'] ?? 'United Kingdom');

    if ($branch_id) {
        $oldStmt = $pdo->prepare("SELECT branch_name, branch_code, status FROM BRANCH WHERE branch_id = ?");
        $oldStmt->execute([$branch_id]);
        $oldData = $oldStmt->fetch();

        $stmt = $pdo->prepare("UPDATE BRANCH SET branch_name = ?, branch_code = ? WHERE branch_id = ?");
        $stmt->execute([$branch_name, $branch_code, $branch_id]);

        auditModification($pdo, 'BRANCH', (int)$branch_id, 'UPDATE', $oldData, [
            'branch_name' => $branch_name,
            'branch_code' => $branch_code,
        ]);
        $check = $pdo->prepare("SELECT contact_id FROM CONTACT WHERE branch_id = ?");
        $check->execute([$branch_id]);
        if ($check->fetch()) {
            $stmt = $pdo->prepare("UPDATE CONTACT SET email = ?, phone = ?, address = ?, postcode = ?, country = ? WHERE branch_id = ?");
            $stmt->execute([$email, $phone, $address, $postcode, $country, $branch_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO CONTACT (branch_id, email, phone, address, postcode, country) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$branch_id, $email, $phone, $address, $postcode, $country]);
        }
        $message = "Branch updated successfully.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO BRANCH (branch_name, branch_code) VALUES (?, ?)");
        $stmt->execute([$branch_name, $branch_code]);
        $newBranchId = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO CONTACT (branch_id, email, phone, address, postcode, country) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$newBranchId, $email, $phone, $address, $postcode, $country]);
        $message = "Branch added successfully.";
    }
}

if (isset($_GET['toggle_status'])) {
    $toggle_id = (int) $_GET['toggle_status'];
    $current = $pdo->prepare("SELECT status FROM BRANCH WHERE branch_id = ?");
    $current->execute([$toggle_id]);
    $currentStatus = $current->fetchColumn();
    $newStatus = $currentStatus === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
    $pdo->prepare("UPDATE BRANCH SET status = ? WHERE branch_id = ?")->execute([$newStatus, $toggle_id]);
    auditModification($pdo, 'BRANCH', $toggle_id, 'STATUS_CHANGE', ['status' => $currentStatus], ['status' => $newStatus]);
    $message = "Branch status updated to {$newStatus}.";
}

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

$search       = trim($_GET['search'] ?? '');
$filterStatus = $_GET['filter_status'] ?? '';
$sortCol      = $_GET['sort'] ?? 'branch_id';
$sortDir      = $_GET['dir'] ?? 'asc';

$allowedSorts = ['branch_id', 'branch_name', 'branch_code', 'status', 'time_created'];
if (!in_array($sortCol, $allowedSorts)) $sortCol = 'branch_id';
$sortDir = $sortDir === 'asc' ? 'asc' : 'desc';
$nextDir = $sortDir === 'asc' ? 'desc' : 'asc';

$whereParts = ['1=1'];
$params     = [];

if ($search !== '') {
    $whereParts[] = "(b.branch_name LIKE ? OR b.branch_code LIKE ? OR co.email LIKE ? OR co.address LIKE ?)";
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like, $like);
}
if ($filterStatus !== '') {
    $whereParts[] = "b.status = ?";
    $params[]     = $filterStatus;
}

$whereSQL = 'WHERE ' . implode(' AND ', $whereParts);

$branchStmt = $pdo->prepare("
    SELECT b.*, co.email, co.phone, co.address,
        COUNT(DISTINCT e.employee_id) AS employee_count,
        COUNT(DISTINCT a.account_id) AS account_count
    FROM BRANCH b
    LEFT JOIN CONTACT co ON co.branch_id = b.branch_id
    LEFT JOIN EMPLOYEE e ON e.branch_id = b.branch_id
    LEFT JOIN ACCOUNT a ON a.branch_id = b.branch_id AND a.account_category = 'CUSTOMER'
    {$whereSQL}
    GROUP BY b.branch_id, co.email, co.phone, co.address
    ORDER BY b.{$sortCol} {$sortDir}
");
$branchStmt->execute($params);
$branches = $branchStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';

function sortLink(string $col, string $label, string $currentCol, string $nextDir, string $search, string $filterStatus): string {
    $arrow  = $currentCol === $col ? ' &#8597;' : '';
    $params = http_build_query(['page' => 'branches', 'sort' => $col, 'dir' => $nextDir, 'search' => $search, 'filter_status' => $filterStatus]);
    return "<a href='/neobank/?{$params}' class='text-decoration-none text-dark'>{$label}{$arrow}</a>";
}
?>

<h1>Branch Management</h1>

<?php if ($message): ?>
    <div class="alert <?= str_starts_with($message, 'Error:') ? 'alert-danger' : 'alert-success' ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header"><?= $editBranch ? 'Edit Branch' : 'Add New Branch' ?></div>
    <div class="card-body">
        <form method="POST" action="/neobank/?page=branches">
            <?= csrfField() ?>
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
                <a href="/neobank/?page=branches" class="btn btn-secondary mt-3">Cancel</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="/neobank/" class="row g-2 align-items-end">
            <input type="hidden" name="page" value="branches">
            <div class="col-md-5">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control"
                       placeholder="Branch name, code, email, address..."
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="filter_status" class="form-control">
                    <option value="">All</option>
                    <option value="ACTIVE"   <?= $filterStatus === 'ACTIVE'   ? 'selected' : '' ?>>Active</option>
                    <option value="INACTIVE" <?= $filterStatus === 'INACTIVE' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
            <div class="col-md-2">
                <a href="/neobank/?page=branches" class="btn btn-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<h5 class="mb-3">Branches <span class="badge bg-secondary"><?= count($branches) ?> results</span></h5>
<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th><?= sortLink('branch_id',    'ID',          $sortCol, $nextDir, $search, $filterStatus) ?></th>
            <th><?= sortLink('branch_name',  'Branch Name', $sortCol, $nextDir, $search, $filterStatus) ?></th>
            <th><?= sortLink('branch_code',  'Code',        $sortCol, $nextDir, $search, $filterStatus) ?></th>
            <th>Email</th>
            <th>Phone</th>
            <th>Employees</th>
            <th>Accounts</th>
            <th><?= sortLink('status',       'Status',      $sortCol, $nextDir, $search, $filterStatus) ?></th>
            <th><?= sortLink('time_created', 'Created',     $sortCol, $nextDir, $search, $filterStatus) ?></th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($branches) === 0): ?>
        <tr><td colspan="10" class="text-center text-muted">No branches found.</td></tr>
        <?php endif; ?>
        <?php foreach ($branches as $branch): ?>
        <tr>
            <td><?= htmlspecialchars($branch['branch_id']) ?></td>
            <td><?= htmlspecialchars($branch['branch_name']) ?></td>
            <td><?= htmlspecialchars($branch['branch_code']) ?></td>
            <td><?= htmlspecialchars($branch['email'] ?? '-') ?></td>
            <td><?= htmlspecialchars($branch['phone'] ?? '-') ?></td>
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
                <a href="/neobank/?page=branches&edit=<?= $branch['branch_id'] ?>" class="btn btn-sm btn-warning me-1">Edit</a>
                <a href="/neobank/?page=branches&toggle_status=<?= $branch['branch_id'] ?>"
                   class="btn btn-sm <?= ($branch['status'] ?? 'ACTIVE') === 'ACTIVE' ? 'btn-secondary' : 'btn-success' ?>"
                   onclick="return confirm('<?= ($branch['status'] ?? 'ACTIVE') === 'ACTIVE' ? 'Deactivate' : 'Activate' ?> this branch?')">
                    <?= ($branch['status'] ?? 'ACTIVE') === 'ACTIVE' ? 'Deactivate' : 'Activate' ?>
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
