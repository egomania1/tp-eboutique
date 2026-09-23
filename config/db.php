<?php

// ============================================================
// FICHIER : config/db.php
// RÔLE    : Connexion à la base de données MySQL via PDO
// ============================================================

// On définit les informations nécessaires pour se connecter à MySQL
// En local (XAMPP), aucune de ces variables d'environnement n'existe : on retombe
// alors sur les valeurs XAMPP par défaut. En ligne (hébergeur), ces variables sont
// fournies par le service de base de données et prennent le dessus.
$host     = getenv('MYSQLHOST') ?: 'localhost';
$dbname   = getenv('MYSQLDATABASE') ?: 'eboutique';
$user     = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';
$port     = getenv('MYSQLPORT') ?: '3306';

// On tente la connexion dans un bloc try/catch pour gérer les erreurs proprement
try {

    // new PDO(...) crée une connexion à MySQL
    // "mysql:host=..." indique le type de base (mysql), le serveur (host) et la base (dbname)
    // charset=utf8 permet de gérer les accents et caractères spéciaux
    // $user et $password sont les identifiants de connexion MySQL
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $password);

    // setAttribute configure le comportement de PDO
    // PDO::ATTR_ERRMODE = quel mode d'erreur on utilise
    // PDO::ERRMODE_EXCEPTION = PDO lève une exception si une requête échoue → on peut la capturer
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // Si la connexion échoue (mauvais mot de passe, base inexistante...) on attrape l'erreur ici
    // die() arrête tout le script et affiche le message d'erreur pour qu'on sache quoi corriger
    die("Erreur de connexion : " . $e->getMessage());
}
