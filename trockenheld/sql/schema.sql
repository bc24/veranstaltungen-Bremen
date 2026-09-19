-- =====================================================================
-- Trockenheld – Datenbankschema
-- Import: phpMyAdmin -> Import, oder per Konsole:
--   mysql -u DEIN_USER -p DEIN_DATENBANKNAME < schema.sql
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- Tabelle: benutzer
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS benutzer (
    id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    benutzername            VARCHAR(50)  NOT NULL,
    email                   VARCHAR(150) NOT NULL,
    passwort_hash           VARCHAR(255) NOT NULL,
    anzeigename             VARCHAR(100) NULL,
    avatar_farbe            CHAR(7)      NOT NULL DEFAULT '#0f9d78',
    bio                     TEXT         NULL,
    warum_text              TEXT         NULL,
    wohnort                 VARCHAR(100) NULL,
    geburtsjahr             SMALLINT     NULL,
    start_datum             DATE         NOT NULL,
    ziel_tage               INT          NULL,
    profil_oeffentlich      TINYINT(1)   NOT NULL DEFAULT 1,
    email_erinnerung        TINYINT(1)   NOT NULL DEFAULT 1,
    punkte                  INT          NOT NULL DEFAULT 0,
    aktueller_streak        INT          NOT NULL DEFAULT 0,
    laengster_streak        INT          NOT NULL DEFAULT 0,
    letzter_checkin         DATE         NULL,
    rolle                   ENUM('mitglied','admin') NOT NULL DEFAULT 'mitglied',
    datenschutz_akzeptiert  DATETIME     NOT NULL,
    erstellt_am             DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_benutzername (benutzername),
    UNIQUE KEY uq_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabelle: checkins  (ein Eintrag pro Tag und Benutzer = "nichts getrunken")
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS checkins (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    benutzer_id     INT UNSIGNED NOT NULL,
    checkin_datum   DATE NOT NULL,
    notiz           VARCHAR(255) NULL,
    erstellt_am     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_benutzer_tag (benutzer_id, checkin_datum),
    CONSTRAINT fk_checkins_benutzer FOREIGN KEY (benutzer_id)
        REFERENCES benutzer(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabelle: meilensteine  (Bonuspunkte-Stufen: Woche/Monat/Halbjahr/Jahr)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS meilensteine (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    benutzer_id     INT UNSIGNED NOT NULL,
    typ             ENUM('woche','monat','halbjahr','jahr') NOT NULL,
    streak_tage     INT NOT NULL,
    streak_start    DATE NOT NULL,
    punkte_erhalten INT NOT NULL,
    erreicht_am     DATE NOT NULL,
    UNIQUE KEY uq_streak_typ (benutzer_id, typ, streak_start),
    CONSTRAINT fk_meilensteine_benutzer FOREIGN KEY (benutzer_id)
        REFERENCES benutzer(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabelle: belohnungen  (selbst eingetragene Belohnung zu einem Meilenstein)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS belohnungen (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    benutzer_id     INT UNSIGNED NOT NULL,
    meilenstein_id  INT UNSIGNED NULL,
    text            VARCHAR(255) NOT NULL,
    erstellt_am     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_belohnungen_benutzer FOREIGN KEY (benutzer_id)
        REFERENCES benutzer(id) ON DELETE CASCADE,
    CONSTRAINT fk_belohnungen_meilenstein FOREIGN KEY (meilenstein_id)
        REFERENCES meilensteine(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
