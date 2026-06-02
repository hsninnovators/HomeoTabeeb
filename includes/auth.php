<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function current_user(): ?array { return $_SESSION['admin_user'] ?? null; }
function login_admin(string $email, string $password): bool
{
    $user = db_fetch('SELECT * FROM users WHERE email=? AND status="active" LIMIT 1', [$email]);
    if (!$user || !password_verify($password, $user['password_hash'])) return false;
    session_regenerate_id(true); $_SESSION['admin_user'] = ['id'=>(int)$user['id'],'name'=>$user['name'],'email'=>$user['email'],'role'=>$user['role']]; $_SESSION['last_seen'] = time();
    log_activity((int)$user['id'], 'login', ['email'=>$email]); return true;
}
function logout_admin(): void { $id = current_user()['id'] ?? null; $_SESSION = []; session_destroy(); if ($id) log_activity((int)$id, 'logout'); }
function require_login(array $roles = []): void
{
    if (!current_user()) redirect('login.php');
    if ((time() - ($_SESSION['last_seen'] ?? 0)) > ADMIN_SESSION_TIMEOUT) { logout_admin(); redirect('login.php?timeout=1'); }
    $_SESSION['last_seen'] = time();
    if ($roles && !in_array(current_user()['role'], $roles, true) && current_user()['role'] !== 'super_admin') { http_response_code(403); exit('Access denied.'); }
}
function can_manage(string $area): bool
{
    $role = current_user()['role'] ?? '';
    $map = ['orders'=>['super_admin','admin','manager'], 'medical'=>['super_admin','admin','doctor'], 'settings'=>['super_admin','admin']];
    return in_array($role, $map[$area] ?? ['super_admin'], true);
}
