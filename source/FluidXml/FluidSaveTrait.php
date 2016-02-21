<?php

namespace FluidXml;

trait FluidSaveTrait
{
        public function save($file, $strip = false)
        {
                $status = \file_put_contents($file, $this->xml($strip));

                if (! $status) {
                        throw new \Exception("The file '$file' is not writable.");
                }

                return $this;
        }

        abstract public function xml($strip = false);
}
