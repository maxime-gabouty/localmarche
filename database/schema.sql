-- =====================================================================
-- LocalMarché - Schéma de base de données
-- TI616 Numérique Durable - EFREI Paris 2025-2026
-- MySQL 8.x - Conforme au MCD du Livrable 1
-- =====================================================================

CREATE DATABASE IF NOT EXISTS localmarche
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE localmarche;

-- ---------------------------------------------------------------------
-- Table users : Consommateurs / Producteurs / Administrateurs
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nom             VARCHAR(80)  NOT NULL,
  prenom          VARCHAR(80)  NOT NULL,
  email           VARCHAR(150) NOT NULL,
  password        VARCHAR(255) NOT NULL,            -- bcrypt
  role            ENUM('consommateur','producteur','admin') NOT NULL DEFAULT 'consommateur',
  ville           VARCHAR(80)  NOT NULL,
  code_postal     VARCHAR(10)  NOT NULL,
  date_inscription DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_users_email (email),
  KEY idx_users_role (role),
  KEY idx_users_cp   (code_postal)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table products : entité métier principale
-- ---------------------------------------------------------------------
CREATE TABLE products (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nom           VARCHAR(120) NOT NULL,
  description   VARCHAR(200) NOT NULL,
  prix          DECIMAL(8,2) NOT NULL,
  unite         ENUM('kg','piece','litre','botte') NOT NULL DEFAULT 'kg',
  disponible    TINYINT(1)   NOT NULL DEFAULT 1,
  id_producteur INT UNSIGNED NOT NULL,
  date_creation DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_products_producteur (id_producteur),
  KEY idx_products_dispo (disponible),
  CONSTRAINT fk_products_user
    FOREIGN KEY (id_producteur) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table orders : commande passée par un consommateur à un producteur
-- ---------------------------------------------------------------------
CREATE TABLE orders (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_consommateur INT UNSIGNED NOT NULL,
  id_producteur   INT UNSIGNED NOT NULL,
  date_commande   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  statut          ENUM('en_attente','validee','refusee') NOT NULL DEFAULT 'en_attente',
  total           DECIMAL(10,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_orders_consommateur (id_consommateur),
  KEY idx_orders_producteur (id_producteur),
  KEY idx_orders_statut (statut),
  CONSTRAINT fk_orders_consommateur
    FOREIGN KEY (id_consommateur) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_orders_producteur
    FOREIGN KEY (id_producteur) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table order_items : table de liaison N-N entre orders et products
-- ---------------------------------------------------------------------
CREATE TABLE order_items (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_order      INT UNSIGNED NOT NULL,
  id_product    INT UNSIGNED NOT NULL,
  quantite      DECIMAL(8,2) NOT NULL,
  prix_unitaire DECIMAL(8,2) NOT NULL,
  PRIMARY KEY (id),
  KEY idx_items_order (id_order),
  KEY idx_items_product (id_product),
  CONSTRAINT fk_items_order
    FOREIGN KEY (id_order) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_items_product
    FOREIGN KEY (id_product) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- Données de démonstration (seed)
-- Mots de passe : "demo1234" hashé via password_hash(PASSWORD_BCRYPT).
-- Hash bcrypt vérifié, fonctionnel avec password_verify().
-- =====================================================================
INSERT INTO users (nom, prenom, email, password, role, ville, code_postal) VALUES
('Martin',  'Sophie', 'admin@localmarche.fr',     '$2y$10$vilDveqIKyIXUwwYBigk5.iJariEu0oo39Nc9iYYjqIdfdi6gs6cO', 'admin',        'Limoges',      '87000'),
('Dubois',  'Pierre', 'pierre@ferme-bio.fr',      '$2y$10$vilDveqIKyIXUwwYBigk5.iJariEu0oo39Nc9iYYjqIdfdi6gs6cO', 'producteur',   'Limoges',      '87000'),
('Lefevre', 'Marie',  'marie@verger.fr',          '$2y$10$vilDveqIKyIXUwwYBigk5.iJariEu0oo39Nc9iYYjqIdfdi6gs6cO', 'producteur',   'Saint-Junien', '87200'),
('Bernard', 'Lucas',  'lucas.bernard@mail.fr',    '$2y$10$vilDveqIKyIXUwwYBigk5.iJariEu0oo39Nc9iYYjqIdfdi6gs6cO', 'consommateur', 'Limoges',      '87000'),
('Petit',   'Emma',   'emma.petit@mail.fr',       '$2y$10$vilDveqIKyIXUwwYBigk5.iJariEu0oo39Nc9iYYjqIdfdi6gs6cO', 'consommateur', 'Couzeix',      '87270');

INSERT INTO products (nom, description, prix, unite, disponible, id_producteur) VALUES
('Tomates anciennes',    'Mélange de tomates Cœur de bœuf, Noire de Crimée, Green Zebra. Récolte du jour.', 4.50, 'kg',    1, 2),
('Pommes de terre',      'Pommes de terre Charlotte, culture sans pesticides, terre du Limousin.',         2.20, 'kg',    1, 2),
('Œufs fermiers',        'Œufs de poules élevées en plein air, ramassés chaque matin.',                    3.80, 'piece', 1, 2),
('Pommes Reinette',      'Variété ancienne, croquante et acidulée. Verger en agriculture raisonnée.',      2.50, 'kg',    1, 3),
('Jus de pomme',         'Jus pressé à froid, sans sucre ajouté, en bouteille consignée.',                 4.00, 'litre', 1, 3),
('Salade verte',         'Laitue feuille de chêne, cueillie le matin même.',                               1.20, 'piece', 0, 2);
