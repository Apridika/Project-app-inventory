<?php
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function redirectTo(string $path): void
{
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}

function requireLogin(): void
{
    if (empty($_SESSION['user_id'])) {
        redirectTo('login.php');
    }
}

function requireRole(array $allowedRoles): void
{
    requireLogin();

    $role = $_SESSION['role'] ?? '';

    if (!in_array($role, $allowedRoles, true)) {
        redirectTo('dashboard.php?error=' . urlencode('Akses ditolak'));
    }
}