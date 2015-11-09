<?php

////////////////////////////////////////////////////////////////////////////////
function fluidxml(...$arguments)
{
        return new FluidXml(...$arguments);
}
////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////
interface FluidInterface
{
        public function query($xpath);
        public function appendChild($child, ...$optionals);
        // public function prependSibling(sibling, ...$optionals);
        // public function appendSibling(sibling, ...$optionals);
        // public function appendElement($element, ...$optionals);
        // public function appendText($text);
        // public function appendCdata($text);
        // public function setText($text);
        public function setAttribute(...$arguments);
        // public function remove($xpath);
        // Aliases:
        // public function add($child, ...$optionals);
        // public function prepend(sibling, ...$optionals);
        // public function append(sibling, ...$optionals);
        // public function insertSiblingBefore(sibling, ...$optionals);
        // public function insertSiblingAfter(sibling, ...$optionals);
        // public function attr(...$arguments);
        // public function text($text);

}
////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////
class FluidNamespace
{

}
////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////
class FluidContext implements FluidInterface, \ArrayAccess
{
        protected $dom;
        protected $namespace;
        protected $nodes = [];

        public function __construct(\DOMDocument $dom, $context, $namespace = null)
        {
                $this->dom       = $dom;
                $this->namespace = $namespace;

                if (! \is_array($context)) {
                        $context = [ $context ];
                }

                foreach ($context as $n) {
                        if ($n instanceof \DOMNodeList) {
                                for ($i = 0, $l = $n->length; $i < $l; ++$i) {
                                        $this->nodes[] = $n->item($i);
                                }
                        } else if ($n instanceof \DOMNode) {
                                $this->nodes[] = $n;
                        } else if ($n instanceof FluidContext) {
                                $this->nodes = \array_merge($this->nodes, $n->asArray());
                        } else {
                                throw new \Exception('Node type not recognized.');
                        }
                }
        }

        public function asArray()
        {
                return $this->nodes;
        }

        public function offsetSet($offset, $value)
        {
                if (\is_null($offset)) {
                        $this->nodes[] = $value;
                } else {
                        $this->nodes[$offset] = $value;
                }
        }

        public function offsetExists($offset)
        {
                return isset($this->nodes[$offset]);
        }

        public function offsetUnset($offset) {
                unset($this->nodes[$offset]);
        }

        public function offsetGet($offset) {
                if (isset($this->nodes[$offset])) {
                        return $this->nodes[$offset];
                }

                return null;
        }

        public function length()
        {
                return \count($this->nodes);
        }

        public function query($xpath)
        {
                if (! \is_array($xpath)) {
                        $xpath = [ $xpath ];
                }

                $results = [];

                $domxp = new \DOMXPath($this->dom);

                foreach ($this->nodes as $n) {
                        // TODO: benchmark of for vs foreach
                        // $nodes = $domxp->query($x, $node);
                        // for ($i = 0, $l = $nodes->length; $i < $l; ++$i) {
                        //         $nodesList[] = $nodes->item($i);
                        // }
                        foreach ($xpath as $x) {
                                // Returns a DOMNodeList.
                                $res = $domxp->query($x, $n);

                                foreach ($res as $r) {
                                        $results[] = $r;
                                }
                        }
                }

                // Performing over multiple sibling nodes a query that ascends
                // the xpath, relative (../..) or absolute (//), returns identical
                // matching results that must be collapsed in an unique result
                // (otherwise a subsequent operation is performed multiple times).
                $unique_results = [];
                foreach ($results as $r) {
                        $found = false;

                        foreach ($unique_results as $u) {
                                if ($r === $u) {
                                        $found = true;
                                }
                        }

                        if (! $found) {
                                $unique_results[] = $r;
                        }
                }

                return $this->newContext($unique_results);
        }

        // Arguments can be in the form of:
        // appendChild($child, $switchContext = false, array $attributes = [])
        // appendChild($child, array $attributes = [], $switchContext = false)
        public function appendChild($child, ...$optionals)
        {
                if (! \is_array($child)) {
                        $child = [ $child ];
                }

                $switchContext = false;
                $attributes = [];

                foreach ($optionals as $opt) {
                        if (\is_array($opt)) {
                                $attributes = $opt;
                        } else if (\is_bool($opt)){
                                $switchContext = $opt;
                        } else {
                                throw new \Exception('Optional argument not recognized.');
                        }
                }

                $newContext = [];

                foreach ($child as $k => $v) {
                        // Default case are:
                        // - [ 'element' => 'Text content of the element.' ]
                        // - [ 'element' => [] ]
                        $name  = $k;
                        $value = $v;

                        // If the array key is an integer, the user has specified
                        // only the child name, without a value.
                        // [ 'element' ]
                        if (\is_int($k)) {
                                $name = $v;
                                $value = null;
                        }

                        if (! \is_array($value)) {
                                foreach ($this->nodes as $n) {
                                        // The DOMElement instance must be different
                                        // for every node, otherwise only one element
                                        // is attached to the DOM.
                                        $el = new \DOMElement($name, $value);
                                        $newContext[] = $n->appendChild($el);
                                }
                        } else {
                                // TODO
                                throw new \Exception('Appending nested children is not yet implemented.');
                        }
                }

                $cx = $this->newContext($newContext);

                // Setting the attributes is an help that the appendChild method
                // offers to the user and is the same of:
                // 1. appending a child switching the context
                // 2. setting the attributes over the new context.
                if ($attributes) {
                        $cx->setAttribute($attributes);
                }

                if ($switchContext) {
                        return $cx;
                }

                return $this;
        }

        // Arguments can be in the form of:
        // setAttribute($name, $value)
        // setAttribute(['name' => 'value', ...])
        public function setAttribute(...$arguments)
        {
                // Default case is:
                // [ 'name' => 'value', ... ]
                $attrs = $arguments[0];

                // If the first argument is not an array,
                // the user has passed two arguments:
                // 1. is the attribute name
                // 2. is the attribute value
                if (! \is_array($arguments[0])) {
                        $attrs = [$arguments[0] => $arguments[1]];
                }

                foreach ($this->nodes as $n) {
                        foreach ($attrs as $k => $v) {
                                // $n->setAttribute($k, $v);
                                $n->appendChild(new \DOMAttr($k, $v));
                        }
                }

                return $this;
        }

        protected function newContext($context)
        {
                return new FluidContext($this->dom, $context, $this->namespace);
        }
}
////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////
class FluidXml implements FluidInterface
{
        protected $dom;

        public function __construct($options = [])
        {
                $defaults = [ 'version' => '1.0',
                              'encoding' => 'UTF-8',
                              'namespace' => null,
                              'root' => 'doc' ];

                $opts = \array_merge($defaults, $options);

                $this->dom = new \DOMDocument($opts['version'], $opts['encoding']);
                $this->dom->formatOutput       = true;
                $this->dom->preserveWhiteSpace = false;
                $this->dom->resolveExternals   = true;

                $this->namespace = $opts['namespace'];

                $this->appendRoot($opts['root']);
        }

        public function xml()
        {
                return $this->dom->saveXML();
        }

        public function query($xpath)
        {
                $cx = $this->newContext();

                return $cx->query($xpath);
        }

        public function appendRoot($child, ...$optionals)
        {
                $context = $this->newContext($this->dom);
                return $this->append($context, $child, ...$optionals);
        }

        public function appendChild($child, ...$optionals)
        {
                $context = $this->newContext();
                return $this->append($context, $child, ...$optionals);
        }

        public function setAttribute(...$arguments)
        {
                $cx = $this->newContext();
                $cx->setAttribute(...$arguments);

                return $this;
        }

        protected function append($context, $child, ...$optionals)
        {
                $newContext = $context->appendChild($child, ...$optionals);

                // If the two contextes are diffent, the user has requested
                // a switch of the context and we have to return it.
                if ($context !== $newContext) {
                        return $newContext;
                }

                return $this;
        }

        protected function newContext($context)
        {
                if (\is_null($context)) {
                        $context = $this->dom->documentElement;
                }

                return new FluidContext($this->dom, $context, $this->namespace);
        }
}
////////////////////////////////////////////////////////////////////////////////
