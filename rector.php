<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/doc',
        __DIR__ . '/source',
        __DIR__ . '/specs',
        __DIR__ . '/support',
    ]);

    $rectorConfig->rules([
            InlineConstructorDefaultToPropertyRector::class,
            NullToStrictStringFuncCallArgRector::class,
    ]);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_82
    ]);
};
