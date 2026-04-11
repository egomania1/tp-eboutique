-- ============================================
-- BASE DE DONNÉES : eboutique
-- Fichier SQL à importer dans phpMyAdmin
-- ============================================

-- On crée la base de données
CREATE DATABASE IF NOT EXISTS eboutique;

-- On l'utilise
USE eboutique;

-- ============================================
-- TABLE : categories
-- Stocke les catégories de produits
-- ============================================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,  -- Numéro unique auto-incrémenté
    nom VARCHAR(100) NOT NULL           -- Nom de la catégorie
);

-- ============================================
-- TABLE : users
-- Stocke les utilisateurs (clients et vendeurs)
-- ============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,          -- Numéro unique auto-incrémenté
    nom VARCHAR(100) NOT NULL,                  -- Nom de l'utilisateur
    email VARCHAR(150) NOT NULL UNIQUE,         -- Email unique (pas deux fois le même)
    password VARCHAR(255) NOT NULL,             -- Mot de passe hashé (crypté)
    role ENUM('client', 'vendeur') DEFAULT 'client'  -- Rôle : client ou vendeur
);

-- ============================================
-- TABLE : products
-- Stocke les produits de la boutique
-- ============================================
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,          -- Numéro unique auto-incrémenté
    nom VARCHAR(150) NOT NULL,                  -- Nom du produit
    description TEXT,                           -- Description longue
    prix DECIMAL(10,2) NOT NULL,               -- Prix avec 2 décimales (ex: 19.99)
    categorie_id INT NOT NULL,                  -- Lien vers la catégorie
    image VARCHAR(255),                         -- Nom du fichier image
    vendeur_id INT NOT NULL,                    -- Lien vers le vendeur qui a ajouté le produit
    FOREIGN KEY (categorie_id) REFERENCES categories(id),  -- Clé étrangère vers categories
    FOREIGN KEY (vendeur_id) REFERENCES users(id)          -- Clé étrangère vers users
);

-- ============================================
-- TABLE : commandes
-- Stocke les commandes passées par les clients
-- ============================================
CREATE TABLE commandes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    date_commande DATETIME NOT NULL,
    statut ENUM('en_attente', 'expediee', 'livree', 'annulee') DEFAULT 'en_attente',
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- ============================================
-- TABLE : commande_items
-- Stocke les produits de chaque commande
-- ============================================
CREATE TABLE commande_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL,
    produit_id INT NOT NULL,
    nom_produit VARCHAR(150) NOT NULL,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    quantite INT NOT NULL,
    FOREIGN KEY (commande_id) REFERENCES commandes(id)
);

-- ============================================
-- DONNÉES DE DÉPART : catégories
-- ============================================
INSERT INTO categories (nom) VALUES
('T-shirt'),
('Casque'),
('DVD'),
('Livre'),
('Aspirateur');

-- ============================================
-- DONNÉES DE DÉPART : utilisateur vendeur test
-- Mot de passe : vendeur123 (hashé avec password_hash)
-- ============================================
INSERT INTO users (nom, email, password, role) VALUES
('Jean Vendeur', 'vendeur@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendeur'),
('Marie Client', 'client@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client');

-- ============================================
-- DONNÉES DE DÉPART : 5 produits cybersécurité/développement
-- ============================================
INSERT INTO products (nom, description, prix, categorie_id, image, vendeur_id) VALUES
('T-shirt blanc basique', 'T-shirt en coton 100%, coupe classique, disponible en plusieurs tailles.', 12.99, 1, '', 1),
('T-shirt graphique noir', 'T-shirt avec imprimé tendance, coton doux, lavage à 30°C.', 19.99, 1, '', 1),
('T-shirt col V gris', 'T-shirt col V coupe slim, idéal pour un look décontracté.', 15.99, 1, '', 1),
('Casque Sony WH-1000XM5', 'Casque Bluetooth avec réduction de bruit active, autonomie 30h.', 299.99, 2, '', 1),
('Casque JBL Tune 510BT', 'Casque sans fil léger, son puissant, autonomie 40h.', 49.99, 2, '', 1),
('DVD Le Seigneur des Anneaux', 'Trilogie complète en DVD, version longue, sous-titres français inclus.', 24.99, 3, '', 1),
('DVD Interstellar', 'Film de Christopher Nolan en DVD, édition collector avec bonus.', 12.99, 3, '', 1),
('Livre PHP 8 : les fondamentaux', 'Apprenez PHP 8 de zéro à avancé avec des exercices pratiques.', 34.99, 4, '', 1),
('Livre JavaScript moderne', 'Guide complet sur ES6+, les APIs modernes et les bonnes pratiques.', 29.99, 4, '', 1),
('Aspirateur Dyson V15', 'Aspirateur balai sans fil, puissant, autonomie 60 minutes.', 499.99, 5, '', 1),
('Aspirateur Rowenta Silence Force', 'Aspirateur traineau silencieux, grande capacité, filtre HEPA.', 149.99, 5, '', 1);
