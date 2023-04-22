<?php

namespace App\Database;

use App\Core\Http\Exceptions\HttpBadRequestException;
use GeekLab\Conf\GLConf;
use PDO;

class DatabaseService
{
    private PDO $pdo;
    private GLConf $config;

    public function __construct(GLConf $config, PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->config = $config;
    }

    /**
     * Get databases listed on the host.
     *
     * @return array
     */
    public function getDatabases(): array
    {
        return array_map(
            static function ($row) {
                return $row['Database'];
            },
            $this->pdo->query('SHOW DATABASES')->fetchAll(PDO::FETCH_ASSOC),
        );
    }

    /**
     * Get an array of excluded tables.
     *
     * @param int $hostIdx
     *
     * @return array
     */
    public function getExcludedDatabases(int $hostIdx): array
    {
        $excludedTables = $this->config->get("servers.$hostIdx.excluded_databases");

        return is_array($excludedTables) ? $excludedTables : [];
    }

    /**
     * Get the tables listed in a DB.
     *
     * @param int    $hostIdx
     * @param string $database
     *
     * @return array
     * @throws HttpBadRequestException
     */
    public function getTables(int $hostIdx, string $database): array
    {
        $excludedDatabases = $this->getExcludedDatabases($hostIdx);
        if (in_array($database, $excludedDatabases, true)) {
            throw new HttpBadRequestException('Bad Request : 0x0001');
        }

        $dbs = $this->getDatabases();
        if (!in_array($database, $dbs, true)) {
            throw new HttpBadRequestException('Bad Request: 0x0002');
        }

        // Select our db.
        $this->pdo->query("USE `$database`")->execute();

        // Get the table info.
        $stmt = $this->pdo->prepare(
            "
            SELECT
              TABLE_NAME AS `table`,
              (DATA_LENGTH + INDEX_LENGTH) AS `size`
            FROM
              information_schema.TABLES
            WHERE
              TABLE_SCHEMA = :mysql
        ",
        );
        $stmt->bindParam(':mysql', $database);
        $stmt->execute();

        // Loop through the results and format properly.
        $ret = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ret[] = ['table' => $row['table'], 'size' => (int)$row['size']];
        }

        return $ret;
    }
}
