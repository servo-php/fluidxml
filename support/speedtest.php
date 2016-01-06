<?php

$v = isset($argv[1]) ? $argv[1] : '1.0';

require_once 'Codevelox.php';
require_once "$v/FluidXml.php";

$machine = new Codevelox();

$fluidxml = 'fluidxml';
if (! function_exists($fluidxml)) {
        $fluidxml = '\FluidXml\fluidxml';
}

////////////////////////////////////////////////////////////////////////////////

$machine->add('add()', function($data) use ($fluidxml) {
        $xml = $fluidxml()->add('pippo');
});

$machine->add('query+add()', function($data) use ($fluidxml) {
        $fluidxml()->add('pippo')->query('//pippo')->add('pluto');
});

////////////////////////////////////////////////////////////////////////////////

$machine->run_and_show();
