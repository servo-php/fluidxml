<?php

namespace FluidXml;

define('FLUIDXML_VERSION', '2.0.0');

function fluidxml(...$arguments): FluidXml
{
        return new \FluidXml\FluidXml(...$arguments);
}

/**
 * @throws \Exception
 */
function fluidify(...$arguments)
{
        return \FluidXml\FluidXml::load(...$arguments);
}

function fluidns(...$arguments): FluidNamespace
{
        return new \FluidXml\FluidNamespace(...$arguments);
}
