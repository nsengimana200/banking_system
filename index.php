<?php
require_once 'config.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .card { border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .btn-primary { background: #667eea; border: none; }
        .btn-primary:hover { background: #5a67d8; }
    </style>
</head>
<body class="d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center mb-5">
                <h1 class="display-4 text-white fw-bold">Secure Banking System</h1>
                <p class="lead text-white">Manage your finances with ease and security</p>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card p-4">
                    <h3 class="text-center mb-4">Welcome</h3>
                    <div class="d-grid gap-3">
                        <a href="login.php" class="btn btn-primary btn-lg">Login</a>
                        <a href="register.php" class="btn btn-outline-secondary btn-lg">Create New Account</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>