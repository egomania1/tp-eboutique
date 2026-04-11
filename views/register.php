<?php include 'views/header.php'; ?>

<div class="form-wrap">

    <div class="section-tag">Nouveau compte</div>
    <p class="form-title">Inscription</p>
    <p class="form-sub">Rejoignez Root1 en tant que client ou vendeur.</p>

    <?php if (isset($erreur)): ?>
        <div class="message-erreur"><?php echo htmlspecialchars($erreur); ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php?page=register" class="formulaire">

        <div class="champ">
            <label for="nom">Nom</label>
            <input type="text" id="nom" name="nom" placeholder="Votre nom" required>
        </div>

        <div class="champ">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="votre@email.com" required>
        </div>

        <div class="champ">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required>
        </div>

        <div class="champ">
            <label for="role">Je suis</label>
            <!-- Liste déroulante pour choisir entre client et vendeur -->
            <select id="role" name="role">
                <option value="client">Client — j'achète des produits</option>
                <option value="vendeur">Vendeur — je vends des produits</option>
            </select>
        </div>

        <!-- name="inscription" : AuthController vérifie isset($_POST['inscription']) pour savoir que c'est ce formulaire -->
        <button type="submit" name="inscription" class="btn-form">Créer mon compte →</button>

    </form>

    <p class="form-link">
        Déjà un compte ? <a href="index.php?page=login">Se connecter</a>
    </p>

</div>

<?php include 'views/footer.php'; ?>
