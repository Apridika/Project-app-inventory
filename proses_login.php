<?php
require_once 'includes/koneksi.php';
require_once 'includes/auth_check.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    redirectTo('login.php?error=' . urlencode('Username dan password wajib diisi'));
}

$stmt = mysqli_prepare($conn, "SELECT id, name, username, password, role FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    redirectTo('dashboard.php');
} else {
    redirectTo('login.php?error=' . urlencode('Username atau password salah'));
}