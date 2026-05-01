<?php
/**
 * Modèle User : CRUD complet + authentification.
 * Toutes les requêtes sont paramétrées (anti-injection SQL).
 * SELECT explicites, jamais SELECT *.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

class User
{
    /** Création avec hash bcrypt */
    public static function create(array $data): int
    {
        $hash = password_hash($data['password'], PASSWORD_BCRYPT);
        $stmt = db()->prepare(
            'INSERT INTO users (nom, prenom, email, password, role, ville, code_postal)
             VALUES (:nom, :prenom, :email, :pwd, :role, :ville, :cp)'
        );
        $stmt->execute([
            ':nom'    => $data['nom'],
            ':prenom' => $data['prenom'],
            ':email'  => strtolower($data['email']),
            ':pwd'    => $hash,
            ':role'   => $data['role'] ?? 'consommateur',
            ':ville'  => $data['ville'],
            ':cp'     => $data['code_postal'],
        ]);
        return (int) db()->lastInsertId();
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = db()->prepare(
            'SELECT id, nom, prenom, email, password, role, ville, code_postal
             FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->execute([':email' => strtolower($email)]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT id, nom, prenom, email, role, ville, code_postal, date_inscription
             FROM users WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Liste paginée (max 20 / page) */
    public static function listPaginated(int $page = 1, int $perPage = 20): array
    {
        $page    = max(1, $page);
        $perPage = min(50, max(1, $perPage));
        $offset  = ($page - 1) * $perPage;

        $stmt = db()->prepare(
            'SELECT id, nom, prenom, email, role, ville, code_postal, date_inscription
             FROM users
             ORDER BY id DESC
             LIMIT :lim OFFSET :off'
        );
        $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function count(): int
    {
        return (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    public static function update(int $id, array $data): bool
    {
        $stmt = db()->prepare(
            'UPDATE users
             SET nom = :nom, prenom = :prenom, email = :email,
                 ville = :ville, code_postal = :cp, role = :role
             WHERE id = :id'
        );
        return $stmt->execute([
            ':id'     => $id,
            ':nom'    => $data['nom'],
            ':prenom' => $data['prenom'],
            ':email'  => strtolower($data['email']),
            ':ville'  => $data['ville'],
            ':cp'     => $data['code_postal'],
            ':role'   => $data['role'],
        ]);
    }

    public static function updatePassword(int $id, string $password): bool
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = db()->prepare('UPDATE users SET password = :pwd WHERE id = :id');
        return $stmt->execute([':pwd' => $hash, ':id' => $id]);
    }

    public static function delete(int $id): bool
    {
        $stmt = db()->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    /** Liste des producteurs avec filtre ville (optionnel), paginée */
    public static function listProducteurs(?string $ville, int $page = 1, int $perPage = 12): array
    {
        $page    = max(1, $page);
        $perPage = min(50, max(1, $perPage));
        $offset  = ($page - 1) * $perPage;

        if ($ville) {
            $stmt = db()->prepare(
                'SELECT id, nom, prenom, ville, code_postal
                 FROM users
                 WHERE role = :r AND ville = :v
                 ORDER BY nom ASC
                 LIMIT :lim OFFSET :off'
            );
            $stmt->bindValue(':r', 'producteur');
            $stmt->bindValue(':v', $ville);
        } else {
            $stmt = db()->prepare(
                'SELECT id, nom, prenom, ville, code_postal
                 FROM users
                 WHERE role = :r
                 ORDER BY nom ASC
                 LIMIT :lim OFFSET :off'
            );
            $stmt->bindValue(':r', 'producteur');
        }
        $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countProducteurs(?string $ville): int
    {
        if ($ville) {
            $stmt = db()->prepare(
                'SELECT COUNT(*) FROM users WHERE role = :r AND ville = :v'
            );
            $stmt->execute([':r' => 'producteur', ':v' => $ville]);
        } else {
            $stmt = db()->prepare('SELECT COUNT(*) FROM users WHERE role = :r');
            $stmt->execute([':r' => 'producteur']);
        }
        return (int) $stmt->fetchColumn();
    }

    public static function distinctCities(): array
    {
        $stmt = db()->query(
            "SELECT DISTINCT ville FROM users WHERE role = 'producteur' ORDER BY ville ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
