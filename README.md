# Lien: https://github.com/maxime-gabouty/localmarche

#  LocalMarché

Plateforme web de circuits courts mettant en relation directe **producteurs locaux**
(maraîchers, artisans, agriculteurs) et **consommateurs de proximité**.
Site **éco-conçu** — pages < 100 Ko, aucune dépendance JavaScript externe,
polices système, pas d'image décorative.


##  URL de déploiement

> https://localmarche-production.up.railway.app/index.php

##  Équipe

| Membre | Rôle principal |
|--------|----------------|
| Gosse | Front-end (pages publiques, CSS) |
| Favry | Back-end (CRUD produits, commandes) |
| Gabouty | Authentification, sessions, sécurité |
| Sitbon | Base de données, déploiement, mesures Green IT |

##  Stack technique et justifications Green IT

| Couche | Technologie | Justification Green IT | Alternative écartée |
|--------|-------------|------------------------|---------------------|
| Front-end | HTML5 + CSS3 + JS vanilla | Bundle = 0 Ko de framework, polices système | React (+ ~200 Ko de JS) |
| Back-end | PHP 8 natif | Pas de couche d'abstraction, démarrage immédiat | Laravel (lourd, inutile ici) |
| Base de données | MySQL 8 (PDO) | Standard, requêtes paramétrées performantes | MongoDB (surdimensionné) |
| Hébergement | Railway | Plateforme légère, base intégrée | AWS / Vercel premium |
| Versioning | Git + GitHub | Standard collaboratif | — |

**Aucune dépendance Composer ou npm en V1** : l'autoload est manuel via `require_once`,
les chargements sont explicites et minimaux.

##  Structure du dépôt

```
localmarche/
├── frontend/                  # Pages publiques (point d'entrée web)
│   ├── index.php              # Accueil
│   ├── producers.php          # Liste des producteurs
│   ├── producer.php           # Fiche d'un producteur
│   ├── products.php           # Catalogue
│   ├── order.php              # Passer une commande
│   ├── orders.php             # Mes commandes (consommateur)
│   ├── order_detail.php       # Détail d'une commande
│   ├── order_status.php       # Endpoint POST changement statut
│   ├── login.php              # Connexion
│   ├── register.php           # Inscription
│   ├── logout.php             # Déconnexion
│   ├── account.php            # Profil consommateur (CRUD)
│   ├── dashboard.php          # Espace producteur
│   ├── product_form.php       # Création/édition produit
│   ├── product_delete.php     # Endpoint POST suppression produit
│   ├── admin/
│   │   ├── users.php          # Liste paginée utilisateurs
│   │   ├── user_form.php      # Création/édition utilisateur
│   │   └── user_delete.php    # Endpoint POST suppression
│   ├── pages/
│   │   ├── _header.php        # Layout commun (head, nav)
│   │   └── _footer.php        # Layout commun (eco indicator)
│   ├── css/style.css          # Feuille de style unique (~5 Ko)
│   └── js/main.js             # Confirmation des suppressions (~200 o)
├── backend/                   # Logique applicative (jamais exposée publiquement)
│   ├── config/database.php    # Connexion PDO + lecture .env
│   ├── helpers/
│   │   ├── session.php        # Sessions, CSRF, droits
│   │   ├── validators.php     # Validation serveur
│   │   └── green.php          # Mesure poids + estimation CO2
│   └── models/
│       ├── User.php           # CRUD utilisateurs complet
│       ├── Product.php        # CRUD produits complet
│       └── Order.php          # Commandes (transactionnel)
├── database/
│   └── schema.sql             # Création BDD + données de démo
├── docs/                      # Diagrammes UML, captures, rapport
├── .env.example               # Modèle de configuration
├── .gitignore
└── README.md
```

##  Lancer le projet localement

### Prérequis
- PHP 8.1+
- MySQL 8.x
- Git

### Installation

```bash
# 1. Cloner le dépôt
git clone https://github.com/<votre-org>/localmarche.git
cd localmarche

# 2. Configurer la base de données
mysql -u root -p < database/schema.sql

# 3. Configurer l'environnement
cp .env.example .env
# Éditer .env avec vos identifiants MySQL

# 4. Lancer le serveur PHP intégré
php -S 127.0.0.1:8000 -t frontend
```

Ouvrir [http://127.0.0.1:8000](http://127.0.0.1:8000).

### Comptes de démonstration

Tous les comptes du jeu de données ont pour mot de passe **`demo1234`**
(hash bcrypt déjà inséré dans `schema.sql`, prêt à l'emploi).

| Email | Rôle |
|-------|------|
| `admin@localmarche.fr` | admin |
| `pierre@ferme-bio.fr` | producteur |
| `marie@verger.fr` | producteur |
| `lucas.bernard@mail.fr` | consommateur |
| `emma.petit@mail.fr` | consommateur |

> Pour régénérer un hash si vous changez de mot de passe :
> ```bash
> php -r "echo password_hash('votre_mdp', PASSWORD_BCRYPT);"
> ```

##  Conventions de commit

```
type(scope): description courte

feat(auth): ajout de la connexion par session
fix(products): correction du calcul de pagination
docs(readme): mise à jour des instructions
chore(db): ajout d'un index sur products.disponible
```

Types autorisés : `feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`, `green`.

##  Engagements Green IT vérifiables

| Indicateur | Cible | Vérification |
|------------|-------|--------------|
| Poids page d'accueil | < 100 Ko | Footer dynamique + Lighthouse |
| Requêtes HTTP / page | ≤ 3 (HTML + CSS + JS) | Onglet Network du navigateur |
| Dépendances JS tierces | 0 | `package.json` absent |
| Polices externes | 0 | Aucun `@font-face`, aucun Google Fonts |
| Score EcoIndex visé | A ou B | https://www.ecoindex.fr |
| Score Lighthouse Performance | > 95 | DevTools |

Le **footer affiche en temps réel** le poids HTML+CSS+JS de la page courante
et une estimation CO₂ par visite (méthode Sustainable Web Design : 1.8 g CO₂ / Mo).

##  Sécurité

- Mots de passe hashés avec **bcrypt** (`password_hash` / `password_verify`)
- Toutes les requêtes SQL sont **paramétrées** (PDO, anti-injection)
- Protection **CSRF** sur tous les formulaires sensibles
- Cookies de session : `HttpOnly`, `SameSite=Lax`, `Secure` en HTTPS
- `session_regenerate_id` après login (anti-fixation)
- Vérification des droits avant chaque action sensible (producteur ne peut pas modifier le produit d'un autre)
- Aucune variable sensible dans le dépôt (`.env` dans `.gitignore`)

##  Rapport final

Le rapport PDF complet (sections 1 à 9, mesures avant/après EcoIndex,
captures Lighthouse, tableau de tests fonctionnels) est disponible dans `/docs/`.
