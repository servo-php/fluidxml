<?php

trait FluidXmlShadowTrait
{
}

trait FluidNamespaceShadowTrait
{
        public function __call($method, $arguments)
        {
                if ($method === 'namespace') {
                        return $this->registerNamespace(...$arguments);
                }

                throw new \Exception("Method '$method' not found.");
        }
}
