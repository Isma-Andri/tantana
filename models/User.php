<?php
// models/User.php

require_once __DIR__ . '/../config/database.php';

class User
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getPDO();
    }

    public function create(string $nom, string $prenom, string $email, string $password, int $id_role): int|false
    {
        if ($this->findByEmail($email)) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO users (nom, prenom, email, password, id_role)
             VALUES (:nom, :prenom, :email, :password, :id_role)'
        );
        $stmt->execute([
            ':nom'      => trim($nom),
            ':prenom'   => trim($prenom),
            ':email'    => strtolower(trim($email)),
            ':password' => password_hash($password, PASSWORD_BCRYPT),
            ':id_role'  => $id_role,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.*, r.libelle AS role_libelle
             FROM users u JOIN roles r ON u.id_role = r.id_role
             WHERE u.email = :email LIMIT 1'
        );
        $stmt->execute([':email' => strtolower(trim($email))]);

        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.*, r.libelle AS role_libelle
             FROM users u JOIN roles r ON u.id_role = r.id_role
             WHERE u.id_user = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    // Vérifie email + mot de passe, retourne les données utilisateur ou false
    public function authenticate(string $email, string $password): array|false
    {
        $user = $this->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        return $user;
    }

    public function getRoles(): array
    {
        return $this->pdo->query('SELECT * FROM roles ORDER BY id_role')->fetchAll();
    }
}
