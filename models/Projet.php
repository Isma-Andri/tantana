<?php
// models/Projet.php

require_once __DIR__ . '/../config/database.php';

class Projet
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getPDO();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO projets (nom, description, date_creation, date_debut, date_fin, date_limite, id_statut, id_workflow, cree_par)
             VALUES (:nom, :description, CURRENT_DATE, :date_debut, :date_fin, :date_limite, 1, :id_workflow, :cree_par)'
        );
        $stmt->execute([
            ':nom'         => trim($data['nom']),
            ':description' => trim($data['description'] ?? ''),
            ':date_debut'  => $data['date_debut']  ?: null,
            ':date_fin'    => $data['date_fin']    ?: null,
            ':date_limite' => $data['date_limite'] ?: null,
            ':id_workflow' => (int) ($data['id_workflow'] ?? 1),
            ':cree_par'    => (int) $data['cree_par'],
        ]);

        $id = (int) $this->pdo->lastInsertId();
        $this->addMember($id, (int) $data['cree_par'], 'Chef de projet');

        return $id;
    }

    public function getAllForUser(int $userId, string $role): array
    {
        // Chef de projet : ses projets + ceux où il participe
        if ($role === 'Chef de projet') {
            $stmt = $this->pdo->prepare(
                'SELECT DISTINCT p.*, s.libelle AS statut_libelle, sw.libelle AS workflow_libelle,
                        u.nom AS createur_nom, u.prenom AS createur_prenom,
                        (SELECT COUNT(*) FROM participer WHERE id_projet = p.id_projet) AS nb_membres
                 FROM projets p
                 JOIN statut s ON p.id_statut = s.id_statut
                 JOIN statut_workflow sw ON p.id_workflow = sw.id_workflow
                 JOIN users  u ON p.cree_par  = u.id_user
                 LEFT JOIN participer pa ON pa.id_projet = p.id_projet AND pa.id_user = :uid
                 WHERE p.cree_par = :uid2 OR pa.id_user = :uid3
                 ORDER BY p.date_creation DESC'
            );
            $stmt->execute([':uid' => $userId, ':uid2' => $userId, ':uid3' => $userId]);
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT p.*, s.libelle AS statut_libelle, sw.libelle AS workflow_libelle,
                        u.nom AS createur_nom, u.prenom AS createur_prenom,
                        (SELECT COUNT(*) FROM participer WHERE id_projet = p.id_projet) AS nb_membres
                 FROM projets p
                 JOIN statut     s  ON p.id_statut = s.id_statut
                 JOIN statut_workflow sw ON p.id_workflow = sw.id_workflow
                 JOIN users      u  ON p.cree_par  = u.id_user
                 JOIN participer pa ON pa.id_projet = p.id_projet AND pa.id_user = :uid
                 ORDER BY p.date_creation DESC'
            );
            $stmt->execute([':uid' => $userId]);
        }

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.*, s.libelle AS statut_libelle, sw.libelle AS workflow_libelle,
                    u.nom AS createur_nom, u.prenom AS createur_prenom
             FROM projets p
             JOIN statut s ON p.id_statut = s.id_statut
             JOIN statut_workflow sw ON p.id_workflow = sw.id_workflow
             JOIN users  u ON p.cree_par  = u.id_user
             WHERE p.id_projet = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function update(int $id, array $data, int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE projets
             SET nom = :nom, description = :description, date_debut = :date_debut,
                 date_fin = :date_fin, date_limite = :date_limite, id_statut = :id_statut, id_workflow = :id_workflow
             WHERE id_projet = :id AND cree_par = :cree_par'
        );

        return $stmt->execute([
            ':nom'         => trim($data['nom']),
            ':description' => trim($data['description'] ?? ''),
            ':date_debut'  => $data['date_debut']  ?: null,
            ':date_fin'    => $data['date_fin']    ?: null,
            ':date_limite' => $data['date_limite'] ?: null,
            ':id_statut'   => (int) $data['id_statut'],
            ':id_workflow' => (int) ($data['id_workflow'] ?? 1),
            ':id'          => $id,
            ':cree_par'    => $userId,
        ]);
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM projets WHERE id_projet = :id AND cree_par = :cree_par'
        );

        return $stmt->execute([':id' => $id, ':cree_par' => $userId]);
    }

    public function addMember(int $projetId, int $userId, string $role = 'Membre'): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT IGNORE INTO participer (id_user, id_projet, date_participation, role_dans_projet)
             VALUES (:uid, :pid, CURRENT_DATE, :role)'
        );

        return $stmt->execute([':uid' => $userId, ':pid' => $projetId, ':role' => $role]);
    }

    public function getMembers(int $projetId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.id_user, u.nom, u.prenom, u.email, pa.role_dans_projet, pa.date_participation
             FROM participer pa JOIN users u ON pa.id_user = u.id_user
             WHERE pa.id_projet = :pid ORDER BY pa.date_participation'
        );
        $stmt->execute([':pid' => $projetId]);

        return $stmt->fetchAll();
    }

    public function getStatuts(): array
    {
        return getPDO()->query('SELECT * FROM statut ORDER BY id_statut')->fetchAll();
    }
}
