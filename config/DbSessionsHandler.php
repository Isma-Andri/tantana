<?php

class DbSessionsHandler implements SessionHandlerInterface  // interface php qui définit les méthodes qu'on doit implementer dans le handler de session
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;                                  // la connexion pdo à utiliser dans toutes les méthodes
    }

    // signature conforme à SessionHandlerInterface
    public function open(string $savePath, string $sessionName): bool
    {
        // methode appelée au démarrage de la session
        return true;    // rien à signaler...
    }

    // signature conforme à SessionHandlerInterface
    public function close(): bool 
    {
        // methode appelée à la fin de la session
        return true;
    }

    // signature conforme à SessionHandlerInterface
    public function read(string $id): string|false    // quand php veut recupérer les données de session pour $id      
    {
        // lire les données de session depuis la table sessions dans la bdd
        $stmt = $this->pdo->prepare('SELECT data FROM sessions WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // si la session existe, return les data, sinon return chaine vide
        return $row ? (string) $row['data'] : '';
    }

    // signature conforme à SessionHandlerInterface
    public function write(string $id, string $data): bool     // quand php sauvegarde la session
    {
        // récuperation de l'id de l'user connecté si la session a un user
        $userId = $_SESSION['user']['id'] ?? null;

        // remplacer la ligne ou créer une nouvelle dans la table sessions
        $stmt = $this->pdo->prepare(
            'REPLACE INTO sessions (id, data, last_activity, user_id) VALUES (:id, :data, :last_activity, :user_id)'
        );

        return (bool) $stmt->execute([
            'id' => $id,
            'data' => $data,
            'last_activity' => time(),
            'user_id' => $userId,
        ]);

    }

    // signature conforme à SessionHandlerInterface
    public function destroy(string $id): bool
    {
        // suppression de la session de la table quand l'user se déconnecte (quand on appelle session_destroy())
        $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE id = :id');
        return (bool) $stmt->execute(['id' => $id]);
    }

    // signature conforme à SessionHandlerInterface
    public function gc(int $maxLifetime): int|false  // ramasse miettes des sessions
    {
        // nettoie les sessions qui ne sont plus valides (expirées après time() - $maxLifetime)
        $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE last_activity < :time');
        $ok = $stmt->execute([
            'time' => time() - $maxLifetime,
        ]);

        return $ok ? $stmt->rowCount() : false;
    }
}
