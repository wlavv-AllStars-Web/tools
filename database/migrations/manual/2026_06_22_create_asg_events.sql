CREATE TABLE IF NOT EXISTS `galleries` (
  `id_gallery` int unsigned NOT NULL AUTO_INCREMENT,
  `id_shop` int unsigned NOT NULL DEFAULT 2,
  `name` varchar(255) NOT NULL,
  `name_en` varchar(255) NOT NULL DEFAULT '',
  `name_es` varchar(255) NOT NULL DEFAULT '',
  `name_fr` varchar(255) NOT NULL DEFAULT '',
  `name_pt` varchar(255) NOT NULL DEFAULT '',
  `name_it` varchar(255) NOT NULL DEFAULT '',
  `event_date` varchar(50) DEFAULT NULL,
  `gallery_type` enum('internal','flickr') NOT NULL DEFAULT 'internal',
  `cover_desktop` varchar(500) DEFAULT NULL,
  `cover_mobile` varchar(500) DEFAULT NULL,
  `flickr_url` varchar(500) DEFAULT NULL,
  `display` tinyint(1) NOT NULL DEFAULT 1,
  `position` int unsigned NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_gallery`),
  KEY `idx_display_position` (`display`,`position`),
  KEY `idx_gallery_type` (`gallery_type`),
  KEY `idx_id_shop` (`id_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `galleries_images` (
  `id_gallery_image` int unsigned NOT NULL AUTO_INCREMENT,
  `id_gallery` int unsigned NOT NULL,
  `image` varchar(500) NOT NULL,
  `position` int unsigned NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_gallery_image`),
  UNIQUE KEY `uniq_gallery_image` (`id_gallery`,`image`),
  KEY `idx_gallery_position` (`id_gallery`,`position`),
  CONSTRAINT `fk_galleries_images_gallery`
    FOREIGN KEY (`id_gallery`) REFERENCES `galleries` (`id_gallery`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
