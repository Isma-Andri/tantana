<?php
// models/Action.php

class Action
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getPDO();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO tache (nom, description, date_debut, date_fin, date_limite, id_statut, id_priorite, id_projet)
            VALUES (:nom, :description, :date_debut, :date_fin, :date_limite, :id_statut, :id_priorite, :id_projet)
        ");
        $stmt->execute([
            'nom'         => $data['nom'],
            'description' => $data['description'] ?? null,
            'date_debut'  => $data['date_debut'] ?? null,
            'date_fin'    => $data['date_fin'] ?? null,
            'date_limite' => $data['date_limite'] ?? null,
            'id_statut'   => $data['id_statut'] ?? 1,
            'id_priorite' => $data['id_priorite'] ?? 2,
            'id_projet'   => $data['id_projet'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getByDossier(int $idProjet): array
    {
        $stmt = $this->db->prepare("
            SELECT t.*, s.libelle as statut_libelle, p.libelle as priorite_libelle,
                   u.id_user, u.prenom, u.nom
            FROM tache t
            JOIN statut s ON t.id_statut = s.id_statut
            JOIN priorite p ON t.id_priorite = p.id_priorite
            LEFT JOIN affecter a ON t.id_tache = a.id_tache
            LEFT JOIN users u ON a.id_user = u.id_user
            WHERE t.id_projet = :projet
            ORDER BY t.date_creation DESC
        ");
        $stmt->execute(['projet' => $idProjet]);
        return $stmt->fetchAll();
    }

    public function updateStatut(int $idTache, int $idStatut): bool
    {
        $stmt = $this->db->prepare("UPDATE tache SET id_statut = :statut WHERE id_tache = :tache");
        return $stmt->execute([
            'statut' => $idStatut,
            'tache'  => $idTache
        ]);
    }

    public function assign(int $idTache, int $idUser): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM affecter WHERE id_user = :user AND id_tache = :tache");
        $stmt->execute(['user' => $idUser, 'tache' => $idTache]);
        if ($stmt->fetch()) {
            return true;
        }

        $stmt = $this->db->prepare("INSERT INTO affecter (id_user, id_tache) VALUES (:user, :tache)");
        return $stmt->execute(['user' => $idUser, 'tache' => $idTache]);
    }

    public function getStatuts(): array
    {
        return $this->db->query("SELECT * FROM statut")->fetchAll();
    }

    public function getPriorites(): array
    {
        return $this->db->query("SELECT * FROM priorite")->fetchAll();
    }
}
