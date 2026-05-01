  </div><!-- /.container -->
</main>
<?php
require_once __DIR__ . '/../../backend/helpers/green.php';
// Récupération du HTML accumulé depuis _header.php pour mesurer son poids
$html = ob_get_clean();
$size = page_size_estimate($html);
$co2  = co2_estimate_grams($size);
// On réémet l'intégralité du HTML capturé
echo $html;
?>
<footer class="site">
  <div class="container eco">
    <div>
      <strong>LocalMarché</strong> — circuits courts &amp; sobriété numérique
    </div>
    <div class="ecoline">
      <span title="Poids estimé HTML+CSS+JS de cette page">
        Poids : <strong><?= format_kb($size) ?></strong>
      </span>
      <span title="Estimation CO2 par visite (méthode Sustainable Web Design)">
        CO₂ : <strong><?= number_format($co2, 3, ',', ' ') ?> g</strong> / visite
      </span>
    </div>
  </div>
</footer>
</body>
</html>
