<?php

namespace App;

class LabCode
{
    protected string $dataDir;

    /**
     * LabCode constructor.
     * @param string $dataDir
     */
    public function __construct(string $dataDir)
    {
        $this->dataDir = $dataDir;
    }

    /**
     * convert a UUID to a unique lab code using database lookup
     *
     * @param string $uuid
     * @return string  // Lab code in base31 format without prefix and suffix
     */
    public function uuid2LabCode(string $uuid): string
    {
        // Search in Database for uuid and return lab code, if found.
        $pdo = $this->getDatabase();
        $stmt = $pdo->prepare("SELECT lab_code FROM lab_codes WHERE uuid = :uuid");
        $stmt->execute(['uuid' => $uuid]);
        $labCode = $stmt->fetchColumn();

        if ($labCode) {
            return $labCode;
        }

        // If not found insert uuid to database and return DB id and convert it using base31 to lab code
        $stmt = $pdo->prepare("INSERT INTO lab_codes (uuid) VALUES (:uuid)");
        $stmt->execute(['uuid' => $uuid]);
        $id = $pdo->lastInsertId();
        $labCode = self::convertToBase31((int)$id);

        // Update the database with the new lab code
        $stmt = $pdo->prepare("UPDATE lab_codes SET lab_code = :lab_code WHERE id = :id");
        $stmt->execute(['lab_code' => $labCode, 'id' => $id]);

        return $labCode;
    }

    private static function convertToBase31(int $id): string
    {
        $base31 = '';
        $alphabet = '0123456789ABCDEFGHJKMNPQRTVWXYZ'; // Base 31 alphabet without 'ILOSU' (like GC Code)
        while ($id > 0) {
            $base31 = $alphabet[$id % 31] . $base31;
            $id = (int)($id / 31);
        }
        return $base31;
    }

    /**
     * Returns a persistent SQLite database connection.
     * Ensures the required table exists.
     *
     * @return \PDO
     */
    private function getDatabase()
    {
        // Path to the persistent SQLite database file
        $dbPath = $this->dataDir . '/labcodes.sqlite';

        // Create or open the SQLite database
        $pdo = new \PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // Create the table if it does not exist
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS lab_codes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                uuid TEXT UNIQUE NOT NULL,
                lab_code TEXT
            )"
        );
        // Create index after table creation
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_lab_codes_uuid ON lab_codes(uuid)");

        return $pdo;
    }
}
