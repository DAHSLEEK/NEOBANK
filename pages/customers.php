<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('Teller');
require_once __DIR__ . '/../config/db.php';
$pdo = getDBConnection();

$editCustomer = null;
$message = '';

// Handle Add / Update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        // UPDATE existing customer
        $stmt = $pdo->prepare("
            UPDATE CUSTOMER SET customer_name = ?, date_of_birth = ?, customer_type = ?,
                gender = ?, nationality = ?, occupation = ?, id_type = ?, id_number = ?
            WHERE customer_id = ?
        ");
        $stmt->execute([$customer_name, $date_of_birth, $customer_type, $gender,
                         $nationality, $occupation, $id_type, $id_number, $customer_id]);

        // Update or insert CONTACT row for this customer
        $check = $pdo->prepare("SELECT contact_id FROM CONTACT WHERE customer_id = ?");
        $check->execute([$customer_id]);
        if ($check->fetch()) {
            $stmt = $pdo->prepare("
                UPDATE CONTACT SET email = ?, phone = ?, mobile = ?, address = ?, postcode = ?, country = ?
                WHERE customer_id = ?
            ");
            $stmt->execute([$email, $phone, $mobile, $address, $postcode, $country, $customer_id]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO CONTACT (customer_id, email, phone, mobile, address, postcode, country)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$customer_id, $email, $phone, $mobile, $address, $postcode, $country]);
        }
        $message = "Customer updated successfully.";
    } else {
        // INSERT new customer
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
        $message = "Customer added successfully.";
    }
}

// Handle Edit link click - load existing customer + contact into the form
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

// Fetch all customers with their contact info for the table
$customers = $pdo->query("
    SELECT c.*, co.email, co.phone
    FROM CUSTOMER c
    LEFT JOIN CONTACT co ON co.customer_id = c.customer_id
    ORDER BY c.customer_id DESC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
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
        <form method="POST">
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
                        <option value="Male" <?= ($editCustomer['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
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
                        <option value="Passport" <?= ($editCustomer['id_type'] ?? '') === 'Passport' ? 'selected' : '' ?>>Passport</option>
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
                <a href="customers.php" class="btn btn-secondary mt-3">Cancel</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Customer List -->
<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Type</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($customers as $cust): ?>
        <tr>
            <td><?= htmlspecialchars($cust['customer_id']) ?></td>
            <td><?= htmlspecialchars($cust['customer_name']) ?></td>
            <td><?= htmlspecialchars($cust['customer_type']) ?></td>
            <td><?= htmlspecialchars($cust['email'] ?? '-') ?></td>
            <td><?= htmlspecialchars($cust['phone'] ?? '-') ?></td>
            <td>
                <a href="?edit=<?= $cust['customer_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>