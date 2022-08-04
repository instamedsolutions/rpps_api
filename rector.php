<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use Rector\Nette\Set\NetteSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SensiolabsSetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\TypeDeclaration\Rector\Property\AddPropertyTypeDeclarationRector;
use PHPStan\Type\StringType;
use Rector\TypeDeclaration\ValueObject\AddPropertyTypeDeclaration;


return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src'
    ]);

    $rectorConfig->importNames();

    // register a single rule
    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);
    $rectorConfig->ruleWithConfiguration(
        AddPropertyTypeDeclarationRector::class,
        [new AddPropertyTypeDeclaration('ParentClass', 'name', new StringType())]
    );
    $rectorConfig->rule(RemoveUselessParamTagRector::class);
    $rectorConfig->rule(RemoveAlwaysElseRector::class);

    // define sets of rules
    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
        NetteSetList::ANNOTATIONS_TO_ATTRIBUTES,
        SensiolabsSetList::FRAMEWORK_EXTRA_50,
        LevelSetList::UP_TO_PHP_81
    ]);
};
