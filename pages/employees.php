<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('Compliance Officer');
require_once __DIR__ . '/../config/db.php';
$pdo = getDBConnection();

$editEmployee = null;
$message = '';

// Handle Add / Update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'] ?? null;
    $branch_id   = $_POST['branch_id'];
    $full_name   = trim($_POST['full_name']);
    $role        = trim($_POST['role']);
    $hire_date   = $_POST['hire_date'];
    $email       = trim($_POST['email'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $mobile      = trim($_POST['mobile'] ?? '');
    $address     = trim($_POST['address'] ?? '');
    $postcode    = trim($_POST['postcode'] ?? '');
    $country     = trim($_POST['country'] ?? 'United Kingdom');

    if ($employee_id) {
        $stmt = $pdo->prepare("
            UPDATE EMPLOYEE SET branch_id = ?, full_name = ?, role = ?, hire_date = ?
            WHERE employee_id = ?
        ");
        $stmt->execute([$branch_id, $full_name, $role, $hire_date, $employee_id]);

        // Update or insert CONTACT row for this employee
        $check = $pdo->prepare("SELECT contact_id FROM CONTACT WHERE employee_id = ?");
        $check->execute([$employee_id]);
        if ($check->fetch()) {
            $stmt = $pdo->prepare("
                UPDATE CONTACT SET email = ?, phone = ?, mobile = ?, address = ?, postcode = ?, country = ?
                WHERE employee_id = ?
            ");
            $stmt->execute([$email, $phone, $mobile, $address, $postcode, $country, $employee_id]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO CONTACT (employee_id, email, phone, mobile, address, postcode, country)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$employee_id, $email, $phone, $mobile, $address, $postcode, $country]);
        }
        $message = "Employee updated successfully.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO EMPLOYEE (branch_id, full_name, role, hire_date)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$branch_id, $full_name, $role, $hire_date]);
        $newEmployeeId = $pdo->lastInsertId();

        $stmt = $pdo->prepare("
            INSERT INTO CONTACT (employee_id, email, phone, mobile, address, postcode, country)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$newEmployeeId, $email, $phone, $mobile, $address, $postcode, $country]);

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

// Dropdown data
$branches = $pdo->query("SELECT branch_id, branch_name FROM BRANCH ORDER BY branch_name")->fetchAll();

// Fetch all employees with branch name and contact info
$employees = $pdo->query("
    SELECT e.*, b.branch_name, co.email, co.phone
    FROM EMPLOYEE e
    LEFT JOIN BRANCH b ON b.branch_id = e.branch_id
    LEFT JOIN CONTACT co ON co.employee_id = e.employee_id
    ORDER BY e.employee_id ASC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<h1>Employee Management</h1>

<?php if ($message): ?>
    <div class="alert <?= str_starts_with($message, 'Error:') ? 'alert-danger' : 'alert-success' ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- Add / Edit Form -->
<div class="card mb-4">
    <div class="card-header">
        <?= $editEmployee ? 'Edit Employee' : 'Add New Employee' ?>
    </div>
    <div class="card-body">
        <form method="POST">
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
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control" required>
                        <option value="Branch Manager"    <?= ($editEmployee['role'] ?? '') === 'Branch Manager'    ? 'selected' : '' ?>>Branch Manager</option>
                        <option value="Customer Advisor"  <?= ($editEmployee['role'] ?? '') === 'Customer Advisor'  ? 'selected' : '' ?>>Customer Advisor</option>
                        <option value="Loans Officer"     <?= ($editEmployee['role'] ?? '') === 'Loans Officer'     ? 'selected' : '' ?>>Loans Officer</option>
                        <option value="Compliance Officer"<?= ($editEmployee['role'] ?? '') === 'Compliance Officer'? 'selected' : '' ?>>Compliance Officer</option>
                        <option value="Teller"            <?= ($editEmployee['role'] ?? '') === 'Teller'            ? 'selected' : '' ?>>Teller</option>
                        <option value="Admin"             <?= ($editEmployee['role'] ?? '') === 'Admin'             ? 'selected' : '' ?>>Admin</option>
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
                <a href="employees.php" class="btn btn-secondary mt-3">Cancel</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Employee List -->
<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Branch</th>
            <th>Role</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Hire Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($employees as $emp): ?>
        <tr>
            <td><?= htmlspecialchars($emp['employee_id']) ?></td>
            <td><?= htmlspecialchars($emp['full_name']) ?></td>
            <td><?= htmlspecialchars($emp['branch_name'] ?? '-') ?></td>
            <td><?= htmlspecialchars($emp['role']) ?></td>
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
                <a href="?edit=<?= $emp['employee_id'] ?>" class="btn btn-sm btn-warning me-1">Edit</a>
                <a href="?toggle_status=<?= $emp['employee_id'] ?>"
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