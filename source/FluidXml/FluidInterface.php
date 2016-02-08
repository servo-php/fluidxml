<?php

namespace FluidXml;

interface FluidInterface
{
        public function query(...$query);
        public function __invoke(...$query);
        public function filter(callable $fn);
        public function each(callable $fn);
        public function times($times, callable $fn = null);
        public function add($child, ...$optionals);
        public function addChild($child, ...$optionals);
        public function prepend($sibling, ...$optionals);
        public function prependSibling($sibling, ...$optionals);
        public function append($sibling, ...$optionals);
        public function appendSibling($sibling, ...$optionals);
        public function attr(...$arguments);
        public function setAttribute(...$arguments);
        public function text($text);
        public function setText($text);
        public function addText($text);
        public function cdata($text);
        public function setCdata($text);
        public function addCdata($text);
        public function comment($text);
        public function setComment($text);
        public function addComment($text);
        public function remove(...$query);
        public function dom();
        public function __toString();
        public function xml($strip = false);
        public function html($strip = false);
        public function save($file, $strip = false);
}
