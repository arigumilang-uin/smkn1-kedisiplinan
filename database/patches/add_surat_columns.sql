-- Add missing columns to surat_panggilan table

ALTER TABLE `surat_panggilan` 
ADD COLUMN `lampiran` VARCHAR(255) NULL AFTER `nomor_surat`,
ADD COLUMN `hal` VARCHAR(255) NOT NULL DEFAULT 'Panggilan Orang Tua / Wali Murid' AFTER `lampiran`,
ADD COLUMN `tempat_pertemuan` VARCHAR(255) NOT NULL DEFAULT 'Ruang BK SMK Negeri 1 Lubuk Dalam' AFTER `waktu_pertemuan`;
