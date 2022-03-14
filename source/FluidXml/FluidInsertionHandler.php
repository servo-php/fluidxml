<?php

namespace FluidXml;

class FluidInsertionHandler
{
        private $document;
        private $dom;
        private $namespaces;

        public function __construct($document)
        {
                $this->document   = $document;
                $this->dom        = $document->dom;
                $this->namespaces =& $document->namespaces;
        }

        public function insertElement(&$nodes, $element, &$optionals, $fn, $orig_context)
        {
                list($element, $attributes, $switch_context) = $this->handleOptionals($element, $optionals);

                $new_nodes = [];

                foreach ($nodes as $n) {
                        foreach ($element as $k => $v) {
                                $cx        = $this->handleInsertion($n, $k, $v, $fn, $optionals);
                                $new_nodes = \array_merge($new_nodes, $cx);
                        }
                }

                $new_context = $this->newContext($new_nodes);

                // Setting the attributes is an help that the addChild method
                // offers to the user and is the same of:
                // 1. appending a child switching the context
                // 2. setting the attributes over the new context.
                if (! empty($attributes)) {
                        $new_context->setAttribute($attributes);
                }

                return $switch_context ? $new_context : $orig_context;
        }

        protected function newContext(&$context)
        {
                return new FluidContext($this->document, $this, $context);
        }

        protected function handleOptionals($element, &$optionals)
        {
                if (! \is_array($element)) {
                        $element = [ $element ];
                }

                $switch_context = false;
                $attributes     = [];

                foreach ($optionals as $opt) {
                        if (\is_array($opt)) {
                                $attributes = $opt;
                                continue;
                        }
                        if (\is_bool($opt)) {
                                $switch_context = $opt;
                                continue;
                        }
                        if (\is_string($opt) || \is_numeric($opt)) {
                                $e = \array_pop($element);
                                $element[$e] = $opt;
                                continue;
                        }
                }

                return [ $element, $attributes, $switch_context ];
        }


        protected function handleInsertion($parent, $k, $v, $fn, &$optionals)
        {
                // This is an highly optimized method.
                // Good code design would split this method in many different handlers
                // each one with its own checks. But it is too much expensive in terms
                // of performances for a core method like this, so this implementation
                // is prefered to collapse many identical checks to one.

                $recognizers = ['recognizeStringMixed', 'recognizeIntegerMixed', 'recognizeIntegerDocument'];

                foreach ($recognizers as $recognizer) {
                        $handler = $this->$recognizer($k, $v);

                        if ($handler !== null) {
                                return $this->$handler($parent, $k, $v, $fn, $optionals);
                        }
                }

                throw new \Exception('Input type not supported.');
        }

        protected function recognizeStringMixed($k, $v)
        {
                if (! \is_string($k)) {
                        return;
                }

                if ($k[0] === '@') {
                        if ($k === '@') {
                                return 'insertSpecialContent';
                        }
                        return 'insertSpecialAttribute';
                }

                if (\is_string($v) || $v === null) {
                        if (! FluidHelper::isAnXmlString($v)) {
                                return 'insertStringSimple';
                        }
                } else {
                        if (\is_numeric($v)) {
                                return 'insertStringSimple';
                        }
                }

                return 'insertStringMixed';
        }

        protected function recognizeIntegerMixed($k, $v)
        {
                // if (! \is_integer($k)) {
                //         return;
                // }

                if (\is_string($v)) {
                        if (FluidHelper::isAnXmlString($v)) {
                                return 'insertIntegerXml';
                        }

                        return 'insertIntegerString';
                }

                if (\is_array($v)) {
                        return 'insertIntegerArray';
                }
        }

        protected function recognizeIntegerDocument($k, $v)
        {
                // if (! \is_integer($k)) {
                //         return;
                // }

                if ($v instanceof \DOMDocument) {
                        return 'insertIntegerDomdocument';
                }

                if ($v instanceof \DOMNodeList) {
                        return 'insertIntegerDomnodelist';
                }

                if ($v instanceof \DOMNode) {
                        return 'insertIntegerDomnode';
                }

                if ($v instanceof \SimpleXMLElement) {
                        return 'insertIntegerSimplexml';
                }

                if ($v instanceof FluidXml) {
                        return 'insertIntegerFluidxml';
                }

                if ($v instanceof FluidContext) {
                        return 'insertIntegerFluidcontext';
                }
        }

        protected function createElement($name, $value = null)
        {
                // The DOMElement instance must be different for every node,
                // otherwise only one element is attached to the DOM.

                $id  = null;
                $uri = null;

                // The node name can contain the namespace id prefix.
                // Example: xsl:template
                $colon_pos = \strpos($name, ':');

                if ($colon_pos !== false) {
                        $id   = \substr($name, 0, $colon_pos);
                        $name = \substr($name, $colon_pos + 1);
                }

                if ($id !== null) {
                        $ns  = $this->namespaces[$id];
                        $uri = $ns->uri();

                        if ($ns->mode() === FluidNamespace::MODE_EXPLICIT) {
                                $name = "{$id}:{$name}";
                        }
                }

                if (is_null($uri)) {
                        $uri = '';
                }
                // Algorithm 1:
                $el = new \DOMElement($name, $value, $uri);

                // Algorithm 2:
                // $el = $dom->createElement($name, $value);

                return $el;
        }

        protected function attachNodes($parent, $nodes, $fn)
        {
                if (! \is_array($nodes) && ! $nodes instanceof \Traversable) {
                        $nodes = [ $nodes ];
                }

                $context = [];

                foreach ($nodes as $el) {
                        $el        = $this->dom->importNode($el, true);
                        $context[] = $fn($parent, $el);
                }

                return $context;
        }

        protected function insertSpecialContent($parent, $k, $v)
        {
                // The user has passed an element text content:
                // [ '@' => 'Element content.' ]

                // Algorithm 1:
                $this->newContext($parent)->addText($v);

                // Algorithm 2:
                // $this->setText($v);

                // The user can specify multiple '@' special elements
                // so Algorithm 1 is the right choice.

                return [];
        }

        protected function insertSpecialAttribute($parent, $k, $v)
        {
                // The user has passed an attribute name and an attribute value:
                // [ '@attribute' => 'Attribute content' ]

                $attr = \substr($k, 1);
                $this->newContext($parent)->setAttribute($attr, $v);

                return [];
        }

        protected function insertStringSimple($parent, $k, $v, $fn)
        {
                // The user has passed an element name and an element value:
                // [ 'element' => 'Element content' ]

                $el = $this->createElement($k, $v);
                $el = $fn($parent, $el);

                return [ $el ];
        }

        protected function insertStringMixed($parent, $k, $v, $fn, &$optionals)
        {
                // The user has passed one of these cases:
                // - [ 'element' => [...] ]
                // - [ 'element' => '<xml>...</xml>' ]
                // - [ 'element' => DOMNode|SimpleXMLElement|FluidXml ]

                $el = $this->createElement($k);
                $el = $fn($parent, $el);

                // The new children elements must be created in the order
                // they are supplied, so 'addChild' is the perfect operation.
                $this->newContext($el)->addChild($v, ...$optionals);

                return [ $el ];
        }

        protected function insertIntegerArray($parent, $k, $v, $fn, &$optionals)
        {
                // The user has passed a wrapper array:
                // [ [...], ... ]

                $context = [];

                foreach ($v as $kk => $vv) {
                        $cx = $this->handleInsertion($parent, $kk, $vv, $fn, $optionals);

                        $context = \array_merge($context, $cx);
                }

                return $context;
        }

        protected function insertIntegerString($parent, $k, $v, $fn)
        {
                // The user has passed a node name without a node value:
                // [ 'element', ... ]

                $el = $this->createElement($v);
                $el = $fn($parent, $el);

                return [ $el ];
        }

        protected function insertIntegerXml($parent, $k, $v, $fn)
        {
                // The user has passed an XML document instance:
                // [ '<tag></tag>', DOMNode, SimpleXMLElement, FluidXml ]

                $wrapper = new \DOMDocument();
                $wrapper->formatOutput       = true;
                $wrapper->preserveWhiteSpace = false;

                $v = \ltrim($v);

                if ($v[1] === '?') {
                        $wrapper->loadXML($v);
                        $nodes = $wrapper->childNodes;
                } else {
                        // A way to import strings with multiple root nodes.
                        $wrapper->loadXML("<root>$v</root>");

                        // Algorithm 1:
                        $nodes = $wrapper->documentElement->childNodes;

                        // Algorithm 2:
                        // $xp = new \DOMXPath($wrapper);
                        // $nodes = $xp->query('/root/*');
                }

                return $this->attachNodes($parent, $nodes, $fn);
        }

        protected function insertIntegerDomdocument($parent, $k, $v, $fn)
        {
                // A DOMDocument can have multiple root nodes.

                // Algorithm 1:
                return $this->attachNodes($parent, $v->childNodes, $fn);

                // Algorithm 2:
                // return $this->attachNodes($parent, $v->documentElement, $fn);
        }

        protected function insertIntegerDomnodelist($parent, $k, $v, $fn)
        {
                return $this->attachNodes($parent, $v, $fn);
        }

        protected function insertIntegerDomnode($parent, $k, $v, $fn)
        {
                return $this->attachNodes($parent, $v, $fn);
        }

        protected function insertIntegerSimplexml($parent, $k, $v, $fn)
        {
                return $this->attachNodes($parent, \dom_import_simplexml($v), $fn);
        }

        protected function insertIntegerFluidxml($parent, $k, $v, $fn)
        {
                return $this->attachNodes($parent, $v->dom()->documentElement, $fn);
        }

        protected function insertIntegerFluidcontext($parent, $k, $v, $fn)
        {
                return $this->attachNodes($parent, $v->array(), $fn);
        }
}
