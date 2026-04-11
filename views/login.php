<?php include 'views/header.php'; ?>

<div class="form-wrap">

    <div class="section-tag">Accès</div>
    <p class="form-title">Connexion</p>
    <p class="form-sub">Content de vous revoir.</p>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'compte_cree'): ?>
        <div class="message-succes">✓ Compte créé ! Vous pouvez vous connecter.</div>
    <?php endif; ?>

    <?php if (isset($erreur)): ?>
        <div class="message-erreur"><?php echo htmlspecialchars($erreur); ?></div>
    <?php endif; ?>

    <!--
        method="POST" : les données du formulaire ne s'affichent pas dans l'URL
        action : vers quelle page envoyer les données
    -->
    <form method="POST" action="index.php?page=login" class="formulaire">

        <div class="champ">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="votre@email.com" required>
        </div>

        <div class="champ">
            <label for="password">Mot de passe</label>
            <!-- type="password" masque les caractères tapés -->
            <input type="password" id="password" name="password" placeholder="••••••••" required>
        </div>

        <!-- name="connexion" permet à AuthController de détecter ce formulaire avec isset($_POST['connexion']) -->
        <button type="submit" name="connexion" class="btn-form">Se connecter →</button>

    </form>

    <p class="form-link">
        Pas encore de compte ? <a href="index.php?page=register">Créer un compte</a>
    </p>

</div>

<?php include 'views/footer.php'; ?>
