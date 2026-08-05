<?php
// models/Dossier.php

require_once __DIR__ . '/../config/database.php';

class Dossier
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getPDO();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO dossiers (nom, description, date_creation, date_debut, date_fin, date_limite, id_statut, id_workflow, cree_par)
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
        $this->addMember($id, (int) $data['cree_par'], 'Responsable de dossier');

        return $id;
    }

    public function getAllForUser(int $userId, string $role): array
    {
        if ($role === 'Administrateur') {
            $stmt = $this->pdo->query(
                'SELECT p.*, s.libelle AS statut_libelle, sw.libelle AS workflow_libelle,
                        u.nom AS createur_nom, u.prenom AS createur_prenom,
                        (SELECT COUNT(*) FROM participer WHERE id_dossier = p.id_dossier) AS nb_membres
                 FROM dossiers p
                 JOIN statut s ON p.id_statut = s.id_statut
                 JOIN statut_workflow sw ON p.id_workflow = sw.id_workflow
                 JOIN users  u ON p.cree_par  = u.id_user
                 ORDER BY p.date_creation DESC'
            );
            return $stmt->fetchAll();
        }

        // Responsable de dossier : ses dossiers + ceux où il participe
        if ($role === 'Responsable de dossier') {
            $stmt = $this->pdo->prepare(
                'SELECT DISTINCT p.*, s.libelle AS statut_libelle, sw.libelle AS workflow_libelle,
                        u.nom AS createur_nom, u.prenom AS createur_prenom,
                        (SELECT COUNT(*) FROM participer WHERE id_dossier = p.id_dossier) AS nb_membres
                 FROM dossiers p
                 JOIN statut s ON p.id_statut = s.id_statut
                 JOIN statut_workflow sw ON p.id_workflow = sw.id_workflow
                 JOIN users  u ON p.cree_par  = u.id_user
                 LEFT JOIN participer pa ON pa.id_dossier = p.id_dossier AND pa.id_user = :uid
                 WHERE p.cree_par = :uid2 OR pa.id_user = :uid3
                 ORDER BY p.date_creation DESC'
            );
            $stmt->execute([':uid' => $userId, ':uid2' => $userId, ':uid3' => $userId]);
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT p.*, s.libelle AS statut_libelle, sw.libelle AS workflow_libelle,
                        u.nom AS createur_nom, u.prenom AS createur_prenom,
                        (SELECT COUNT(*) FROM participer WHERE id_dossier = p.id_dossier) AS nb_membres
                 FROM dossiers p
                 JOIN statut     s  ON p.id_statut = s.id_statut
                 JOIN statut_workflow sw ON p.id_workflow = sw.id_workflow
                 JOIN users      u  ON p.cree_par  = u.id_user
                 JOIN participer pa ON pa.id_dossier = p.id_dossier AND pa.id_user = :uid
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
             FROM dossiers p
             JOIN statut s ON p.id_statut = s.id_statut
             JOIN statut_workflow sw ON p.id_workflow = sw.id_workflow
             JOIN users  u ON p.cree_par  = u.id_user
             WHERE p.id_dossier = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function hasAccess(int $dossierId, int $userId, string $role): bool
    {
        if ($role === 'Administrateur') {
            return true;
        }

        // Check if creator
        $stmt = $this->pdo->prepare('SELECT cree_par FROM dossiers WHERE id_dossier = :id LIMIT 1');
        $stmt->execute([':id' => $dossierId]);
        $creatorId = $stmt->fetchColumn();
        if ($creatorId !== false && (int)$creatorId === $userId) {
            return true;
        }

        // Check if participant
        $stmt = $this->pdo->prepare('SELECT 1 FROM participer WHERE id_dossier = :did AND id_user = :uid LIMIT 1');
        $stmt->execute([':did' => $dossierId, ':uid' => $userId]);
        if ($stmt->fetch()) {
            return true;
        }

        // Check if shared
        $stmt = $this->pdo->prepare('SELECT 1 FROM partage_dossier WHERE id_dossier = :did AND id_user = :uid LIMIT 1');
        $stmt->execute([':did' => $dossierId, ':uid' => $userId]);
        if ($stmt->fetch()) {
            return true;
        }

        return false;
    }

    public function update(int $id, array $data, int $userId, bool $isAdmin = false): bool
    {
        $sql = 'UPDATE dossiers
             SET nom = :nom, description = :description, date_debut = :date_debut,
                 date_fin = :date_fin, date_limite = :date_limite, id_statut = :id_statut, id_workflow = :id_workflow
             WHERE id_dossier = :id';
        if (!$isAdmin) {
            $sql .= ' AND cree_par = :cree_par';
        }
        $stmt = $this->pdo->prepare($sql);

        $params = [
            ':nom'         => trim($data['nom']),
            ':description' => trim($data['description'] ?? ''),
            ':date_debut'  => $data['date_debut']  ?: null,
            ':date_fin'    => $data['date_fin']    ?: null,
            ':date_limite' => $data['date_limite'] ?: null,
            ':id_statut'   => (int) $data['id_statut'],
            ':id_workflow' => (int) ($data['id_workflow'] ?? 1),
            ':id'          => $id,
        ];
        if (!$isAdmin) {
            $params[':cree_par'] = $userId;
        }

        return $stmt->execute($params);
    }

    public function delete(int $id, int $userId, bool $isAdmin = false): bool
    {
        $sql = 'DELETE FROM dossiers WHERE id_dossier = :id';
        if (!$isAdmin) {
            $sql .= ' AND cree_par = :cree_par';
        }
        $stmt = $this->pdo->prepare($sql);

        $params = [':id' => $id];
        if (!$isAdmin) {
            $params[':cree_par'] = $userId;
        }

        return $stmt->execute($params);
    }

    public function addMember(int $dossierId, int $userId, string $role = 'Collaborateur'): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT IGNORE INTO participer (id_user, id_dossier, date_participation, role_dans_dossier)
             VALUES (:uid, :pid, CURRENT_DATE, :role)'
        );

        return $stmt->execute([':uid' => $userId, ':pid' => $dossierId, ':role' => $role]);
    }

    public function getMembers(int $dossierId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.id_user, u.nom, u.prenom, u.email, pa.role_dans_dossier, pa.date_participation
             FROM participer pa JOIN users u ON pa.id_user = u.id_user
             WHERE pa.id_dossier = :pid ORDER BY pa.date_participation'
        );
        $stmt->execute([':pid' => $dossierId]);

        return $stmt->fetchAll();
    }

    public function getStatuts(): array
    {
        return getPDO()->query('SELECT * FROM statut ORDER BY id_statut')->fetchAll();
    }
}
