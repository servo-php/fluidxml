<?php

namespace FluidXml;

trait FluidAliasesTrait
{
        public function size()                           { return $this->length(); }
        public function __invoke(...$query)              { return $this->query(...$query); }
        public function add($child, ...$optionals)       { return $this->addChild($child, ...$optionals); }
        public function prepend($sibling, ...$optionals) { return $this->prependSibling($sibling, ...$optionals); }
        public function append($sibling, ...$optionals)  { return $this->appendSibling($sibling, ...$optionals); }
        public function attr($name, $value = null)       { return $this->setAttribute($name, $value); }
        public function text($text)                      { return $this->setText($text); }
        public function cdata($text)                     { return $this->setCdata($text); }
        public function comment($text)                   { return $this->setComment($text); }
        abstract public function length();
        abstract public function query(...$query);
        abstract public function addChild($child, ...$optionals);
        abstract public function prependSibling($sibling, ...$optionals);
        abstract public function appendSibling($sibling, ...$optionals);
        abstract public function setAttribute($name, $value = null);
        abstract public function setText($text);
        abstract public function setCdata($text);
        abstract public function setComment($text);
}
