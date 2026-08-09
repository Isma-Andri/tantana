<?php
// models/DemandeValidation.php

require_once __DIR__ . '/../config/database.php';

class DemandeValidation
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getPDO();
    }

    /**
     * Soumet une nouvelle demande de validation.
     * @param int    $idDossier
     * @param int    $idDemandeur
     * @param string $type        'upload_fichier' | 'modif_action' | 'changement_workflow'
     * @param array  $payload     Données de la demande (sera sérialisé en JSON)
     */
    public function soumettre(int $idDossier, int $idDemandeur, string $type, array $payload): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO demande_validation (id_dossier, id_demandeur, type_demande, payload)
             VALUES (:dossier, :demandeur, :type, :payload)'
        );
        $stmt->execute([
            ':dossier'   => $idDossier,
            ':demandeur' => $idDemandeur,
            ':type'      => $type,
            ':payload'   => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Récupère toutes les demandes en attente pour un dossier donné.
     */
    public function getEnAttenteByDossier(int $idDossier): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT dv.*, u.prenom, u.nom, u.email
             FROM demande_validation dv
             JOIN users u ON dv.id_demandeur = u.id_user
             WHERE dv.id_dossier = :dossier AND dv.statut = \'en_attente\'
             ORDER BY dv.created_at ASC'
        );
        $stmt->execute([':dossier' => $idDossier]);
        $rows = $stmt->fetchAll();

        // Décoder le JSON du payload pour chaque ligne
        foreach ($rows as &$row) {
            $row['payload'] = json_decode($row['payload'], true) ?? [];
        }
        return $rows;
    }

    /**
     * Récupère une demande par son ID.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT dv.*, u.prenom, u.nom
             FROM demande_validation dv
             JOIN users u ON dv.id_demandeur = u.id_user
             WHERE dv.id_demande = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        $row['payload'] = json_decode($row['payload'], true) ?? [];
        return $row;
    }

    /**
     * Approuve ou rejette une demande.
     * @param string $statut   'approuvee' | 'rejetee'
     */
    public function resoudre(int $idDemande, int $idResolveur, string $statut, string $commentaire = ''): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE demande_validation
             SET statut = :statut, resolved_at = NOW(), id_resolveur = :resolveur, commentaire = :commentaire
             WHERE id_demande = :id AND statut = \'en_attente\''
        );
        return $stmt->execute([
            ':statut'      => $statut,
            ':resolveur'   => $idResolveur,
            ':commentaire' => $commentaire,
            ':id'          => $idDemande,
        ]);
    }

    /**
     * Vérifie qu'une demande en attente de même type existe déjà pour un objet
     * (évite les doublons de demandes). Passe le champ d'identification en paramètre.
     */
    public function demandeExiste(int $idDossier, string $type, string $clePayload, mixed $valeur): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM demande_validation
             WHERE id_dossier = :dossier
               AND type_demande = :type
               AND statut = 'en_attente'
               AND JSON_EXTRACT(payload, :cle) = :valeur"
        );
        $stmt->execute([
            ':dossier' => $idDossier,
            ':type'    => $type,
            ':cle'     => '$.' . $clePayload,
            ':valeur'  => $valeur,
        ]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
