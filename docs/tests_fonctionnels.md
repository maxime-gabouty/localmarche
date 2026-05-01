# Tests fonctionnels — LocalMarché

> Légende : ✓ = OK · ✗ = KO · ❑ = non testé

| # | Scénario | Procédure | Résultat attendu | Statut |
|---|----------|-----------|------------------|:----:|
| T01 | Inscription valide | `/register.php` → remplir tous les champs avec un email unique, mdp ≥ 8 car. | Ligne ajoutée dans `users`, redirection vers `/account.php` ou `/dashboard.php` | ✓ |
| T02 | Inscription email vide | `/register.php` → soumettre sans email | Message d'erreur côté serveur, aucune insertion | ✓ |
| T03 | Inscription email déjà pris | `/register.php` → soumettre avec `admin@localmarche.fr` | Erreur « Cet email est déjà utilisé. » | ✓ |
| T04 | Connexion identifiants valides | `/login.php` → `pierre@ferme-bio.fr` / `demo1234` | Redirection vers `/dashboard.php` | ✓ |
| T05 | Connexion mauvais mot de passe | `/login.php` → `pierre@ferme-bio.fr` / `wrong` | « Identifiants invalides. », pas de session créée | ✓ |
| T06 | Modification profil | Connecté, `/account.php` → changer prénom et soumettre | UPDATE sur `users`, message « Profil mis à jour. » | ✓ |
| T07 | Suppression compte (consommateur) | `/account.php` → bouton « Supprimer mon compte », confirmation JS | DELETE sur `users`, session détruite, redirection accueil | ✓ |
| T08 | Liste paginée utilisateurs | Admin, `/admin/users.php` | Tableau, max 20 lignes, navigation pagination | ✓ |
| T09 | Création produit (CRUD entité métier) | Producteur connecté, `/product_form.php` | INSERT dans `products`, retour au dashboard avec produit visible | ✓ |
| T10 | Modification produit | Dashboard → « Modifier » sur un produit | UPDATE en BDD, modifications affichées | ✓ |
| T11 | Suppression produit avec confirmation | Dashboard → « Supprimer » → confirmation JS native | DELETE sur `products`, suppression confirmée | ✓ |
| T12 | Passer une commande | Consommateur, `/order.php?produit=1` → quantité valide | Ligne dans `orders` + `order_items`, statut « en_attente » | ✓ |
| T13 | Validation commande par producteur | `/dashboard.php` → bouton « Valider » sur une commande en attente | UPDATE `orders.statut = 'validee'` | ✓ |
| T14 | Accès page protégée non connecté | `/dashboard.php` sans session | Redirection 302 vers `/login.php` | ✓ |
| T15 | Accès admin par non-admin | Consommateur connecté → `/admin/users.php` | HTTP 403, accès refusé | ✓ |
| T16 | Filtre producteurs par ville | `/producers.php?ville=Limoges` | Affichage uniquement des producteurs limougeauds | ✓ |
| T17 | Pagination catalogue | `/products.php?page=2` (avec > 12 produits) | Affichage des produits 13-24 | ✓ |
| T18 | Tentative SQL injection | Inscription avec email = `' OR 1=1 --` | Échec validation email, requête PDO paramétrée bloque l'injection | ✓ |
| T19 | Modification produit d'un autre producteur | Producteur A → URL `/product_form.php?id=<produit-de-B>` | HTTP 404 (vérification `belongsTo`) | ✓ |
| T20 | Footer Green IT actif | Toute page | Affiche « Poids : X Ko · CO₂ : Y g / visite » correctement calculé | ✓ |

## Tests de sécurité (Étape 6.3)

| Vérification | Méthode | Résultat |
|--------------|---------|:--------:|
| Aucun mot de passe en clair en BDD | `SELECT email, password FROM users LIMIT 5;` → vérifier que tous les hashes commencent par `$2y$` | ✓ |
| Aucune variable sensible dans Git | `git log --all -p \| grep -iE 'password=\|api_key\|secret'` → aucun match | ✓ |
| Rejet des entrées malformées | Inscription avec `nom = '; DROP TABLE users--` | ✓ : champ stocké comme texte, aucune injection |
| Pages protégées inaccessibles via URL | Sans session, accès direct à `/dashboard.php`, `/admin/users.php`, `/account.php`, `/product_form.php` | ✓ : redirection vers `/login.php` ou HTTP 403 |
