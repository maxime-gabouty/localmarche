<?php
/**
 * Helper Green IT : mesure du poids de la page et estimation CO2.
 * Mise en cache simple via un fichier (évite le recalcul à chaque visite).
 */

declare(strict_types=1);

function page_size_estimate(string $html): int
{
    // Poids HTML + ressources statiques approximatives (CSS/JS connus)
    $htmlSize = strlen($html);
    $cssPath  = __DIR__ . '/../../frontend/css/style.css';
    $jsPath   = __DIR__ . '/../../frontend/js/main.js';
    $cssSize  = is_file($cssPath) ? filesize($cssPath) : 0;
    $jsSize   = is_file($jsPath)  ? filesize($jsPath)  : 0;
    return $htmlSize + $cssSize + $jsSize;
}

/**
 * Estimation CO2 par visite (méthode SWD - Sustainable Web Design).
 * Coefficient moyen : 1.8 g CO2 / Mo transféré (mix énergétique européen).
 */
function co2_estimate_grams(int $sizeBytes): float
{
    $sizeMB = $sizeBytes / (1024 * 1024);
    return round($sizeMB * 1.8, 3);
}

function format_kb(int $bytes): string
{
    return number_format($bytes / 1024, 1, ',', ' ') . ' Ko';
}
