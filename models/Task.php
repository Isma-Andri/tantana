<?php
// Task.php
require_once __DIR__ . '/ ../config/database.php'

class Task
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getPDO();
    }

    // récup toutes les taches d'un projet avec leurs libellés de statut et priorité
    public function getByProjet(int $projetId): array
    {
        $stmt = $this->pdo->prepare(
            /* requete sql */
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // creation d'une nouvelle tache
    public function create(array $data): int
    {
    }

    // recuperation des priorités pour les formulaires
    public function getPriorites(): array
    {
        return $this->pdo->query('SELECT * FROM priorite ORDER BY id_priorite')->fetchAll();
    }
}

