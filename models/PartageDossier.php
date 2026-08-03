<?php
// models/PartageDossier.php

require_once __DIR__ . '/../config/database.php';

class PartageDossier
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getPDO();
    }

    public function addPartage(int $idDossier, int $idUser, string $niveauAcces = 'Lecture'): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT IGNORE INTO partage_dossier (id_dossier, id_user, niveau_acces)
             VALUES (:id_dossier, :id_user, :niveau_acces)'
        );
        return $stmt->execute([
            ':id_dossier' => $idDossier,
            ':id_user' => $idUser,
            ':niveau_acces' => $niveauAcces
        ]);
    }

    public function removePartage(int $idDossier, int $idUser): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM partage_dossier WHERE id_dossier = :id_dossier AND id_user = :id_user');
        return $stmt->execute([
            ':id_dossier' => $idDossier,
            ':id_user' => $idUser
        ]);
    }

    public function getPartagesByDossier(int $idDossier): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT pd.*, u.nom, u.prenom, u.email 
             FROM partage_dossier pd
             JOIN users u ON pd.id_user = u.id_user
             WHERE pd.id_dossier = :id_dossier'
        );
        $stmt->execute([':id_dossier' => $idDossier]);
        return $stmt->fetchAll();
    }

    public function getPartagesByUser(int $idUser): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT pd.*, p.nom AS dossier_nom, p.description
             FROM partage_dossier pd
             JOIN dossiers p ON pd.id_dossier = p.id_dossier
             WHERE pd.id_user = :id_user'
        );
        $stmt->execute([':id_user' => $idUser]);
        return $stmt->fetchAll();
    }
}
