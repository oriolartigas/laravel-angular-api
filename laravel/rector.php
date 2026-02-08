<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use SavinMikhail\AddNamedArgumentsRector\AddNamedArgumentsRector;

return static function (RectorConfig $rectorConfig): void {
    // Main directories
    $rectorConfig->paths([
        // __DIR__ . '/app',
        // __DIR__ . '/routes',
        // __DIR__ . '/database',
        __DIR__.'/database/migrations/',
    ]);

    // Skip changes in these directories
    $rectorConfig->skip([
        __DIR__.'/vendor',
        __DIR__.'/storage',
        __DIR__.'/bootstrap',
    ]);

    $rectorConfig->rule(AddNamedArgumentsRector::class);
};
