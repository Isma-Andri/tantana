<?php
// models/Workflow.php

require_once __DIR__ . '/../config/database.php';

class Workflow
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getPDO();
    }

    public function getAllStatuts(): array
    {
        return $this->pdo->query('SELECT * FROM statut_workflow ORDER BY id_workflow')->fetchAll();
    }

    public function getStatutById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM statut_workflow WHERE id_workflow = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }
}
