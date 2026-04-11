<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Encodage pour les accents -->
    <meta charset="UTF-8">
    <!-- Responsive mobile -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Root1</title>

    <!-- Polices Google : Cormorant Garamond (titres) + Jost (texte) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Jost:wght@300;400;500;600&family=Unbounded:wght@900&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">

    <!-- Lien vers notre CSS -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Three.js chargé en avance pour les effets WebGL -->
    <script src="js/three.min.js"></script>
    <script src="js/gsap.min.js"></script>
</head>
<body>

<!-- ===== FOND LIQUID ETHER ===== -->
<div id="liquid-bg"></div>

<!-- ===== FOND PARTICULES (effet scroll) ===== -->
<div class="canvas-wrap"></div>

<!-- ===== GRAIN (effet texture légère sur toute la page) ===== -->
<div class="grain"></div>


<!-- ===== BARRE DE NAVIGATION ===== -->
<nav class="nav" id="nav">

    <!-- Logo cliquable vers l'accueil -->


    <!-- Liens de navigation (cachés sur mobile, gérés par le bouton hamburger) -->
    <div class="nav__links" id="navLinks">
        <!-- Liens toujours visibles -->
        <a href="index.php?page=home">Accueil</a>
        <a href="index.php?page=catalogue">Catalogue</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Affiché seulement si l'utilisateur est connecté -->

            <!-- Panier avec compteur d'articles -->
            <a href="index.php?page=cart">
                Panier
                <?php
                // On compte le total d'articles dans le panier
                $nb_articles = 0;
                if (isset($_SESSION['panier'])) {
                    foreach ($_SESSION['panier'] as $item) {
                        $nb_articles += $item['quantite'];
                    }
                }
                // Affiche le nombre entre parenthèses si > 0
                if ($nb_articles > 0) echo '<span class="nav__badge">' . $nb_articles . '</span>';
                ?>
            </a>

            <!-- Lien profil -->
            <a href="index.php?page=profile">Profil</a>

            <?php if ($_SESSION['user_role'] === 'vendeur'): ?>
                <!-- Affiché UNIQUEMENT pour les vendeurs -->
                <a href="index.php?page=add_product" class="nav__add">+ Produit</a>
            <?php endif; ?>

            <!-- Déconnexion -->
            <a href="index.php?page=logout&action=deconnexion">Déconnexion</a>

        <?php else: ?>
            <!-- Affiché si l'utilisateur N'EST PAS connecté -->
            <a href="index.php?page=login">Connexion</a>
            <a href="index.php?page=register">Inscription</a>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Affiche le nom de l'utilisateur connecté -->
        <div class="nav__cta">
            <?php echo htmlspecialchars($_SESSION['user_nom']); ?>
            <!-- Badge "Vendeur" si le rôle est vendeur -->
            <?php if ($_SESSION['user_role'] === 'vendeur'): ?>
                <span class="nav__role">Vendeur</span>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Bouton "Rejoindre" si pas connecté -->
        <a class="nav__cta" href="index.php?page=register">Rejoindre →</a>
    <?php endif; ?>

    <!-- Bouton hamburger pour mobile (les 2 spans = les 2 traits) -->
    <button class="nav__toggle" id="navToggle" aria-label="Menu">
        <span></span>
        <span></span>
    </button>
</nav>

<!-- ===== ZONE PRINCIPALE DU CONTENU ===== -->
<!-- Tout le contenu de chaque page sera inséré ici -->
<main class="main-content">
