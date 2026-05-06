<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        setMessage('Invalid security token.', 'danger');
        redirect('withdraw.php');
    }

    $amount = floatval($_POST['amount'] ?? 0);
    if ($amount <= 0) {
        setMessage('Please enter a valid positive amount.', 'danger');
        redirect('withdraw.php');
    }
    if ($amount > $_SESSION['user_balance']) {
        setMessage('Insufficient balance.', 'danger');
        redirect('withdraw.php');
    }

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE account_number = ? AND balance >= ?");
        $stmt->execute([$amount, $_SESSION['user_account'], $amount]);
        if ($stmt->rowCount() == 0) throw new Exception('Insufficient balance.');
        $stmt = $pdo->prepare("INSERT INTO transactions (from_account, to_account, amount, type, description) VALUES (?, NULL, ?, 'withdraw', 'Cash Withdrawal')");
        $stmt->execute([$_SESSION['user_account'], $amount]);
        $pdo->commit();
        $_SESSION['user_balance'] -= $amount;
        setMessage("Successfully withdrew $" . number_format($amount, 2), 'success');
    } catch (Exception $e) {
        $pdo->rollBack();
        setMessage('Withdrawal failed: ' . $e->getMessage(), 'danger');
    }
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Withdraw - Banking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark"><div class="container"><a class="navbar-brand" href="dashboard.php">🏦 Banking System</a><a class="nav-link text-light" href="dashboard.php">← Back</a></div></nav>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card p-4">
                    <h3 class="text-center mb-4">Withdraw Money</h3>
                    <div class="alert alert-info">Available Balance: $<?= number_format($_SESSION['user_balance'], 2) ?></div>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <div class="mb-3"><label>Amount to Withdraw</label><div class="input-group"><span class="input-group-text">$</span><input type="number" step="0.01" name="amount" class="form-control" required></div></div>
                        <button type="submit" class="btn btn-warning w-100">Process Withdrawal</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>