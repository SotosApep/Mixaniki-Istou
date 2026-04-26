-- ============================================================
-- seed.sql
-- Demo data for all 7 tables.
--
-- Passwords (bcrypt, cost 10):
--   admin@demo.cy  → Admin1234!
--   maria@demo.cy  → Maria1234!
--   nikos@demo.cy  → Nikos1234!
-- ============================================================

USE mixaniki_istou;

-- ------------------------------------------------------------
-- Users (1 admin, 2 candidates)
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
-- Specialties
-- ------------------------------------------------------------
INSERT INTO specialties (code, name) VALUES
('ΠΕ02',    'Φιλόλογοι'),
('ΠΕ04.01', 'Φυσικοί'),
('ΠΕ04.02', 'Χημικοί'),
('ΠΕ04.04', 'Βιολόγοι'),
('ΠΕ06',    'Αγγλικής Γλώσσας');

-- ------------------------------------------------------------
-- Regions (districts of Cyprus)
-- ------------------------------------------------------------
INSERT INTO regions (name) VALUES
('Λευκωσία'),
('Λεμεσός'),
('Λάρνακα'),
('Πάφος'),
('Αμμόχωστος');

-- ------------------------------------------------------------
-- Appointment Lists
-- specialty_id: 1=ΠΕ02, 2=ΠΕ04.01, 3=ΠΕ04.02
-- region_id:    1=Λευκωσία, 2=Λεμεσός, 3=Λάρνακα
-- ------------------------------------------------------------
INSERT INTO appointment_lists (specialty_id, region_id, list_year, list_period) VALUES
(1, 1, 2024, 'Α΄ Περίοδος 2024'),   -- id=1 ΠΕ02 Λευκωσία 2024
(1, 2, 2023, 'Β΄ Περίοδος 2023'),   -- id=2 ΠΕ02 Λεμεσός 2023
(2, 1, 2024, 'Α΄ Περίοδος 2024'),   -- id=3 ΠΕ04.01 Λευκωσία 2024
(2, 3, 2023, 'Α΄ Περίοδος 2023'),   -- id=4 ΠΕ04.01 Λάρνακα 2023
(3, 2, 2022, 'Β΄ Περίοδος 2022');   -- id=5 ΠΕ04.02 Λεμεσός 2022

-- ------------------------------------------------------------
-- Candidate Lists (many-to-many: users ↔ appointment_lists)
-- user_id: Maria=2, Nikos=3
-- ------------------------------------------------------------
INSERT INTO candidate_lists (user_id, list_id, rank_position, status) VALUES
(2, 1, 14, 'pending'),     -- Maria on list 1 (ΠΕ02 Λευκωσία 2024)
(2, 2,  9, 'appointed'),   -- Maria on list 2 (ΠΕ02 Λεμεσός 2023)
(3, 3, 22, 'pending'),     -- Nikos on list 3 (ΠΕ04.01 Λευκωσία 2024)
(3, 4, 18, 'rejected'),    -- Nikos on list 4 (ΠΕ04.01 Λάρνακα 2023)
(3, 5,  5, 'appointed');   -- Nikos on list 5 (ΠΕ04.02 Λεμεσός 2022)

-- ------------------------------------------------------------
-- Appointees (flat table used by existing UI pages)
-- ------------------------------------------------------------
INSERT INTO appointees (user_id, full_name, specialty, rank_position, list_year, list_period, status) VALUES
(2, 'Maria Παπαδοπούλου', 'ΠΕ02 - Φιλόλογοι',    14, 2024, 'Α΄ Περίοδος 2024', 'pending'),
(2, 'Maria Παπαδοπούλου', 'ΠΕ02 - Φιλόλογοι',     9, 2023, 'Β΄ Περίοδος 2023', 'appointed'),
(3, 'Νίκος Κωνσταντίνου', 'ΠΕ04.01 - Φυσικοί',   22, 2024, 'Α΄ Περίοδος 2024', 'pending'),
(3, 'Νίκος Κωνσταντίνου', 'ΠΕ04.01 - Φυσικοί',   18, 2023, 'Α΄ Περίοδος 2023', 'rejected'),
(3, 'Νίκος Κωνσταντίνου', 'ΠΕ04.02 - Χημικοί',    5, 2022, 'Β΄ Περίοδος 2022', 'appointed');

-- ------------------------------------------------------------
-- Audit Log (sample admin actions)
-- user_id 1 = admin
-- ------------------------------------------------------------
INSERT INTO audit_log (user_id, action, target_id, detail) VALUES
(1, 'CREATE_APPOINTEE', 1, 'Added Maria ΠΕ02 2024'),
(1, 'CREATE_APPOINTEE', 2, 'Added Maria ΠΕ02 2023'),
(1, 'UPDATE_STATUS',    3, 'Set Nikos ΠΕ04.01 2024 → pending'),
(1, 'CREATE_APPOINTEE', 4, 'Added Nikos ΠΕ04.01 2023'),
(1, 'CREATE_APPOINTEE', 5, 'Added Nikos ΠΕ04.02 2022');
