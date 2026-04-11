<?php

// ============================================================
// FICHIER : controllers/CartController.php
// RÔLE    : Gère le panier d'achat stocké en session
//           Ajout, suppression, vidage et calcul du total
// ============================================================

// On charge le modèle produit pour pouvoir récupérer les infos d'un produit par son ID
require_once ROOT . 'models/ProductModel.php';

// Si la clé 'panier' n'existe pas encore dans $_SESSION on l'initialise à un tableau vide
// Ça évite les erreurs "undefined index" quand on essaie de lire ou modifier le panier
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = []; // Tableau vide = panier vide au départ
}

// ---------------------------------------------------------------
// BLOC AJOUT AU PANIER
// Se déclenche quand l'utilisateur clique sur "Ajouter au panier"
// dans la vue catalogue (bouton avec name="ajouter_panier")
// ---------------------------------------------------------------
if (isset($_POST['ajouter_panier'])) {

    // intval() convertit la valeur en entier (integer)
    // Sécurité : si quelqu'un envoie "1; DROP TABLE products" → intval() retourne 1
    $produit_id = intval($_POST['produit_id']); // ID du produit envoyé par le formulaire caché

    // Vérification : seul un utilisateur connecté peut ajouter au panier
    // Si 'user_id' n'est pas dans la session → non connecté → on redirige vers le login
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?page=login');
        exit();
    }

    // On récupère les infos complètes du produit depuis la BDD (nom, prix...)
    // getProduitParId() retourne un tableau avec toutes les colonnes du produit, ou FALSE
    $produit = getProduitParId($pdo, $produit_id);

    // On vérifie que le produit existe bien en BDD (évite les ID inventés)
    if ($produit) {

        // isset() vérifie si ce produit est déjà dans le panier (clé = ID du produit)
        if (isset($_SESSION['panier'][$produit_id])) {
            // Le produit est déjà dans le panier → on augmente juste la quantité de 1
            $_SESSION['panier'][$produit_id]['quantite']++;

        } else {
            // Le produit n'est pas encore dans le panier → on l'ajoute avec quantité 1
            // On utilise l'ID du produit comme clé pour pouvoir le retrouver facilement
            $_SESSION['panier'][$produit_id] = [
                'nom'      => $produit['nom'],  // On sauvegarde le nom au moment de l'ajout
                'prix'     => $produit['prix'], // On sauvegarde le prix au moment de l'ajout
                'quantite' => 1                 // Première fois dans le panier = 1 exemplaire
            ];
        }
    }

    // On redirige vers le catalogue avec un message de confirmation dans l'URL
    header('Location: index.php?page=catalogue&message=panier_ok');
    exit();
}

// ---------------------------------------------------------------
// BLOC SUPPRESSION D'UN ARTICLE DU PANIER
// Se déclenche via ?action=supprimer_panier&id=XX dans l'URL
// ---------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'supprimer_panier') {

    // On récupère l'ID du produit à supprimer depuis l'URL, converti en entier (sécurité)
    $produit_id = intval($_GET['id']);

    // unset() supprime une entrée d'un tableau
    // Ici on supprime la case [$produit_id] du tableau $_SESSION['panier']
    unset($_SESSION['panier'][$produit_id]);

    // On redirige vers le panier après la suppression
    header('Location: index.php?page=cart');
    exit();
}

// ---------------------------------------------------------------
// BLOC VIDAGE COMPLET DU PANIER
// Se déclenche via ?action=vider_panier dans l'URL
// ---------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'vider_panier') {

    // On réassigne un tableau vide à $_SESSION['panier']
    // Ça efface tous les produits du panier d'un coup
    $_SESSION['panier'] = [];

    // On redirige vers la page panier (qui affichera "votre panier est vide")
    header('Location: index.php?page=cart');
    exit();
}

// ---------------------------------------------------------------
// CALCUL DU TOTAL
// On calcule le montant total avant d'afficher la vue
// ---------------------------------------------------------------

// On initialise le total à 0
$total = 0;

// foreach parcourt chaque article du panier
// $item = tableau avec 'nom', 'prix', 'quantite' de chaque produit
foreach ($_SESSION['panier'] as $item) {
    // Pour chaque article : prix × quantité, ajouté au total
    // ex : T-shirt à 25€ × 2 = 50€ ajoutés au total
    $total += $item['prix'] * $item['quantite'];
}

// On inclut la vue du panier en lui passant $total et $_SESSION['panier'] (déjà accessibles)
include ROOT . 'views/cart.php';
