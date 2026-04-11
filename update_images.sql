-- Mise à jour des images produits
USE eboutique;

UPDATE products SET image = 'usb_rubber_ducky.svg'   WHERE nom LIKE '%Rubber Ducky%';
UPDATE products SET image = 'cle_usb_kingston.svg'   WHERE nom LIKE '%Kingston%';
UPDATE products SET image = 'souris_logitech_g502.svg' WHERE nom LIKE '%G502%';
UPDATE products SET image = 'cable_rj45_cat6.svg'    WHERE nom LIKE '%RJ45%' OR nom LIKE '%Cat6%';
UPDATE products SET image = 'switch_8_ports.svg'     WHERE nom LIKE '%Switch%' OR nom LIKE '%switch%';
UPDATE products SET image = 'camera_ip_foscam.svg'   WHERE nom LIKE '%Foscam%' OR nom LIKE '%Caméra IP%' OR nom LIKE '%Camera IP%';

-- Raspberry Pi et Logitech MX Keys (si pas déjà fait)
UPDATE products SET image = 'raspberry_pi_4.svg'     WHERE nom LIKE '%Raspberry%';
UPDATE products SET image = 'logitech_mx_keys.svg'   WHERE nom LIKE '%MX Keys%';
