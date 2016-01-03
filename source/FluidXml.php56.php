<?php

namespace FluidXml;

trait FluidXmlShadowTrait
{
}

trait FluidNamespaceShadowTrait
{
        public function __call($method, $arguments)
        {
                if ($method === 'namespace') {
                        return $this->namespace_(...$arguments);
                }

                throw new \Exception("Method '$method' not found.");
        }
}
