<?php

use Evenement\EventEmitterInterface;
use Peridot\Reporter\CodeCoverage\AbstractCodeCoverageReporter;
use Peridot\Reporter\CodeCoverageReporters;
// use Peridot\Plugin\Watcher\WatcherPlugin;

return function (EventEmitterInterface $eventEmitter) {
        $eventEmitter->on('error', function ($errn, $msg, $file, $line) {
                printf("$file:$line\n");
                printf("    $msg\n");
        });

        // $eventEmitter->on('peridot.start', function (\Peridot\Console\Environment $environment) {
        //         $environment->getDefinition()->getArgument('path')->setDefault(__DIR__ . '/../specs');
        // });

        (new CodeCoverageReporters($eventEmitter))->register();
        $eventEmitter->on('code-coverage.start', function (AbstractCodeCoverageReporter $reporter) {
                $reporter->addDirectoryToWhitelist(__DIR__ . '/../source');
                // $reporter->addFilesToWhitelist([__DIR__ . '/../source/FluidXml.php']);
                // $reporter->addDirectoryToWhitelist(__DIR__ . '/../source')
                //          ->addFilesToBlacklist([__DIR__ . '/../source/FluidXml.php56.php',
                //                                 __DIR__ . '/../source/FluidXml.php70.php']);
        });

        // $watcher = new WatcherPlugin($eventEmitter);
        // $watcher->track(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'source');
};
