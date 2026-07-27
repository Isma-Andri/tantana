<?php
// models/ActivityLog.php

class ActivityLog
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getPDO();
    }

    public function log(int $idProjet, int $idUser, string $action): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO activity_log (id_projet, id_user, action)
            VALUES (:projet, :user, :action)
        ");
        $stmt->execute([
            'projet' => $idProjet,
            'user'   => $idUser,
            'action' => $action
        ]);
    }

    public function getByDossier(int $idProjet, int $limit = 20): array
    {
        $stmt = $this->db->prepare("
            SELECT l.*, u.prenom, u.nom 
            FROM activity_log l
            JOIN users u ON l.id_user = u.id_user
            WHERE l.id_projet = :projet
            ORDER BY l.date_action DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':projet', $idProjet, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllLogs(int $limit = 30): array
    {
        $stmt = $this->db->prepare("
            SELECT l.*, u.prenom, u.nom, p.nom AS dossier_nom
            FROM activity_log l
            JOIN users u ON l.id_user = u.id_user
            JOIN projets p ON l.id_projet = p.id_projet
            ORDER BY l.date_action DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
