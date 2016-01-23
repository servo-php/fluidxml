<?php

if (! isset($argv[1])) {
        \printf(" Usage: %s <coverage_data>.php\n", \basename($argv[0]));
        exit(1);
}

$travis_job_id = '';

if (isset($argv[2])) {
        $travis_job_id = $argv[2];
}

$data_file = $argv[1];

$DS = \DIRECTORY_SEPARATOR;
$root_dir = \realpath(__DIR__ . "{$DS}..");

require_once "{$root_dir}{$DS}sandbox{$DS}composer{$DS}autoload.php";

$data = require "$data_file";

$data = $data->getData();

$payload = [ 'service_name'   => 'travis-ci',
             'service_job_id' => $travis_job_id,
             'repo_token'     => 'c1DEnhEDEsdeHDUepRI24RibVJ6yDw2kN',
             'source_files'   => [ ] ];

foreach ($data as $file => $c) {
        $splfile = new \SplFileObject($file, 'r');
        $splfile->seek(PHP_INT_MAX);
        $lines = $splfile->key();

        $coverage = [];
        for ($i = 0; $i < $lines; ++$i) {
                // PHP Code Coverage starts from 1,
                // Coveralls from 0.
                $l = $i + 1;

                $val = 1;

                if (! isset($c[$l])) {
                        $val = null;
                } else if (\is_array($c[$l]) && empty($c[$l])) {
                        $val = 0;
                }

                $coverage[$i] = $val;
        }

        $file = [ 'name'          => \substr($file, \strlen($root_dir) + 1),
                  'source_digest' => \md5_file($file),
                  'coverage'      => $coverage ];

        $payload['source_files'][] = $file;
}

$data = \json_encode($payload);

echo $data;
