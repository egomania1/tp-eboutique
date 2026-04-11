<?php

// ============================================================
// FICHIER : controllers/ProfileController.php
// RÔLE    : Gère la mise à jour du profil utilisateur
//           Bio (texte) et avatar (photo de profil)
// ============================================================

// On charge le modèle utilisateur pour accéder aux fonctions
// updateUserBio(), updateUserAvatar(), getUserById()
require_once ROOT . 'models/UserModel.php';

// ---------------------------------------------------------------
// BLOC MISE À JOUR DE LA BIOGRAPHIE
// Se déclenche quand l'utilisateur soumet le formulaire de bio
// isset($_POST['bio']) vérifie que le champ "bio" a été envoyé
// ---------------------------------------------------------------
if (isset($_POST['bio'])) {

    // Sécurité : on vérifie que l'utilisateur est bien connecté
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?page=login');
        exit();
    }

    // trim() enlève les espaces en début/fin de la bio saisie
    $bio    = trim($_POST['bio']);
    // On récupère l'ID de l'utilisateur connecté depuis la session
    $userId = $_SESSION['user_id'];

    // strlen() compte le nombre de caractères de la chaîne
    // On limite la bio à 500 caractères pour éviter des textes trop longs
    if (strlen($bio) > 500) {
        // On stocke le message d'erreur en session pour l'afficher après la redirection
        $_SESSION['erreur'] = "La biographie ne doit pas dépasser 500 caractères.";
        header('Location: index.php?page=profile');
        exit();
    }

    // Tout est OK → on met à jour la bio en BDD via la fonction du modèle
    updateUserBio($pdo, $userId, $bio);

    // On met aussi à jour la session pour que l'affichage soit immédiatement à jour
    $_SESSION['user_bio'] = $bio;

    // Message de succès stocké en session, affiché dans la vue profile.php
    $_SESSION['message'] = "Biographie mise à jour !";

    // On redirige vers la page profil pour afficher les changements
    header('Location: index.php?page=profile');
    exit();
}

// ---------------------------------------------------------------
// BLOC MISE À JOUR DE L'AVATAR (PHOTO DE PROFIL)
// Se déclenche quand l'utilisateur envoie un fichier image
// $_FILES contient les données des fichiers uploadés
// ['error'] === 0 vérifie qu'aucune erreur d'upload ne s'est produite
// ---------------------------------------------------------------
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {

    // Vérification connexion
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?page=login');
        exit();
    }

    $userId = $_SESSION['user_id'];
    $file   = $_FILES['avatar']; // On stocke les infos du fichier dans une variable pour simplifier

    // On vérifie le type MIME du fichier (type réel du fichier détecté par PHP)
    // C'est plus fiable que vérifier l'extension car le type MIME ne peut pas être falsifié facilement
    $typesAutorises = ['image/jpeg', 'image/png', 'image/webp'];

    // in_array() vérifie si le type du fichier est dans la liste autorisée
    if (!in_array($file['type'], $typesAutorises)) {
        $_SESSION['erreur'] = "Format non autorisé. Utilisez JPG, PNG ou WEBP.";
        header('Location: index.php?page=profile');
        exit();
    }

    // On vérifie la taille du fichier : maximum 2 Mo
    // 2 * 1024 * 1024 = 2 097 152 octets = 2 Mo
    if ($file['size'] > 2 * 1024 * 1024) {
        $_SESSION['erreur'] = "L'image ne doit pas dépasser 2 Mo.";
        header('Location: index.php?page=profile');
        exit();
    }

    // pathinfo() extrait l'extension depuis le nom original du fichier
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);

    // On génère un nom unique pour l'avatar
    // 'avatar_' + ID utilisateur + '_' + identifiant unique + '.' + extension
    // ex : avatar_3_6421abc3f1234.jpg
    // Ça évite d'écraser l'ancien avatar et garantit l'unicité du nom
    $nomUnique = 'avatar_' . $userId . '_' . uniqid() . '.' . $extension;

    // Chemin complet où le fichier sera stocké sur le serveur
    $chemin = ROOT . 'uploads/avatars/' . $nomUnique;

    // is_dir() vérifie si le dossier avatars/ existe déjà
    // Si non → mkdir() le crée automatiquement
    // 0755 = permissions du dossier (lecture/exécution pour tous, écriture pour le propriétaire)
    // true = crée les dossiers parents si nécessaire (uploads/ s'il n'existe pas)
    if (!is_dir(ROOT . 'uploads/avatars/')) {
        mkdir(ROOT . 'uploads/avatars/', 0755, true);
    }

    // move_uploaded_file() déplace le fichier du dossier temporaire PHP vers notre dossier uploads/avatars/
    // tmp_name = chemin du fichier temporaire créé par PHP lors de l'upload
    if (move_uploaded_file($file['tmp_name'], $chemin)) {
        // Upload réussi → on met à jour le nom de l'avatar en BDD
        updateUserAvatar($pdo, $userId, $nomUnique);

        // On met à jour la session pour afficher immédiatement la nouvelle photo
        $_SESSION['user_avatar'] = $nomUnique;
        $_SESSION['message']     = "Photo de profil mise à jour !";

    } else {
        // move_uploaded_file() a échoué (problème de permissions sur le serveur par exemple)
        $_SESSION['erreur'] = "Erreur lors de l'upload.";
    }

    // Dans tous les cas on redirige vers le profil
    header('Location: index.php?page=profile');
    exit();
}

// Si aucun formulaire n'a été soumis → on affiche simplement la page profil
include ROOT . 'views/profile.php';
