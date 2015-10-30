<?php

$ds = DIRECTORY_SEPARATOR;
$source_dir = __DIR__ . "{$ds}..{$ds}source";
set_include_path($source_dir . PATH_SEPARATOR . get_include_path());

function __($actual, $expected)
{
        $v = [ 'actual'   => \var_export($actual, true),
               'expected' => \var_export($expected, true) ];

        $sep = ' ';
        $msg_l = \strlen($v['actual'] . $v['expected']);

        if ($msg_l > 60) {
                $sep = "\n";
        }

        return "expected " . $v['expected'] . ",$sep"
               . "given " . $v['actual'] . ".";
}
