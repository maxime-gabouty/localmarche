<?php
/**
 * Helpers de validation côté serveur + échappement HTML.
 */

declare(strict_types=1);

function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function clean(?string $s): string
{
    return trim((string) $s);
}

function validate_email(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_required(array $data, array $fields): array
{
    $errors = [];
    foreach ($fields as $f) {
        if (!isset($data[$f]) || trim((string) $data[$f]) === '') {
            $errors[$f] = 'Ce champ est obligatoire.';
        }
    }
    return $errors;
}

function validate_password(string $pwd): ?string
{
    if (strlen($pwd) < 8) {
        return 'Le mot de passe doit contenir au moins 8 caractères.';
    }
    return null;
}

function redirect(string $url): void
{
    header("Location: $url");
    exit;
}
