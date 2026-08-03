<?php
// models/Fichier.php

require_once __DIR__ . '/../config/database.php';

class Fichier
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getPDO();
    }

    public function uploadFichier(array $fileData, int $idUser): ?int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO fichier (nom, chemin, taille, ajoute_par)
             VALUES (:nom, :chemin, :taille, :ajoute_par)'
        );
        $success = $stmt->execute([
            ':nom' => $fileData['nom'],
            ':chemin' => $fileData['chemin'],
            ':taille' => $fileData['taille'],
            ':ajoute_par' => $idUser
        ]);

        if ($success) {
            return (int) $this->pdo->lastInsertId();
        }
        return null;
    }

    public function linkToDossier(int $idFichier, int $idDossier): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT IGNORE INTO dossier_fichier (id_fichier, id_dossier) VALUES (:id_fichier, :id_dossier)'
        );
        return $stmt->execute([
            ':id_fichier' => $idFichier,
            ':id_dossier' => $idDossier
        ]);
    }

    public function linkToAction(int $idFichier, int $idTache): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT IGNORE INTO contenir (id_fichier, id_tache) VALUES (:id_fichier, :id_tache)'
        );
        return $stmt->execute([
            ':id_fichier' => $idFichier,
            ':id_tache' => $idTache
        ]);
    }

    public function getFichiersByDossier(int $idDossier): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT f.*, u.nom AS auteur_nom, u.prenom AS auteur_prenom 
             FROM fichier f
             JOIN dossier_fichier df ON f.id_fichier = df.id_fichier
             JOIN users u ON f.ajoute_par = u.id_user
             WHERE df.id_dossier = :id_dossier
             ORDER BY f.date_ajout DESC'
        );
        $stmt->execute([':id_dossier' => $idDossier]);
        return $stmt->fetchAll();
    }

    public function delete(int $idFichier): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM fichier WHERE id_fichier = :id_fichier');
        return $stmt->execute([':id_fichier' => $idFichier]);
    }
}
