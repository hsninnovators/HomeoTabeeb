<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

function e(?string $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function redirect(string $path): never { header('Location: ' . $path); exit; }
function current_ip(): string { return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; }
function json_encode_safe(mixed $data): string { return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}'; }
function json_decode_safe(?string $json, mixed $default = []): mixed { $d = json_decode((string)$json, true); return json_last_error() === JSON_ERROR_NONE ? $d : $default; }

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function verify_csrf(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403); exit('Invalid CSRF token.');
    }
}
function generate_code(string $prefix): string { return $prefix . '-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3))); }
function normalize_text(string $text): string { return trim(mb_strtolower(preg_replace('/\s+/u', ' ', $text))); }
function excerpt(string $text, int $limit = 360): string { return mb_strlen($text) <= $limit ? $text : mb_substr($text, 0, $limit) . '…'; }
function setting(string $key, ?string $default = null): ?string { $row = db_fetch('SELECT setting_value FROM system_settings WHERE setting_key=?', [$key]); return $row['setting_value'] ?? $default; }
function log_activity(?int $userId, string $action, array $details = []): void
{
    try { db_execute('INSERT INTO activity_logs (user_id, action, details_json, ip_address, created_at) VALUES (?,?,?,?,NOW())', [$userId, $action, json_encode_safe($details), current_ip()]); }
    catch (Throwable $e) { error_log('activity log failed: ' . $e->getMessage()); }
}
function public_header(string $title): void { ?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= e($title) ?> | <?= SITE_NAME ?></title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="assets/css/style.css" rel="stylesheet"></head><body><nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top"><div class="container"><a class="navbar-brand fw-bold text-teal" href="index.php">✚ <?= SITE_NAME ?></a><button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button><div class="collapse navbar-collapse" id="nav"><div class="navbar-nav ms-auto"><a class="nav-link" href="index.php#how">How it works</a><a class="nav-link" href="track-case.php">Track Case</a><a class="btn btn-teal ms-lg-2" href="consultation-form.php">Start Consultation</a></div></div></div></nav><main><?php }
function public_footer(): void { ?></main><footer class="footer py-4 mt-5"><div class="container d-flex flex-column flex-md-row justify-content-between gap-2"><div><strong><?= SITE_NAME ?></strong><br><small>AI-Powered Homeopathic Doctor. Urdu/Roman Urdu supported.</small></div><div><a href="privacy-policy.php">Privacy Policy</a> · <a href="terms.php">Terms</a> · <a href="admin/login.php">Admin</a></div></div></footer><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script><script src="assets/js/app.js"></script></body></html><?php }
function admin_header(string $title): void { require_once __DIR__ . '/auth.php'; require_login(); $u = current_user(); ?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= e($title) ?> | Admin</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="../assets/css/style.css" rel="stylesheet"></head><body><div class="admin-shell"><aside class="admin-sidebar"><a class="brand" href="dashboard.php">✚ <?= SITE_NAME ?></a><small class="text-white-50 d-block mb-3"><?= e($u['role']) ?></small><?php $links=['dashboard.php'=>'Dashboard','cases.php'=>'Cases','prescriptions.php'=>'Prescriptions','orders.php'=>'Orders','users.php'=>'Users','roles.php'=>'Roles','doctors.php'=>'Doctors','vendors.php'=>'Vendors','knowledge-import.php'=>'Knowledge Import','formula-library.php'=>'Formula Library','ai-corrections.php'=>'AI Corrections','safety-rules.php'=>'Safety Rules','settings.php'=>'Settings','activity-logs.php'=>'Activity Logs']; foreach($links as $href=>$label): ?><a href="<?= $href ?>"><?= e($label) ?></a><?php endforeach; ?><a href="login.php?logout=1">Logout</a></aside><section class="admin-main"><div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3 mb-0"><?= e($title) ?></h1><span><?= e($u['name']) ?></span></div><?php }
function admin_footer(): void { ?></section></div><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script></body></html><?php }
function empty_state(string $message): string { return '<div class="card soft-card"><div class="card-body text-center text-muted py-5">' . e($message) . '</div></div>'; }
function wa_link(string $phone, string $message): string { $num = preg_replace('/\D+/', '', $phone); if (str_starts_with($num, '0')) $num = '92' . substr($num, 1); return 'https://wa.me/' . $num . '?text=' . rawurlencode($message); }
