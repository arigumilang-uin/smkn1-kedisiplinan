# Manual SQL to fix print_log table

-- Drop existing table (if migration failed)
DROP TABLE IF EXISTS `surat_panggilan_print_log`;

-- Create table with correct schema
CREATE TABLE `surat_panggilan_print_log` (
    `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `surat_panggilan_id` bigint UNSIGNED NOT NULL,
    `user_id` bigint UNSIGNED NOT NULL,
    `printed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` varchar(255) DEFAULT NULL,
    
    KEY `surat_panggilan_print_log_surat_panggilan_id_printed_at_index` (`surat_panggilan_id`, `printed_at`),
    
    CONSTRAINT `surat_panggilan_print_log_surat_panggilan_id_foreign` 
        FOREIGN KEY (`surat_panggilan_id`) 
        REFERENCES `surat_panggilan` (`id`) 
        ON DELETE CASCADE,
    
    CONSTRAINT `surat_panggilan_print_log_user_id_foreign` 
        FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) 
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
