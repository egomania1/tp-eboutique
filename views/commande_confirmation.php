<?php include 'views/header.php'; ?>

<div class="page-section">

    <div class="section-tag">Commande validée</div>
    <h1>Merci pour votre commande !</h1>

    <div class="confirmation-box">

        <?php
        // On affiche le numéro de commande et la date
        // strtotime() convertit la date MySQL en timestamp PHP
        // date() formate ce timestamp en date lisible
        ?>
        <p class="confirmation-ref">
            Référence commande : <strong>#<?php echo $commande['id']; ?></strong>
        </p>
        <p class="confirmation-date">
            Passée le <?php echo date('d/m/Y à H:i', strtotime($commande['date_commande'])); ?>
        </p>

        <!-- Tableau récapitulatif des produits commandés -->
        <table class="tableau-panier">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Prix unitaire</th>
                    <th>Quantité</th>
                    <th>Sous-total</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($commande['items'] as $item): ?>
                <tr>
                    <!-- htmlspecialchars() protège contre les injections XSS -->
                    <td><?php echo htmlspecialchars($item['nom_produit']); ?></td>
                    <td class="prix-panier">
                        <?php echo number_format($item['prix_unitaire'], 2, ',', ' '); ?> €
                    </td>
                    <td><?php echo $item['quantite']; ?></td>
                    <td class="prix-panier">
                        <?php echo number_format($item['prix_unitaire'] * $item['quantite'], 2, ',', ' '); ?> €
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">Total payé</td>
                    <td class="prix-panier" style="font-size: 1.1rem;">
                        <?php echo number_format($commande['total'], 2, ',', ' '); ?> €
                    </td>
                </tr>
            </tfoot>
        </table>

        <div class="panier-actions">
            <a href="index.php?page=catalogue" class="btn">← Retour au catalogue</a>
        </div>

    </div>

</div>

<?php include 'views/footer.php'; ?>
