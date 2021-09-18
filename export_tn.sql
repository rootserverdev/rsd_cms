-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server Version:               8.0.23 - MySQL Community Server - GPL
-- Server Betriebssystem:        Win64
-- HeidiSQL Version:             11.2.0.6213
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Exportiere Datenbank Struktur für serviceportal
CREATE DATABASE IF NOT EXISTS `serviceportal` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `serviceportal`;

-- Exportiere Struktur von Tabelle serviceportal.as_appserver_content
CREATE TABLE IF NOT EXISTS `as_appserver_content` (
  `appcontent_id` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`appcontent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.as_appserver_content: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `as_appserver_content` DISABLE KEYS */;
/*!40000 ALTER TABLE `as_appserver_content` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.as_appserver_kategorien
CREATE TABLE IF NOT EXISTS `as_appserver_kategorien` (
  `appkategorie_id` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`appkategorie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.as_appserver_kategorien: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `as_appserver_kategorien` DISABLE KEYS */;
/*!40000 ALTER TABLE `as_appserver_kategorien` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.bd_dokumente
CREATE TABLE IF NOT EXISTS `bd_dokumente` (
  `dokument_id` int NOT NULL AUTO_INCREMENT,
  `konto` int DEFAULT NULL,
  `gruppe` int DEFAULT NULL,
  PRIMARY KEY (`dokument_id`),
  KEY `FK_bd_dokumente_bd_konto` (`konto`),
  KEY `FK_bd_dokumente_sd_benutzergruppen` (`gruppe`),
  CONSTRAINT `FK_bd_dokumente_bd_konto` FOREIGN KEY (`konto`) REFERENCES `bd_konto` (`konto_id`),
  CONSTRAINT `FK_bd_dokumente_sd_benutzergruppen` FOREIGN KEY (`gruppe`) REFERENCES `sd_benutzergruppen` (`benutzergruppen_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.bd_dokumente: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `bd_dokumente` DISABLE KEYS */;
/*!40000 ALTER TABLE `bd_dokumente` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.bd_konto
CREATE TABLE IF NOT EXISTS `bd_konto` (
  `konto_id` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`konto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.bd_konto: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `bd_konto` DISABLE KEYS */;
/*!40000 ALTER TABLE `bd_konto` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.bd_nachrichten
CREATE TABLE IF NOT EXISTS `bd_nachrichten` (
  `nachricht_id` int NOT NULL AUTO_INCREMENT,
  `konto` int DEFAULT NULL,
  `gruppe` int DEFAULT NULL,
  PRIMARY KEY (`nachricht_id`),
  KEY `FK_bd_nachrichten_bd_konto` (`konto`),
  KEY `FK_bd_nachrichten_sd_benutzergruppen` (`gruppe`),
  CONSTRAINT `FK_bd_nachrichten_bd_konto` FOREIGN KEY (`konto`) REFERENCES `bd_konto` (`konto_id`),
  CONSTRAINT `FK_bd_nachrichten_sd_benutzergruppen` FOREIGN KEY (`gruppe`) REFERENCES `sd_benutzergruppen` (`benutzergruppen_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.bd_nachrichten: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `bd_nachrichten` DISABLE KEYS */;
/*!40000 ALTER TABLE `bd_nachrichten` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.cm_contents
CREATE TABLE IF NOT EXISTS `cm_contents` (
  `content_id` int NOT NULL AUTO_INCREMENT,
  `seite` int DEFAULT NULL,
  `template` int DEFAULT NULL,
  PRIMARY KEY (`content_id`),
  KEY `FK_cm_contents_cm_sites` (`seite`),
  KEY `FK_cm_contents_cm_content_templates` (`template`),
  CONSTRAINT `FK_cm_contents_cm_content_templates` FOREIGN KEY (`template`) REFERENCES `cm_content_templates` (`content_template_id`),
  CONSTRAINT `FK_cm_contents_cm_sites` FOREIGN KEY (`seite`) REFERENCES `cm_sites` (`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.cm_contents: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `cm_contents` DISABLE KEYS */;
/*!40000 ALTER TABLE `cm_contents` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.cm_content_templates
CREATE TABLE IF NOT EXISTS `cm_content_templates` (
  `content_template_id` int NOT NULL AUTO_INCREMENT,
  `mandant` int DEFAULT NULL,
  PRIMARY KEY (`content_template_id`),
  KEY `FK_cm_content_templates_sd_mandanten` (`mandant`),
  CONSTRAINT `FK_cm_content_templates_sd_mandanten` FOREIGN KEY (`mandant`) REFERENCES `sd_mandanten` (`mandant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.cm_content_templates: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `cm_content_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `cm_content_templates` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.cm_sites
CREATE TABLE IF NOT EXISTS `cm_sites` (
  `site_id` int NOT NULL AUTO_INCREMENT,
  `mandant` int DEFAULT NULL,
  `template` int DEFAULT NULL,
  PRIMARY KEY (`site_id`),
  KEY `FK_cm_sites_sd_mandanten` (`mandant`),
  KEY `FK_cm_sites_cm_site_templates` (`template`),
  CONSTRAINT `FK_cm_sites_cm_site_templates` FOREIGN KEY (`template`) REFERENCES `cm_site_templates` (`site_template_id`),
  CONSTRAINT `FK_cm_sites_sd_mandanten` FOREIGN KEY (`mandant`) REFERENCES `sd_mandanten` (`mandant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.cm_sites: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `cm_sites` DISABLE KEYS */;
/*!40000 ALTER TABLE `cm_sites` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.cm_site_templates
CREATE TABLE IF NOT EXISTS `cm_site_templates` (
  `site_template_id` int NOT NULL AUTO_INCREMENT,
  `mandant` int DEFAULT NULL,
  PRIMARY KEY (`site_template_id`),
  KEY `FK_cm_site_templates_sd_mandanten` (`mandant`),
  CONSTRAINT `FK_cm_site_templates_sd_mandanten` FOREIGN KEY (`mandant`) REFERENCES `sd_mandanten` (`mandant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.cm_site_templates: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `cm_site_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `cm_site_templates` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.dl_dienstleistungen
CREATE TABLE IF NOT EXISTS `dl_dienstleistungen` (
  `dienstleistung_id` int NOT NULL AUTO_INCREMENT,
  `mandant` int DEFAULT NULL,
  `leika` int DEFAULT NULL,
  `ozg` int DEFAULT NULL,
  PRIMARY KEY (`dienstleistung_id`),
  KEY `FK_dl_dienstleistungen_dl_leika` (`leika`),
  KEY `FK_dl_dienstleistungen_dl_ozg` (`ozg`),
  KEY `FK_dl_dienstleistungen_sd_mandanten` (`mandant`),
  CONSTRAINT `FK_dl_dienstleistungen_dl_leika` FOREIGN KEY (`leika`) REFERENCES `dl_leika` (`leika_id`),
  CONSTRAINT `FK_dl_dienstleistungen_dl_ozg` FOREIGN KEY (`ozg`) REFERENCES `dl_ozg` (`ozg_id`),
  CONSTRAINT `FK_dl_dienstleistungen_sd_mandanten` FOREIGN KEY (`mandant`) REFERENCES `sd_mandanten` (`mandant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.dl_dienstleistungen: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `dl_dienstleistungen` DISABLE KEYS */;
/*!40000 ALTER TABLE `dl_dienstleistungen` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.dl_dienstleistung_dokumente
CREATE TABLE IF NOT EXISTS `dl_dienstleistung_dokumente` (
  `dokumente_id` int NOT NULL AUTO_INCREMENT,
  `dienstleistung` int DEFAULT NULL,
  PRIMARY KEY (`dokumente_id`),
  KEY `FK_dl_dienstleistung_dokumente_dl_dienstleistungen` (`dienstleistung`),
  CONSTRAINT `FK_dl_dienstleistung_dokumente_dl_dienstleistungen` FOREIGN KEY (`dienstleistung`) REFERENCES `dl_dienstleistungen` (`dienstleistung_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.dl_dienstleistung_dokumente: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `dl_dienstleistung_dokumente` DISABLE KEYS */;
/*!40000 ALTER TABLE `dl_dienstleistung_dokumente` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.dl_dienstleistung_informationen
CREATE TABLE IF NOT EXISTS `dl_dienstleistung_informationen` (
  `information_id` int NOT NULL AUTO_INCREMENT,
  `dienstleistung` int DEFAULT NULL,
  PRIMARY KEY (`information_id`),
  KEY `FK_dl_dienstleistung_informationen_dl_dienstleistungen` (`dienstleistung`),
  CONSTRAINT `FK_dl_dienstleistung_informationen_dl_dienstleistungen` FOREIGN KEY (`dienstleistung`) REFERENCES `dl_dienstleistungen` (`dienstleistung_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.dl_dienstleistung_informationen: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `dl_dienstleistung_informationen` DISABLE KEYS */;
/*!40000 ALTER TABLE `dl_dienstleistung_informationen` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.dl_dienstleistung_mittel
CREATE TABLE IF NOT EXISTS `dl_dienstleistung_mittel` (
  `mittel_id` int NOT NULL AUTO_INCREMENT,
  `dienstleistung` int DEFAULT NULL,
  PRIMARY KEY (`mittel_id`),
  KEY `FK_dl_dienstleistung_mittel_dl_dienstleistungen` (`dienstleistung`),
  CONSTRAINT `FK_dl_dienstleistung_mittel_dl_dienstleistungen` FOREIGN KEY (`dienstleistung`) REFERENCES `dl_dienstleistungen` (`dienstleistung_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.dl_dienstleistung_mittel: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `dl_dienstleistung_mittel` DISABLE KEYS */;
/*!40000 ALTER TABLE `dl_dienstleistung_mittel` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.dl_leika
CREATE TABLE IF NOT EXISTS `dl_leika` (
  `leika_id` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`leika_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.dl_leika: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `dl_leika` DISABLE KEYS */;
/*!40000 ALTER TABLE `dl_leika` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.dl_ozg
CREATE TABLE IF NOT EXISTS `dl_ozg` (
  `ozg_id` int NOT NULL AUTO_INCREMENT,
  `kategorie` int DEFAULT NULL,
  PRIMARY KEY (`ozg_id`),
  KEY `FK_dl_ozg_dl_ozg_kategorien` (`kategorie`),
  CONSTRAINT `FK_dl_ozg_dl_ozg_kategorien` FOREIGN KEY (`kategorie`) REFERENCES `dl_ozg_kategorien` (`ozgkategorie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.dl_ozg: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `dl_ozg` DISABLE KEYS */;
/*!40000 ALTER TABLE `dl_ozg` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.dl_ozg_kategorien
CREATE TABLE IF NOT EXISTS `dl_ozg_kategorien` (
  `ozgkategorie_id` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`ozgkategorie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.dl_ozg_kategorien: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `dl_ozg_kategorien` DISABLE KEYS */;
/*!40000 ALTER TABLE `dl_ozg_kategorien` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.dl_z_dienstleistung_formulare
CREATE TABLE IF NOT EXISTS `dl_z_dienstleistung_formulare` (
  `zdienstleistung` int NOT NULL,
  `zformular` int NOT NULL,
  PRIMARY KEY (`zdienstleistung`,`zformular`),
  KEY `FK_dl_z_dienstleistung_formulare_fs_formulare` (`zformular`),
  CONSTRAINT `FK_dl_z_dienstleistung_formulare_dl_dienstleistungen` FOREIGN KEY (`zdienstleistung`) REFERENCES `dl_dienstleistungen` (`dienstleistung_id`),
  CONSTRAINT `FK_dl_z_dienstleistung_formulare_fs_formulare` FOREIGN KEY (`zformular`) REFERENCES `fs_formulare` (`formular_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.dl_z_dienstleistung_formulare: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `dl_z_dienstleistung_formulare` DISABLE KEYS */;
/*!40000 ALTER TABLE `dl_z_dienstleistung_formulare` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.dl_z_dienstleistung_kontaktdaten
CREATE TABLE IF NOT EXISTS `dl_z_dienstleistung_kontaktdaten` (
  `zdienstleistung` int NOT NULL,
  `zkontaktdaten` int NOT NULL,
  PRIMARY KEY (`zdienstleistung`,`zkontaktdaten`),
  KEY `FK_dl_z_dienstleistung_kontaktdaten_sd_kontaktdaten` (`zkontaktdaten`),
  CONSTRAINT `FK_dl_z_dienstleistung_kontaktdaten_dl_dienstleistungen` FOREIGN KEY (`zdienstleistung`) REFERENCES `dl_dienstleistungen` (`dienstleistung_id`),
  CONSTRAINT `FK_dl_z_dienstleistung_kontaktdaten_sd_kontaktdaten` FOREIGN KEY (`zkontaktdaten`) REFERENCES `sd_kontaktdaten` (`kontaktdaten_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.dl_z_dienstleistung_kontaktdaten: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `dl_z_dienstleistung_kontaktdaten` DISABLE KEYS */;
/*!40000 ALTER TABLE `dl_z_dienstleistung_kontaktdaten` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.dl_z_formular_formularfelder
CREATE TABLE IF NOT EXISTS `dl_z_formular_formularfelder` (
  `zformular` int NOT NULL,
  `zformularfeld` int NOT NULL,
  PRIMARY KEY (`zformular`,`zformularfeld`),
  KEY `FK_dl_z_formular_formularfelder_fs_formularfelder` (`zformularfeld`),
  CONSTRAINT `FK_dl_z_formular_formularfelder_fs_formulare` FOREIGN KEY (`zformular`) REFERENCES `fs_formulare` (`formular_id`),
  CONSTRAINT `FK_dl_z_formular_formularfelder_fs_formularfelder` FOREIGN KEY (`zformularfeld`) REFERENCES `fs_formularfelder` (`formularfeld_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.dl_z_formular_formularfelder: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `dl_z_formular_formularfelder` DISABLE KEYS */;
/*!40000 ALTER TABLE `dl_z_formular_formularfelder` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.fs_formulare
CREATE TABLE IF NOT EXISTS `fs_formulare` (
  `formular_id` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`formular_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.fs_formulare: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `fs_formulare` DISABLE KEYS */;
/*!40000 ALTER TABLE `fs_formulare` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.fs_formularfelder
CREATE TABLE IF NOT EXISTS `fs_formularfelder` (
  `formularfeld_id` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`formularfeld_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.fs_formularfelder: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `fs_formularfelder` DISABLE KEYS */;
/*!40000 ALTER TABLE `fs_formularfelder` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.gd_benachrichtigungen
CREATE TABLE IF NOT EXISTS `gd_benachrichtigungen` (
  `benachrichtigung_id` int NOT NULL AUTO_INCREMENT,
  `konto` int DEFAULT NULL,
  `benutzer` int DEFAULT NULL,
  PRIMARY KEY (`benachrichtigung_id`),
  KEY `FK_gd_benachrichtigungen_bd_konto` (`konto`),
  KEY `FK_gd_benachrichtigungen_sd_benutzer` (`benutzer`),
  CONSTRAINT `FK_gd_benachrichtigungen_bd_konto` FOREIGN KEY (`konto`) REFERENCES `bd_konto` (`konto_id`),
  CONSTRAINT `FK_gd_benachrichtigungen_sd_benutzer` FOREIGN KEY (`benutzer`) REFERENCES `sd_benutzer` (`benutzer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.gd_benachrichtigungen: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `gd_benachrichtigungen` DISABLE KEYS */;
/*!40000 ALTER TABLE `gd_benachrichtigungen` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.gd_config
CREATE TABLE IF NOT EXISTS `gd_config` (
  `config_id` varchar(250) NOT NULL,
  `config_mandant` int DEFAULT NULL,
  `config_value` longtext,
  `config_default` longtext,
  PRIMARY KEY (`config_id`),
  KEY `FK_gd_config_sd_mandanten` (`config_mandant`),
  CONSTRAINT `FK_gd_config_sd_mandanten` FOREIGN KEY (`config_mandant`) REFERENCES `sd_mandanten` (`mandant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.gd_config: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `gd_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `gd_config` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.gd_sessions
CREATE TABLE IF NOT EXISTS `gd_sessions` (
  `session_id` int NOT NULL AUTO_INCREMENT,
  `konto` int DEFAULT NULL,
  `benutzer` int DEFAULT NULL,
  `session` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`session_id`),
  KEY `FK_gd_sessions_bd_konto` (`konto`),
  KEY `FK_gd_sessions_sd_benutzer` (`benutzer`),
  CONSTRAINT `FK_gd_sessions_bd_konto` FOREIGN KEY (`konto`) REFERENCES `bd_konto` (`konto_id`),
  CONSTRAINT `FK_gd_sessions_sd_benutzer` FOREIGN KEY (`benutzer`) REFERENCES `sd_benutzer` (`benutzer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.gd_sessions: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `gd_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `gd_sessions` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.sd_benutzer
CREATE TABLE IF NOT EXISTS `sd_benutzer` (
  `benutzer_id` int NOT NULL AUTO_INCREMENT,
  `mandant` int DEFAULT NULL,
  PRIMARY KEY (`benutzer_id`),
  KEY `FK_sd_benutzer_sd_mandanten` (`mandant`),
  CONSTRAINT `FK_sd_benutzer_sd_mandanten` FOREIGN KEY (`mandant`) REFERENCES `sd_mandanten` (`mandant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.sd_benutzer: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `sd_benutzer` DISABLE KEYS */;
/*!40000 ALTER TABLE `sd_benutzer` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.sd_benutzergruppen
CREATE TABLE IF NOT EXISTS `sd_benutzergruppen` (
  `benutzergruppen_id` int NOT NULL AUTO_INCREMENT,
  `mandant` int DEFAULT NULL,
  PRIMARY KEY (`benutzergruppen_id`),
  KEY `FK_sd_benutzergruppen_sd_mandanten` (`mandant`),
  CONSTRAINT `FK_sd_benutzergruppen_sd_mandanten` FOREIGN KEY (`mandant`) REFERENCES `sd_mandanten` (`mandant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.sd_benutzergruppen: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `sd_benutzergruppen` DISABLE KEYS */;
/*!40000 ALTER TABLE `sd_benutzergruppen` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.sd_kontaktdaten
CREATE TABLE IF NOT EXISTS `sd_kontaktdaten` (
  `kontaktdaten_id` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`kontaktdaten_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.sd_kontaktdaten: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `sd_kontaktdaten` DISABLE KEYS */;
/*!40000 ALTER TABLE `sd_kontaktdaten` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.sd_mandanten
CREATE TABLE IF NOT EXISTS `sd_mandanten` (
  `mandant_id` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`mandant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.sd_mandanten: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `sd_mandanten` DISABLE KEYS */;
/*!40000 ALTER TABLE `sd_mandanten` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.sd_nachrichtenvorlagen
CREATE TABLE IF NOT EXISTS `sd_nachrichtenvorlagen` (
  `vorlagen_id` int NOT NULL AUTO_INCREMENT,
  `benutzerrolle` int DEFAULT NULL,
  PRIMARY KEY (`vorlagen_id`),
  KEY `FK_sd_nachrichtenvorlagen_sd_rollen` (`benutzerrolle`),
  CONSTRAINT `FK_sd_nachrichtenvorlagen_sd_rollen` FOREIGN KEY (`benutzerrolle`) REFERENCES `sd_rollen` (`rollen_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.sd_nachrichtenvorlagen: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `sd_nachrichtenvorlagen` DISABLE KEYS */;
/*!40000 ALTER TABLE `sd_nachrichtenvorlagen` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.sd_rollen
CREATE TABLE IF NOT EXISTS `sd_rollen` (
  `rollen_id` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`rollen_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.sd_rollen: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `sd_rollen` DISABLE KEYS */;
/*!40000 ALTER TABLE `sd_rollen` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.sd_z_benutzergruppen_rollen
CREATE TABLE IF NOT EXISTS `sd_z_benutzergruppen_rollen` (
  `benutzergruppe` int NOT NULL,
  `rolle` int NOT NULL,
  PRIMARY KEY (`benutzergruppe`,`rolle`),
  KEY `FK_sd_z_benutzergruppen_rollen_sd_rollen` (`rolle`),
  CONSTRAINT `FK__sd_benutzergruppen` FOREIGN KEY (`benutzergruppe`) REFERENCES `sd_benutzergruppen` (`benutzergruppen_id`),
  CONSTRAINT `FK_sd_z_benutzergruppen_rollen_sd_rollen` FOREIGN KEY (`rolle`) REFERENCES `sd_rollen` (`rollen_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.sd_z_benutzergruppen_rollen: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `sd_z_benutzergruppen_rollen` DISABLE KEYS */;
/*!40000 ALTER TABLE `sd_z_benutzergruppen_rollen` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle serviceportal.sd_z_benutzergruppe_benutzer
CREATE TABLE IF NOT EXISTS `sd_z_benutzergruppe_benutzer` (
  `benutzergruppe` int NOT NULL,
  `benutzer` int NOT NULL,
  PRIMARY KEY (`benutzergruppe`,`benutzer`),
  KEY `FK_benutzer_sd_benutzer` (`benutzer`),
  CONSTRAINT `FK_benutzer_sd_benutzer` FOREIGN KEY (`benutzer`) REFERENCES `sd_benutzer` (`benutzer_id`),
  CONSTRAINT `FK_benutzer_sd_benutzergruppen` FOREIGN KEY (`benutzergruppe`) REFERENCES `sd_benutzergruppen` (`benutzergruppen_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Exportiere Daten aus Tabelle serviceportal.sd_z_benutzergruppe_benutzer: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `sd_z_benutzergruppe_benutzer` DISABLE KEYS */;
/*!40000 ALTER TABLE `sd_z_benutzergruppe_benutzer` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
