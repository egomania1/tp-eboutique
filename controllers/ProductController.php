<?php

// ============================================================
// FICHIER : controllers/ProductController.php
// RÔLE    : Gère l'affichage du catalogue et l'ajout de produits
// ============================================================

// On charge les modèles nécessaires pour les requêtes BDD
require_once ROOT . 'models/ProductModel.php';   // fonctions produits
require_once ROOT . 'models/CategoryModel.php';  // fonctions catégories

// ---------------------------------------------------------------
// BLOC AJOUT DE PRODUIT
// Ce bloc s'exécute seulement quand le formulaire d'ajout est soumis
// isset($_POST['ajouter_produit']) vérifie si le bouton submit du formulaire a été cliqué
// ---------------------------------------------------------------
if (isset($_POST['ajouter_produit'])) {

    // Vérification du rôle : seul un vendeur peut ajouter un produit
    // On vérifie que 'user_role' existe dans la session ET qu'il vaut 'vendeur'
    // Si ce n'est pas le cas (visiteur, acheteur), on redirige vers l'accueil
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'vendeur') {
        header('Location: index.php?page=home');
        exit(); // exit() arrête le script après la redirection
    }

    // Récupération et nettoyage des données du formulaire
    $nom          = trim($_POST['nom']);          // trim() supprime les espaces inutiles autour
    $description  = trim($_POST['description']);  // Description du produit
    $prix         = $_POST['prix'];               // Prix numérique, pas besoin de trim
    $categorie_id = $_POST['categorie_id'];       // ID de la catégorie choisie dans le <select>

    // $_FILES['image'] contient les infos du fichier uploadé via <input type="file" name="image">
    // ['error'] === 0 signifie qu'aucune erreur d'upload n'est survenue (0 = UPLOAD_ERR_OK)
    if ($_FILES['image']['error'] === 0) {

        // $_FILES['image']['name'] = nom original du fichier envoyé par l'utilisateur
        $nomFichier = $_FILES['image']['name'];

        // pathinfo() décompose un chemin de fichier
        // PATHINFO_EXTENSION récupère uniquement l'extension (jpg, png, gif...)
        // strtolower() met en minuscule pour éviter les problèmes avec "JPG" vs "jpg"
        $extension = strtolower(pathinfo($nomFichier, PATHINFO_EXTENSION));

        // Tableau des extensions autorisées pour la sécurité
        // On refuse les fichiers .php, .exe etc. qui pourraient être dangereux
        $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'gif'];

        // in_array() vérifie si l'extension du fichier est dans la liste autorisée
        // Le '!' inverse la condition : si l'extension N'EST PAS dans la liste → erreur
        if (!in_array($extension, $extensionsAutorisees)) {
            $erreur = "Seules les images JPG, PNG et GIF sont acceptées.";

        // On vérifie que la taille du fichier ne dépasse pas 2 Mo
        // 2 * 1024 * 1024 = 2 097 152 octets = 2 Mo (les tailles sont en octets en PHP)
        } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $erreur = "L'image ne doit pas dépasser 2 Mo.";

        } else {
            // Tous les contrôles sont passés → on peut enregistrer l'image

            // uniqid() génère un identifiant unique basé sur l'heure (ex: 6421abc3f1234)
            // On lui ajoute l'extension pour avoir un nom de fichier unique
            // Ça évite d'écraser un fichier existant si deux vendeurs uploadent la même image
            $nomUnique = uniqid() . '.' . $extension;

            // move_uploaded_file() déplace le fichier temporaire (stocké par PHP pendant l'upload)
            // vers notre dossier uploads/ avec le nom unique qu'on a généré
            // $_FILES['image']['tmp_name'] = chemin temporaire du fichier sur le serveur
            move_uploaded_file($_FILES['image']['tmp_name'], ROOT . 'uploads/' . $nomUnique);

            // On appelle la fonction du modèle pour insérer le produit en base de données
            // $_SESSION['user_id'] = l'ID du vendeur connecté qui publie ce produit
            ajouterProduit($pdo, $nom, $description, $prix, $categorie_id, $nomUnique, $_SESSION['user_id']);

            // On redirige vers le catalogue avec un message de succès dans l'URL
            header('Location: index.php?page=catalogue&message=produit_ajoute');
            exit();
        }

    } else {
        // L'utilisateur n'a pas sélectionné de fichier ou l'upload a échoué
        $erreur = "Veuillez choisir une image.";
    }
}

// ---------------------------------------------------------------
// PRÉPARATION DES DONNÉES POUR LA VUE
// On récupère les données depuis la BDD avant d'afficher quoi que ce soit
// ---------------------------------------------------------------

// getTousLesProduits() retourne tous les produits avec leur catégorie
$produits = getTousLesProduits($pdo);

// getToutesLesCategories() retourne toutes les catégories pour le <select> du formulaire
$categories = getToutesLesCategories($pdo);

// ---------------------------------------------------------------
// AFFICHAGE DE LA VUE
// On choisit quelle vue afficher selon la page demandée
// ---------------------------------------------------------------

// Si ?page=add_product → on affiche le formulaire d'ajout de produit
if (isset($_GET['page']) && $_GET['page'] === 'add_product') {
    include ROOT . 'views/add_product.php';

} else {
    // Sinon → on affiche le catalogue des produits groupés par catégorie
    // getProduitsParCategorie() retourne un tableau organisé par catégorie
    $produitsParCategorie = getProduitsParCategorie($pdo);
    include ROOT . 'views/catalogue_simple.php';
}
