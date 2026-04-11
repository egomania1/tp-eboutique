<?php include 'views/header.php'; ?>

<div class="page-section">

    <div class="section-tag">Mon panier</div>
    <h1>Panier</h1>

    <?php if (empty($_SESSION['panier'])): ?>

        <div class="panier-vide">
            <p>Votre panier est vide.</p>
            <a href="index.php?page=home" class="btn">Voir le catalogue →</a>
        </div>

    <?php else: ?>

        <table class="tableau-panier">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Prix unitaire</th>
                    <th>Quantité</th>
                    <th>Sous-total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
            // On boucle sur le panier stocké en session
            // $produit_id c'est la clé du tableau (l'ID du produit)
            // $item c'est le tableau avec le nom, le prix et la quantité
            foreach ($_SESSION['panier'] as $produit_id => $item):
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['nom']); ?></td>

                    <td class="prix-panier">
                        <?php echo number_format($item['prix'], 2, ',', ' '); ?> €
                    </td>

                    <td><?php echo $item['quantite']; ?></td>

                    <!-- Le sous-total c'est simplement prix multiplié par la quantité -->
                    <td class="prix-panier">
                        <?php echo number_format($item['prix'] * $item['quantite'], 2, ',', ' '); ?> €
                    </td>

                    <!-- On passe l'ID du produit dans l'URL pour savoir lequel supprimer -->
                    <td>
                        <a href="index.php?page=cart&action=supprimer_panier&id=<?php echo $produit_id; ?>" class="btn-rouge">
                            Retirer
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>

            <tfoot>
                <tr>
                    <td colspan="3">Total général</td>
                    <!-- $total est calculé dans CartController.php avec une boucle foreach -->
                    <td colspan="2" class="prix-panier" style="font-size: 1.1rem;">
                        <?php echo number_format($total, 2, ',', ' '); ?> €
                    </td>
                </tr>
            </tfoot>
        </table>

        <div class="panier-actions">
            <a href="index.php?page=catalogue" class="btn">← Continuer les achats</a>
            <a href="index.php?page=cart&action=vider_panier" class="btn-rouge">Vider le panier</a>
        </div>

        <!-- Le formulaire envoie une requête POST vers OrderController qui va créer la commande -->
        <form method="POST" action="index.php?page=order" class="form-valider">
            <button type="submit" name="valider_commande" class="btn-valider">
                Valider la commande →
            </button>
        </form>

    <?php endif; ?>

</div>

<?php include 'views/footer.php'; ?>
