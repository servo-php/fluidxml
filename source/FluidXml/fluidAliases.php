<?php

namespace FluidXml;

trait FluidAliasesTrait
{

        // Alias of ->length().
        public function size()
        {
                return $this->length();
        }

        // Alias of ->query().
        public function __invoke(...$query)
        {
                return $this->query(...$query);
        }

        // Alias of ->addChild().
        public function add($child, ...$optionals)
        {
                return $this->addChild($child, ...$optionals);
        }

        // Alias of ->prependSibling().
        public function prepend($sibling, ...$optionals)
        {
                return $this->prependSibling($sibling, ...$optionals);
        }

        // Alias of ->appendSibling().
        public function append($sibling, ...$optionals)
        {
                return $this->appendSibling($sibling, ...$optionals);
        }

        // Alias of ->setAttribute().
        public function attr(...$arguments)
        {
                return $this->setAttribute(...$arguments);
        }

        // Alias of ->setText().
        public function text($text)
        {
                return $this->setText($text);
        }

        // Alias of ->setCdata().
        public function cdata($text)
        {
                return $this->setCdata($text);
        }

        // Alias of ->setComment().
        public function comment($text)
        {
                return $this->setComment($text);
        }

}
