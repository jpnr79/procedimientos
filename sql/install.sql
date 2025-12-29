CREATE TABLE IF NOT EXISTS `glpi_plugin_procedimientos_accions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment` longtext COLLATE utf8mb4_unicode_ci,
  `date_mod` TIMESTAMP NULL DEFAULT NULL,
  `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
  `entities_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `plugin_procedimientos_tipoaccions_id` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `entities_id` (`entities_id`),
  KEY `is_recursive` (`is_recursive`),
  KEY `date_mod` (`date_mod`),
  KEY `type` (`plugin_procedimientos_tipoaccions_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_procedimientos_validacions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `plugin_procedimientos_accions_id` BIGINT UNSIGNED NOT NULL,
  `groups_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `users_id_validate` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `comment_submission` text COLLATE utf8mb4_unicode_ci,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `validador` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `groups_id` (`groups_id`),
  KEY `users_id_validate` (`users_id_validate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_procedimientos_updatetickets` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `plugin_procedimientos_accions_id` BIGINT UNSIGNED NOT NULL,
  `requesttypes_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `status` BIGINT UNSIGNED NOT NULL DEFAULT '1',
  `itilcategories_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `type` BIGINT UNSIGNED NOT NULL DEFAULT '1',
  `slts_ttr_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `solutiontemplates_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unicity` (`plugin_procedimientos_accions_id`),
  KEY `requesttypes_id` (`requesttypes_id`),
  KEY `status` (`status`),
  KEY `itilcategories_id` (`itilcategories_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_procedimientos_tipoaccions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `entities_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comment` text COLLATE utf8mb4_unicode_ci,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `entities_id` (`entities_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `glpi_plugin_procedimientos_tipoaccions` (`id`,`entities_id`,`is_recursive`,`name`,`comment`,`uuid`) VALUES (1,0,1,'Tarea','','c0dff0d6-9e4abb40-5a61e7e35e2256.00000003');
INSERT INTO `glpi_plugin_procedimientos_tipoaccions` (`id`,`entities_id`,`is_recursive`,`name`,`comment`,`uuid`) VALUES (2,0,1,'Escalado','','c0dff0d6-9e4abb40-5a61e7e35e2256.00000004');
INSERT INTO `glpi_plugin_procedimientos_tipoaccions` (`id`,`entities_id`,`is_recursive`,`name`,`comment`,`uuid`) VALUES (3,0,1,'Modificación ticket','','c0dff0d6-9e4abb40-5a61e7e35e2256.00000005');
INSERT INTO `glpi_plugin_procedimientos_tipoaccions` (`id`,`entities_id`,`is_recursive`,`name`,`comment`,`uuid`) VALUES (4,0,1,'Seguimiento','','c0dff0d6-9e4abb40-5a61e7e35e2256.00000006');
INSERT INTO `glpi_plugin_procedimientos_tipoaccions` (`id`,`entities_id`,`is_recursive`,`name`,`comment`,`uuid`) VALUES (5,0,1,'Validación','','c0dff0d6-9e4abb40-5a61e7e35e2256.00000007');





CREATE TABLE IF NOT EXISTS `glpi_plugin_procedimientos_tareas` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `plugin_procedimientos_accions_id` BIGINT UNSIGNED NOT NULL,
  `taskcategories_id` BIGINT UNSIGNED DEFAULT NULL,
  `users_id_tech` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `groups_id_tech` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `is_private` tinyint(1) NOT NULL DEFAULT '1',
  `state` BIGINT UNSIGNED NOT NULL DEFAULT '1',
  `tasktemplates_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unicity` (`plugin_procedimientos_accions_id`),
  KEY `taskcategories_id` (`taskcategories_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_procedimientos_seguimientos` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `plugin_procedimientos_accions_id` BIGINT UNSIGNED NOT NULL,
  `users_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `is_private` tinyint(1) NOT NULL DEFAULT '0',
  `requesttypes_id` BIGINT UNSIGNED DEFAULT '0',
  `followuptypes_id` BIGINT UNSIGNED DEFAULT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'for display and transfert',
  `tag` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unicity` (`plugin_procedimientos_accions_id`),
  KEY `users_id` (`users_id`),
  KEY `is_private` (`is_private`),
  KEY `requesttypes_id` (`requesttypes_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_procedimientos_saltos` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `plugin_procedimientos_procedimientos_id` BIGINT UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment` longtext COLLATE utf8mb4_unicode_ci,
  `date_mod` TIMESTAMP NULL DEFAULT NULL,
  `goto` tinyint(11) NOT NULL DEFAULT '0',
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `goto_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `date_mod` (`date_mod`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_procedimientos_procedimientos_tickets` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `plugin_procedimientos_procedimientos_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `tickets_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `line` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `itemtype` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `items_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `state` tinyint(1) NOT NULL DEFAULT '0',
  `instancia_id` BIGINT UNSIGNED DEFAULT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unicity` (`plugin_procedimientos_procedimientos_id`,`tickets_id`,`line`),
  KEY `tickets_id` (`tickets_id`),
  KEY `plugin_procedimientos_procedimientos_id` (`plugin_procedimientos_procedimientos_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_procedimientos_procedimientos_ticketrecurrents` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `plugin_procedimientos_procedimientos_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `ticketrecurrents_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unicity` (`plugin_procedimientos_procedimientos_id`,`ticketrecurrents_id`),
  KEY `ticketrecurrents_id` (`ticketrecurrents_id`),
  KEY `plugin_procedimientos_procedimientos_id` (`plugin_procedimientos_procedimientos_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_procedimientos_procedimientos_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `plugin_procedimientos_procedimientos_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `date_mod` TIMESTAMP NULL DEFAULT NULL,
  `line` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `itemtype` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `items_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unicity` (`id`),
  KEY `plugin_procedimientos_procedimientos_id` (`plugin_procedimientos_procedimientos_id`),
  KEY `date_mod` (`date_mod`),
  KEY `line` (`line`),
  KEY `itemtype` (`itemtype`),
  KEY `items_id` (`items_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_procedimientos_procedimientos_groups` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `plugin_procedimientos_procedimientos_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `groups_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unicity` (`plugin_procedimientos_procedimientos_id`,`groups_id`),
  KEY `groups_id` (`groups_id`),
  KEY `plugin_procedimientos_procedimientos_id` (`plugin_procedimientos_procedimientos_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_procedimientos_procedimientos_forms` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `plugin_procedimientos_procedimientos_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `plugin_formcreator_forms_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `plugin_formcreator_targettickets_id` BIGINT UNSIGNED NULL DEFAULT 0,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unicity` (`plugin_procedimientos_procedimientos_id`,`plugin_formcreator_forms_id`, `plugin_formcreator_targettickets_id`),
  KEY `plugin_formcreator_forms_id` (`plugin_formcreator_forms_id`),
  KEY `plugin_procedimientos_procedimientos_id` (`plugin_procedimientos_procedimientos_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_procedimientos_procedimientos` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment` longtext COLLATE utf8mb4_unicode_ci,
  `date_mod` TIMESTAMP NULL DEFAULT NULL,
  `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
  `entities_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `date_mod` (`date_mod`),
  KEY `entities_id` (`entities_id`),
  KEY `is_recursive` (`is_recursive`),
  KEY `active` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_procedimientos_marcadors` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comment` text COLLATE utf8mb4_unicode_ci,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `glpi_plugin_procedimientos_marcadors` (`id`,`name`,`comment`,`uuid`) VALUES (1,'Inicio','Iniciar procedimiento','c0dff0d6-9e4abb40-5a61e7e35e2256.00000001');
INSERT INTO `glpi_plugin_procedimientos_marcadors` (`id`,`name`,`comment`,`uuid`) VALUES (2,'Fin','Finalizar procedimiento','c0dff0d6-9e4abb40-5a61e7e35e2256.00000002');



CREATE TABLE IF NOT EXISTS `glpi_plugin_procedimientos_links` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_mod` TIMESTAMP NULL DEFAULT NULL,
  `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
  `entities_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `date_mod` (`date_mod`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_procedimientos_escalados` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `plugin_procedimientos_accions_id` BIGINT UNSIGNED NOT NULL,
  `users_id_asignado` BIGINT UNSIGNED DEFAULT NULL,
  `groups_id_asignado` BIGINT UNSIGNED DEFAULT NULL,
  `users_id_observ` BIGINT UNSIGNED DEFAULT NULL,
  `groups_id_observ` BIGINT UNSIGNED DEFAULT NULL,
  `suppliers_id` BIGINT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unicity` (`plugin_procedimientos_accions_id`),
  KEY `users_id_asignado` (`users_id_asignado`),
  KEY ` group_id_asignado` (`groups_id_asignado`),
  KEY ` users_id_observ` (`users_id_observ`),
  KEY ` group_id_observ` (`groups_id_observ`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_procedimientos_documents` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `documentcategories_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `documents_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `itemtype` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `items_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `documentcategories_id` (`documentcategories_id`),
  KEY `documents_id` (`documents_id`),
  KEY `itemtype` (`itemtype`),
  KEY `items_id` (`items_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_procedimientos_condicions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `plugin_procedimientos_procedimientos_id` BIGINT UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment` longtext COLLATE utf8mb4_unicode_ci,
  `date_mod` TIMESTAMP NULL DEFAULT NULL,
  `way_yes` tinyint(11) NOT NULL DEFAULT '0',
  `way_no` tinyint(11) NOT NULL DEFAULT '0',
  `tag_0` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Si',
  `tag_1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No',
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_1` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `tag_id_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_2` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `tag_id_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_3` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `tag_id_3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_4` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `tag_id_4` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_5` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `tag_id_5` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `line_id_1` tinyint(11) unsigned NOT NULL DEFAULT '0',
  `line_id_2` tinyint(11) NOT NULL DEFAULT '0',
  `line_id_3` tinyint(11) NOT NULL DEFAULT '0',
  `line_id_4` tinyint(11) NOT NULL DEFAULT '0',
  `line_id_5` tinyint(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `date_mod` (`date_mod`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_procedimientos_accions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment` longtext COLLATE utf8mb4_unicode_ci,
  `date_mod` TIMESTAMP NULL DEFAULT NULL,
  `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
  `entities_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `plugin_procedimientos_tipoaccions_id` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `entities_id` (`entities_id`),
  KEY `is_recursive` (`is_recursive`),
  KEY `date_mod` (`date_mod`),
  KEY `type` (`plugin_procedimientos_tipoaccions_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


