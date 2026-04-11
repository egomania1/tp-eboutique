<?php

// ============================================================
// FICHIER : index.php
// RÔLE    : Point d'entrée unique du site — "routeur" principal
//           Toutes les pages passent par ici via ?page=xxx
// ============================================================

// session_start() démarre le système de session PHP
// Une session permet de conserver des données entre les pages (utilisateur connecté, panier...)
// Elle doit être appelée AVANT tout affichage HTML
session_start();

// define() crée une constante nommée ROOT qui contient le chemin absolu vers la racine du projet
// __DIR__ est une variable magique PHP qui retourne le dossier du fichier actuel
// On ajoute '/' à la fin pour pouvoir écrire ROOT.'views/login.php' directement
define('ROOT', __DIR__ . '/');

// require_once inclut le fichier de connexion à la base de données
// require_once = inclure une seule fois, et planter si le fichier est introuvable
// Après cette ligne, la variable $pdo est disponible dans tout le script
require_once ROOT . 'config/db.php';

// $_GET est un tableau qui contient les paramètres passés dans l'URL (après le ?)
// isset() vérifie que la clé 'page' existe dans $_GET et qu'elle n'est pas null
// Si ?page=catalogue → $page vaut 'catalogue'
// Si rien dans l'URL   → $page vaut 'home' (valeur par défaut)
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// switch évalue la variable $page et exécute le bloc correspondant
// C'est le "routeur" : selon la page demandée, on charge le bon contrôleur
switch ($page) {

    case 'home':
        // Page d'accueil → on charge directement la vue landing (pas besoin de contrôleur)
        include ROOT . 'views/landing.php';
        break; // break empêche d'exécuter les cases suivants

    case 'catalogue':
        // Page catalogue → le contrôleur va récupérer les produits et afficher la vue
        require_once ROOT . 'controllers/ProductController.php';
        break;

    case 'login':
        // Page de connexion → l'AuthController gère à la fois login, register et logout
        require_once ROOT . 'controllers/AuthController.php';
        break;

    case 'register':
        // Page d'inscription → même contrôleur que login, il détecte la page via $_GET['page']
        require_once ROOT . 'controllers/AuthController.php';
        break;

    case 'logout':
        // Déconnexion → même contrôleur, il détecte l'action 'deconnexion' via $_GET['action']
        require_once ROOT . 'controllers/AuthController.php';
        break;

    case 'profile':
        // Page de profil → le ProfileController gère l'affichage et la mise à jour
        require_once ROOT . 'controllers/ProfileController.php';
        break;

    case 'update_profile':
        // Mise à jour du profil (bio + avatar) → même contrôleur que profile
        require_once ROOT . 'controllers/ProfileController.php';
        break;

    case 'cart':
        // Le panier est réservé aux utilisateurs connectés
        // isset($_SESSION['user_id']) vérifie si l'utilisateur est connecté
        // Le '!' inverse la condition : si user_id N'est PAS dans la session → non connecté
        if (!isset($_SESSION['user_id'])) {
            // header('Location: ...') redirige le navigateur vers une autre page
            header('Location: index.php?page=login');
            // exit() arrête immédiatement le script après la redirection pour éviter d'exécuter le reste
            exit();
        }
        require_once ROOT . 'controllers/CartController.php';
        break;

    case 'order':
        // La validation de commande aussi nécessite d'être connecté
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=login');
            exit();
        }
        require_once ROOT . 'controllers/OrderController.php';
        break;

    case 'confirmation':
        // La page de confirmation après commande nécessite aussi d'être connecté
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=login');
            exit();
        }
        require_once ROOT . 'controllers/OrderController.php';
        break;

    default:
        // Si la valeur de $page ne correspond à aucun case connu,
        // on affiche le catalogue par défaut plutôt qu'une page blanche
        require_once ROOT . 'controllers/ProductController.php';
        break;
}
