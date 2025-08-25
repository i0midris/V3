<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use RectorLaravel\Set\LaravelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__.'/app',
        __DIR__.'/routes',
        __DIR__.'/resources',
        __DIR__.'/config',
        __DIR__.'/Modules',
    ]);

    $rectorConfig->skip([
        __DIR__.'/vendor',
        __DIR__.'/storage',
        __DIR__.'/public',
        __DIR__.'/database',
        __DIR__.'/tests',
        __DIR__.'/packages',
        __DIR__.'/nc_assets',
        __DIR__.'/bootstrap/cache',
    ]);

    // FULL POWER REFACTORING
    $rectorConfig->sets([
        LaravelSetList::LARAVEL_110,
        LevelSetList::UP_TO_PHP_83,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,
        SetList::CODING_STYLE,
        SetList::INSTANCEOF,
        SetList::PRIVATIZATION,
        SetList::PHP_83,
    ]);
    

    $rectorConfig->importNames();
    $rectorConfig->disableParallel(); // safer for memory
    $rectorConfig->cacheDirectory(__DIR__.'/var/cache/rector');
};
