<?php

// ============================================================
// FICHIER : controllers/OrderController.php
// RÔLE    : Gère deux choses :
//           1. La validation du panier → création de la commande en BDD
//           2. L'affichage de la page de confirmation de commande
// ============================================================

// On charge le modèle commande pour avoir accès aux fonctions
// creerCommande(), ajouterItemCommande(), getCommandeAvecItems()
require_once ROOT . 'models/OrderModel.php';

// Vérification globale : si l'utilisateur n'est pas connecté on le redirige
// Cette vérification est aussi faite dans index.php mais on la double ici par sécurité
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// ---------------------------------------------------------------
// BLOC VALIDATION DE COMMANDE
// Se déclenche quand l'utilisateur clique sur "Valider la commande"
// dans la vue cart.php (bouton avec name="valider_commande")
// ---------------------------------------------------------------
if (isset($_POST['valider_commande'])) {

    // empty() vérifie si le panier est vide (tableau vide = rien à commander)
    // On ne peut pas créer une commande vide → retour au panier
    if (empty($_SESSION['panier'])) {
        header('Location: index.php?page=cart');
        exit();
    }

    // Calcul du total de la commande en parcourant tous les articles du panier
    $total = 0;
    foreach ($_SESSION['panier'] as $item) {
        // Pour chaque article : prix unitaire × quantité → ajouté au total global
        $total += $item['prix'] * $item['quantite'];
    }

    // creerCommande() insère une ligne dans la table "commandes" et retourne l'ID créé
    // On passe l'ID de l'utilisateur connecté et le total calculé
    $commande_id = creerCommande($pdo, $_SESSION['user_id'], $total);

    // On parcourt chaque article du panier pour créer les lignes dans commande_items
    // $produit_id = la clé du tableau (l'ID du produit)
    // $item = tableau avec nom, prix, quantite
    foreach ($_SESSION['panier'] as $produit_id => $item) {
        // ajouterItemCommande() insère une ligne dans commande_items pour ce produit
        // On sauvegarde le nom et le prix tels qu'ils étaient au moment de l'achat
        ajouterItemCommande($pdo, $commande_id, $produit_id, $item['nom'], $item['prix'], $item['quantite']);
    }

    // La commande est enregistrée → on vide le panier en session
    // Le client repart avec un panier vide après avoir commandé
    $_SESSION['panier'] = [];

    // On redirige vers la page de confirmation avec l'ID de la commande dans l'URL
    // Ça permet à la page de confirmation de retrouver et afficher la bonne commande
    header('Location: index.php?page=confirmation&commande_id=' . $commande_id);
    exit();
}

// ---------------------------------------------------------------
// BLOC PAGE DE CONFIRMATION
// Se déclenche quand on arrive sur ?page=confirmation dans l'URL
// Affiche le récapitulatif de la commande qui vient d'être passée
// ---------------------------------------------------------------
if (isset($_GET['page']) && $_GET['page'] === 'confirmation') {

    // ?? est l'opérateur "null coalescing" : si $_GET['commande_id'] n'existe pas → on met 0
    // intval() convertit en entier pour sécuriser (éviter les injections)
    $commande_id = intval($_GET['commande_id'] ?? 0);

    // On récupère la commande avec tous ses produits depuis la BDD
    // Retourne un tableau enrichi ou null si l'ID n'existe pas
    $commande = getCommandeAvecItems($pdo, $commande_id);

    // Double vérification de sécurité :
    // 1. La commande existe en BDD
    // 2. Elle appartient bien à l'utilisateur connecté
    // Sans ce test, un utilisateur pourrait voir la commande d'un autre en changeant l'ID dans l'URL
    if (!$commande || $commande['user_id'] != $_SESSION['user_id']) {
        // Commande inexistante ou qui ne lui appartient pas → retour à l'accueil
        header('Location: index.php?page=home');
        exit();
    }

    // Tout est OK → on affiche la page de confirmation avec les détails de la commande
    include ROOT . 'views/commande_confirmation.php';
}
