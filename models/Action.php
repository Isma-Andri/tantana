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
            INSERT INTO actions (nom, description, date_debut, date_fin, date_limite, id_statut, id_priorite, id_dossier)
            VALUES (:nom, :description, :date_debut, :date_fin, :date_limite, :id_statut, :id_priorite, :id_dossier)
        ");
        $stmt->execute([
            'nom'         => $data['nom'],
            'description' => $data['description'] ?? null,
            'date_debut'  => $data['date_debut'] ?? null,
            'date_fin'    => $data['date_fin'] ?? null,
            'date_limite' => $data['date_limite'] ?? null,
            'id_statut'   => $data['id_statut'] ?? 1,
            'id_priorite' => $data['id_priorite'] ?? 2,
            'id_dossier'   => $data['id_dossier'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getByDossier(int $idDossier): array
    {
        $stmt = $this->db->prepare("
            SELECT t.*, s.libelle as statut_libelle, p.libelle as priorite_libelle,
                   u.id_user, u.prenom as assigne_prenom, u.nom as assigne_nom
            FROM actions t
            JOIN statut s ON t.id_statut = s.id_statut
            JOIN priorite p ON t.id_priorite = p.id_priorite
            LEFT JOIN affecter a ON t.id_action = a.id_action
            LEFT JOIN users u ON a.id_user = u.id_user
            WHERE t.id_dossier = :dossier
            ORDER BY t.date_creation DESC
        ");
        $stmt->execute(['dossier' => $idDossier]);
        return $stmt->fetchAll();
    }

    public function updateStatut(int $idAction, int $idStatut): bool
    {
        $stmt = $this->db->prepare("UPDATE actions SET id_statut = :statut WHERE id_action = :action");
        return $stmt->execute([
            'statut' => $idStatut,
            'action'  => $idAction
        ]);
    }

    public function assign(int $idAction, int $idUser): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM affecter WHERE id_user = :user AND id_action = :action");
        $stmt->execute(['user' => $idUser, 'action' => $idAction]);
        if ($stmt->fetch()) {
            return true;
        }

        $stmt = $this->db->prepare("INSERT INTO affecter (id_user, id_action) VALUES (:user, :action)");
        return $stmt->execute(['user' => $idUser, 'action' => $idAction]);
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
