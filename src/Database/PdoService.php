<?php

namespace App\Database;

use \PDO;

class PdoService
{
    private ?PDO $pdo;

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
        if ($this->pdo) {
            return $this->pdo;
        }

        $this->pdo = new PDO(
            "mysql:host=$host;port=$port",
            $username,
            $password,
            [PDO::ATTR_PERSISTENT => false]
        );
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $this->pdo;
    }
}
