<?php
$pageTitle = 'Accueil';
require __DIR__ . '/pages/_header.php';
?>

<section class="hero">
  <h1>Producteurs et consommateurs locaux, en direct.</h1>
  <p>
    LocalMarché met en relation maraîchers, artisans et agriculteurs
    avec les habitants de leur région — sans intermédiaire ni surcouche
    technique inutile. Une plateforme volontairement légère, conçue
    pour consommer aussi peu d'énergie que possible.
  </p>
  <div class="row">
    <a href="/producers.php" class="btn">Voir les producteurs</a>
    <a href="/products.php" class="btn secondary">Parcourir les produits</a>
  </div>
</section>

<section>
  <h2>Trois engagements</h2>
  <div class="grid">
    <div class="card">
      <h3>Circuits courts</h3>
      <p class="muted">
        Achetez directement aux producteurs de votre ville ou des communes voisines.
        Aucune marge intermédiaire, aucune chaîne logistique surdimensionnée.
      </p>
    </div>
    <div class="card">
      <h3>Sobriété numérique</h3>
      <p class="muted">
        Pages &lt; 100 Ko, aucune dépendance JavaScript externe, polices système,
        pas d'image décorative. Chaque ressource chargée est nécessaire.
      </p>
    </div>
    <div class="card">
      <h3>Transparence</h3>
      <p class="muted">
        Le poids de chaque page et l'estimation CO₂ associée sont affichés
        en bas de cette page — vous voyez ce que coûte votre visite.
      </p>
    </div>
  </div>
</section>

<?php require __DIR__ . '/pages/_footer.php'; ?>
