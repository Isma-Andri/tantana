<?php
// models/Projet.php
// Modèle gérant toutes les requêtes SQL liées aux projets

require_once __DIR__ . '/../config/database.php';

class Projet
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getPDO();
    }

    /**
     * Crée un nouveau projet.
     *
     * @param array $data  Clés attendues : nom, description, date_debut, date_fin, date_limite, cree_par
     * @return int         ID du projet créé
     */
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO projets
                (nom, description, date_creation, date_debut, date_fin, date_limite, id_statut, cree_par)
             VALUES
                (:nom, :description, CURRENT_DATE, :date_debut, :date_fin, :date_limite, 1, :cree_par)'
        );

        $stmt->execute([
            ':nom'         => trim($data['nom']),
            ':description' => trim($data['description'] ?? ''),
            ':date_debut'  => $data['date_debut']  ?: null,
            ':date_fin'    => $data['date_fin']    ?: null,
            ':date_limite' => $data['date_limite'] ?: null,
            ':cree_par'    => (int) $data['cree_par'],
        ]);

        $projetId = (int) $this->pdo->lastInsertId();

        // Le créateur est automatiquement ajouté comme participant
        $this->addMember($projetId, (int) $data['cree_par'], 'Chef de projet');

        return $projetId;
    }

    /**
     * Retourne tous les projets visibles pour un utilisateur.
     * - Chef de projet : voit ses propres projets + ceux où il participe
     * - Membre         : voit uniquement les projets où il participe
     *
     * @param int    $userId
     * @param string $roleLibelle  'Chef de projet' | 'Membre'
     * @return array
     */
    public function getAllForUser(int $userId, string $roleLibelle): array
    {
        if ($roleLibelle === 'Chef de projet') {
            // Projets créés par lui OU projets où il participe
            $stmt = $this->pdo->prepare(
                'SELECT DISTINCT p.*, s.libelle AS statut_libelle,
                        u.nom AS createur_nom, u.prenom AS createur_prenom,
                        (SELECT COUNT(*) FROM participer WHERE id_projet = p.id_projet) AS nb_membres
                 FROM projets p
                 JOIN statut s ON p.id_statut = s.id_statut
                 JOIN users  u ON p.cree_par  = u.id_user
                 LEFT JOIN participer pa ON pa.id_projet = p.id_projet AND pa.id_user = :uid2
                 WHERE p.cree_par = :uid1 OR pa.id_user = :uid3
                 ORDER BY p.date_creation DESC'
            );
            $stmt->execute([':uid1' => $userId, ':uid2' => $userId, ':uid3' => $userId]);
        } else {
            // Membres : uniquement les projets auxquels ils participent
            $stmt = $this->pdo->prepare(
                'SELECT p.*, s.libelle AS statut_libelle,
                        u.nom AS createur_nom, u.prenom AS createur_prenom,
                        (SELECT COUNT(*) FROM participer WHERE id_projet = p.id_projet) AS nb_membres
                 FROM projets p
                 JOIN statut     s  ON p.id_statut  = s.id_statut
                 JOIN users      u  ON p.cree_par   = u.id_user
                 JOIN participer pa ON pa.id_projet  = p.id_projet
                 WHERE pa.id_user = :uid
                 ORDER BY p.date_creation DESC'
            );
            $stmt->execute([':uid' => $userId]);
        }

        return $stmt->fetchAll();
    }

    /**
     * Retourne un projet par son ID.
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.*, s.libelle AS statut_libelle,
                    u.nom AS createur_nom, u.prenom AS createur_prenom
             FROM projets p
             JOIN statut s ON p.id_statut = s.id_statut
             JOIN users  u ON p.cree_par  = u.id_user
             WHERE p.id_projet = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Met à jour un projet existant.
     * Seul le créateur (Chef de projet) peut modifier.
     *
     * @param int   $id
     * @param array $data  Clés : nom, description, date_debut, date_fin, date_limite, id_statut
     * @param int   $userId
     * @return bool
     */
    public function update(int $id, array $data, int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE projets
             SET nom = :nom,
                 description = :description,
                 date_debut  = :date_debut,
                 date_fin    = :date_fin,
                 date_limite = :date_limite,
                 id_statut   = :id_statut
             WHERE id_projet = :id AND cree_par = :cree_par'
        );

        return $stmt->execute([
            ':nom'         => trim($data['nom']),
            ':description' => trim($data['description'] ?? ''),
            ':date_debut'  => $data['date_debut']  ?: null,
            ':date_fin'    => $data['date_fin']    ?: null,
            ':date_limite' => $data['date_limite'] ?: null,
            ':id_statut'   => (int) $data['id_statut'],
            ':id'          => $id,
            ':cree_par'    => $userId,
        ]);
    }

    /**
     * Supprime un projet (uniquement par son créateur).
     *
     * @param int $id
     * @param int $userId
     * @return bool
     */
    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM projets WHERE id_projet = :id AND cree_par = :cree_par'
        );
        return $stmt->execute([':id' => $id, ':cree_par' => $userId]);
    }

    /**
     * Ajoute un membre à un projet.
     *
     * @param int    $projetId
     * @param int    $userId
     * @param string $role
     * @return bool
     */
    public function addMember(int $projetId, int $userId, string $role = 'Membre'): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT IGNORE INTO participer (id_user, id_projet, date_participation, role_dans_projet)
             VALUES (:uid, :pid, CURRENT_DATE, :role)'
        );
        return $stmt->execute([':uid' => $userId, ':pid' => $projetId, ':role' => $role]);
    }

    /**
     * Liste les membres d'un projet.
     *
     * @param int $projetId
     * @return array
     */
    public function getMembers(int $projetId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.id_user, u.nom, u.prenom, u.email,
                    pa.role_dans_projet, pa.date_participation
             FROM participer pa
             JOIN users u ON pa.id_user = u.id_user
             WHERE pa.id_projet = :pid
             ORDER BY pa.date_participation'
        );
        $stmt->execute([':pid' => $projetId]);
        return $stmt->fetchAll();
    }

    /**
     * Retourne tous les statuts disponibles.
     *
     * @return array
     */
    public function getStatuts(): array
    {
        return getPDO()->query('SELECT * FROM statut ORDER BY id_statut')->fetchAll();
    }
}
