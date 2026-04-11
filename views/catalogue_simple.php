<?php include 'views/header.php'; ?>

<div class="page-section">
    <div class="section-tag">Catalogue</div>
    <h1>Nos Produits</h1>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'panier_ok'): ?>
        <div class="message-succes">Produit ajouté au panier ✓</div>
    <?php endif; ?>

    <?php
    // On boucle sur le tableau $produitsParCategorie qui vient de ProductController
    // Pour chaque catégorie, on affiche son titre puis ses produits
    foreach ($produitsParCategorie as $categorie => $produits):
    ?>
        <div class="categorie-bloc">
            <h2 class="categorie-titre"><?php echo htmlspecialchars($categorie); ?></h2>

            <div class="catalogue-grid">
                <?php foreach ($produits as $p): ?>
                    <div class="produit-card">

                        <div class="produit-image-container">
                            <?php
                            // Si le produit a une image, on l'affiche
                            if (!empty($p['image'])) {
                                if (str_starts_with($p['image'], 'http')) {
                                    $src = htmlspecialchars($p['image']);
                                } else {
                                    $src = 'uploads/' . htmlspecialchars($p['image']);
                                }
                                echo '<img src="' . $src . '" alt="' . htmlspecialchars($p['nom']) . '" class="produit-img">';
                            } else {
                                echo '<div class="produit-placeholder"></div>';
                            }
                            ?>
                        </div>

                        <div class="produit-body">
                            <h3><?php echo htmlspecialchars($p['nom']); ?></h3>
                            <p><?php echo htmlspecialchars($p['description']); ?></p>
                            <div class="produit-price">
                                <?php echo number_format($p['prix'], 2, ',', ' '); ?> €
                            </div>

                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'client'): ?>
                                <!-- Formulaire qui envoie l'ID du produit en POST vers CartController -->
                                <form method="POST" action="index.php?page=cart">
                                    <input type="hidden" name="produit_id" value="<?php echo $p['id']; ?>">
                                    <button type="submit" name="ajouter_panier" class="btn-add">Ajouter au panier</button>
                                </form>
                            <?php else: ?>
                                <a href="index.php?page=login" class="btn-add">Connectez-vous</a>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    <?php endforeach; ?>

</div>

<style>
.categorie-bloc { margin-bottom: 3rem; }

.categorie-titre {
    font-family: var(--font-display);
    font-size: 1.4rem;
    color: var(--accent);
    border-bottom: 1px solid var(--glass-border);
    padding-bottom: 0.5rem;
    margin-bottom: 1.5rem;
}

.catalogue-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1.5rem;
}

.produit-card {
    background: var(--glass);
    border: 1px solid var(--glass-border);
    border-radius: 12px;
    overflow: hidden;
    transition: transform 0.3s, box-shadow 0.3s;
}

.produit-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.produit-image-container { height: 200px; overflow: hidden; }

.produit-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.produit-card:hover .produit-img { transform: scale(1.05); }

.produit-placeholder {
    width: 100%;
    height: 100%;
    background: var(--bg-alt);
}

.produit-body { padding: 1.2rem; }

.produit-body h3 {
    font-family: var(--font-display);
    font-size: 1.1rem;
    color: var(--text);
    margin-bottom: 0.4rem;
}

.produit-body p {
    color: var(--text-muted);
    font-size: 0.85rem;
    margin-bottom: 1rem;
    line-height: 1.4;
}

.produit-price {
    font-size: 1.3rem;
    font-weight: 600;
    color: var(--accent);
    margin-bottom: 1rem;
}

.btn-add {
    display: block;
    width: 100%;
    padding: 0.7rem;
    background: var(--accent);
    color: var(--bg);
    text-align: center;
    border-radius: 6px;
    font-weight: 500;
    text-decoration: none;
    border: none;
    cursor: pointer;
    font-family: inherit;
    font-size: inherit;
    transition: background 0.3s;
}

.btn-add:hover { background: #b8e600; }

form { margin: 0; }

.message-succes {
    background: rgba(0, 255, 136, 0.1);
    border: 1px solid var(--accent);
    color: var(--accent);
    padding: 0.8rem 1.2rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}
</style>

<?php include 'views/footer.php'; ?>
