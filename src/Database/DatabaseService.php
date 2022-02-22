<?php

namespace App\Database;

use PDO;

class DatabaseService
{
    /**
     * @param string $host
     * @param string $username
     * @param string $password
     * @param int    $port
     *
     * @return PDO
     */
    public function createPDO(string $host, string $username, string $password, int $port = 3306): PDO
    {
        $pdo = new PDO(
            "mysql:host=$host;port=$port",
            $username,
            $password,
            [PDO::ATTR_PERSISTENT => false]
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }
}
