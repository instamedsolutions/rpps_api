<?php

namespace App\Doctrine;

use App\Doctrine\Types\PointType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Statement as BaseStatement;

class Statement extends BaseStatement
{
    public ?Connection $connexion = null;

    public function bindValue($param, $value, $type = ParameterType::STRING)
    {
        if ($this->connexion && PointType::POINT === $type) {
            $lat = $value['latitude'] ?? 0;
            $lng = $value['longitude'] ?? 0;
            $value = $this->connexion->fetchOne("SELECT ST_GeomFromText('POINT($lat $lng)',4326);");
        }

        return parent::bindValue($param, $value, $type);
    }
}
