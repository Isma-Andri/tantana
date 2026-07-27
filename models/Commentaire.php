<?php
// models/Commentaire.php

class Commentaire
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getPDO();
    }

    public function add(int $idProjet, int $idUser, string $contenu): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO commentaire (id_projet, id_user, contenu)
            VALUES (:projet, :user, :contenu)
        ");
        $stmt->execute([
            'projet'  => $idProjet,
            'user'    => $idUser,
            'contenu' => $contenu
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getByDossier(int $idProjet): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, u.prenom, u.nom 
            FROM commentaire c
            JOIN users u ON c.id_user = u.id_user
            WHERE c.id_projet = :projet
            ORDER BY c.date_commentaire DESC
        ");
        $stmt->execute(['projet' => $idProjet]);
        return $stmt->fetchAll();
    }
}
