<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Update balance in session
$stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$_SESSION['user_balance'] = $stmt->fetchColumn();

// Get recent transactions
$stmt = $pdo->prepare("
    SELECT t.*, 
           u_from.name as from_name,
           u_to.name as to_name
    FROM transactions t
    LEFT JOIN users u_from ON t.from_account = u_from.account_number
    LEFT JOIN users u_to ON t.to_account = u_to.account_number
    WHERE t.from_account = ? OR t.to_account = ?
    ORDER BY t.created_at DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_account'], $_SESSION['user_account']]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Banking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; }
        .balance-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px; }
        .stat-card { border-radius: 15px; transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">🏦 Banking System</a>
            <div class="navbar-nav ms-auto">
                <span class="nav-link text-light">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php $msg = getMessage(); if ($msg): ?>
            <div class="alert alert-<?= $msg['type'] ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($msg['text']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card balance-card p-4">
                    <h5>Current Balance</h5>
                    <h2 class="display-5 fw-bold">$<?= number_format($_SESSION['user_balance'], 2) ?></h2>
                    <small>Account: <?= htmlspecialchars($_SESSION['user_account']) ?></small>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card stat-card p-4 bg-white">
                    <h5>Quick Actions</h5>
                    <div class="d-grid gap-2 d-md-flex">
                        <a href="deposit.php" class="btn btn-success me-md-2"><i class="fas fa-plus-circle"></i> Deposit</a>
                        <a href="withdraw.php" class="btn btn-warning me-md-2"><i class="fas fa-minus-circle"></i> Withdraw</a>
                        <a href="transfer.php" class="btn btn-info"><i class="fas fa-exchange-alt"></i> Transfer</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-white"><h5>Recent Transactions</h5></div>
            <div class="card-body p-0">
                <?php if (count($transactions) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light"><tr><th>Date</th><th>Description</th><th>Amount</th><th>Type</th></tr></thead>
                            <tbody>
                                <?php foreach ($transactions as $trans): ?>
                                    <?php
                                    $isCredit = false;
                                    $description = '';
                                    if ($trans['type'] == 'deposit' && $trans['to_account'] == $_SESSION['user_account']) {
                                        $isCredit = true; $description = 'Deposit';
                                    } elseif ($trans['type'] == 'withdraw' && $trans['from_account'] == $_SESSION['user_account']) {
                                        $isCredit = false; $description = 'Withdrawal';
                                    } elseif ($trans['type'] == 'transfer') {
                                        if ($trans['from_account'] == $_SESSION['user_account']) {
                                            $isCredit = false; $description = 'Transfer to ' . ($trans['to_name'] ?? $trans['to_account']);
                                        } else {
                                            $isCredit = true; $description = 'Transfer from ' . ($trans['from_name'] ?? $trans['from_account']);
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td><?= date('M d, H:i', strtotime($trans['created_at'])) ?></td>
                                        <td><?= htmlspecialchars($description) ?></td>
                                        <td class="<?= $isCredit ? 'text-success' : 'text-danger' ?> fw-bold"><?= $isCredit ? '+' : '-' ?>$<?= number_format($trans['amount'], 2) ?></td>
                                        <td><span class="badge bg-secondary"><?= ucfirst($trans['type']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center p-4 text-muted">No transactions yet.</div>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-white text-end"><a href="transactions.php" class="btn btn-sm btn-outline-primary">View All Transactions</a></div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>