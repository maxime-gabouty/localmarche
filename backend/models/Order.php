<?php
/**
 * Modèle Order : création d'une commande avec ses items en transaction.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

class Order
{
    /**
     * Crée une commande à partir d'un panier.
     * $items = [['id_product' => int, 'quantite' => float], ...]
     * Tous les produits doivent appartenir au même producteur (V1 simple).
     */
    public static function createFromCart(int $idConsommateur, array $items): int
    {
        if (count($items) === 0) {
            throw new InvalidArgumentException('Panier vide.');
        }

        $pdo = db();
        $pdo->beginTransaction();

        try {
            // Récupération en une seule requête de tous les produits du panier
            $ids = array_map(fn($i) => (int) $i['id_product'], $items);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare(
                "SELECT id, prix, id_producteur, disponible
                 FROM products WHERE id IN ($placeholders)"
            );
            $stmt->execute($ids);
            $rows = $stmt->fetchAll();

            if (count($rows) !== count($ids)) {
                throw new RuntimeException('Produit introuvable.');
            }

            $byId = [];
            foreach ($rows as $r) {
                if (!$r['disponible']) {
                    throw new RuntimeException('Produit indisponible.');
                }
                $byId[(int) $r['id']] = $r;
            }

            // Vérifier producteur unique
            $producteurs = array_unique(array_map(fn($r) => (int) $r['id_producteur'], $rows));
            if (count($producteurs) > 1) {
                throw new RuntimeException('Tous les produits doivent provenir du même producteur.');
            }
            $idProducteur = $producteurs[0];

            // Calcul du total
            $total = 0.0;
            foreach ($items as $it) {
                $p = $byId[(int) $it['id_product']];
                $total += (float) $p['prix'] * (float) $it['quantite'];
            }

            // Création de la commande
            $stmt = $pdo->prepare(
                'INSERT INTO orders (id_consommateur, id_producteur, statut, total)
                 VALUES (:c, :p, :s, :t)'
            );
            $stmt->execute([
                ':c' => $idConsommateur,
                ':p' => $idProducteur,
                ':s' => 'en_attente',
                ':t' => $total,
            ]);
            $orderId = (int) $pdo->lastInsertId();

            // Insertion des items
            $stmt = $pdo->prepare(
                'INSERT INTO order_items (id_order, id_product, quantite, prix_unitaire)
                 VALUES (:o, :p, :q, :pu)'
            );
            foreach ($items as $it) {
                $p = $byId[(int) $it['id_product']];
                $stmt->execute([
                    ':o'  => $orderId,
                    ':p'  => (int) $it['id_product'],
                    ':q'  => (float) $it['quantite'],
                    ':pu' => (float) $p['prix'],
                ]);
            }

            $pdo->commit();
            return $orderId;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function listForConsumer(int $idUser, int $page = 1, int $perPage = 20): array
    {
        $page    = max(1, $page);
        $perPage = min(50, max(1, $perPage));
        $offset  = ($page - 1) * $perPage;

        $stmt = db()->prepare(
            'SELECT o.id, o.date_commande, o.statut, o.total,
                    u.nom AS prod_nom, u.prenom AS prod_prenom, u.ville AS prod_ville
             FROM orders o
             INNER JOIN users u ON u.id = o.id_producteur
             WHERE o.id_consommateur = :u
             ORDER BY o.date_commande DESC
             LIMIT :lim OFFSET :off'
        );
        $stmt->bindValue(':u',   $idUser,  PDO::PARAM_INT);
        $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function listForProducer(int $idUser, int $page = 1, int $perPage = 20): array
    {
        $page    = max(1, $page);
        $perPage = min(50, max(1, $perPage));
        $offset  = ($page - 1) * $perPage;

        $stmt = db()->prepare(
            'SELECT o.id, o.date_commande, o.statut, o.total,
                    u.nom AS cons_nom, u.prenom AS cons_prenom, u.ville AS cons_ville
             FROM orders o
             INNER JOIN users u ON u.id = o.id_consommateur
             WHERE o.id_producteur = :u
             ORDER BY o.date_commande DESC
             LIMIT :lim OFFSET :off'
        );
        $stmt->bindValue(':u',   $idUser,  PDO::PARAM_INT);
        $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function items(int $orderId): array
    {
        $stmt = db()->prepare(
            'SELECT oi.id, oi.quantite, oi.prix_unitaire,
                    p.nom AS produit_nom, p.unite
             FROM order_items oi
             INNER JOIN products p ON p.id = oi.id_product
             WHERE oi.id_order = :o'
        );
        $stmt->execute([':o' => $orderId]);
        return $stmt->fetchAll();
    }

    public static function updateStatus(int $orderId, string $status): bool
    {
        if (!in_array($status, ['en_attente', 'validee', 'refusee'], true)) {
            return false;
        }
        $stmt = db()->prepare('UPDATE orders SET statut = :s WHERE id = :id');
        return $stmt->execute([':s' => $status, ':id' => $orderId]);
    }
}
