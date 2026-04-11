<?php

// ============================================================
// FICHIER : models/OrderModel.php
// RÔLE    : Fonctions pour créer et lire des commandes en BDD
//           Deux tables : commandes (entête) + commande_items (lignes)
// ============================================================

// --- FONCTION : creerCommande ---
// Crée une nouvelle commande dans la table "commandes" et retourne son ID
// Appelée dans OrderController quand l'utilisateur valide son panier
function creerCommande($pdo, $user_id, $total) {

    // INSERT INTO commandes crée une nouvelle ligne dans la table des commandes
    // user_id = qui a passé la commande
    // total = montant total calculé depuis le panier
    // NOW() = fonction MySQL qui insère automatiquement la date et heure actuelles
    // 'en_attente' = statut initial de toute nouvelle commande
    $req = $pdo->prepare("
        INSERT INTO commandes (user_id, total, date_commande, statut)
        VALUES (:user_id, :total, NOW(), 'en_attente')
    ");

    // On exécute l'insertion avec l'ID de l'utilisateur et le montant total
    $req->execute([
        ':user_id' => $user_id, // ID de l'utilisateur connecté (depuis $_SESSION['user_id'])
        ':total'   => $total    // Montant total calculé depuis le panier
    ]);

    // lastInsertId() retourne l'ID auto-incrémenté de la ligne qu'on vient d'insérer
    // On en a besoin pour rattacher les produits à cette commande dans commande_items
    return $pdo->lastInsertId();
}

// --- FONCTION : ajouterItemCommande ---
// Ajoute un produit dans la table "commande_items" (une ligne par produit commandé)
// On sauvegarde le nom et le prix AU MOMENT de l'achat car ils peuvent changer ensuite
function ajouterItemCommande($pdo, $commande_id, $produit_id, $nom, $prix, $quantite) {

    // INSERT INTO commande_items ajoute une ligne pour CE produit dans CETTE commande
    // commande_id = lien vers la commande parente (clé étrangère)
    // produit_id = lien vers le produit
    // nom_produit et prix_unitaire = snapshots des données du produit au moment de l'achat
    $req = $pdo->prepare("
        INSERT INTO commande_items (commande_id, produit_id, nom_produit, prix_unitaire, quantite)
        VALUES (:commande_id, :produit_id, :nom_produit, :prix_unitaire, :quantite)
    ");

    // On exécute l'insertion avec toutes les valeurs de cet article
    $req->execute([
        ':commande_id'   => $commande_id, // ID de la commande créée juste avant
        ':produit_id'    => $produit_id,  // ID du produit
        ':nom_produit'   => $nom,         // Nom du produit sauvegardé (snapshot)
        ':prix_unitaire' => $prix,        // Prix unitaire sauvegardé (snapshot)
        ':quantite'      => $quantite     // Nombre d'exemplaires commandés
    ]);
}

// --- FONCTION : getCommandeAvecItems ---
// Récupère une commande complète avec tous ses produits
// Utilisé dans la page de confirmation pour afficher le récapitulatif
function getCommandeAvecItems($pdo, $commande_id) {

    // On récupère les infos de la commande + le nom du client grâce au JOIN avec users
    // JOIN users u ON c.user_id = u.id = on relie chaque commande à son client
    // u.nom AS client_nom = on récupère le nom du client dans la même requête
    $req = $pdo->prepare("
        SELECT c.*, u.nom AS client_nom
        FROM commandes c
        JOIN users u ON c.user_id = u.id
        WHERE c.id = :id
    ");

    // On filtre sur l'ID de la commande spécifique
    $req->execute([':id' => $commande_id]);

    // fetch() récupère la ligne de la commande (ou FALSE si l'ID n'existe pas)
    $commande = $req->fetch();

    // Si la commande n'existe pas (ID invalide), on retourne null pour l'indiquer au contrôleur
    if (!$commande) return null;

    // On fait une 2e requête pour récupérer tous les produits de cette commande
    // WHERE commande_id = :commande_id filtre sur la commande concernée
    $req2 = $pdo->prepare("SELECT * FROM commande_items WHERE commande_id = :commande_id");
    $req2->execute([':commande_id' => $commande_id]);

    // On ajoute le tableau des produits dans le tableau de la commande sous la clé 'items'
    // Ainsi on retourne un seul objet $commande qui contient tout : infos + produits
    $commande['items'] = $req2->fetchAll(); // fetchAll() = toutes les lignes de produits

    // On retourne la commande enrichie avec ses produits
    return $commande;
}
