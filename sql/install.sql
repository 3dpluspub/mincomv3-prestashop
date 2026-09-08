CREATE TABLE IF NOT EXISTS `PREFIX_mincomv3` (
  `id_mincomv3` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `postal_code` VARCHAR(10),
  `city_name` VARCHAR(100) NOT NULL,
  `shipping_cost` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `free_shipping_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `active` TINYINT(1) DEFAULT 1,
  `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `postal_code_unique` (`postal_code`),
  INDEX `idx_city_name` (`city_name`),
  INDEX `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
