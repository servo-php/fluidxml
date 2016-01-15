<?php

$v = isset($argv[1]) ? $argv[1] : '1.0';

require_once 'Codevelox.php';
require_once "$v/FluidXml.php";

$machine = new Codevelox(10000);

$fluidxml = 'fluidxml';
if (! function_exists($fluidxml)) {
        $fluidxml = '\FluidXml\fluidxml';
}

////////////////////////////////////////////////////////////////////////////////

$machine->add('fluidxml()', function($data) use ($fluidxml) {
        $fluidxml();
});

$machine->add('add()', function($data) use ($fluidxml) {
        $fluidxml()->add('el');
});

$machine->add('add(true)->add()', function($data) use ($fluidxml) {
        $fluidxml()->add('el', true)->add('el');
});

$machine->add('add()+query()+add()', function($data) use ($fluidxml) {
        $fluidxml()->add('el')->query('//el')->add('el');
});

$machine->add('add([...])->add([...])', function($data) use ($fluidxml) {
        $fluidxml()->add([ 'el' => 'el' ])
                   ->add([ 'el' => [ 'el'  => 'el' ] ]);
});

////////////////////////////////////////////////////////////////////////////////

$machine->run_and_show();
