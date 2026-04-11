<?php include 'views/header.php'; ?>

<div class="page-section">

    <div class="section-tag">Mon compte</div>
    <h1>Profil</h1>

    <div class="profil-card">

        <div class="profil-header">

            <!-- Photo de profil ou initiales si pas de photo -->
            <div class="profil-avatar">
                <?php if (!empty($_SESSION['user_avatar'])): ?>
                    <img src="uploads/avatars/<?php echo htmlspecialchars($_SESSION['user_avatar']); ?>"
                         alt="Photo de profil" class="avatar-img">
                <?php else: ?>
                    <!-- On génère les initiales à partir du nom de l'utilisateur -->
                    <div class="avatar-default">
                        <?php
                        $nom      = $_SESSION['user_nom'];
                        $mots     = explode(' ', $nom);
                        $initiales = '';
                        foreach ($mots as $mot) {
                            // substr() prend le 1er caractère, strtoupper() le met en majuscule
                            $initiales .= strtoupper(substr($mot, 0, 1));
                        }
                        // On affiche maximum 2 initiales
                        echo substr($initiales, 0, 2);
                        ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="profil-info">
                <h2><?php echo htmlspecialchars($_SESSION['user_nom']); ?></h2>
                <?php if ($_SESSION['user_role'] === 'vendeur'): ?>
                    <span class="badge-role badge-vendeur">Vendeur</span>
                <?php else: ?>
                    <span class="badge-role badge-client">Client</span>
                <?php endif; ?>
            </div>

            <button class="btn-modifier-profil" id="btnModifier">Modifier mon profil</button>

            <!-- Formulaire d'upload de photo, enctype obligatoire pour les fichiers -->
            <form method="POST" action="index.php?page=update_profile" enctype="multipart/form-data" id="avatarForm">
                <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display:none;">
                <div id="avatarUpload" style="display:none;">
                    <button type="button" class="avatar-pick" id="avatarPickBtn">+ Choisir une photo</button>
                    <button type="submit" class="avatar-save" id="avatarSaveBtn" style="display:none;">Enregistrer</button>
                </div>
            </form>
        </div>

        <div class="profil-details">
            <h3>Informations</h3>

            <div class="profil-ligne">
                <span class="profil-label">Email</span>
                <span class="profil-valeur"><?php echo htmlspecialchars($_SESSION['user_email']); ?></span>
            </div>

            <div class="profil-ligne">
                <span class="profil-label">Statut</span>
                <span class="profil-valeur">
                    <?php if ($_SESSION['user_role'] === 'vendeur'): ?>
                        <span style="color: var(--accent);">Vendeur vérifié</span>
                    <?php else: ?>
                        <span style="color: var(--text-muted);">Client actif</span>
                    <?php endif; ?>
                </span>
            </div>
        </div>

    </div>

    <div class="profil-actions">
        <?php if ($_SESSION['user_role'] === 'vendeur'): ?>
            <a href="index.php?page=add_product" class="btn">+ Ajouter un produit</a>
        <?php endif; ?>

        <a href="index.php?page=home" class="btn-ghost">Voir le catalogue</a>

        <a href="index.php?page=logout&action=deconnexion" class="btn-rouge">Se déconnecter</a>
    </div>

</div>

<script>
// Quand on clique sur "Modifier mon profil", on affiche le bouton d'upload
document.getElementById('btnModifier').addEventListener('click', function() {
    const wrap = document.getElementById('avatarUpload');
    wrap.style.display = wrap.style.display === 'none' ? 'flex' : 'none';
});

// Quand on clique sur "+ Choisir une photo", on ouvre l'explorateur de fichiers
document.getElementById('avatarPickBtn').addEventListener('click', function() {
    document.getElementById('avatarInput').click();
});

// Quand un fichier est sélectionné, on affiche le bouton "Enregistrer"
document.getElementById('avatarInput').addEventListener('change', function() {
    if (!this.files[0]) return;
    document.getElementById('avatarSaveBtn').style.display = 'inline';
});
</script>

<?php include 'views/footer.php'; ?>
