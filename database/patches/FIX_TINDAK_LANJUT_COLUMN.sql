-- SQL untuk menambahkan pembina_roles ke tindak_lanjut
-- Jalankan di phpMyAdmin

ALTER TABLE `tindak_lanjut` 
ADD COLUMN `pembina_roles` JSON NULL 
COMMENT 'Role pembina yang terlibat (untuk filtering dashboard)' 
AFTER `sanksi_deskripsi`;
