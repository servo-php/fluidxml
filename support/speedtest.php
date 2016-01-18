<?php

$v = isset($argv[1]) ? $argv[1] : '1.0';

require_once 'Codevelox.php';
require_once "$v/FluidXml.php";

$machine = new Codevelox(1000);

$fluidxml = 'fluidxml';
if (! function_exists($fluidxml)) {
        $fluidxml = '\FluidXml\fluidxml';
}

////////////////////////////////////////////////////////////////////////////////

$machine->add('fluidxml()', function($data) use ($fluidxml) {
        $fluidxml();
});

$machine->add('add()', function($data) use ($fluidxml) {
        $xml = $fluidxml();
        for ($i = 0; $i < 10; ++$i) {
                $xml->add('el');
        }
});

$machine->add('add(true)->add()', function($data) use ($fluidxml) {
        $xml = $fluidxml();
        for ($i = 0; $i < 10; ++$i) {
                $xml->add('el', true)->add('el');
        }
});

$machine->add('query()+add()', function($data) use ($fluidxml) {
        $xml = $fluidxml();
        for ($i = 0; $i < 10; ++$i) {
                $xml->query('//el')->add('el');
        }
});

$machine->add('add([...])', function($data) use ($fluidxml) {
        $xml = $fluidxml();
        for ($i = 0; $i < 10; ++$i) {
                $xml->add([ 'el' => [ 'el'  => 'el' ] ]);
        }
});

////////////////////////////////////////////////////////////////////////////////

$machine->run_and_show();
