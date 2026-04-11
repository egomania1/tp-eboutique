# eBoutique — TP PHP MVC

Application web e-commerce développée en PHP avec une architecture MVC.

## Fonctionnalités

- Catalogue de produits organisés par catégorie
- Inscription et connexion (client / vendeur)
- Gestion des rôles : un vendeur peut ajouter des produits, un client ne peut pas
- Ajout de produits avec upload d'image (espace vendeur)
- Panier d'achat stocké en session
- Calcul du total du panier
- Validation de commande
- Page de profil utilisateur

## Technologies utilisées

- PHP (architecture MVC)
- MySQL avec PDO (requêtes préparées)
- Sessions PHP
- HTML / CSS
- XAMPP (serveur local)

## Structure du projet

```
eboutique/
├── config/
│   └── db.php               # Connexion à la base de données
├── controllers/
│   ├── AuthController.php   # Inscription, connexion, déconnexion
│   ├── CartController.php   # Gestion du panier
│   ├── OrderController.php  # Validation de commande
│   ├── ProductController.php# Catalogue et ajout de produits
│   └── ProfileController.php# Profil utilisateur
├── models/
│   ├── UserModel.php        # Requêtes BDD utilisateurs
│   ├── ProductModel.php     # Requêtes BDD produits
│   ├── CategoryModel.php    # Requêtes BDD catégories
│   └── OrderModel.php       # Requêtes BDD commandes
├── views/
│   ├── landing.php          # Page d'accueil
│   ├── login.php            # Connexion
│   ├── register.php         # Inscription
│   ├── catalogue_simple.php # Catalogue produits
│   ├── add_product.php      # Formulaire ajout produit
│   ├── cart.php             # Panier
│   ├── profile.php          # Profil utilisateur
│   └── commande_confirmation.php
├── uploads/                 # Images produits et avatars
├── index.php                # Point d'entrée unique (routeur)
└── eboutique.sql            # Base de données SQL
```

## Installation

1. Cloner le projet dans `xampp/htdocs/`
```bash
git clone https://github.com/egomania1/tp-eboutique.git eboutique
```

2. Importer la base de données dans phpMyAdmin
   - Créer une base nommée `eboutique`
   - Importer le fichier `eboutique.sql`

3. Lancer Apache et MySQL depuis XAMPP

4. Accéder au site : `http://localhost/eboutique`

## Sécurité

- Mots de passe hashés avec `password_hash()` / `password_verify()`
- Requêtes préparées PDO contre les injections SQL
- Vérification du rôle avant chaque action sensible
- Validation du type et de la taille des fichiers uploadés
