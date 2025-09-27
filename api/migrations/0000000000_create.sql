CREATE TABLE IF NOT EXISTS lab_codes
(
    id       INTEGER PRIMARY KEY AUTOINCREMENT,
    uuid     TEXT UNIQUE NOT NULL,
    lab_code TEXT
);

CREATE INDEX IF NOT EXISTS idx_lab_codes_uuid ON lab_codes(uuid);

-- initial id, just because I don't like the short codes around 30k is where base31 becomes 4 digits
INSERT INTO lab_codes (id, uuid) VALUES (30000, 'delete_me');
DELETE FROM lab_codes WHERE id = 30000;
