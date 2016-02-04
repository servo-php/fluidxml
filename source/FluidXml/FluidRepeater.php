<?php

namespace FluidXml;

class FluidRepeater
{
        private $document;
        private $handler;
        private $context;
        private $times;

        public function __construct($document, $handler, $context, $times)
        {
                $this->document = $document;
                $this->handler  = $handler;
                $this->context  = $context;
                $this->times    = $times;
        }

        public function __call($method, $arguments)
        {
                $nodes = [];
                $new_context = $this->context;

                for ($i = 0, $l = $this->times; $i < $l; ++$i) {
                        $new_context = $this->context->$method(...$arguments);
                        $nodes       = \array_merge($nodes, $new_context->array());
                }

                if ($new_context !== $this->context) {
                        return new FluidContext($this->document, $this->handler, $nodes);
                }

                return $this->context;
        }
}
