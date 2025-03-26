<?php

namespace App\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Throwable;

class PointWrapper extends Connection
{
    public function prepare($sql)
    {
        try {
            $stmt = new Statement($sql, $this);
            if ($this->getDriver()->getDatabasePlatform() instanceof MySqlPlatform) {
                $stmt->connexion = $this;
            }
        } catch (Throwable $e) {
            $this->handleExceptionDuringQuery($e, $sql);
        }

        $stmt->setFetchMode($this->defaultFetchMode);

        return $stmt;
    }

    public function convertToDatabaseValue($value, $type)
    {
        dump($value, $type);
        exit;
    }
}
