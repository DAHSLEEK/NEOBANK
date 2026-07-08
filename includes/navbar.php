<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="/neobank/">NeoBank</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="/neobank/?page=customers">Customers</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/neobank/?page=accounts">Accounts</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/neobank/?page=transactions">Transactions</a>
        </li>
        <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['Admin', 'Branch Manager', 'Compliance Officer'])): ?>
        <li class="nav-item">
          <a class="nav-link" href="/neobank/?page=branches">Branches</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/neobank/?page=employees">Employees</a>
        </li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav ms-auto">
        <?php if (isset($_SESSION['full_name'])): ?>
        <li class="nav-item">
          <span class="nav-link text-light">
            <?= htmlspecialchars($_SESSION['full_name']) ?>
            <span class="badge bg-secondary ms-1"><?= htmlspecialchars($_SESSION['role']) ?></span>
          </span>
        </li>
        <li class="nav-item">
          <a class="nav-link text-warning" href="/neobank/logout.php">Logout</a>
        </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>