<?php

namespace FluidXml;

function fluidxml(...$arguments)
{
        return new \FluidXml\FluidXml(...$arguments);
}

function fluidify(...$arguments)
{
        return \FluidXml\FluidXml::load(...$arguments);
}

function fluidns(...$arguments)
{
        return new \FluidXml\FluidNamespace(...$arguments);
}
