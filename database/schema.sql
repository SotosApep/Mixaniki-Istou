-- ============================================================
-- schema.sql
-- Εφαρμογή Παρακολούθησης Πινάκων Διοριστέων
-- 7 tables: users, specialties, regions, appointment_lists,
--           candidate_lists (many-to-many), appointees, audit_log
-- ============================================================

CREATE DATABASE IF NOT EXISTS mixaniki_istou
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE mixaniki_istou;

-- ------------------------------------------------------------
-- Table: users
-- Stores all registered users (admins and candidates).
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    username      VARCHAR(60)     NOT NULL,
    email         VARCHAR(191)    NOT NULL UNIQUE,
    password_hash VARCHAR(255)    NOT NULL,
    role          ENUM('admin', 'candidate') NOT NULL DEFAULT 'candidate',
    created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: specialties
-- Lookup table for teacher specialties (e.g. ΠΕ02 - Φιλόλογοι).
-- One-to-many: one specialty → many appointment_lists
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS specialties (
    id         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    code       VARCHAR(20)   NOT NULL UNIQUE,   -- e.g. ΠΕ02
    name       VARCHAR(120)  NOT NULL,           -- e.g. Φιλόλογοι
    created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_specialties_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: regions
-- Lookup table for districts of Cyprus.
-- One-to-many: one region → many appointment_lists
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS regions (
    id         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name       VARCHAR(80)   NOT NULL UNIQUE,   -- e.g. Λευκωσία
    created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: appointment_lists
-- Represents an official appointment list for a specific
-- specialty, region, year, and period.
-- One-to-many: one list → many candidate_lists entries
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS appointment_lists (
    id           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    specialty_id INT UNSIGNED  NOT NULL,   -- FK → specialties.id
    region_id    INT UNSIGNED  NOT NULL,   -- FK → regions.id
    list_year    YEAR          NOT NULL,
    list_period  VARCHAR(60)   NOT NULL,   -- e.g. "Α΄ Περίοδος 2024"
    created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_al_year (list_year),

    CONSTRAINT fk_al_specialty
        FOREIGN KEY (specialty_id) REFERENCES specialties (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_al_region
        FOREIGN KEY (region_id) REFERENCES regions (id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: candidate_lists  (MANY-TO-MANY junction)
-- Links candidates (users) to appointment_lists.
-- A candidate can appear on many lists;
-- a list can have many candidates.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS candidate_lists (
    id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_id       INT UNSIGNED  NOT NULL,   -- FK → users.id
    list_id       INT UNSIGNED  NOT NULL,   -- FK → appointment_lists.id
    rank_position INT UNSIGNED  NOT NULL,
    status        ENUM('pending', 'appointed', 'rejected') NOT NULL DEFAULT 'pending',
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_candidate_list (user_id, list_id),   -- one entry per candidate per list
    INDEX idx_cl_status (status),

    CONSTRAINT fk_cl_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT fk_cl_list
        FOREIGN KEY (list_id) REFERENCES appointment_lists (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: appointees
-- Legacy flat table kept for the existing UI pages.
-- Stores appointment-list entries linked to candidate users.
-- One-to-many: one user → many appointee records
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS appointees (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id       INT UNSIGNED    NOT NULL,
    full_name     VARCHAR(120)    NOT NULL,
    specialty     VARCHAR(120)    NOT NULL,
    rank_position INT UNSIGNED    NOT NULL,
    list_year     YEAR            NOT NULL,
    list_period   VARCHAR(60)     NOT NULL,
    status        ENUM('pending', 'appointed', 'rejected') NOT NULL DEFAULT 'pending',
    created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_appointees_status (status),
    INDEX idx_appointees_year (list_year),

    CONSTRAINT fk_appointees_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: audit_log
-- Tracks admin actions for accountability.
-- One-to-many: one user → many log entries
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS audit_log (
    id         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_id    INT UNSIGNED  NOT NULL,          -- FK → users.id (who performed the action)
    action     VARCHAR(100)  NOT NULL,          -- e.g. 'DELETE_USER', 'UPDATE_STATUS'
    target_id  INT UNSIGNED  DEFAULT NULL,      -- ID of the affected record (optional)
    detail     VARCHAR(255)  DEFAULT NULL,      -- extra context
    created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_log_user (user_id),
    INDEX idx_log_created (created_at),

    CONSTRAINT fk_log_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
