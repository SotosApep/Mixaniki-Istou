-- ============================================================
-- seed.sql
-- Εφαρμογή Παρακολούθησης Πινάκων Διοριστέων
-- Inserts demo data: 3 users (1 admin, 2 candidates)
-- and 5 appointee records.
--
-- Passwords (bcrypt, cost 10):
--   admin@demo.cy   → Admin1234!
--   maria@demo.cy   → Maria1234!
--   nikos@demo.cy   → Nikos1234!
-- ============================================================

USE mixaniki_istou;

-- ------------------------------------------------------------
-- Demo users
-- password_hash values were generated with:
--   password_hash('...', PASSWORD_DEFAULT)
-- ------------------------------------------------------------
INSERT INTO users (username, email, password_hash, role) VALUES
(
    'admin',
    'admin@demo.cy',
    '$2y$10$hTH95G3y9RC6QzZ9tXTf4u1uqmRhzdV6p0nHI2W7Udg4c.08.Fu52',
    'admin'
),
(
    'Maria Παπαδοπούλου',
    'maria@demo.cy',
    '$2y$10$83emHEERNAZwoWDpnohS6.Q06fcCPxZ6qg2ZPwi..I27GPpmUmi46',
    'candidate'
),
(
    'Νίκος Κωνσταντίνου',
    'nikos@demo.cy',
    '$2y$10$tHRy594qtxtNIVn0KgwF2eqLSE5tF2zUu5RstqkPvr6HSCJLewmxC',
    'candidate'
);

-- ------------------------------------------------------------
-- Demo appointee records (5 total, linked to the 2 candidates)
-- Candidate IDs: Maria = 2, Nikos = 3
-- ------------------------------------------------------------
INSERT INTO appointees (user_id, full_name, specialty, rank_position, list_year, list_period, status) VALUES
(
    2,
    'Maria Παπαδοπούλου',
    'ΠΕ02 - Φιλόλογοι',
    14,
    2024,
    'Α΄ Περίοδος 2024',
    'pending'
),
(
    2,
    'Maria Παπαδοπούλου',
    'ΠΕ02 - Φιλόλογοι',
    9,
    2023,
    'Β΄ Περίοδος 2023',
    'appointed'
),
(
    3,
    'Νίκος Κωνσταντίνου',
    'ΠΕ04.01 - Φυσικοί',
    22,
    2024,
    'Α΄ Περίοδος 2024',
    'pending'
),
(
    3,
    'Νίκος Κωνσταντίνου',
    'ΠΕ04.01 - Φυσικοί',
    18,
    2023,
    'Α΄ Περίοδος 2023',
    'rejected'
),
(
    3,
    'Νίκος Κωνσταντίνου',
    'ΠΕ04.02 - Χημικοί',
    5,
    2022,
    'Β΄ Περίοδος 2022',
    'appointed'
);
