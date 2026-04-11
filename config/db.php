<?php

// ============================================================
// FICHIER : config/db.php
// RÔLE    : Connexion à la base de données MySQL via PDO
// ============================================================

// On définit les informations nécessaires pour se connecter à MySQL
$host     = 'localhost';   // Le serveur de base de données — ici sur la même machine (XAMPP)
$dbname   = 'eboutique';   // Le nom de la base de données qu'on veut utiliser
$user     = 'root';        // L'utilisateur MySQL par défaut de XAMPP (pas de mot de passe)
$password = '';            // Mot de passe vide car XAMPP ne met pas de mot de passe par défaut

// On tente la connexion dans un bloc try/catch pour gérer les erreurs proprement
try {

    // new PDO(...) crée une connexion à MySQL
    // "mysql:host=..." indique le type de base (mysql), le serveur (host) et la base (dbname)
    // charset=utf8 permet de gérer les accents et caractères spéciaux
    // $user et $password sont les identifiants de connexion MySQL
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);

    // setAttribute configure le comportement de PDO
    // PDO::ATTR_ERRMODE = quel mode d'erreur on utilise
    // PDO::ERRMODE_EXCEPTION = PDO lève une exception si une requête échoue → on peut la capturer
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // Si la connexion échoue (mauvais mot de passe, base inexistante...) on attrape l'erreur ici
    // die() arrête tout le script et affiche le message d'erreur pour qu'on sache quoi corriger
    die("Erreur de connexion : " . $e->getMessage());
}
