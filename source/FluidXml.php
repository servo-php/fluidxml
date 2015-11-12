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
        public function prependSibling($sibling, ...$optionals);
        public function appendSibling($sibling, ...$optionals);
        public function appendXml($xml);
        public function appendText($text);
        public function appendCdata($cdata);
        public function setText($text);
        public function setAttribute(...$arguments);
        public function remove($xpath);
        // Aliases:
        public function add($child, ...$optionals);
        public function prepend($sibling, ...$optionals);
        public function insertSiblingBefore($sibling, ...$optionals);
        public function append($sibling, ...$optionals);
        public function insertSiblingAfter($sibling, ...$optionals);
        // public function attr(...$arguments);
        // public function text($text);

}
////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////
class FluidNamespace
{
        // TODO
}
////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////
class FluidContext implements FluidInterface, \ArrayAccess, \Iterator
{
        private $dom;
        private $namespace;
        private $nodes = [];
        private $seek = 0;

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

        // \ArrayAccess interface.
        public function offsetSet($offset, $value)
        {
                if (\is_null($offset)) {
                        $this->nodes[] = $value;
                } else {
                        $this->nodes[$offset] = $value;
                }
        }

        // \ArrayAccess interface.
        public function offsetExists($offset)
        {
                return isset($this->nodes[$offset]);
        }

        // \ArrayAccess interface.
        public function offsetUnset($offset)
        {
                unset($this->nodes[$offset]);
        }

        // \ArrayAccess interface.
        public function offsetGet($offset)
        {
                if (isset($this->nodes[$offset])) {
                        return $this->nodes[$offset];
                }

                return null;
        }

        // \Iterator interface.
        function rewind()
        {
                $this->seek = 0;
        }

        // \Iterator interface.
        function current()
        {
                return $this->nodes[$this->seek];
        }

        // \Iterator interface.
        function key()
        {
                return $this->seek;
        }

        // \Iterator interface.
        function next()
        {
                ++$this->seek;
        }

        // \Iterator interface.
        function valid()
        {
                return isset($this->nodes[$this->seek]);
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
                $fn = function($node, $newElement) {
                        return $node->appendChild($newElement);
                };

                return $this->insertNode($fn, $child, ...$optionals);
        }

        // Alias of appendChild.
        public function add($child, ...$optionals)
        {
                return $this->appendChild($child, ...$optionals);
        }

        public function prependSibling($sibling, ...$optionals)
        {
                $fn = function($node, $newElement) {
                        return $node->parentNode->insertBefore($newElement, $node);
                };

                return $this->insertNode($fn, $sibling, ...$optionals);
        }

        // Alias of prependSibling.
        public function prepend($sibling, ...$optionals)
        {
                return $this->prependSibling($sibling, ...$optionals);
        }

        // Alias of prependSibling.
        public function insertSiblingBefore($sibling, ...$optionals)
        {
                return $this->prependSibling($sibling, ...$optionals);
        }

        public function appendSibling($sibling, ...$optionals)
        {
                $fn = function($node, $newElement) {
                        /* if nextSibling is null, it is simply appended as last sibling. */
                        return $node->parentNode->insertBefore($newElement, $node->nextSibling);
                };

                return $this->insertNode($fn, $sibling, ...$optionals);
        }

        // Alias of appendSibling.
        public function append($sibling, ...$optionals)
        {
                return $this->appendSibling($sibling, ...$optionals);
        }

        // Alias of appendSibling.
        public function insertSiblingAfter($sibling, ...$optionals)
        {
                return $this->appendSibling($sibling, ...$optionals);
        }

        public function appendXml($xml)
        {
                $newDom = new \DOMDocument();
                // A workaround to import strings with multiple root nodes.
                $newDom->loadXML("<root>$xml</root>");

                $newDomXp = new \DOMXPath($newDom);
                // It returns different results from '//*'.
                $newNodes = $newDomXp->query('/root/*');

                foreach ($this->nodes as $n) {
                        foreach ($newNodes as $e) {
                                $n->appendChild($this->dom->importNode($e, true));
                        }
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

        public function appendText($text)
        {
                foreach ($this->nodes as $n) {
                        $n->appendChild(new \DOMText($text));
                }

                return $this;
        }

        public function appendCdata($cdata)
        {
                foreach ($this->nodes as $n) {
                        $n->appendChild(new \DOMCDATASection($cdata));
                }

                return $this;
        }

        public function setText($text)
        {
                foreach ($this->nodes as $n) {
                        // Algorithm 1:
                        // foreach ($n->childNodes as $c) {
                        //         $n->removeChild($c);
                        // }
                        // $n->appendChild(new \DOMText($text));

                        // Algorithm 2:
                        // foreach ($n->childNodes as $c) {
                        //         $n->replaceChild(new \DOMText($text), $c);
                        // }

                        // Algorithm 3:
                        $n->nodeValue = $text;
                }

                return $this;
        }

        public function remove($xpath)
        {
                // The function accepts a plain XPath string
                // or a specific context.
                $targets = $xpath;

                if (! $xpath instanceof FluidContext) {
                        $targets = $this->query($xpath);
                }

                foreach ($targets as $t) {
                        $t->parentNode->removeChild($t);
                }

                return $this;
        }

        protected function newContext($context)
        {
                return new FluidContext($this->dom, $context, $this->namespace);
        }

        protected function insertNode($fn, $node, ...$optionals)
        {
                if (! \is_array($node)) {
                        $node = [ $node ];
                }

                $switchContext = false;
                $attributes = [];

                foreach ($optionals as $opt) {
                        if (\is_array($opt)) {
                                $attributes = $opt;
                        } else if (\is_bool($opt)){
                                $switchContext = $opt;
                        } else {
                                throw new \Exception('Optional argument "'.$opt.'" not recognized.');
                        }
                }

                $newContext = [];

                foreach ($node as $k => $v) {
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
                                        $newContext[] = $fn($n, $el);
                                }
                        } else {
                                // TODO
                                throw new \Exception('Appending nested children is not yet implemented.');
                        }
                }

                $context = $this->newContext($newContext);

                // Setting the attributes is an help that the appendChild method
                // offers to the user and is the same of:
                // 1. appending a child switching the context
                // 2. setting the attributes over the new context.
                if ($attributes) {
                        $context->setAttribute($attributes);
                }

                if ($switchContext) {
                        return $context;
                }

                return $this;
        }
}
////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////
class FluidXml implements FluidInterface
{
        private $dom;

        public function __construct($options = [])
        {
                $defaults = [ 'version'    => '1.0',
                              'encoding'   => 'UTF-8',
                              'stylesheet' => null,
                              'namespace'  => null,
                              'root'       => 'doc' ];

                $opts = \array_merge($defaults, $options);

                $this->dom = new \DOMDocument($opts['version'], $opts['encoding']);
                $this->dom->formatOutput       = true;
                $this->dom->preserveWhiteSpace = false;
                $this->dom->resolveExternals   = true;

                $this->namespace = $opts['namespace'];


                if ($opts['root']) {
                        $this->appendRoot($opts['root']);
                }

                if ($stylesheet) {
                        $this->dom->insertStylesheet($stylesheet, $opts['encoding']);
                }
        }

        public function xml()
        {
                return $this->dom->saveXML();
        }

        public function query($xpath)
        {
                return $this->newContext()->query($xpath);
        }

        public function appendRoot($child, ...$optionals)
        {
                return $this->appendNode($this->newContext($this->dom), $child, ...$optionals);
        }

        public function appendChild($child, ...$optionals)
        {
                return $this->appendNode($this->newContext(), $child, ...$optionals);
        }

        // Alias of appendChild.
        public function add($child, ...$optionals)
        {
                return $this->appendChild($child, ...$optionals);
        }

        public function prependSibling($sibling, ...$optionals)
        {
                throw new \Exception('Not yet implemented.');
        }

        // Alias of prependSibling.
        public function prepend($sibling, ...$optionals)
        {
                return $this->prependSibling($sibling, ...$optionals);
        }

        // Alias of prependSibling.
        public function insertSiblingBefore($sibling, ...$optionals)
        {
                return $this->prependSibling($sibling, ...$optionals);
        }

        public function appendSibling($sibling, ...$optionals)
        {
                throw new \Exception('Not yet implemented.');
        }

        // Alias of appendSibling.
        public function append($sibling, ...$optionals)
        {
                return $this->appendSibling($sibling, ...$optionals);
        }

        // Alias of appendSibling.
        public function insertSiblingAfter($sibling, ...$optionals)
        {
                return $this->appendSibling($sibling, ...$optionals);
        }

        public function appendXml($xml, $asRoot = false)
        {
                if ($asRoot) {
                        $cx = $this->newContext($this->dom);
                } else {
                        $cx = $this->newContext();
                }

                $cx->appendXml($xml);

                return $this;
        }

        public function setAttribute(...$arguments)
        {
                $this->newContext()->setAttribute(...$arguments);

                return $this;
        }

        public function appendText($text)
        {
                $this->newContext()->appendText($text);

                return $this;
        }

        public function appendCdata($cdata)
        {
                $this->newContext()->appendCdata($cdata);

                return $this;
        }

        public function setText($text)
        {
                $this->newContext()->setText($text);

                return $this;
        }

        public function remove($xpath)
        {
                $this->newContext()->remove($xpath);

                return $this;
        }

        protected function appendNode($context, $child, ...$optionals)
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
