<?php

// ============================================================
// FICHIER : models/UserModel.php
// RÔLE    : Toutes les fonctions qui touchent la table "users"
//           en base de données (créer, chercher, mettre à jour)
// ============================================================

// --- FONCTION : creerUtilisateur ---
// Insère un nouvel utilisateur dans la base de données
// Paramètres : $pdo = connexion BDD, $nom, $email, $password, $role = données du formulaire
function creerUtilisateur($pdo, $nom, $email, $password, $role) {

    // password_hash() transforme le mot de passe en une chaîne chiffrée (hash)
    // On ne stocke JAMAIS un mot de passe en clair dans la base de données
    // PASSWORD_DEFAULT = algorithme de hachage recommandé par PHP (actuellement bcrypt)
    $passwordHashe = password_hash($password, PASSWORD_DEFAULT);

    // $pdo->prepare() crée une requête préparée avec des paramètres nommés (:nom, :email...)
    // Les paramètres nommés empêchent les injections SQL car PHP les traite comme des données
    // et non comme du code SQL
    $req = $pdo->prepare("INSERT INTO users (nom, email, password, role) VALUES (:nom, :email, :password, :role)");

    // execute() envoie la requête en remplaçant chaque :parametre par sa vraie valeur
    // On passe un tableau associatif : clé = nom du paramètre, valeur = donnée à insérer
    $req->execute([
        ':nom'      => $nom,           // Le nom saisi dans le formulaire
        ':email'    => $email,         // L'email saisi dans le formulaire
        ':password' => $passwordHashe, // Le mot de passe HASHÉ (jamais le mot de passe brut)
        ':role'     => $role           // 'acheteur' ou 'vendeur' selon le choix de l'utilisateur
    ]);
}

// --- FONCTION : trouverUtilisateurParEmail ---
// Cherche un utilisateur dans la base par son adresse email
// Retourne les données de l'utilisateur sous forme de tableau, ou FALSE si non trouvé
function trouverUtilisateurParEmail($pdo, $email) {

    // SELECT * = récupère toutes les colonnes de la ligne (id, nom, email, password, role...)
    // WHERE email = :email = filtre sur l'email exact
    $req = $pdo->prepare("SELECT * FROM users WHERE email = :email");

    // On exécute la requête en passant l'email comme paramètre sécurisé
    $req->execute([':email' => $email]);

    // fetch() retourne UNE SEULE ligne (la première trouvée)
    // Retourne un tableau associatif si trouvé, FALSE s'il n'y a aucun résultat
    return $req->fetch();
}

// --- FONCTION : emailExisteDeja ---
// Vérifie si un email est déjà enregistré dans la base
// Retourne TRUE si l'email existe, FALSE sinon
// Utilisé lors de l'inscription pour éviter les doublons
function emailExisteDeja($pdo, $email) {

    // COUNT(*) compte le nombre de lignes correspondantes (0 ou 1 ici car l'email est unique)
    $req = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    $req->execute([':email' => $email]);

    // fetchColumn() récupère la valeur de la première colonne de la première ligne
    // Ici cette valeur est le nombre (0 ou 1)
    // > 0 retourne TRUE si au moins un utilisateur a cet email, FALSE si 0
    return $req->fetchColumn() > 0;
}

// --- FONCTION : updateUserBio ---
// Met à jour la biographie d'un utilisateur identifié par son ID
function updateUserBio($pdo, $userId, $bio) {

    // UPDATE modifie une ligne existante dans la table users
    // SET bio = :bio → on écrase l'ancienne valeur de bio
    // WHERE id = :id → IMPORTANT : sans WHERE on écraserait les bios de TOUS les utilisateurs
    $req = $pdo->prepare("UPDATE users SET bio = :bio WHERE id = :id");

    // On passe la nouvelle bio et l'ID de l'utilisateur à modifier
    $req->execute([':bio' => $bio, ':id' => $userId]);
}

// --- FONCTION : updateUserAvatar ---
// Met à jour le nom du fichier avatar d'un utilisateur
function updateUserAvatar($pdo, $userId, $avatar) {

    // Même logique que updateUserBio mais pour le champ avatar
    // $avatar contient le nom du fichier image (ex: avatar_3_abc123.jpg)
    $req = $pdo->prepare("UPDATE users SET avatar = :avatar WHERE id = :id");
    $req->execute([':avatar' => $avatar, ':id' => $userId]);
}

// --- FONCTION : getUserById ---
// Récupère toutes les informations d'un utilisateur à partir de son ID
// Utilisé sur la page profil pour afficher les infos à jour depuis la BDD
function getUserById($pdo, $userId) {

    // On sélectionne la ligne de l'utilisateur dont l'id correspond
    $req = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $req->execute([':id' => $userId]);

    // fetch(PDO::FETCH_ASSOC) retourne un tableau associatif (clés = noms des colonnes)
    // ex : $user['nom'], $user['email'], $user['bio']...
    return $req->fetch(PDO::FETCH_ASSOC);
}
