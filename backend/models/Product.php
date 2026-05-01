<?php
/**
 * Modèle Product : CRUD complet, paginé, sans SELECT *.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

class Product
{
    public static function create(array $data): int
    {
        $stmt = db()->prepare(
            'INSERT INTO products (nom, description, prix, unite, disponible, id_producteur)
             VALUES (:nom, :desc, :prix, :unite, :dispo, :idp)'
        );
        $stmt->execute([
            ':nom'   => $data['nom'],
            ':desc'  => $data['description'],
            ':prix'  => $data['prix'],
            ':unite' => $data['unite'],
            ':dispo' => !empty($data['disponible']) ? 1 : 0,
            ':idp'   => $data['id_producteur'],
        ]);
        return (int) db()->lastInsertId();
    }

    public static function findById(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT p.id, p.nom, p.description, p.prix, p.unite, p.disponible,
                    p.id_producteur, u.nom AS producteur_nom, u.prenom AS producteur_prenom,
                    u.ville AS producteur_ville
             FROM products p
             INNER JOIN users u ON u.id = p.id_producteur
             WHERE p.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Liste paginée d'un producteur donné */
    public static function listByProducer(int $idProducteur, int $page = 1, int $perPage = 20): array
    {
        $page    = max(1, $page);
        $perPage = min(50, max(1, $perPage));
        $offset  = ($page - 1) * $perPage;

        $stmt = db()->prepare(
            'SELECT id, nom, description, prix, unite, disponible
             FROM products
             WHERE id_producteur = :idp
             ORDER BY id DESC
             LIMIT :lim OFFSET :off'
        );
        $stmt->bindValue(':idp', $idProducteur, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $perPage,      PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset,       PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countByProducer(int $idProducteur): int
    {
        $stmt = db()->prepare(
            'SELECT COUNT(*) FROM products WHERE id_producteur = :idp'
        );
        $stmt->execute([':idp' => $idProducteur]);
        return (int) $stmt->fetchColumn();
    }

    /** Catalogue complet paginé, avec filtre ville optionnel */
    public static function catalog(?string $ville, int $page = 1, int $perPage = 12): array
    {
        $page    = max(1, $page);
        $perPage = min(50, max(1, $perPage));
        $offset  = ($page - 1) * $perPage;

        $sql = 'SELECT p.id, p.nom, p.description, p.prix, p.unite, p.disponible,
                       u.id AS producteur_id, u.nom AS producteur_nom,
                       u.prenom AS producteur_prenom, u.ville AS producteur_ville
                FROM products p
                INNER JOIN users u ON u.id = p.id_producteur
                WHERE p.disponible = 1';

        $params = [];
        if ($ville) {
            $sql .= ' AND u.ville = :v';
            $params[':v'] = $ville;
        }
        $sql .= ' ORDER BY p.id DESC LIMIT :lim OFFSET :off';

        $stmt = db()->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countCatalog(?string $ville): int
    {
        if ($ville) {
            $stmt = db()->prepare(
                'SELECT COUNT(*) FROM products p
                 INNER JOIN users u ON u.id = p.id_producteur
                 WHERE p.disponible = 1 AND u.ville = :v'
            );
            $stmt->execute([':v' => $ville]);
        } else {
            $stmt = db()->query(
                'SELECT COUNT(*) FROM products WHERE disponible = 1'
            );
        }
        return (int) $stmt->fetchColumn();
    }

    public static function update(int $id, array $data): bool
    {
        $stmt = db()->prepare(
            'UPDATE products
             SET nom = :nom, description = :desc, prix = :prix,
                 unite = :unite, disponible = :dispo
             WHERE id = :id'
        );
        return $stmt->execute([
            ':id'    => $id,
            ':nom'   => $data['nom'],
            ':desc'  => $data['description'],
            ':prix'  => $data['prix'],
            ':unite' => $data['unite'],
            ':dispo' => !empty($data['disponible']) ? 1 : 0,
        ]);
    }

    public static function delete(int $id): bool
    {
        $stmt = db()->prepare('DELETE FROM products WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    /** Vérifie qu'un produit appartient bien au producteur (sécurité) */
    public static function belongsTo(int $productId, int $userId): bool
    {
        $stmt = db()->prepare(
            'SELECT 1 FROM products WHERE id = :id AND id_producteur = :u LIMIT 1'
        );
        $stmt->execute([':id' => $productId, ':u' => $userId]);
        return (bool) $stmt->fetchColumn();
    }
}
