<?php

namespace App\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\Type;

class PointType extends Type
{
    public const string POINT = 'point'; // Custom type name

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        // Pour SQLite, on stocke tout dans un champ "TEXT"
        if ('sqlite' === $platform->getName()) {
            return 'TEXT';
        }

        // Pour MySQL, on peut utiliser POINT, etc.
        return 'POINT';
    }

    /**
     * Convert a geometry string from the DB to a PHP array ['longitude' => x, 'latitude' => y].
     */
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

        $lon = (float) ($value['longitude'] ?? 0);
        $lat = (float) ($value['latitude'] ?? 0);

        if ('sqlite' === $platform->getName()) {
            // Just store "POINT(lon lat)" as TEXT
            return sprintf('POINT(%F %F)', $lon, $lat);
        }

        // For MySQL, produce an expression MySQL understands as geometry
        // e.g. ST_GeomFromText('POINT(lon lat)')
        return sprintf("ST_GeomFromText('POINT(%F %F)')", $lon, $lat);
    }

    public function convertToPHPValueSQL($sqlExpr, $platform): string
    {
        if ($platform instanceof MySqlPlatform) {
            // So the query uses ST_AsText(coordinates) AS coordinates
            // which yields "POINT(x y)" to parse in convertToPHPValue()
            return sprintf('ST_AsText(%s)', $sqlExpr);
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

    public function getName(): string
    {
        return self::POINT;
    }
}
