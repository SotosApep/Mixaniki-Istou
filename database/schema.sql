-- ============================================================
-- schema.sql
-- Εφαρμογή Παρακολούθησης Πινάκων Διοριστέων
-- Creates the database tables for the application.
-- ============================================================

-- Use (or create) the application database.
-- Adjust the database name to match your environment.
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
-- Table: appointees
-- Stores appointment-list entries linked to candidate users.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS appointees (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id       INT UNSIGNED    NOT NULL,           -- FK → users.id
    full_name     VARCHAR(120)    NOT NULL,
    specialty     VARCHAR(120)    NOT NULL,
    rank_position INT UNSIGNED    NOT NULL,
    list_year     YEAR            NOT NULL,
    list_period   VARCHAR(60)     NOT NULL,           -- e.g. "Α΄ Περίοδος 2024"
    status        ENUM('pending', 'appointed', 'rejected') NOT NULL DEFAULT 'pending',
    created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    -- Foreign key: every appointee record must belong to a valid user.
    CONSTRAINT fk_appointees_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
