<?php
// security.php → inclure en 1er dans chaque page (avant session_start)

// headers http //
header('X-Frame-Options: SAMEORIGIN');          // anti-clickjacking
header('X-Content-Type-Options: nosniff');       // pas de mime sniff
header('X-XSS-Protection: 1; mode=block');       // xss natif navigateur
header('Referrer-Policy: strict-origin-when-cross-origin'); // referer → même domaine
header_remove('X-Powered-By');                   // cache signature php

// session → cookie seulement, inacc js //
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');       // passer à 1 en prod (https)
ini_set('session.use_only_cookies', 1);
ini_set('session.use_trans_sid', 0);

// csrf //
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// → champ hidden à mettre dans chaque form POST //
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

// → false si token invalide = stopper le traitement //
function csrf_verify(): bool {
    $token_post    = $_POST['csrf_token']    ?? '';
    $token_session = $_SESSION['csrf_token'] ?? '';
    if (!$token_session || !$token_post) return false;
    return hash_equals($token_session, $token_post);
}

// safe_redirect → anti open redirect, accepte chemin relatif ou même domaine //
function safe_redirect(string $url, string $fallback = 'index.php'): string {
    if (!preg_match('#^https?://#i', $url)) {
        $url = ltrim($url, '/');
        return $url ?: $fallback;
    }
    $host_request = $_SERVER['HTTP_HOST'] ?? '';
    $host_url     = parse_url($url, PHP_URL_HOST);
    if ($host_url && $host_url === $host_request) {
        return $url;
    }
    return $fallback;
}

// brute force : 5 essais max / 15 min, stockage session (→ BDD/Redis en prod) //
function brute_force_check(): bool {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = 'bf_' . md5($ip);
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'time' => time()];
    }
    if (time() - $_SESSION[$key]['time'] > 900) { // reset 15 min
        $_SESSION[$key] = ['count' => 0, 'time' => time()];
    }
    return $_SESSION[$key]['count'] >= 5;
}

function brute_force_increment(): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = 'bf_' . md5($ip);
    $_SESSION[$key]['count'] = ($_SESSION[$key]['count'] ?? 0) + 1;
    $_SESSION[$key]['time']  = time();
}

function brute_force_reset(): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = 'bf_' . md5($ip);
    unset($_SESSION[$key]);
}
