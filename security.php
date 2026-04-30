<?php
/*
 * security.php — inclure EN PREMIER dans chaque page (avant session_start)
 * Gère : en-têtes HTTP, tokens CSRF, validation des redirections
 */

// ── En-têtes de sécurité HTTP ─────────────────────────────────────────────
// Empêche le site d'être embarqué dans une iframe (clickjacking)
header('X-Frame-Options: SAMEORIGIN');
// Le navigateur ne doit pas deviner le type MIME
header('X-Content-Type-Options: nosniff');
// Indique aux navigateurs d'activer les protections XSS natives
header('X-XSS-Protection: 1; mode=block');
// Politique de référent : ne transmet l'URL qu'aux pages du même site
header('Referrer-Policy: strict-origin-when-cross-origin');
// Retire la signature du serveur PHP
header_remove('X-Powered-By');

// ── Configuration session sécurisée ───────────────────────────────────────
// Cookie de session inaccessible au JavaScript
ini_set('session.cookie_httponly', 1);
// Cookie uniquement sur HTTPS (mettre à 1 en production)
ini_set('session.cookie_samesite', 'Lax');
// Pas de passage de session dans l'URL
ini_set('session.use_only_cookies', 1);
ini_set('session.use_trans_sid', 0);

// ── Fonctions CSRF ─────────────────────────────────────────────────────────

/**
 * Génère un token CSRF et le stocke en session.
 * Appeler dans chaque formulaire qui modifie des données.
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Retourne le champ HTML caché contenant le token CSRF.
 * À mettre dans chaque <form> POST.
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/**
 * Vérifie que le token CSRF soumis est correct.
 * Retourne false si invalide → arrêter le traitement.
 */
function csrf_verify(): bool {
    $token_post    = $_POST['csrf_token']    ?? '';
    $token_session = $_SESSION['csrf_token'] ?? '';
    if (!$token_session || !$token_post) return false;
    return hash_equals($token_session, $token_post);
}

// ── Validation de redirection ──────────────────────────────────────────────

/**
 * Valide une URL de redirection : accepte uniquement les URLs du même domaine
 * ou les chemins relatifs. Évite les open redirects.
 */
function safe_redirect(string $url, string $fallback = 'index.php'): string {
    // Chemin relatif → ok
    if (!preg_match('#^https?://#i', $url)) {
        // On s'assure qu'il n'y a pas de traversal
        $url = ltrim($url, '/');
        return $url ?: $fallback;
    }

    // URL absolue → vérifier que c'est le même domaine
    $host_request = $_SERVER['HTTP_HOST'] ?? '';
    $host_url     = parse_url($url, PHP_URL_HOST);
    if ($host_url && $host_url === $host_request) {
        return $url;
    }

    return $fallback;
}

// ── Brute force login ──────────────────────────────────────────────────────

/**
 * Vérifie si l'IP est temporairement bloquée après trop de tentatives.
 * Stockage simple en session (à remplacer par BDD ou Redis en prod).
 */
function brute_force_check(): bool {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = 'bf_' . md5($ip);
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'time' => time()];
    }
    // Réinitialise après 15 minutes
    if (time() - $_SESSION[$key]['time'] > 900) {
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
