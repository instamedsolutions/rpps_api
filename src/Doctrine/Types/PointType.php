<?php

namespace App\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\Type;

class PointType extends Type
{
    public const POINT = 'point'; // Custom type name

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'POINT';
    }

    public function canRequireSQLConversion(): bool
    {
        return true;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?array
    {
        if (null === $value) {
            return null;
        }

        // Convert the database format (POINT) into a PHP array
        $point = sscanf($value, 'POINT(%f %f)');

        return [
            'longitude' => $point[0] ?? null,
            'latitude' => $point[1] ?? null,
        ];
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return [];
        }

        // Convert the PHP array to MySQL's POINT format
        return sprintf('POINT(%s %s)', $value['longitude'] ?? 0, $value['latitude'] ?? 0);
    }

    public function getName(): string
    {
        return self::POINT;
    }

    public function convertToPHPValueSQL($sqlExpr, $platform): string
    {
        if ($platform instanceof MySqlPlatform) {
            return sprintf('ST_AsBinary(%s)', $sqlExpr);
        }

        return $sqlExpr;
    }

    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform): string
    {
        if ($platform instanceof MySqlPlatform) {
            return sprintf('ST_GeomFromText(%s)', $sqlExpr);
        }

        return $sqlExpr;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
