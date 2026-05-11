<?php
// models/User.php
// Modèle gérant toutes les requêtes SQL liées aux utilisateurs

require_once __DIR__ . '/../config/database.php';

class User
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getPDO();
    }

    /**
     * Crée un nouvel utilisateur.
     * Le mot de passe est hashé avec bcrypt avant insertion.
     *
     * @param string $nom
     * @param string $prenom
     * @param string $email
     * @param string $password     Mot de passe en clair
     * @param int    $id_role      1 = Membre, 2 = Chef de projet
     * @return int|false           ID du nouvel utilisateur, ou false en cas d'erreur
     */
    public function create(string $nom, string $prenom, string $email, string $password, int $id_role): int|false
    {
        // Vérifier si l'email existe déjà
        if ($this->findByEmail($email)) {
            return false;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->pdo->prepare(
            'INSERT INTO users (nom, prenom, email, password, id_role)
             VALUES (:nom, :prenom, :email, :password, :id_role)'
        );

        $stmt->execute([
            ':nom'      => trim($nom),
            ':prenom'   => trim($prenom),
            ':email'    => strtolower(trim($email)),
            ':password' => $hash,
            ':id_role'  => $id_role,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Recherche un utilisateur par son email.
     *
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.*, r.libelle AS role_libelle
             FROM users u
             JOIN roles r ON u.id_role = r.id_role
             WHERE u.email = :email
             LIMIT 1'
        );
        $stmt->execute([':email' => strtolower(trim($email))]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Recherche un utilisateur par son ID.
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.*, r.libelle AS role_libelle
             FROM users u
             JOIN roles r ON u.id_role = r.id_role
             WHERE u.id_user = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Vérifie les identifiants de connexion.
     *
     * @param string $email
     * @param string $password  Mot de passe en clair
     * @return array|false      Données utilisateur si OK, false sinon
     */
    public function authenticate(string $email, string $password): array|false
    {
        $user = $this->findByEmail($email);

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        return $user;
    }

    /**
     * Retourne tous les rôles disponibles.
     *
     * @return array
     */
    public function getRoles(): array
    {
        return $this->pdo->query('SELECT * FROM roles ORDER BY id_role')->fetchAll();
    }

    /**
     * Liste tous les utilisateurs (sauf le current user) — utile pour affecter des membres.
     *
     * @param int $excludeId
     * @return array
     */
    public function getAllExcept(int $excludeId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.id_user, u.nom, u.prenom, u.email, r.libelle AS role_libelle
             FROM users u
             JOIN roles r ON u.id_role = r.id_role
             WHERE u.id_user != :id
             ORDER BY u.nom, u.prenom'
        );
        $stmt->execute([':id' => $excludeId]);
        return $stmt->fetchAll();
    }
}
