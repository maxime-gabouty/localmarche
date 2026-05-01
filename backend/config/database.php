<?php
/**
 * Configuration de la connexion à la base de données.
 * Lit les variables d'environnement (.env / Railway) - aucune valeur sensible en dur.
 */

declare(strict_types=1);

// Chargement minimaliste du .env (pas de dépendance Composer pour rester sobre)
$envPath = __DIR__ . '/../../.env';
if (is_file($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if (!getenv($key)) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $name = getenv('DB_NAME') ?: 'localmarche';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';

    $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // requêtes vraiment préparées
            PDO::ATTR_PERSISTENT         => false,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        // En production : ne jamais exposer le message d'erreur brut
        error_log('DB connection failed: ' . $e->getMessage());
        exit('Service indisponible.');
    }

    return $pdo;
}
