<?php require_once __DIR__ . '/../includes/header.php'; ?>

<h1>Welcome to NeoBank, <?= htmlspecialchars($_SESSION['full_name']) ?></h1>
<p class="text-muted">You are logged in as <strong><?= htmlspecialchars($_SESSION['role']) ?></strong>.</p>

<div class="row mt-4">
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-dark">
            <div class="card-body">
                <h5 class="card-title">Customers</h5>
                <p class="card-text">View and manage customer records.</p>
                <a href="/neobank/?page=customers" class="btn btn-light btn-sm">Go</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-dark">
            <div class="card-body">
                <h5 class="card-title">Accounts</h5>
                <p class="card-text">Open and manage customer accounts.</p>
                <a href="/neobank/?page=accounts" class="btn btn-light btn-sm">Go</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-dark">
            <div class="card-body">
                <h5 class="card-title">Transactions</h5>
                <p class="card-text">Process and authorise transactions.</p>
                <a href="/neobank/?page=transactions" class="btn btn-light btn-sm">Go</a>
            </div>
        </div>
    </div>

    <?php if (in_array($_SESSION['role'], ['Admin', 'Branch Manager', 'Compliance Officer'])): ?>
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-secondary">
            <div class="card-body">
                <h5 class="card-title">Branches</h5>
                <p class="card-text">View and manage branch records.</p>
                <a href="/neobank/?page=branches" class="btn btn-light btn-sm">Go</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-secondary">
            <div class="card-body">
                <h5 class="card-title">Employees</h5>
                <p class="card-text">View and manage employee records.</p>
                <a href="/neobank/?page=employees" class="btn btn-light btn-sm">Go</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-secondary">
            <div class="card-body">
                <h5 class="card-title">Reports</h5>
                <p class="card-text">Generate account, branch and transaction reports.</p>
                <a href="/neobank/?page=reports" class="btn btn-light btn-sm">Go</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>