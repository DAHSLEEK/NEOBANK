<?php
require_once __DIR__ . '/../config/db.php';
$pdo = getDBConnection();

$editEmployee = null;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $employee_id = $_POST['employee_id'] ?? null;
    $branch_id   = $_POST['branch_id'];
    $full_name   = trim($_POST['full_name']);
    $job_title   = trim($_POST['job_title']);
    $hire_date   = $_POST['hire_date'];
    $email       = trim($_POST['email'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $mobile      = trim($_POST['mobile'] ?? '');
    $address     = trim($_POST['address'] ?? '');
    $postcode    = trim($_POST['postcode'] ?? '');
    $country     = trim($_POST['country'] ?? 'United Kingdom');

    if ($employee_id) {
        // Fetch existing values for audit before updating
        $oldStmt = $pdo->prepare("SELECT branch_id, full_name, job_title, hire_date, status FROM EMPLOYEE WHERE employee_id = ?");
        $oldStmt->execute([$employee_id]);
        $oldData = $oldStmt->fetch();

        $stmt = $pdo->prepare("UPDATE EMPLOYEE SET branch_id = ?, full_name = ?, job_title = ?, hire_date = ? WHERE employee_id = ?");
        $stmt->execute([$branch_id, $full_name, $job_title, $hire_date, $employee_id]);

        auditModification($pdo, 'EMPLOYEE', (int)$employee_id, 'UPDATE', $oldData, [
            'branch_id' => $branch_id,
            'full_name' => $full_name,
            'job_title' => $job_title,
            'hire_date' => $hire_date,
        ]);

        // Update or insert CONTACT row
        $check = $pdo->prepare("SELECT contact_id FROM CONTACT WHERE employee_id = ?");
        $check->execute([$employee_id]);
        if ($check->fetch()) {
            // Fetch old contact values for audit
            $oldContactStmt = $pdo->prepare("SELECT email, phone, mobile, address, postcode, country FROM CONTACT WHERE employee_id = ?");
            $oldContactStmt->execute([$employee_id]);
            $oldContact = $oldContactStmt->fetch();

            $stmt = $pdo->prepare("UPDATE CONTACT SET email = ?, phone = ?, mobile = ?, address = ?, postcode = ?, country = ? WHERE employee_id = ?");
            $stmt->execute([$email, $phone, $mobile, $address, $postcode, $country, $employee_id]);

            auditModification($pdo, 'CONTACT', (int)$employee_id, 'UPDATE', $oldContact, [
                'email'    => $email,
                'phone'    => $phone,
                'mobile'   => $mobile,
                'address'  => $address,
                'postcode' => $postcode,
                'country'  => $country,
            ]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO CONTACT (employee_id, email, phone, mobile, address, postcode, country) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$employee_id, $email, $phone, $mobile, $address, $postcode, $country]);

            auditModification($pdo, 'CONTACT', (int)$employee_id, 'INSERT', null, [
                'email'    => $email,
                'phone'    => $phone,
                'mobile'   => $mobile,
                'address'  => $address,
                'postcode' => $postcode,
                'country'  => $country,
            ]);
        }
        $message = "Employee updated successfully.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO EMPLOYEE (branch_id, full_name, job_title, hire_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$branch_id, $full_name, $job_title, $hire_date]);
        $newEmployeeId = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO CONTACT (employee_id, email, phone, mobile, address, postcode, country) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$newEmployeeId, $email, $phone, $mobile, $address, $postcode, $country]);

        auditModification($pdo, 'EMPLOYEE', (int)$newEmployeeId, 'INSERT', null, [
            'full_name' => $full_name,
            'job_title' => $job_title,
            'branch_id' => $branch_id,
            'hire_date' => $hire_date,
        ]);
        $message = "Employee added successfully.";
    }
}

// Handle status toggle
if (isset($_GET['toggle_status'])) {
    $toggle_id = (int) $_GET['toggle_status'];
    $current = $pdo->prepare("SELECT status FROM EMPLOYEE WHERE employee_id = ?");
    $current->execute([$toggle_id]);
    $currentStatus = $current->fetchColumn();
    $newStatus = $currentStatus === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
    $pdo->prepare("UPDATE EMPLOYEE SET status = ? WHERE employee_id = ?")->execute([$newStatus, $toggle_id]);
    auditModification($pdo, 'EMPLOYEE', $toggle_id, 'STATUS_CHANGE', ['status' => $currentStatus], ['status' => $newStatus]);
    $message = "Employee status updated to {$newStatus}.";
}

// Handle Edit link click
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("
        SELECT e.*, co.email, co.phone, co.mobile, co.address, co.postcode, co.country
        FROM EMPLOYEE e
        LEFT JOIN CONTACT co ON co.employee_id = e.employee_id
        WHERE e.employee_id = ?
    ");
    $stmt->execute([$_GET['edit']]);
    $editEmployee = $stmt->fetch();
}

$branches = $pdo->query("SELECT branch_id, branch_name FROM BRANCH ORDER BY branch_name")->fetchAll();

// Search and sort parameters
$search         = trim($_GET['search'] ?? '');
$filterJobTitle = $_GET['filter_job_title'] ?? '';
$filterStatus   = $_GET['filter_status'] ?? '';
$sortCol        = $_GET['sort'] ?? 'employee_id';
$sortDir        = $_GET['dir'] ?? 'asc';

$allowedSorts = ['employee_id', 'full_name', 'job_title', 'hire_date', 'status'];
if (!in_array($sortCol, $allowedSorts)) $sortCol = 'employee_id';
$sortDir = $sortDir === 'asc' ? 'asc' : 'desc';
$nextDir = $sortDir === 'asc' ? 'desc' : 'asc';

$whereParts = ['1=1'];
$params     = [];

if ($search !== '') {
    $whereParts[] = "(e.full_name LIKE ? OR b.branch_name LIKE ? OR co.email LIKE ? OR co.phone LIKE ?)";
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like, $like);
}
if ($filterJobTitle !== '') {
    $whereParts[] = "e.job_title = ?";
    $params[]     = $filterJobTitle;
}
if ($filterStatus !== '') {
    $whereParts[] = "e.status = ?";
    $params[]     = $filterStatus;
}

$whereSQL = 'WHERE ' . implode(' AND ', $whereParts);

$empStmt = $pdo->prepare("
    SELECT e.*, b.branch_name, co.email, co.phone
    FROM EMPLOYEE e
    LEFT JOIN BRANCH b ON b.branch_id = e.branch_id
    LEFT JOIN CONTACT co ON co.employee_id = e.employee_id
    {$whereSQL}
    ORDER BY e.{$sortCol} {$sortDir}
");
$empStmt->execute($params);
$employees = $empStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';

function sortLink(string $col, string $label, string $currentCol, string $nextDir, string $search, string $filterJobTitle, string $filterStatus): string {
    $arrow  = $currentCol === $col ? ' &#8597;' : '';
    $params = http_build_query(['page' => 'employees', 'sort' => $col, 'dir' => $nextDir, 'search' => $search, 'filter_job_title' => $filterJobTitle, 'filter_status' => $filterStatus]);
    return "<a href='/neobank/?{$params}' class='text-decoration-none text-dark'>{$label}{$arrow}</a>";
}
?>

<h1>Employee Management</h1>

<?php if ($message): ?>
    <div class="alert <?= str_starts_with($message, 'Error:') ? 'alert-danger' : 'alert-success' ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- Add / Edit Form -->
<div class="card mb-4">
    <div class="card-header"><?= $editEmployee ? 'Edit Employee' : 'Add New Employee' ?></div>
    <div class="card-body">
        <form method="POST" action="/neobank/?page=employees">
            <?= csrfField() ?>
            <?php if ($editEmployee): ?>
                <input type="hidden" name="employee_id" value="<?= htmlspecialchars($editEmployee['employee_id']) ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" required
                           value="<?= htmlspecialchars($editEmployee['full_name'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-control" required>
                        <option value="">Select branch</option>
                        <?php foreach ($branches as $br): ?>
                            <option value="<?= $br['branch_id'] ?>"
                                <?= ($editEmployee['branch_id'] ?? '') == $br['branch_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($br['branch_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Job Title</label>
                    <select name="job_title" class="form-control" required>
                        <option value="Branch Manager"     <?= ($editEmployee['job_title'] ?? '') === 'Branch Manager'     ? 'selected' : '' ?>>Branch Manager</option>
                        <option value="Customer Advisor"   <?= ($editEmployee['job_title'] ?? '') === 'Customer Advisor'   ? 'selected' : '' ?>>Customer Advisor</option>
                        <option value="Loans Officer"      <?= ($editEmployee['job_title'] ?? '') === 'Loans Officer'      ? 'selected' : '' ?>>Loans Officer</option>
                        <option value="Compliance Officer" <?= ($editEmployee['job_title'] ?? '') === 'Compliance Officer' ? 'selected' : '' ?>>Compliance Officer</option>
                        <option value="Teller"             <?= ($editEmployee['job_title'] ?? '') === 'Teller'             ? 'selected' : '' ?>>Teller</option>
                        <option value="Admin"              <?= ($editEmployee['job_title'] ?? '') === 'Admin'              ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Hire Date</label>
                    <input type="date" name="hire_date" class="form-control" required
                           value="<?= htmlspecialchars($editEmployee['hire_date'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($editEmployee['email'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control"
                           value="<?= htmlspecialchars($editEmployee['phone'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Mobile</label>
                    <input type="text" name="mobile" class="form-control"
                           value="<?= htmlspecialchars($editEmployee['mobile'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control"
                           value="<?= htmlspecialchars($editEmployee['address'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Postcode</label>
                    <input type="text" name="postcode" class="form-control"
                           value="<?= htmlspecialchars($editEmployee['postcode'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Country</label>
                    <input type="text" name="country" class="form-control"
                           value="<?= htmlspecialchars($editEmployee['country'] ?? 'United Kingdom') ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">
                <?= $editEmployee ? 'Update Employee' : 'Add Employee' ?>
            </button>
            <?php if ($editEmployee): ?>
                <a href="/neobank/?page=employees" class="btn btn-secondary mt-3">Cancel</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Search and Filter -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="/neobank/" class="row g-2 align-items-end">
            <input type="hidden" name="page" value="employees">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control"
                       placeholder="Name, branch, email, phone..."
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Job Title</label>
                <select name="filter_job_title" class="form-control">
                    <option value="">All Job Titles</option>
                    <option value="Branch Manager"     <?= $filterJobTitle === 'Branch Manager'     ? 'selected' : '' ?>>Branch Manager</option>
                    <option value="Customer Advisor"   <?= $filterJobTitle === 'Customer Advisor'   ? 'selected' : '' ?>>Customer Advisor</option>
                    <option value="Loans Officer"      <?= $filterJobTitle === 'Loans Officer'      ? 'selected' : '' ?>>Loans Officer</option>
                    <option value="Compliance Officer" <?= $filterJobTitle === 'Compliance Officer' ? 'selected' : '' ?>>Compliance Officer</option>
                    <option value="Teller"             <?= $filterJobTitle === 'Teller'             ? 'selected' : '' ?>>Teller</option>
                    <option value="Admin"              <?= $filterJobTitle === 'Admin'              ? 'selected' : '' ?>>Admin</option>
                </select>
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
                <a href="/neobank/?page=employees" class="btn btn-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Employee List -->
<h5 class="mb-3">Employees <span class="badge bg-secondary"><?= count($employees) ?> results</span></h5>
<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th><?= sortLink('employee_id', 'ID',        $sortCol, $nextDir, $search, $filterJobTitle, $filterStatus) ?></th>
            <th><?= sortLink('full_name',   'Full Name', $sortCol, $nextDir, $search, $filterJobTitle, $filterStatus) ?></th>
            <th>Branch</th>
            <th><?= sortLink('job_title',   'Job Title', $sortCol, $nextDir, $search, $filterJobTitle, $filterStatus) ?></th>
            <th>Email</th>
            <th>Phone</th>
            <th><?= sortLink('hire_date',   'Hire Date', $sortCol, $nextDir, $search, $filterJobTitle, $filterStatus) ?></th>
            <th><?= sortLink('status',      'Status',    $sortCol, $nextDir, $search, $filterJobTitle, $filterStatus) ?></th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($employees) === 0): ?>
        <tr><td colspan="9" class="text-center text-muted">No employees found.</td></tr>
        <?php endif; ?>
        <?php foreach ($employees as $emp): ?>
        <tr>
            <td><?= htmlspecialchars($emp['employee_id']) ?></td>
            <td><?= htmlspecialchars($emp['full_name']) ?></td>
            <td><?= htmlspecialchars($emp['branch_name'] ?? '-') ?></td>
            <td><?= htmlspecialchars($emp['job_title']) ?></td>
            <td><?= htmlspecialchars($emp['email'] ?? '-') ?></td>
            <td><?= htmlspecialchars($emp['phone'] ?? '-') ?></td>
            <td><?= htmlspecialchars($emp['hire_date']) ?></td>
            <td>
                <?php
                    $empStatus = $emp['status'] ?? 'ACTIVE';
                    $empBadge  = $empStatus === 'ACTIVE' ? 'bg-success' : 'bg-secondary';
                ?>
                <span class="badge <?= $empBadge ?>"><?= htmlspecialchars($empStatus) ?></span>
            </td>
            <td>
                <a href="/neobank/?page=employees&edit=<?= $emp['employee_id'] ?>" class="btn btn-sm btn-warning me-1">Edit</a>
                <a href="/neobank/?page=employees&toggle_status=<?= $emp['employee_id'] ?>"
                   class="btn btn-sm <?= ($emp['status'] ?? 'ACTIVE') === 'ACTIVE' ? 'btn-secondary' : 'btn-success' ?>"
                   onclick="return confirm('<?= ($emp['status'] ?? 'ACTIVE') === 'ACTIVE' ? 'Deactivate' : 'Activate' ?> this employee?')">
                    <?= ($emp['status'] ?? 'ACTIVE') === 'ACTIVE' ? 'Deactivate' : 'Activate' ?>
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>