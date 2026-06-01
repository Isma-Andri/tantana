<?php

class DbSessionsHandler implements SessionHandlerInterface  // interface php qui définit les méthodes qu'on doit implementer dans le handler de session
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;                                  // la connexion pdo à utiliser dans toutes les méthodes
    }

    public function open($savePath, $sessionName): bool
    {
        // methode appelée au démarrage de la session
        return true;    // rien à signaler...
    }

    public function close(): bool 
    {
        // methode appelée à la fin de la session
        return true;
    }

    public function read($id, $data): string        
    {
        // lire les données de session depuis la table sessions dans la bdd
        $stmt = $this->pdo->prepare('SELECT data FROM sessions WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['data'] : '';    // si la session existe, return les data, sinon return chaine vide
    }

    public function write($id, $data): bool
    {
        // récuperation de l'id de l'user connecté si la session a un user
        $userId = $_SESSION['user']['id'] ?? null;

        $stmt = $this->pdo->prepare(
            'REPLACE INTO sessions (id, data, last_activity,user_id) VALUES (:id, :data, :last_activity, :user_id)'
        );

        return $stmt->execute([
            'id' => $id,
            'data' => $data,
            'last_activity' => time(),
            'user_id' => $userId,
        ]);

    }

    public function destroy($id): bool
    {
        // suppression de la session de la table quand l'user se déconnecte
        $stmt = $this->pdo-prepare('DELETE FROM sessions WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function gc($maxLifetime): bool
    {
        // nettoie les sessions qui ne sont plus valides (expirées après maxLifetime)
        $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE last_activity < :time');
        return $stmt->execute([
            'time' => time() - $maxLifetime,
        ]);
    }
}
