<?php

namespace FluidXml;

class FluidNamespace
{
        const ID   = 'id'  ;
        const URI  = 'uri' ;
        const MODE = 'mode';

        const MODE_IMPLICIT = 0;
        const MODE_EXPLICIT = 1;

        private $config = [ self::ID   => '',
                            self::URI  => '',
                            self::MODE => self::MODE_EXPLICIT ];

        public function __construct($id, $uri, $mode = 1)
        {
                $this->config[self::ID]   = $id;
                $this->config[self::URI]  = $uri;
                $this->config[self::MODE] = $mode;
        }

        public function id()
        {
                return $this->config[self::ID];
        }

        public function uri()
        {
                return $this->config[self::URI];
        }

        public function mode()
        {
                return $this->config[self::MODE];
        }

        public function __invoke($xpath)
        {
                $id = $this->id();

                if (! empty($id)) {
                        $id .= ':';
                }

                // An XPath query may not start with a slash ('/').
                // Relative queries are an example '../target".
                $new_xpath = '';

                $nodes = \explode('/', $xpath);

                foreach ($nodes as $node) {
                        if (! empty($node)) {
                                // An XPath query can have multiple slashes.
                                // Example: //target
                                $new_xpath .= "{$id}{$node}";
                        }

                        $new_xpath .= '/';
                }

                // Removes the last appended slash.
                return \substr($new_xpath, 0, -1);
        }
}
