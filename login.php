<?php if (isset($_GET['error'])): ?>

<div class="alert-error-login">
    <?= htmlspecialchars($_GET['error']); ?>
</div>

<?php endif; ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Inventory</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

<div class="login-container">

    <div class="login-card">

        <div class="login-header">
            <img src="assets/logoyh1.jpeg" class="login-logo">
            <h2>Inventory System</h2>
            <p>Silakan login untuk melanjutkan</p>
        </div>

        <form action="proses_login.php" method="POST" class="login-form">

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" class="btn-login">
                Login
            </button>

        </form>

    </div>

</div>

</body>
</html>