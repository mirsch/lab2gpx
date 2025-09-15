<?php

namespace App;

class LabCode
{
    public function __construct(private readonly string $dataDir)
    {
    }

    /**
     * convert a UUID to a unique lab code using database lookup
     * @return string  // Lab code in base31 format without prefix and suffix
     */
    public function uuid2LabCode(string $uuid): string
    {
        $pdo = $this->getDatabase();
        $stmt = $pdo->prepare("SELECT lab_code FROM lab_codes WHERE uuid = :uuid");
        $stmt->execute(['uuid' => $uuid]);
        $labCode = $stmt->fetchColumn();

        if ($labCode) {
            return $labCode;
        }

        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO lab_codes (uuid) VALUES (:uuid)");
        $stmt->execute(['uuid' => $uuid]);
        $id = $pdo->lastInsertId();
        $labCode = $this->convertToBase31((int)$id);

        $stmt = $pdo->prepare("UPDATE lab_codes SET lab_code = :lab_code WHERE id = :id");
        $stmt->execute(['lab_code' => $labCode, 'id' => $id]);
        $pdo->commit();

        return $labCode;
    }

    public function convertToBase31(int $id): string
    {
        $base31 = '';
        $alphabet = '0123456789ABCDEFGHJKMNPQRTVWXYZ'; // Base 31 alphabet without 'ILOSU' (like GC Code)
        while ($id > 0) {
            $base31 = $alphabet[$id % 31] . $base31;
            $id = (int)($id / 31);
        }
        return $base31;
    }

    private function getDatabase(): \PDO
    {
        $dbPath = $this->dataDir . '/labcodes.sqlite';
        $isNewDatabase = false;
        if (!file_exists($dbPath)) {
            $isNewDatabase = true;
        }

        $pdo = new \PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        if ($isNewDatabase) {
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS lab_codes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                uuid TEXT UNIQUE NOT NULL,
                lab_code TEXT
            )");
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_lab_codes_uuid ON lab_codes(uuid)");
            // initial id, just because I don't like the short codes around 30k is where base31 becomes 4 digits
            $pdo->exec("INSERT INTO lab_codes (id, uuid) VALUES (30000, 'delete_me')");
            $pdo->exec('DELETE FROM lab_codes WHERE id = 30000');
        }

        return $pdo;
    }
}
