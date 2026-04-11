-- Vider les anciennes données
DELETE FROM products;
DELETE FROM categories;
ALTER TABLE categories AUTO_INCREMENT = 1;
ALTER TABLE products AUTO_INCREMENT = 1;

-- 5 catégories en rapport avec le thème tech
INSERT INTO categories (nom) VALUES
('Cybersécurité'),
('Développement'),
('Matériel'),
('Livres Tech'),
('Formation');

-- 12 produits répartis dans les 5 catégories
INSERT INTO products (nom, description, prix, categorie_id, image, vendeur_id) VALUES
('USB Rubber Ducky', 'Clé USB qui simule un clavier pour automatiser des scripts. Utilisée en pentest.', 45.99, 1, '', 1),
('Flipper Zero', 'Outil portable pour tester RFID, NFC, radio et BadUSB.', 169.99, 1, '', 1),
('WiFi Pineapple', 'Appareil pour auditer la sécurité des réseaux WiFi.', 299.99, 1, '', 1),
('Raspberry Pi 4', 'Mini ordinateur pour coder, automatiser et créer des outils.', 79.99, 2, '', 1),
('Arduino Uno', 'Carte électronique programmable idéale pour apprendre le développement embarqué.', 24.99, 2, '', 1),
('Clavier Mécanique Keychron K2', 'Clavier mécanique compact, parfait pour les développeurs.', 99.99, 3, '', 1),
('Câble RJ45 Cat6 10m', 'Câble réseau haute vitesse pour installations et tests réseau.', 12.99, 3, '', 1),
('Adaptateur USB WiFi Alfa', 'Adaptateur WiFi longue portée, compatible mode moniteur pour audit réseau.', 39.99, 3, '', 1),
('Livre : Hacking éthique', 'Guide complet sur les techniques de pentest et la cybersécurité offensive.', 34.99, 4, '', 1),
('Livre : PHP 8 avancé', 'Maîtrisez PHP 8, PDO, MVC et les bonnes pratiques de développement web.', 29.99, 4, '', 1),
('Formation : Sécurité réseau', 'Formation en ligne complète sur la sécurité des réseaux et protocoles.', 49.99, 5, '', 1),
('Formation : Développement web', 'Apprenez HTML, CSS, PHP et MySQL de zéro jusqu\'au projet complet.', 59.99, 5, '', 1);
