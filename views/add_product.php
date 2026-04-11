<?php
// Sécurité : seul un vendeur connecté peut accéder à cette page
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'vendeur') {
    header('Location: index.php?page=home');
    exit();
}

include 'views/header.php';
?>

<div class="form-wrap" style="max-width: 560px;">

    <div class="section-tag">Espace Vendeur</div>
    <p class="form-title">Ajouter un produit</p>
    <p class="form-sub">Remplissez les informations ci-dessous pour publier votre produit.</p>

    <?php if (isset($erreur)): ?>
        <div class="message-erreur"><?php echo htmlspecialchars($erreur); ?></div>
    <?php endif; ?>

    <!--
        enctype="multipart/form-data" est obligatoire quand le formulaire contient un fichier
        Sans ça, $_FILES serait vide côté PHP
    -->
    <form method="POST" action="index.php?page=add_product" enctype="multipart/form-data" class="formulaire">

        <div class="champ">
            <label for="nom">Nom du produit</label>
            <input type="text" id="nom" name="nom" placeholder="Ex : T-shirt Premium" required>
        </div>

        <div class="champ">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3" placeholder="Décrivez votre produit..."></textarea>
        </div>

        <div class="champ">
            <label for="prix">Prix (€)</label>
            <!-- step="0.01" permet d'entrer des centimes, min="0" interdit les prix négatifs -->
            <input type="number" id="prix" name="prix" step="0.01" min="0" placeholder="0.00" required>
        </div>

        <div class="champ">
            <label for="categorie_id">Catégorie</label>
            <!-- $categories vient de ProductController.php -->
            <select id="categorie_id" name="categorie_id" required>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>">
                        <?php echo htmlspecialchars($cat['nom']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="champ">
            <label for="image">Image du produit</label>
            <!-- accept="image/*" filtre pour n'afficher que les images dans l'explorateur -->
            <input type="file" id="image" name="image" accept="image/*" required style="color: var(--text-muted);">
            <p style="font-size:0.7rem; color: var(--text-muted); margin-top: 0.4rem;">
                Formats acceptés : JPG, PNG, GIF — Max 2 Mo
            </p>
        </div>

        <!-- name="ajouter_produit" : ProductController détecte ce formulaire avec isset($_POST['ajouter_produit']) -->
        <button type="submit" name="ajouter_produit" class="btn-form">Publier le produit →</button>

    </form>

    <p class="form-link">
        <a href="index.php?page=home">← Retour au catalogue</a>
    </p>

</div>

<?php include 'views/footer.php'; ?>
