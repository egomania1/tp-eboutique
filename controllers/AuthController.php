<?php

// ============================================================
// FICHIER : controllers/AuthController.php
// RÔLE    : Gère l'inscription, la connexion et la déconnexion
// ============================================================

// On inclut le modèle utilisateur pour avoir accès aux fonctions
// creerUtilisateur(), trouverUtilisateurParEmail(), emailExisteDeja()
require_once ROOT . 'models/UserModel.php';

// ---------------------------------------------------------------
// BLOC INSCRIPTION
// isset($_POST['inscription']) vérifie si le formulaire d'inscription
// a été soumis (le bouton submit s'appelle "inscription")
// ---------------------------------------------------------------
if (isset($_POST['inscription'])) {

    // trim() supprime les espaces au début et à la fin de la saisie
    // Évite qu'un utilisateur s'inscrive avec "  " comme nom
    $nom      = trim($_POST['nom']);       // Nom récupéré du champ <input name="nom">
    $email    = trim($_POST['email']);     // Email récupéré du champ <input name="email">
    $password = $_POST['password'];        // Mot de passe brut (pas de trim pour garder les espaces intentionnels)
    $role     = $_POST['role'];            // 'acheteur' ou 'vendeur' selon le <select name="role">

    // empty() vérifie si une variable est vide (chaîne vide, null, 0...)
    // On vérifie les 3 champs obligatoires ; si l'un est vide → message d'erreur
    if (empty($nom) || empty($email) || empty($password)) {
        // On stocke le message dans $erreur, il sera affiché dans la vue register.php
        $erreur = "Tous les champs sont obligatoires.";

    // emailExisteDeja() interroge la BDD pour voir si cet email est déjà utilisé
    // Si oui → on ne peut pas créer un 2e compte avec le même email
    } elseif (emailExisteDeja($pdo, $email)) {
        $erreur = "Cet email est déjà utilisé.";

    } else {
        // Tous les contrôles sont passés → on crée l'utilisateur en base de données
        // creerUtilisateur() va hasher le mot de passe et insérer la ligne dans users
        creerUtilisateur($pdo, $nom, $email, $password, $role);

        // header('Location: ...') redirige vers la page de connexion avec un message de succès
        // Le paramètre message=compte_cree sera lu dans la vue pour afficher une alerte verte
        header('Location: index.php?page=login&message=compte_cree');
        exit(); // exit() arrête le script immédiatement après la redirection
    }
}

// ---------------------------------------------------------------
// BLOC CONNEXION
// isset($_POST['connexion']) vérifie si le formulaire de login a été soumis
// ---------------------------------------------------------------
if (isset($_POST['connexion'])) {

    // On récupère l'email et le mot de passe saisis dans le formulaire
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    // trouverUtilisateurParEmail() fait un SELECT dans la BDD avec cet email
    // Retourne un tableau avec les infos du user, ou FALSE s'il n'existe pas
    $utilisateur = trouverUtilisateurParEmail($pdo, $email);

    // password_verify() compare le mot de passe saisi avec le hash stocké en BDD
    // On ne peut pas comparer directement car le hash est différent à chaque fois
    // Les deux conditions doivent être vraies : l'utilisateur existe ET le mot de passe correspond
    if ($utilisateur && password_verify($password, $utilisateur['password'])) {

        // Connexion réussie → on stocke les infos dans la session
        // $_SESSION est un tableau spécial qui persiste entre les pages pour cet utilisateur
        $_SESSION['user_id']    = $utilisateur['id'];    // L'ID sert à identifier l'utilisateur partout
        $_SESSION['user_nom']   = $utilisateur['nom'];   // Le nom pour l'afficher dans le header
        $_SESSION['user_email'] = $utilisateur['email']; // L'email pour la page profil
        $_SESSION['user_role']  = $utilisateur['role'];  // Le rôle pour restreindre certaines fonctions (vendeur)

        // On redirige vers la page d'accueil une fois connecté
        header('Location: index.php?page=home');
        exit();

    } else {
        // Si l'email n'existe pas OU si le mot de passe est incorrect
        // On ne précise pas lequel des deux est faux (sécurité : éviter le "user enumeration")
        $erreur = "Email ou mot de passe incorrect.";
    }
}

// ---------------------------------------------------------------
// BLOC DÉCONNEXION
// On vérifie que l'action dans l'URL est bien 'deconnexion'
// Ex: ?page=logout&action=deconnexion
// ---------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'deconnexion') {

    // session_destroy() supprime toutes les données de la session en cours
    // Après ça, $_SESSION['user_id'] n'existera plus → l'utilisateur est déconnecté
    session_destroy();

    // On redirige vers la page de connexion après la déconnexion
    header('Location: index.php?page=login');
    exit();
}

// ---------------------------------------------------------------
// AFFICHAGE DE LA VUE
// Selon la page demandée, on charge le bon formulaire HTML
// ---------------------------------------------------------------

// Si ?page=register dans l'URL → on affiche le formulaire d'inscription
if (isset($_GET['page']) && $_GET['page'] === 'register') {
    include ROOT . 'views/register.php'; // include = inclure le fichier HTML/PHP de la vue

} else {
    // Sinon (page=login ou page=logout) → on affiche le formulaire de connexion
    include ROOT . 'views/login.php';
}
