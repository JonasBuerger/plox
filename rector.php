<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;
use Rector\EarlyReturn\Rector\StmtsAwareInterface\ReturnEarlyIfVariableRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitSelfCallRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitThisCallRector;
use Rector\PHPUnit\PHPUnit120\Rector\Class_\AllowMockObjectsForDataProviderRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpSets()
    ->withSkip([
        PreferPHPUnitThisCallRector::class,
        RemoveUnusedPrivateMethodRector::class,
        AllowMockObjectsForDataProviderRector::class,
        ReturnEarlyIfVariableRector::class,
        RemoveEmptyClassMethodRector::class => [
            __DIR__ . '/tests',
        ],
    ])
    ->withRules([
        PreferPHPUnitSelfCallRector::class,
    ])
    ->withImportNames()
    ->withComposerBased(
        phpunit: true,
    )
    ->withAttributesSets()
    ->withRootFiles()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        typeDeclarationDocblocks: true,
        privatization: true,
        phpunitCodeQuality: true,
    );
