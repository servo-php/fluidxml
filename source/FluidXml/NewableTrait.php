<?php

namespace FluidXml;

trait NewableTrait
{
        // This method should be called 'new',
        // but for compatibility with PHP 5.6
        // it is shadowed by the __callStatic() method.
        public static function new_(...$arguments)
        {
                return new static(...$arguments);
        }
}
