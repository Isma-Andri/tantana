<?php
// models/ActivityLog.php

require_once __DIR__ . '/../config/database.php';

class ActivityLog
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getPDO();
    }

    public function log(int $idDossier, int $idUser, string $action): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO activity_log (id_dossier, id_user, action)
            VALUES (:dossier, :user, :action)
        ");
        $stmt->execute([
            'dossier' => $idDossier,
            'user'   => $idUser,
            'action' => $action
        ]);
    }

    public function getByDossier(int $idDossier, int $limit = 20): array
    {
        $stmt = $this->db->prepare("
            SELECT l.*, u.prenom, u.nom 
            FROM activity_log l
            JOIN users u ON l.id_user = u.id_user
            WHERE l.id_dossier = :dossier
            ORDER BY l.date_action DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':dossier', $idDossier, PDO::PARAM_INT);
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
            JOIN dossiers p ON l.id_dossier = p.id_dossier
            ORDER BY l.date_action DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
