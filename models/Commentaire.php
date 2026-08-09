<?php
// models/Commentaire.php

require_once __DIR__ . '/../config/database.php';

class Commentaire
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getPDO();
    }

    public function add(int $idDossier, int $idUser, string $contenu): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO commentaire (id_dossier, id_user, contenu)
            VALUES (:dossier, :user, :contenu)
        ");
        $stmt->execute([
            'dossier'  => $idDossier,
            'user'    => $idUser,
            'contenu' => $contenu
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getByDossier(int $idDossier): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, u.prenom, u.nom 
            FROM commentaire c
            JOIN users u ON c.id_user = u.id_user
            WHERE c.id_dossier = :dossier
            ORDER BY c.date_commentaire DESC
        ");
        $stmt->execute(['dossier' => $idDossier]);
        return $stmt->fetchAll();
    }
}
