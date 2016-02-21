<?php

$v = isset($argv[1]) ? $argv[1] : __DIR__ . '/../source';

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
                $xml->query('/*')->add('el');
        }
});

$machine->add('add([...])', function($data) use ($fluidxml) {
        $xml = $fluidxml();
        for ($i = 0; $i < 10; ++$i) {
                $xml->add([ 'el' => [ 'el'  => 'el' ] ]);
        }
});

$xml = $fluidxml(['doc' => [ 'body' => [ 'div' ] ] ]);

$machine->add('query(xpath)', function($data) use ($xml) {
        $xml->query('//body/div');
});

$machine->add('query(css)', function($data) use ($xml) {
        $xml->query('body > div');
});


////////////////////////////////////////////////////////////////////////////////

$machine->run_and_show();
