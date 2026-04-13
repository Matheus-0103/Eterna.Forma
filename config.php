<?php
// ═══════════════════════════════════════════════════════════════════
// config.php — Configuração central do Eterna Forma
// ═════════════════════════════════════════════════════════════════════

define('DB_HOST',    'localhost');
define('DB_NAME',    'eterna_forma');
define('DB_USER',    'root');      // altere
define('DB_PASS',    '');          // altere
define('DB_CHARSET', 'utf8mb4');

session_start();

// ── PDO singleton ────────────────────────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

// ── Auth helpers ─────────────────────────────────────────────────────
function loginDesportista(int $id, string $nome): void {
    $_SESSION['user_id']   = $id;
    $_SESSION['user_nome'] = $nome;
    $_SESSION['user_tipo'] = 'desportista';
}
function loginTecnico(int $id, string $nome): void {
    $_SESSION['user_id']   = $id;
    $_SESSION['user_nome'] = $nome;
    $_SESSION['user_tipo'] = 'tecnico';
}
function loginAtendente(int $id, string $nome): void {
    $_SESSION['user_id']   = $id;
    $_SESSION['user_nome'] = $nome;
    $_SESSION['user_tipo'] = 'atendente';
}
function logout(): void {
    session_destroy();
    header('Location: index.php');
    exit;
}
function authRequired(string $tipo): void {
    if (($_SESSION['user_tipo'] ?? '') !== $tipo) {
        header('Location: index.php');
        exit;
    }
}
function isLogged(): bool {
    return isset($_SESSION['user_id']);
}

// ── Utilidades ───────────────────────────────────────────────────────
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES); }

function hashSenha(string $s): string {
    return password_hash($s, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Calcula score de similaridade entre dois perfis fitness (0–100).
 * Critérios: nível, objetivo, modalidades, disponibilidade, localização.
 */
function calcularSimilaridade(array $a, array $b): float {
    $score = 0;
    // Nível igual: +30
    if ($a['nivel'] === $b['nivel']) $score += 30;
    // Objetivo igual: +30
    if ($a['objetivo'] === $b['objetivo']) $score += 30;
    // Modalidades em comum: até +25
    $mA = json_decode($a['modalidades'] ?? '[]', true) ?: [];
    $mB = json_decode($b['modalidades'] ?? '[]', true) ?: [];
    $inter = count(array_intersect($mA, $mB));
    $union = count(array_unique(array_merge($mA, $mB)));
    if ($union > 0) $score += round(($inter / $union) * 25);
    // Mesma localização: +15
    if (!empty($a['localizacao']) && $a['localizacao'] === $b['localizacao']) $score += 15;
    return min(100, $score);
}
