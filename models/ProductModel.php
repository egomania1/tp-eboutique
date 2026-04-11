<?php

// ============================================================
// FICHIER : models/ProductModel.php
// RÔLE    : Toutes les fonctions qui touchent la table "products"
//           en base de données (lire, ajouter, chercher)
// ============================================================

// --- FONCTION : getTousLesProduits ---
// Retourne tous les produits avec le nom de leur catégorie
// Utilisé dans le contrôleur pour avoir une liste complète
function getTousLesProduits($pdo) {

    // JOIN relie deux tables : products (alias p) et categories (alias c)
    // ON p.categorie_id = c.id → condition de jointure : on lie un produit à sa catégorie
    // c.nom AS categorie_nom → on renomme la colonne pour éviter le conflit avec p.nom
    // ORDER BY c.nom, p.nom → on trie d'abord par catégorie puis par nom de produit (ordre alphabétique)
    $req = $pdo->prepare("
        SELECT p.*, c.nom AS categorie_nom
        FROM products p
        JOIN categories c ON p.categorie_id = c.id
        ORDER BY c.nom, p.nom
    ");

    // execute() sans paramètres car il n'y a pas de filtre (:parametre) dans cette requête
    $req->execute();

    // fetchAll() retourne TOUTES les lignes du résultat sous forme de tableau de tableaux
    return $req->fetchAll();
}

// --- FONCTION : getProduitsParCategorie ---
// Retourne les produits regroupés dans un tableau indexé par nom de catégorie
// Utilisé dans la vue catalogue pour afficher les sections par catégorie
function getProduitsParCategorie($pdo) {

    // Même requête que getTousLesProduits : tous les produits avec leur catégorie
    $req = $pdo->prepare("
        SELECT p.*, c.nom AS categorie_nom
        FROM products p
        JOIN categories c ON p.categorie_id = c.id
        ORDER BY c.nom, p.nom
    ");
    $req->execute();

    // On récupère tous les produits dans un tableau plat
    $tous = $req->fetchAll();

    // On crée un tableau vide qui va stocker les produits organisés par catégorie
    $groupes = [];

    // foreach parcourt chaque produit du tableau $tous
    // $p = tableau associatif d'un produit avec toutes ses colonnes
    foreach ($tous as $p) {
        // $p['categorie_nom'] est le nom de la catégorie (ex: "Chaussures", "Vêtements")
        // $groupes['Chaussures'][] ajoute le produit $p dans le sous-tableau "Chaussures"
        // Résultat : ['Chaussures' => [produit1, produit2], 'Vêtements' => [produit3...]]
        $groupes[$p['categorie_nom']][] = $p;
    }

    // On retourne le tableau organisé par catégorie
    return $groupes;
}

// --- FONCTION : getProduitParId ---
// Retourne UN seul produit identifié par son ID
// Utilisé dans le CartController pour récupérer les infos d'un produit à ajouter au panier
function getProduitParId($pdo, $id) {

    // WHERE p.id = :id filtre sur un seul produit (on connaît son ID exact)
    $req = $pdo->prepare("
        SELECT p.*, c.nom AS categorie_nom
        FROM products p
        JOIN categories c ON p.categorie_id = c.id
        WHERE p.id = :id
    ");

    // On passe l'ID en paramètre sécurisé
    $req->execute([':id' => $id]);

    // fetch() retourne une seule ligne (le produit trouvé) ou FALSE si l'ID n'existe pas
    return $req->fetch();
}

// --- FONCTION : ajouterProduit ---
// Insère un nouveau produit dans la base de données
// Appelée dans ProductController quand un vendeur soumet le formulaire d'ajout
function ajouterProduit($pdo, $nom, $description, $prix, $categorie_id, $image, $vendeur_id) {

    // INSERT INTO ajoute une nouvelle ligne dans la table products
    // Chaque colonne reçoit sa valeur via un paramètre nommé
    $req = $pdo->prepare("
        INSERT INTO products (nom, description, prix, categorie_id, image, vendeur_id)
        VALUES (:nom, :description, :prix, :categorie_id, :image, :vendeur_id)
    ");

    // On passe toutes les valeurs du produit
    $req->execute([
        ':nom'          => $nom,          // Nom du produit saisi par le vendeur
        ':description'  => $description,  // Description du produit
        ':prix'         => $prix,         // Prix en euros
        ':categorie_id' => $categorie_id, // ID de la catégorie choisie dans le <select>
        ':image'        => $image,        // Nom du fichier image uploadé (ex: abc123.jpg)
        ':vendeur_id'   => $vendeur_id    // ID du vendeur connecté (depuis $_SESSION['user_id'])
    ]);
}
