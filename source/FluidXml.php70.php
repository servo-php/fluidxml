<?php

namespace FluidXml;

trait FluidXmlShadowTrait
{
        public static function new(...$arguments)
        {
                return new FluidXml(...$arguments);
        }
}

trait FluidNamespaceShadowTrait
{
        public function namespace(...$arguments)
        {
                return $this->namespace_(...$arguments);
        }
}
