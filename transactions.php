<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Fetch all transactions as before
$stmt = $pdo->prepare("
    SELECT t.*, 
           u_from.name as from_name,
           u_to.name as to_name
    FROM transactions t
    LEFT JOIN users u_from ON t.from_account = u_from.account_number
    LEFT JOIN users u_to ON t.to_account = u_to.account_number
    WHERE t.from_account = ? OR t.to_account = ?
    ORDER BY t.created_at DESC
");
$stmt->execute([$_SESSION['user_account'], $_SESSION['user_account']]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History - Banking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; }
        .card { border-radius: 15px; }
        .filter-btn.active { background-color: #667eea; color: white; border-color: #667eea; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">🏦 Banking System</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-white">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-history"></i> Transaction History</h4>
                    <div class="btn-group mt-2 mt-sm-0" role="group">
                        <button class="btn btn-outline-primary filter-btn active" data-filter="all">All</button>
                        <button class="btn btn-outline-success filter-btn" data-filter="deposit">Deposits</button>
                        <button class="btn btn-outline-danger filter-btn" data-filter="withdraw">Withdrawals</button>
                        <button class="btn btn-outline-info filter-btn" data-filter="transfer">Transfers</button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (count($transactions) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="transactionTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Type</th>
                                    <th>Reference</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $trans): ?>
                                    <?php
                                    $isCredit = false;
                                    $description = '';
                                    $reference = '';
                                    $typeClass = '';
                                    
                                    if ($trans['type'] == 'deposit' && $trans['to_account'] == $_SESSION['user_account']) {
                                        $isCredit = true;
                                        $description = 'Deposit';
                                        $reference = 'Cash Deposit';
                                        $typeClass = 'deposit';
                                    } elseif ($trans['type'] == 'withdraw' && $trans['from_account'] == $_SESSION['user_account']) {
                                        $isCredit = false;
                                        $description = 'Withdrawal';
                                        $reference = 'Cash Withdrawal';
                                        $typeClass = 'withdraw';
                                    } elseif ($trans['type'] == 'transfer') {
                                        if ($trans['from_account'] == $_SESSION['user_account']) {
                                            $isCredit = false;
                                            $description = 'Transfer Sent';
                                            $reference = 'To: ' . ($trans['to_name'] ?? $trans['to_account']);
                                            $typeClass = 'transfer';
                                        } else {
                                            $isCredit = true;
                                            $description = 'Transfer Received';
                                            $reference = 'From: ' . ($trans['from_name'] ?? $trans['from_account']);
                                            $typeClass = 'transfer';
                                        }
                                    }
                                    ?>
                                    <tr class="transaction-row <?= $typeClass ?>">
                                        <td><?= date('M d, Y H:i:s', strtotime($trans['created_at'])) ?></td>
                                        <td><?= htmlspecialchars($description) ?></td>
                                        <td class="<?= $isCredit ? 'text-success' : 'text-danger' ?> fw-bold">
                                            <?= $isCredit ? '+' : '-' ?>$<?= number_format($trans['amount'], 2) ?>
                                        </td>
                                        <td><span class="badge bg-secondary"><?= ucfirst($trans['type']) ?></span></td>
                                        <td><small><?= htmlspecialchars($reference) ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center p-5 text-muted">
                        <i class="fas fa-receipt fa-3x mb-3"></i>
                        <p>No transactions found. Start by making a deposit or transfer.</p>
                        <a href="deposit.php" class="btn btn-primary btn-sm">Make a Deposit</a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-white text-end">
                <small>Total transactions: <?= count($transactions) ?></small>
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm ms-3">Back to Dashboard</a>
            </div>
        </div>
    </div>

    <!-- JavaScript for filtering -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const rows = document.querySelectorAll('.transaction-row');

            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');

                    const filterValue = this.getAttribute('data-filter');

                    rows.forEach(row => {
                        if (filterValue === 'all') {
                            row.style.display = '';
                        } else {
                            if (row.classList.contains(filterValue)) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        }
                    });
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>