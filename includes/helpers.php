<?php
/**
 * Helpers globaux — fonctions réutilisables dans tous les modules
 */

// ── Journalisation d'une action ─────────────────────────────
function logAction(string $description, ?int $id_projet = null, ?int $id_tache = null): int {
    $user = getCurrentUser();
    if (!$user) return 0;
    try {
        $pdo  = getDB();
        $stmt = $pdo->prepare("INSERT INTO actions (description,id_user,id_projet,id_tache) VALUES (?,?,?,?)");
        $stmt->execute([$description, $user['id'], $id_projet, $id_tache]);
        return (int)$pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log('[Tantana][logAction] ' . $e->getMessage());
        return 0;
    }
}

// ── Envoyer une notification ────────────────────────────────
function notifier(int $id_user, string $contenu, ?int $id_action = null): void {
    try {
        $pdo = getDB();
        $pdo->prepare("INSERT INTO notifications (contenu,id_user,id_action) VALUES (?,?,?)")
            ->execute([$contenu, $id_user, $id_action]);
    } catch (PDOException $e) {
        error_log('[Tantana][notifier] ' . $e->getMessage());
    }
}

// ── Compter les notifications non lues ──────────────────────
function countNotifNonLues(?int $id_user = null): int {
    if (!$id_user) {
        $u = getCurrentUser();
        if (!$u) return 0;
        $id_user = $u['id'];
    }
    try {
        $stmt = getDB()->prepare("SELECT COUNT(*) FROM notifications WHERE id_user=? AND est_lue=0");
        $stmt->execute([$id_user]);
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

// ── Formatage date ──────────────────────────────────────────
function fmtDate(?string $d, string $fmt = 'd/m/Y'): string {
    if (!$d) return '—';
    try { return (new DateTime($d))->format($fmt); }
    catch (Exception $e) { return $d; }
}

function fmtDatetime(?string $d): string {
    return fmtDate($d, 'd/m/Y H:i');
}

function daysUntil(?string $date): ?int {
    if (!$date) return null;
    try {
        $diff = (new DateTime($date))->diff(new DateTime('today'));
        return $diff->invert ? -$diff->days : $diff->days;
    } catch (Exception $e) { return null; }
}

// ── Statut lisible ──────────────────────────────────────────
function labelStatut(string $s): string {
    return match($s) {
        'a_faire'  => 'A faire',
        'en_cours' => 'En cours',
        'termine'  => 'Termine',
        'bloque'   => 'Bloque',
        default    => ucfirst($s),
    };
}
function classBadgeStatut(string $s): string {
    return match($s) {
        'a_faire'  => 'badge-blue',
        'en_cours' => 'badge-orange',
        'termine'  => 'badge-green',
        'bloque'   => 'badge-red',
        default    => 'badge-blue',
    };
}

// ── Priorité lisible ────────────────────────────────────────
function labelPriorite(string $p): string {
    return match($p) {
        'basse'   => 'Basse',
        'moyenne' => 'Moyenne',
        'haute'   => 'Haute',
        default   => ucfirst($p),
    };
}
function classBadgePriorite(string $p): string {
    return match($p) {
        'basse'   => 'badge-blue',
        'moyenne' => 'badge-orange',
        'haute'   => 'badge-red',
        default   => 'badge-blue',
    };
}

// ── Taille lisible ──────────────────────────────────────────
function fmtTaille(int $bytes): string {
    if ($bytes < 1024)        return $bytes . ' o';
    if ($bytes < 1048576)     return round($bytes/1024, 1) . ' Ko';
    return round($bytes/1048576, 1) . ' Mo';
}

// ── Flash messages ──────────────────────────────────────────
function flashSet(string $type, string $msg): void {
    startSession();
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}
function flashGet(): ?array {
    startSession();
    if (!isset($_SESSION['flash'])) return null;
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}
function flashHtml(): string {
    $f = flashGet();
    if (!$f) return '';
    $cls = match($f['type']) {
        'success' => 'alert-success',
        'error'   => 'alert-error',
        default   => 'alert-info',
    };
    $icon = $f['type'] === 'success'
        ? '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>'
        : '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
    return '<div class="alert ' . $cls . '">' . $icon . ' ' . htmlspecialchars($f['msg']) . '</div>';
}

// ── Vérifier qu'un user est chef d'un projet ────────────────
function isChefProjet(int $id_projet, int $id_user): bool {
    try {
        $stmt = getDB()->prepare("SELECT 1 FROM projets WHERE id_projet=? AND id_chef=? LIMIT 1");
        $stmt->execute([$id_projet, $id_user]);
        return (bool)$stmt->fetchColumn();
    } catch (PDOException $e) { return false; }
}

// ── Vérifier qu'un user participe à un projet ───────────────
function isParticipant(int $id_projet, int $id_user): bool {
    try {
        $stmt = getDB()->prepare("SELECT 1 FROM participations WHERE id_projet=? AND id_user=? LIMIT 1");
        $stmt->execute([$id_projet, $id_user]);
        return (bool)$stmt->fetchColumn();
    } catch (PDOException $e) { return false; }
}

// ── Accès projet (chef OU participant) ──────────────────────
function canAccessProjet(int $id_projet, int $id_user): bool {
    return isChefProjet($id_projet, $id_user) || isParticipant($id_projet, $id_user);
}
