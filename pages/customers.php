<?php
require_once __DIR__ . '/../config/db.php';
$pdo = getDBConnection();

$editCustomer = null;
$message = '';

// Handle Add / Update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $customer_id   = $_POST['customer_id'] ?? null;
    $customer_name = trim($_POST['customer_name']);
    $date_of_birth = $_POST['date_of_birth'];
    $customer_type = $_POST['customer_type'];
    $gender        = $_POST['gender'];
    $nationality   = trim($_POST['nationality']);
    $occupation    = trim($_POST['occupation']);
    $id_type       = $_POST['id_type'];
    $id_number     = trim($_POST['id_number']);
    $email         = trim($_POST['email']);
    $phone         = trim($_POST['phone']);
    $mobile        = trim($_POST['mobile']);
    $address       = trim($_POST['address']);
    $postcode      = trim($_POST['postcode']);
    $country       = trim($_POST['country']);

    if ($customer_id) {
        // Fetch existing values for audit before updating
        $oldStmt = $pdo->prepare("
            SELECT customer_name, date_of_birth, customer_type, gender,
                   nationality, occupation, id_type, id_number
            FROM CUSTOMER WHERE customer_id = ?
        ");
        $oldStmt->execute([$customer_id]);
        $oldData = $oldStmt->fetch();

        $stmt = $pdo->prepare("
            UPDATE CUSTOMER SET customer_name = ?, date_of_birth = ?, customer_type = ?,
                gender = ?, nationality = ?, occupation = ?, id_type = ?, id_number = ?
            WHERE customer_id = ?
        ");
        $stmt->execute([$customer_name, $date_of_birth, $customer_type, $gender,
                         $nationality, $occupation, $id_type, $id_number, $customer_id]);

        // Write audit record for customer update
        auditModification($pdo, 'CUSTOMER', (int)$customer_id, 'UPDATE', $oldData, [
            'customer_name' => $customer_name,
            'date_of_birth' => $date_of_birth,
            'customer_type' => $customer_type,
            'gender'        => $gender,
            'nationality'   => $nationality,
            'occupation'    => $occupation,
            'id_type'       => $id_type,
            'id_number'     => $id_number,
        ]);

        // Update or insert CONTACT row
        $check = $pdo->prepare("SELECT contact_id FROM CONTACT WHERE customer_id = ?");
        $check->execute([$customer_id]);
        if ($check->fetch()) {
            // Fetch old contact values for audit
            $oldContactStmt = $pdo->prepare("
                SELECT email, phone, mobile, address, postcode, country
                FROM CONTACT WHERE customer_id = ?
            ");
            $oldContactStmt->execute([$customer_id]);
            $oldContact = $oldContactStmt->fetch();

            $stmt = $pdo->prepare("
                UPDATE CONTACT SET email = ?, phone = ?, mobile = ?, address = ?, postcode = ?, country = ?
                WHERE customer_id = ?
            ");
            $stmt->execute([$email, $phone, $mobile, $address, $postcode, $country, $customer_id]);

            auditModification($pdo, 'CONTACT', (int)$customer_id, 'UPDATE', $oldContact, [
                'email'    => $email,
                'phone'    => $phone,
                'mobile'   => $mobile,
                'address'  => $address,
                'postcode' => $postcode,
                'country'  => $country,
            ]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO CONTACT (customer_id, email, phone, mobile, address, postcode, country)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$customer_id, $email, $phone, $mobile, $address, $postcode, $country]);

            auditModification($pdo, 'CONTACT', (int)$customer_id, 'INSERT', null, [
                'email'    => $email,
                'phone'    => $phone,
                'mobile'   => $mobile,
                'address'  => $address,
                'postcode' => $postcode,
                'country'  => $country,
            ]);
        }
        $message = "Customer updated successfully.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO CUSTOMER (customer_name, date_of_birth, customer_type, gender,
                nationality, occupation, id_type, id_number)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$customer_name, $date_of_birth, $customer_type, $gender,
                         $nationality, $occupation, $id_type, $id_number]);
        $newCustomerId = $pdo->lastInsertId();

        $stmt = $pdo->prepare("
            INSERT INTO CONTACT (customer_id, email, phone, mobile, address, postcode, country)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$newCustomerId, $email, $phone, $mobile, $address, $postcode, $country]);

        auditModification($pdo, 'CUSTOMER', (int)$newCustomerId, 'INSERT', null, [
            'customer_name' => $customer_name,
            'date_of_birth' => $date_of_birth,
            'customer_type' => $customer_type,
            'gender'        => $gender,
            'nationality'   => $nationality,
            'occupation'    => $occupation,
            'id_type'       => $id_type,
            'id_number'     => $id_number,
        ]);
        $message = "Customer added successfully.";
    }
}

// Handle Edit link click
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("
        SELECT c.*, co.email, co.phone, co.mobile, co.address, co.postcode, co.country
        FROM CUSTOMER c
        LEFT JOIN CONTACT co ON co.customer_id = c.customer_id
        WHERE c.customer_id = ?
    ");
    $stmt->execute([$_GET['edit']]);
    $editCustomer = $stmt->fetch();
}

// Search and sort parameters
$search     = trim($_GET['search'] ?? '');
$filterType = $_GET['filter_type'] ?? '';
$sortCol    = $_GET['sort'] ?? 'customer_id';
$sortDir    = $_GET['dir'] ?? 'desc';

$allowedSorts = ['customer_id', 'customer_name', 'customer_type', 'date_of_birth', 'nationality'];
if (!in_array($sortCol, $allowedSorts)) $sortCol = 'customer_id';
$sortDir = $sortDir === 'asc' ? 'asc' : 'desc';
$nextDir = $sortDir === 'asc' ? 'desc' : 'asc';

$whereParts = ['1=1'];
$params     = [];

if ($search !== '') {
    $whereParts[] = "(c.customer_name LIKE ? OR c.id_number LIKE ? OR c.nationality LIKE ? OR c.occupation LIKE ? OR co.email LIKE ? OR co.phone LIKE ?)";
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like, $like, $like, $like);
}
if ($filterType !== '') {
    $whereParts[] = "c.customer_type = ?";
    $params[]     = $filterType;
}

$whereSQL = 'WHERE ' . implode(' AND ', $whereParts);

$custStmt = $pdo->prepare("
    SELECT c.*, co.email, co.phone
    FROM CUSTOMER c
    LEFT JOIN CONTACT co ON co.customer_id = c.customer_id
    {$whereSQL}
    ORDER BY c.{$sortCol} {$sortDir}
");
$custStmt->execute($params);
$customers = $custStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';

function sortLink(string $col, string $label, string $currentCol, string $nextDir, string $search, string $filterType): string {
    $arrow  = $currentCol === $col ? ' &#8597;' : '';
    $params = http_build_query(['page' => 'customers', 'sort' => $col, 'dir' => $nextDir, 'search' => $search, 'filter_type' => $filterType]);
    return "<a href='/neobank/?{$params}' class='text-decoration-none text-dark'>{$label}{$arrow}</a>";
}
?>

<h1>Customer Management</h1>

<?php if ($message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- Add / Edit Form -->
<div class="card mb-4">
    <div class="card-header">
        <?= $editCustomer ? 'Edit Customer' : 'Add New Customer' ?>
    </div>
    <div class="card-body">
        <form method="POST" action="/neobank/?page=customers">
            <?= csrfField() ?>
            <?php if ($editCustomer): ?>
                <input type="hidden" name="customer_id" value="<?= htmlspecialchars($editCustomer['customer_id']) ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="customer_name" class="form-control" required
                           value="<?= htmlspecialchars($editCustomer['customer_name'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control" required
                           value="<?= htmlspecialchars($editCustomer['date_of_birth'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Customer Type</label>
                    <select name="customer_type" class="form-control" required>
                        <option value="Personal" <?= ($editCustomer['customer_type'] ?? '') === 'Personal' ? 'selected' : '' ?>>Personal</option>
                        <option value="Business" <?= ($editCustomer['customer_type'] ?? '') === 'Business' ? 'selected' : '' ?>>Business</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-control">
                        <option value="Male"   <?= ($editCustomer['gender'] ?? '') === 'Male'   ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= ($editCustomer['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nationality</label>
                    <input type="text" name="nationality" class="form-control"
                           value="<?= htmlspecialchars($editCustomer['nationality'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Occupation</label>
                    <input type="text" name="occupation" class="form-control"
                           value="<?= htmlspecialchars($editCustomer['occupation'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">ID Type</label>
                    <select name="id_type" class="form-control" required>
                        <option value="Passport"        <?= ($editCustomer['id_type'] ?? '') === 'Passport'        ? 'selected' : '' ?>>Passport</option>
                        <option value="Driving Licence" <?= ($editCustomer['id_type'] ?? '') === 'Driving Licence' ? 'selected' : '' ?>>Driving Licence</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">ID Number</label>
                    <input type="text" name="id_number" class="form-control" required
                           value="<?= htmlspecialchars($editCustomer['id_number'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($editCustomer['email'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control"
                           value="<?= htmlspecialchars($editCustomer['phone'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Mobile</label>
                    <input type="text" name="mobile" class="form-control"
                           value="<?= htmlspecialchars($editCustomer['mobile'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control"
                           value="<?= htmlspecialchars($editCustomer['address'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Postcode</label>
                    <input type="text" name="postcode" class="form-control"
                           value="<?= htmlspecialchars($editCustomer['postcode'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Country</label>
                    <input type="text" name="country" class="form-control"
                           value="<?= htmlspecialchars($editCustomer['country'] ?? '') ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">
                <?= $editCustomer ? 'Update Customer' : 'Add Customer' ?>
            </button>
            <?php if ($editCustomer): ?>
                <a href="/neobank/?page=customers" class="btn btn-secondary mt-3">Cancel</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Search and Filter -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="/neobank/" class="row g-2 align-items-end">
            <input type="hidden" name="page" value="customers">
            <div class="col-md-5">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control"
                       placeholder="Name, ID number, nationality, occupation, email, phone..."
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Customer Type</label>
                <select name="filter_type" class="form-control">
                    <option value="">All Types</option>
                    <option value="Personal" <?= $filterType === 'Personal' ? 'selected' : '' ?>>Personal</option>
                    <option value="Business" <?= $filterType === 'Business' ? 'selected' : '' ?>>Business</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
            <div class="col-md-2">
                <a href="/neobank/?page=customers" class="btn btn-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Customer List -->
<h5 class="mb-3">
    Customers
    <span class="badge bg-secondary"><?= count($customers) ?> results</span>
</h5>
<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th><?= sortLink('customer_id',   'ID',           $sortCol, $nextDir, $search, $filterType) ?></th>
            <th><?= sortLink('customer_name', 'Name',         $sortCol, $nextDir, $search, $filterType) ?></th>
            <th><?= sortLink('customer_type', 'Type',         $sortCol, $nextDir, $search, $filterType) ?></th>
            <th><?= sortLink('nationality',   'Nationality',  $sortCol, $nextDir, $search, $filterType) ?></th>
            <th>Email</th>
            <th>Phone</th>
            <th><?= sortLink('date_of_birth', 'Date of Birth',$sortCol, $nextDir, $search, $filterType) ?></th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($customers) === 0): ?>
        <tr><td colspan="8" class="text-center text-muted">No customers found.</td></tr>
        <?php endif; ?>
        <?php foreach ($customers as $cust): ?>
        <tr>
            <td><?= htmlspecialchars($cust['customer_id']) ?></td>
            <td><?= htmlspecialchars($cust['customer_name']) ?></td>
            <td><?= htmlspecialchars($cust['customer_type']) ?></td>
            <td><?= htmlspecialchars($cust['nationality'] ?? '-') ?></td>
            <td><?= htmlspecialchars($cust['email'] ?? '-') ?></td>
            <td><?= htmlspecialchars($cust['phone'] ?? '-') ?></td>
            <td><?= htmlspecialchars($cust['date_of_birth']) ?></td>
            <td>
                <a href="/neobank/?page=customers&edit=<?= $cust['customer_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>