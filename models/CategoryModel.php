<?php

// ============================================================
// FICHIER : models/CategoryModel.php
// RÔLE    : Fonctions pour lire les catégories depuis la BDD
//           Utilisé dans le formulaire d'ajout de produit (liste déroulante)
// ============================================================

// --- FONCTION : getToutesLesCategories ---
// Retourne toutes les catégories de la table "categories", triées alphabétiquement
// Utilisé pour remplir le <select> dans le formulaire d'ajout de produit
function getToutesLesCategories($pdo) {

    // SELECT * = récupère toutes les colonnes de la table categories (id + nom)
    // ORDER BY nom = trie les catégories par ordre alphabétique
    $req = $pdo->prepare("SELECT * FROM categories ORDER BY nom");

    // execute() sans paramètres car pas de filtre dans cette requête
    $req->execute();

    // fetchAll() retourne toutes les lignes → tableau de catégories
    // ex : [['id'=>1, 'nom'=>'Chaussures'], ['id'=>2, 'nom'=>'Vêtements'], ...]
    return $req->fetchAll();
}
