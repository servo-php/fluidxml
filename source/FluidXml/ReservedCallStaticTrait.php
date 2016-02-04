<?php

namespace FluidXml;

trait ReservedCallStaticTrait
{
        public static function __callStatic($method, $arguments)
        {
                $m = "{$method}_";

                if (\method_exists(static::class, $m)) {
                        return static::$m(...$arguments);
                }

                throw new \Exception("Method '$method' not found.");
        }
}
