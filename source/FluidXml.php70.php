<?php

namespace FluidXml;

trait FluidNewTrait
{
        public static function new(...$arguments)
        {
                return new static(...$arguments);
        }
}

trait FluidXmlShadowTrait
{
        use FluidNewTrait;
}

trait FluidContextShadowTrait
{
        public function namespace(...$arguments)
        {
                return $this->namespace_(...$arguments);
        }
}
