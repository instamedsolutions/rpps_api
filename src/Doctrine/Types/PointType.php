<?php

namespace App\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Types\Type;

class PointType extends Type
{
    public const string POINT = 'point'; // Custom type name

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        // If SQLite, store as TEXT. If MySQL, store as POINT.
        return $platform instanceof SqlitePlatform ? 'TEXT' : 'POINT';
    }

    /**
     * Convert a geometry string from the DB to a PHP array ['longitude' => x, 'latitude' => y].
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?array
    {
        if (null === $value || '' === $value) {
            return null;
        }

        // Convert the database format (POINT) into a PHP array
        $point = sscanf($value, 'POINT(%f %f)');

        return [
            'longitude' => $point[0] ?? null,
            'latitude' => $point[1] ?? null,
        ];
    }

    /**
     * Convert a PHP array ['longitude' => x, 'latitude' => y] into a WKT string "POINT(x y)".
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (is_string($value)) {
            return $value;
        }

        if (null === $value) {
            return [];
        }

        $lon = (float) ($value['longitude'] ?? 0);
        $lat = (float) ($value['latitude'] ?? 0);

        if ($platform instanceof SqlitePlatform) {
            // Just store "POINT(lon lat)" as TEXT
            return sprintf('POINT(%F %F)', $lon, $lat);
        }

        // For MySQL, produce an expression MySQL understands as geometry
        // e.g. ST_GeomFromText('POINT(lon lat)')
        return sprintf("ST_GeomFromText('POINT(%F %F)',4326)", $lon, $lat);
    }

    public function convertToPHPValueSQL($sqlExpr, $platform): string
    {
        if ($platform instanceof MySQLPlatform) {
            // So the query uses ST_AsText(coordinates) AS coordinates
            // which yields "POINT(x y)" to parse in convertToPHPValue()
            return sprintf('ST_AsText(%s)', $sqlExpr);
        }

        return $sqlExpr;
    }

    /**
     * Tells Doctrine how to place our parameter into the final SQL for MySQL so
     * it becomes ST_GeomFromText(?), not a quoted string literal.
     */
    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform): string
    {
        if ($platform instanceof MySQLPlatform) {
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
