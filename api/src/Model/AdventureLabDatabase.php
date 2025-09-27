<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\Util\Base31Util;
use PDO;
use RuntimeException;

use function array_keys;
use function array_map;
use function file_exists;
use function implode;
use function sprintf;
use function str_replace;

class AdventureLabDatabase
{
    private const string DEEPLINK_ULR_TO_REMOVE = 'https://labs.geocaching.com/goto/';
    private const int ID_OFFSET_IN_GPX = 9000000;

    private PDO $connection;

    public function __construct(private readonly string $dataDir)
    {
    }

    /**
     * @param array<mixed> $adventureLab
     *
     * @return array<mixed>
     */
    public function updateAdventureLabData(array $adventureLab): array
    {
        $labUuid = $adventureLab['Id'];

        $row = $this->findLabByUuid($labUuid);
        $labUrl = $this->extractLabUrl($adventureLab['DeepLink']);

        if ($row) {
            $this->updateLabIfNeeded($row, [
                'url' => $labUrl,
                'title' => $adventureLab['Title'],
                'lat' => $adventureLab['Location']['Latitude'],
                'lon' => $adventureLab['Location']['Longitude'],
            ]);
        } else {
            $row = $this->insertNewLab([
                'uuid' => $labUuid,
                'url' => $labUrl,
                'title' => $adventureLab['Title'],
                'lat' => $adventureLab['Location']['Latitude'],
                'lon' => $adventureLab['Location']['Longitude'],
            ]);
        }

        $adventureLab['LAB2GPX_ID'] = self::ID_OFFSET_IN_GPX + (int) $row['id'];
        $adventureLab['LAB2GPX_CODE'] = $row['code'];

        foreach ($adventureLab['GeocacheSummaries'] as $index => $waypoint) {
            $waypointRow = $this->findLabByUuid($waypoint['Id']);

            if (! $waypointRow) {
                $waypointRow = $this->insertNewLab([
                    'uuid' => $waypoint['Id'],
                    'parent' => $labUuid,
                    'title' => $waypoint['Title'],
                    'lat' => $waypoint['Location']['Latitude'],
                    'lon' => $waypoint['Location']['Longitude'],
                ]);
            }

            $adventureLab['GeocacheSummaries'][$index]['LAB2GPX_ID'] = self::ID_OFFSET_IN_GPX + (int) $waypointRow['id'];
            $adventureLab['GeocacheSummaries'][$index]['LAB2GPX_CODE'] = $waypointRow['code'];
        }

        return $adventureLab;
    }

    /** @return array<string, mixed>|null */
    public function findLabByUuid(string $uuid): array|null
    {
        $stmt = $this->getConnection()->prepare('SELECT * FROM adventure_labs WHERE uuid = :uuid');
        $stmt->execute(['uuid' => $uuid]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /** @return array<string, mixed>|null */
    public function findLabByCode(string $code): array|null
    {
        $stmt = $this->getConnection()->prepare('SELECT * FROM adventure_labs WHERE code = :code');
        $stmt->execute(['code' => $code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    private function extractLabUrl(string $deepLink): string
    {
        return str_replace(self::DEEPLINK_ULR_TO_REMOVE, '', $deepLink);
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, mixed> $data
     */
    private function updateLabIfNeeded(array &$row, array $data): void
    {
        $updateNeeded = false;

        foreach ($data as $field => $value) {
            if ($row[$field] !== null) {
                continue;
            }

            $row[$field] = $value;
            $updateNeeded = true;
        }

        if (! $updateNeeded) {
            return;
        }

        $stmt = $this->getConnection()->prepare('UPDATE adventure_labs SET url = :url, title = :title, lat = :lat, lon = :lon WHERE id = :id');
        $stmt->execute([
            'id' => $row['id'],
            'url' => $row['url'],
            'title' => $row['title'],
            'lat' => $row['lat'],
            'lon' => $row['lon'],
        ]);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function insertNewLab(array $data): array
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        $fields = array_keys($data);
        $placeholders = array_map(static fn ($field) => sprintf(':%s', $field), $fields);

        $sql = sprintf(
            'INSERT INTO adventure_labs (%s) VALUES (%s)',
            implode(', ', $fields),
            implode(', ', $placeholders),
        );

        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);

        $id = (int) $pdo->lastInsertId();
        $code = Base31Util::convertToBase31($id);

        $stmt = $pdo->prepare('UPDATE adventure_labs SET code = :code WHERE id = :id');
        $stmt->execute(['code' => $code, 'id' => $id]);

        $pdo->commit();

        return [
            'id' => $id,
            'code' => $code,
        ];
    }

    private function getConnection(): PDO
    {
        if (isset($this->connection)) {
            return $this->connection;
        }

        $dbPath = $this->dataDir . '/lab2gpx.sqlite';
        if (! file_exists($dbPath)) {
            throw new RuntimeException('Database not found!');
        }

        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection = $pdo;

        return $pdo;
    }
}
