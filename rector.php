<?php

declare(strict_types=1);

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/Admin',
        __DIR__ . '/Controller',
        __DIR__ . '/DependencyInjection',
        __DIR__ . '/Entity',
        __DIR__ . '/EventSubscriber',
        __DIR__ . '/Events',
        __DIR__ . '/Exception',
        __DIR__ . '/Handler',
        __DIR__ . '/PageTree',
        __DIR__ . '/Serializer',
        __DIR__ . '/TaskHandler',
        __DIR__ . '/Tasks',
        __DIR__ . '/Tests',
    ])
    // uncomment to reach your current PHP version
    ->withPhpSets()
    ->withTypeCoverageLevel(\PHP_INT_MAX)
;
