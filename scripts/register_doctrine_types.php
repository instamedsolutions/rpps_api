<?php

use App\Doctrine\Types\PointType;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;

require __DIR__ . '/../vendor/autoload.php';

// 1) On enregistre le type custom dans Doctrine
if (!Type::hasType(PointType::POINT)) {
    Type::addType(PointType::POINT, PointType::class);
    echo "Custom type 'point' registered successfully.\n";
}

// 2) On ouvre une connexion SQLite pour récupérer la plateforme
$dbConnection = DriverManager::getConnection([
    'driver' => 'pdo_sqlite',
    'path' => '/var/test_db.sqlite',
]);

$platform = $dbConnection->getDatabasePlatform();

// 3) Si c’est SQLite, on indique à Doctrine comment mapper le type "point"
if ('sqlite' === $platform->getName()) {
    // Permet d’éviter l’erreur “comment hint” de Doctrine
    $platform->markDoctrineTypeCommented(Type::getType(PointType::POINT));
    // On mappe l’éventuel type SQL 'point' vers du 'string' (ou 'text')
    $platform->registerDoctrineTypeMapping('point', 'string');
    echo "Custom type 'point' mapped to 'string' for SQLite.\n";
}
