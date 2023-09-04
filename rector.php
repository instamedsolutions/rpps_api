<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\CodeQuality\Rector\ClassMethod\ReturnTypeFromStrictScalarReturnExprRector;
use Rector\Comments\CommentRemover;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\Doctrine\Rector\Property\TypedPropertyFromColumnTypeRector;
use Rector\Doctrine\Rector\Property\TypedPropertyFromToManyRelationTypeRector;
use Rector\Doctrine\Rector\Property\TypedPropertyFromToOneRelationTypeRector;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SensiolabsSetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictBoolReturnExprRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictConstantReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeCallRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector;
use Rector\TypeDeclaration\Rector\Property\AddPropertyTypeDeclarationRector;
use PHPStan\Type\StringType;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictGetterMethodReturnTypeRector;
use Rector\TypeDeclaration\ValueObject\AddPropertyTypeDeclaration;


return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src'
    ]);

    $rectorConfig->phpstanConfig(__DIR__ . '/phpstan.neon');

    $services = $rectorConfig->services();
    $services->set(TypedPropertyFromStrictConstructorRector::class);
    $services->set(TypedPropertyFromStrictGetterMethodReturnTypeRector::class);
    $services->set(TypedPropertyFromToManyRelationTypeRector::class);
    $services->set(TypedPropertyFromToOneRelationTypeRector::class);
    $services->set(TypedPropertyFromColumnTypeRector::class);
    $services->set(ReturnTypeFromStrictScalarReturnExprRector::class);
    $services->set(ReturnTypeFromStrictNativeCallRector::class);
    $services->set(ReturnTypeFromStrictBoolReturnExprRector::class);
    $services->set(ReturnTypeFromStrictConstantReturnRector::class);
    $services->set(ReturnTypeFromStrictTypedPropertyRector::class);
    $services->set(ReturnTypeFromReturnNewRector::class);

    $rectorConfig->importNames();

    $rectorConfig->rules([
        ReturnTypeFromStrictNativeCallRector::class,
        ReturnTypeFromStrictScalarReturnExprRector::class,
        InlineConstructorDefaultToPropertyRector::class,
        RemoveUselessParamTagRector::class,
        RemoveAlwaysElseRector::class,
    ]);

    // register a single rule
    $rectorConfig->ruleWithConfiguration(
        AddPropertyTypeDeclarationRector::class,
        [new AddPropertyTypeDeclaration('ParentClass', 'name', new StringType())]
    );

    // define sets of rules
    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
        SensiolabsSetList::FRAMEWORK_EXTRA_50,
        LevelSetList::UP_TO_PHP_81,
    ]);
};
