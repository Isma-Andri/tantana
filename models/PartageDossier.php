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

    public function addPartage(int $idProjet, int $idUser, string $niveauAcces = 'Lecture'): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT IGNORE INTO partage_dossier (id_projet, id_user, niveau_acces)
             VALUES (:id_projet, :id_user, :niveau_acces)'
        );
        return $stmt->execute([
            ':id_projet' => $idProjet,
            ':id_user' => $idUser,
            ':niveau_acces' => $niveauAcces
        ]);
    }

    public function removePartage(int $idProjet, int $idUser): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM partage_dossier WHERE id_projet = :id_projet AND id_user = :id_user');
        return $stmt->execute([
            ':id_projet' => $idProjet,
            ':id_user' => $idUser
        ]);
    }

    public function getPartagesByDossier(int $idProjet): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT pd.*, u.nom, u.prenom, u.email 
             FROM partage_dossier pd
             JOIN users u ON pd.id_user = u.id_user
             WHERE pd.id_projet = :id_projet'
        );
        $stmt->execute([':id_projet' => $idProjet]);
        return $stmt->fetchAll();
    }

    public function getPartagesByUser(int $idUser): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT pd.*, p.nom AS projet_nom, p.description
             FROM partage_dossier pd
             JOIN projets p ON pd.id_projet = p.id_projet
             WHERE pd.id_user = :id_user'
        );
        $stmt->execute([':id_user' => $idUser]);
        return $stmt->fetchAll();
    }
}
