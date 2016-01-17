<?php

// Copyright (c) 2016, Daniele Orlando <fluidxml(at)danieleorlando.com>
// All rights reserved.
//
// Redistribution and use in source and binary forms, with or without modification,
// are permitted provided that the following conditions are met:
//
// 1. Redistributions of source code must retain the above copyright notice, this
//    list of conditions and the following disclaimer.
//
// 2. Redistributions in binary form must reproduce the above copyright notice,
//    this list of conditions and the following disclaimer in the documentation
//    and/or other materials provided with the distribution.
//
// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
// ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
// IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
// INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
// BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
// DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
// LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
// OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
// OF THE POSSIBILITY OF SUCH DAMAGE.

/**
 * FluidXML is a PHP library, under the Servo PHP framework umbrella,
 * specifically designed to manipulate XML documents with a concise
 * and fluent interface.
 *
 * It leverages XPath and the fluent programming technique to be fun
 * and effective.
 *
 * @author Daniele Orlando <fluidxml(at)danieleorlando.com>
 *
 * @license BSD-2-Clause
 * @license https://opensource.org/licenses/BSD-2-Clause
 */

namespace FluidXml;

/**
 * Constructs a new FluidXml instance.
 *
 * ```php
 * $xml = fluidxml();
 * // is the same of
 * $xml = new FluidXml();
 *
 * $xml = fluidxml([
 *
 *   'root'       => 'doc',
 *
 *   'version'    => '1.0',
 *
 *   'encoding'   => 'UTF-8',
 *
 *   'stylesheet' => null ]);
 * ```
 *
 * @param array $arguments Options that influence the construction of the XML document.
 *
 * @return FluidXml A new FluidXml instance.
 */
function fluidify(...$arguments)
{
        return FluidXml::load(...$arguments);
}

function fluidxml(...$arguments)
{
        return new FluidXml(...$arguments);
}

function fluidns(...$arguments)
{
        return new FluidNamespace(...$arguments);
}

function is_an_xml_string($string)
{
        if (! \is_string($string)) {
                return false;
        }

        // Removes any empty new line at the beginning,
        // otherwise the first character check may fail.
        $string = \ltrim($string);

        return $string[0] === '<';
}

function domdocument_to_string_without_headers(\DOMDocument $dom)
{
        return $dom->saveXML($dom->documentElement);
}

function domnodelist_to_string(\DOMNodeList $nodelist)
{
        $nodes = [];

        foreach ($nodelist as $n) {
                $nodes[] = $n;
        }

        return domnodes_to_string($nodes);
}

function domnodes_to_string(array $nodes)
{
        $dom = $nodes[0]->ownerDocument;
        $xml = '';

        foreach ($nodes as $n) {
                $xml .= $dom->saveXML($n) . PHP_EOL;
        }

        return \rtrim($xml);
}

function simplexml_to_string_without_headers(\SimpleXMLElement $element)
{
        $dom = \dom_import_simplexml($element);

        return $dom->ownerDocument->saveXML($dom);
}

interface FluidInterface
{
        /**
         * Executes an XPath query.
         *
         * ```php
         * $xml = fluidxml();

         * $xml->query("/doc/book[@id='123']");
         *
         * // Relative queries are valid.
         * $xml->query("/doc")->query("book[@id='123']");
         * ```
         *
         * @param string $xpath The XPath to execute.
         *
         * @return FluidContext The context associated to the DOMNodeList.
         */
        public function query(...$xpath);
        public function times($times, callable $fn = null);
        public function each(callable $fn);

        /**
         * Append a new node as child of the current context.
         *
         * ```php
         * $xml = fluidxml();

         * $xml->appendChild('title', 'The Theory Of Everything');
         * $xml->appendChild([ 'author' => 'S. Hawking' ]);
         *
         * $xml->appendChild('chapters', true)->appendChild('chapter', ['id'=> 1]);
         *
         * ```
         *
         * @param string|array $child The child/children to add.
         * @param string $value The child text content.
         * @param bool $switchContext Whether to return the current context
         *                            or the context of the created node.
         *
         * @return FluidContext The context associated to the DOMNodeList.
         */
        public function appendChild($child, ...$optionals);
        public function prependSibling($sibling, ...$optionals);
        public function appendSibling($sibling, ...$optionals);
        public function setAttribute(...$arguments);
        public function setText($text);
        public function appendText($text);
        public function setCdata($text);
        public function appendCdata($text);
        public function remove(...$xpath);
        public function xml($strip = false);
        // Aliases:
        public function add($child, ...$optionals);
        public function prepend($sibling, ...$optionals);
        public function insertSiblingBefore($sibling, ...$optionals);
        public function append($sibling, ...$optionals);
        public function insertSiblingAfter($sibling, ...$optionals);
        public function attr(...$arguments);
        public function text($text);
}

trait ReservedCallTrait
{
        public function __call($method, $arguments)
        {
                $m = "{$method}_";

                if (\method_exists($this, $m)) {
                        return $this->$m(...$arguments);
                }

                throw new \Exception("Method '$method' not found.");
        }
}

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

class FluidDocument
{
        public $dom;
        public $xpath;
        public $namespaces = [];
}

class FluidXml implements FluidInterface
{
        use NewableTrait,
            ReservedCallTrait,          // For compatibility with PHP 5.6.
            ReservedCallStaticTrait;    // For compatibility with PHP 5.6.

        const ROOT_NODE = 'doc';

        private $document;

        public static function load($document)
        {
                if (\is_string($document) && ! is_an_xml_string($document)) {
                        // Removes any empty new line at the beginning,
                        // otherwise the first character check fails.

                        $file        = $document;
                        $is_file     = \is_file($file);
                        $is_readable = \is_readable($file);

                        if ($is_file && $is_readable) {
                                $document = \file_get_contents($file);
                        }

                        if (! $is_file || ! $is_readable || ! $document) {
                                throw new \Exception("File '$file' not accessible.");
                        }
                }

                return (new FluidXml(['root' => null]))->appendChild($document);
        }

        public function __construct($root = null, $options = [])
        {
                $this->document = new FluidDocument();

                $defaults = [ 'root'       => self::ROOT_NODE,
                              'version'    => '1.0',
                              'encoding'   => 'UTF-8',
                              'stylesheet' => null ];

                if (\is_string($root)) {
                        // The root option can be specified as first argument
                        // because it is the most common.
                        $defaults['root'] = $root;
                } else if (\is_array($root)) {
                        // If the first argument is an array, the user has skipped
                        // the root option and is passing a bunch of options all together.
                        $options = $root;
                }

                $opts = \array_merge($defaults, $options);

                $this->document->dom = new \DOMDocument($opts['version'], $opts['encoding']);
                $this->document->dom->formatOutput       = true;
                $this->document->dom->preserveWhiteSpace = false;

                $this->document->xpath = new \DOMXPath($this->document->dom);

                if (! empty($opts['root'])) {
                        $this->appendSibling($opts['root']);
                }

                if (! empty($opts['stylesheet'])) {
                        $attrs = 'type="text/xsl" '
                               . "encoding=\"{$opts['encoding']}\" "
                               . 'indent="yes" '
                               . "href=\"{$opts['stylesheet']}\"";
                        $stylesheet = new \DOMProcessingInstruction('xml-stylesheet', $attrs);

                        $this->document->dom->insertBefore($stylesheet, $this->document->dom->documentElement);
                }
        }

        public function xml($strip = false)
        {
                if ($strip) {
                        return domdocument_to_string_without_headers($this->document->dom);
                }

                return $this->document->dom->saveXML();
        }

        public function dom()
        {
                return $this->document->dom;
        }

        public function namespaces()
        {
                return $this->document->namespaces;
        }

        // This method should be called 'namespace',
        // but for compatibility with PHP 5.6
        // it is shadowed by the __call() method.
        protected function namespace_(...$arguments)
        {
                $namespaces = [];

                if (\is_string($arguments[0])) {
                        $args = [ $arguments[0], $arguments[1] ];

                        if (isset($arguments[2])) {
                                $args[] = $arguments[2];
                        }

                        $namespaces[] = new FluidNamespace(...$args);
                } else if (\is_array($arguments[0])) {
                        $namespaces = $arguments[0];
                } else {
                        $namespaces = $arguments;
                }

                foreach ($namespaces as $n) {
                        $this->document->namespaces[$n->id()] = $n;
                        $this->document->xpath->registerNamespace($n->id(), $n->uri());
                }

                return $this;
        }

        public function query(...$xpath)
        {
                return $this->context()->query(...$xpath);
        }

        public function times($times, callable $fn = null)
        {
                return $this->context()->times($times, $fn);
        }

        public function each(callable $fn)
        {
                return $this->context()->each($fn);
        }

        public function appendChild($child, ...$optionals)
        {
                // If the user has requested ['root' => null] at construction time
                // 'context()' promotes DOMDocument as root node.
                $context     = $this->context();
                $new_context = $context->appendChild($child, ...$optionals);

                return $this->chooseContext($context, $new_context);
        }

        // Alias of appendChild().
        public function add($child, ...$optionals)
        {
                return $this->appendChild($child, ...$optionals);
        }

        public function prependSibling($sibling, ...$optionals)
        {
                if ($this->document->dom->documentElement === null) {
                        // If the document doesn't have at least one root node,
                        // the sibling creation fails. In this case we replace
                        // the sibling creation with the creation of a generic node.
                        return $this->appendChild($sibling, ...$optionals);
                }

                $context     = $this->context();
                $new_context = $context->prependSibling($sibling, ...$optionals);

                return $this->chooseContext($context, $new_context);
        }

        // Alias of prependSibling().
        public function prepend($sibling, ...$optionals)
        {
                return $this->prependSibling($sibling, ...$optionals);
        }

        // Alias of prependSibling().
        public function insertSiblingBefore($sibling, ...$optionals)
        {
                return $this->prependSibling($sibling, ...$optionals);
        }

        public function appendSibling($sibling, ...$optionals)
        {
                if ($this->document->dom->documentElement === null) {
                        // If the document doesn't have at least one root node,
                        // the sibling creation fails. In this case we replace
                        // the sibling creation with the creation of a generic node.
                        return $this->appendChild($sibling, ...$optionals);
                }

                $context     = $this->context();
                $new_context = $context->appendSibling($sibling, ...$optionals);

                return $this->chooseContext($context, $new_context);
        }

        // Alias of appendSibling().
        public function append($sibling, ...$optionals)
        {
                return $this->appendSibling($sibling, ...$optionals);
        }

        // Alias of appendSibling().
        public function insertSiblingAfter($sibling, ...$optionals)
        {
                return $this->appendSibling($sibling, ...$optionals);
        }

        public function setAttribute(...$arguments)
        {
                $this->context()->setAttribute(...$arguments);

                return $this;
        }

        // Alias of setAttribute().
        public function attr(...$arguments)
        {
                return $this->setAttribute(...$arguments);
        }

        public function appendText($text)
        {
                $this->context()->appendText($text);

                return $this;
        }

        public function appendCdata($text)
        {
                $this->context()->appendCdata($text);

                return $this;
        }

        public function setText($text)
        {
                $this->context()->setText($text);

                return $this;
        }

        // Alias of setText().
        public function text($text)
        {
                return $this->setText($text);
        }

        public function setCdata($text)
        {
                $this->context()->setCdata($text);

                return $this;
        }

        // Alias of setCdata().
        public function cdata($text)
        {
                return $this->setCdata($text);
        }

        public function remove(...$xpath)
        {
                $this->context()->remove(...$xpath);

                return $this;
        }

        private $context;
        private $contextEl;

        protected function context()
        {
                if ($this->document->dom->documentElement === null) {
                        // If the user has requested ['root' => null] at construction time
                        // the 'documentElement' property is null because we have not created
                        // a root node yet. Whether there is not a root node, the DOMDocument
                        // is promoted as root node.
                        if ($this->context === null) {
                                $this->context = new FluidContext($this->document, $this->document->dom);
                        }

                        return $this->context;
                }


                if ($this->contextEl === null || $this->contextEl !== $this->document->dom->documentElement) {
                        // The user can prepend a root node to the current root node.
                        // In this case we have to update the context with the new first root node.
                        $this->context = new FluidContext($this->document, $this->document->dom->documentElement);
                        $this->contextEl = $this->document->dom->documentElement;
                }

                return $this->context;
        }

        protected function chooseContext($help_context, $new_context)
        {
                // If the two contextes are diffent, the user has requested
                // a switch of the context and we have to return it.
                if ($help_context !== $new_context) {
                        return $new_context;
                }

                return $this;
        }
}

class FluidContext implements FluidInterface, \ArrayAccess, \Iterator
{
        use NewableTrait,
            ReservedCallTrait,          // For compatibility with PHP 5.6.
            ReservedCallStaticTrait;    // For compatibility with PHP 5.6.

        private $document;
        private $nodes = [];
        private $seek = 0;

        public function __construct($document, $context)
        {
                $this->document = $document;

                if (! \is_array($context) && ! $context instanceof \Traversable) {
                        // DOMDocument, DOMElement and DOMNode are not iterable.
                        // DOMNodeList and FluidContext are iterable.
                        $context = [ $context ];
                }

                foreach ($context as $n) {
                        if (! $n instanceof \DOMNode) {
                                throw new \Exception('Node type not recognized.');
                        }

                        $this->nodes[] = $n;
                }
        }

        public function asArray()
        {
                return $this->nodes;
        }

        // \ArrayAccess interface.
        public function offsetSet($offset, $value)
        {
                // if (\is_null($offset)) {
                //         $this->nodes[] = $value;
                // } else {
                //         $this->nodes[$offset] = $value;
                // }
                throw new \Exception('Setting a context element is not allowed.');
        }

        // \ArrayAccess interface.
        public function offsetExists($offset)
        {
                return isset($this->nodes[$offset]);
        }

        // \ArrayAccess interface.
        public function offsetUnset($offset)
        {
                // unset($this->nodes[$offset]);
                \array_splice($this->nodes, $offset, 1);
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
        public function rewind()
        {
                $this->seek = 0;
        }

        // \Iterator interface.
        public function current()
        {
                return $this->nodes[$this->seek];
        }

        // \Iterator interface.
        public function key()
        {
                return $this->seek;
        }

        // \Iterator interface.
        public function next()
        {
                ++$this->seek;
        }

        // \Iterator interface.
        public function valid()
        {
                return isset($this->nodes[$this->seek]);
        }

        public function length()
        {
                return \count($this->nodes);
        }

        public function query(...$xpath)
        {
                $xpaths = $xpath;

                if (\is_array($xpath[0])) {
                        $xpaths = $xpath[0];
                }

                $results = [];

                foreach ($this->nodes as $n) {
                        foreach ($xpaths as $x) {
                                // Returns a DOMNodeList.
                                $res = $this->document->xpath->query($x, $n);

                                // Algorithm 1:
                                $results = \array_merge($results, \iterator_to_array($res));

                                // Algorithm 2:
                                // foreach ($res as $r) {
                                //         $results[] = $r;
                                // }

                                // Algorithm 3:
                                // for ($i = 0, $l = $res->length; $i < $l; ++$i) {
                                //         $results[] = $res->item($i);
                                // }
                        }
                }

                // Performing over multiple sibling nodes a query that ascends
                // the xpath, relative (../..) or absolute (//), returns identical
                // matching results that must be collapsed in an unique result
                // otherwise a subsequent operation is performed multiple times.
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

        public function times($times, callable $fn = null)
        {
                if ($fn === null) {
                        return new FluidRepeater($this->document, $this, $times);
                }

                for ($i = 0; $i < $times; ++$i) {
                        $args = [$this, $i];

                        if ($fn instanceof \Closure) {
                                $fn = $fn->bindTo($this);

                                \array_shift($args);
                        }

                        \call_user_func($fn, ...$args);
                }

                return $this;
        }

        public function each(callable $fn)
        {
                foreach ($this->nodes as $i => $n) {
                        $cx   = $this->newContext($n);
                        $args = [$cx, $i, $n];

                        if ($fn instanceof \Closure) {
                                $fn = $fn->bindTo($cx);

                                \array_shift($args);
                        }

                        \call_user_func($fn, ...$args);
                }

                return $this;
        }

        // appendChild($child, $value?, $attributes? = [], $switchContext? = false)
        public function appendChild($child, ...$optionals)
        {
                return $this->insertElement($child, $optionals, function($parent, $element) {
                        return $parent->appendChild($element);
                });
        }

        // Alias of appendChild().
        public function add($child, ...$optionals)
        {
                return $this->appendChild($child, ...$optionals);
        }

        public function prependSibling($sibling, ...$optionals)
        {
                return $this->insertElement($sibling, $optionals, function($sibling, $element) {
                        return $sibling->parentNode->insertBefore($element, $sibling);
                });
        }

        // Alias of prependSibling().
        public function prepend($sibling, ...$optionals)
        {
                return $this->prependSibling($sibling, ...$optionals);
        }

        // Alias of prependSibling().
        public function insertSiblingBefore($sibling, ...$optionals)
        {
                return $this->prependSibling($sibling, ...$optionals);
        }

        public function appendSibling($sibling, ...$optionals)
        {
                return $this->insertElement($sibling, $optionals, function($sibling, $element) {
                        // If ->nextSibling is null, $element is simply appended as last sibling.
                        return $sibling->parentNode->insertBefore($element, $sibling->nextSibling);
                });
        }

        // Alias of appendSibling().
        public function append($sibling, ...$optionals)
        {
                return $this->appendSibling($sibling, ...$optionals);
        }

        // Alias of appendSibling().
        public function insertSiblingAfter($sibling, ...$optionals)
        {
                return $this->appendSibling($sibling, ...$optionals);
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
                                // Algorithm 1:
                                $n->setAttribute($k, $v);

                                // Algorithm 2:
                                // $n->setAttributeNode(new \DOMAttr($k, $v));

                                // Algorithm 3:
                                // $n->appendChild(new \DOMAttr($k, $v));

                                // Algorithm 2 and 3 have a different behaviour
                                // from Algorithm 1.
                                // The attribute is still created or setted, but
                                // changing the value of an existing attribute
                                // changes even the order of that attribute
                                // in the attribute list.
                        }
                }

                return $this;
        }

        // Alias of setAttribute().
        public function attr(...$arguments)
        {
                return $this->setAttribute(...$arguments);
        }

        public function appendText($text)
        {
                foreach ($this->nodes as $n) {
                        $n->appendChild(new \DOMText($text));
                }

                return $this;
        }

        public function appendCdata($text)
        {
                foreach ($this->nodes as $n) {
                        $n->appendChild(new \DOMCDATASection($text));
                }

                return $this;
        }

        public function setText($text)
        {
                foreach ($this->nodes as $n) {
                        // Algorithm 1:
                        $n->nodeValue = $text;

                        // Algorithm 2:
                        // foreach ($n->childNodes as $c) {
                        //         $n->removeChild($c);
                        // }
                        // $n->appendChild(new \DOMText($text));

                        // Algorithm 3:
                        // foreach ($n->childNodes as $c) {
                        //         $n->replaceChild(new \DOMText($text), $c);
                        // }
                }

                return $this;
        }

        // Alias of setText().
        public function text($text)
        {
                return $this->setText($text);
        }

        public function setCdata($text)
        {
                foreach ($this->nodes as $n) {
                        $n->nodeValue = '';
                        $n->appendChild(new \DOMCDATASection($text));
                }

                return $this;
        }

        // Alias of setCdata().
        public function cdata($text)
        {
                return $this->setCdata($text);
        }

        public function remove(...$xpath)
        {
                // Arguments can be empty, a string or an array of strings.

                if (empty($xpath)) {
                        // The user has requested to remove the nodes of this context.
                        $targets = $this->nodes;
                } else {
                        $targets = $this->query(...$xpath);
                }

                foreach ($targets as $t) {
                        $t->parentNode->removeChild($t);
                }

                return $this;
        }

        public function xml($strip = false)
        {
                return domnodes_to_string($this->nodes);
        }

        protected function newContext($context)
        {
                return new FluidContext($this->document, $context);
        }

        protected function handleOptionals($element, array $optionals)
        {
                if (! \is_array($element)) {
                        $element = [ $element ];
                }

                $switch_context = false;
                $attributes     = [];

                foreach ($optionals as $opt) {
                        if (\is_array($opt)) {
                                $attributes = $opt;

                        } else if (\is_bool($opt)) {
                                $switch_context = $opt;

                        } else if (\is_string($opt)) {
                                $e = \array_pop($element);

                                $element[$e] = $opt;

                        } else {
                                throw new \Exception("Optional argument '$opt' not recognized.");
                        }
                }

                return [ $element, $attributes, $switch_context ];
        }

        protected function insertElement($element, array $optionals, callable $fn)
        {
                list($element, $attributes, $switch_context) = $this->handleOptionals($element, $optionals);

                $nodes = [];

                foreach ($this->nodes as $n) {
                        foreach ($element as $k => $v) {
                                // I give up, it's a too complex job for only one method like me.
                                $cx    = $this->handleInsertion($n, $k, $v, $fn, $optionals);

                                $nodes = \array_merge($nodes, $cx);
                        }
                }

                $new_context = $this->newContext($nodes);

                // Setting the attributes is an help that the appendChild method
                // offers to the user and is the same of:
                // 1. appending a child switching the context
                // 2. setting the attributes over the new context.
                if (! empty($attributes)) {
                        $new_context->setAttribute($attributes);
                }

                if ($switch_context) {
                        return $new_context;
                }

                return $this;
        }

        protected function handleInsertion($parent, $k, $v, $fn, $optionals)
        {
                // This is an highly optimized method.
                // Good code design would split this method in many different handlers
                // each one with its own checks. But it is too much expensive in terms
                // of performances for a core method like this, so this implementation
                // is prefered to collapse many identical checks to one.

                $k_is_string      = \is_string($k);
                $k_is_integer     = \is_integer($k);
                $v_is_string      = \is_string($v);
                $k_is_special     = $v_is_string  && $k[0] === '@';
                $k_is_special_c   = $k_is_special && $k === '@';
                $k_is_special_a   = $k_is_special && ! $k_is_special_c;
                $v_is_xml         = is_an_xml_string($v);
                $v_is_array       = \is_array($v);
                $v_is_dom         = $v instanceof \DOMDocument;
                $v_is_domnodelist = $v instanceof \DOMNodeList;
                $v_is_domnode     = $v instanceof \DOMNode;
                $v_is_simplexml   = $v instanceof \SimpleXMLElement;
                $v_is_fluidxml    = $v instanceof FluidXml;
                $v_is_fluidcx     = $v instanceof FluidContext;

                $v_isnt_string    = ! $v_is_string;
                $k_isnt_special   = ! $k_is_special;
                $v_isnt_xml       = ! $v_is_xml;

                if ($k_is_integer && $v_is_string && $v_isnt_xml) {
                        return $this->integerStringNotXmlHandler(...\func_get_args());
                }

                if ($k_is_integer && $v_is_array) {
                        return $this->integerArrayHandler(...\func_get_args());
                }

                if ($k_is_string && $v_is_string && $k_isnt_special) {
                        return $this->stringStringHandler(...\func_get_args());
                }

                if ($k_is_string && $v_isnt_string) {
                        return $this->stringNotStringHandler(...\func_get_args());
                }

                if ($k_is_special_c && $v_is_string) {
                        return $this->specialContentHandler(...\func_get_args());
                }

                if ($k_is_special_a && $v_is_string) {
                        return $this->specialAttributeHandler(...\func_get_args());
                }

                if ($k_is_integer && $v_is_xml) {
                        return $this->integerXmlHandler(...\func_get_args());
                }

                if ($k_is_integer && $v_is_dom) {
                        return $this->integerDomdocumentHandler(...\func_get_args());
                }

                if ($k_is_integer && $v_is_domnodelist) {
                        return $this->integerDomnodelistHandler(...\func_get_args());
                }

                if ($k_is_integer && ! $v_is_dom && $v_is_domnode) {
                        return $this->integerDomnodeHandler(...\func_get_args());
                }

                if ($k_is_integer && $v_is_simplexml) {
                        return $this->integerSimplexmlHandler(...\func_get_args());
                }

                if ($k_is_integer && $v_is_fluidxml) {
                        return $this->integerFluidxmlHandler(...\func_get_args());
                }

                if ($k_is_integer && $v_is_fluidcx) {
                        return $this->integerFluidcontextHandler(...\func_get_args());
                }

                throw new \Exception('XML document not supported.');
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

                if ($id) {
                        $ns  = $this->document->namespaces[$id];
                        $uri = $ns->uri();

                        if ($ns->mode() === FluidNamespace::MODE_EXPLICIT) {
                                $name = "{$id}:{$name}";
                        }
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
                        $el        = $this->document->dom->importNode($el, true);
                        $context[] = \call_user_func($fn, $parent, $el);
                }

                return $context;
        }

        protected function specialContentHandler($parent, $k, $v)
        {
                // The user has passed an element text content:
                // [ '@' => 'Element content.' ]

                // Algorithm 1:
                $this->newContext($parent)->appendText($v);

                // Algorithm 2:
                // $this->setText($v);

                // The user can specify multiple '@' special elements
                // so Algorithm 1 is the right choice.

                return [];
        }

        protected function specialAttributeHandler($parent, $k, $v)
        {
                // The user has passed an attribute name and an attribute value:
                // [ '@attribute' => 'Attribute content' ]

                $attr = \substr($k, 1);
                $this->newContext($parent)->setAttribute($attr, $v);

                return [];
        }

        protected function stringStringHandler($parent, $k, $v, $fn)
        {
                // The user has passed an element name and an element value:
                // [ 'element' => 'Element content' ]

                $el = $this->createElement($k, $v);
                $el = \call_user_func($fn, $parent, $el);

                return [ $el ];
        }

        protected function stringNotStringHandler($parent, $k, $v, $fn, $optionals)
        {
                // The user has passed one of these two cases:
                // - [ 'element' => [...] ]
                // - [ 'element' => DOMNode|SimpleXMLElement|FluidXml ]

                $el = $this->createElement($k);
                $el = \call_user_func($fn, $parent, $el);

                // The new children elements must be created in the order
                // they are supplied, so 'appendChild' is the perfect operation.
                $this->newContext($el)->appendChild($v, ...$optionals);

                return [ $el ];
        }

        protected function integerArrayHandler($parent, $k, $v, $fn, $optionals)
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

        protected function integerStringNotXmlHandler($parent, $k, $v, $fn)
        {
                // The user has passed a node name without a node value:
                // [ 'element', ... ]

                $el = $this->createElement($v);
                $el = \call_user_func($fn, $parent, $el);

                return [ $el ];
        }

        protected function integerXmlHandler($parent, $k, $v, $fn)
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
                        // $dom_xp = new \DOMXPath($dom);
                        // $nodes = $dom_xp->query('/root/*');
                }

                return $this->attachNodes($parent, $nodes, $fn);
        }

        protected function integerDomdocumentHandler($parent, $k, $v, $fn)
        {
                // A DOMDocument can have multiple root nodes.

                // Algorithm 1:
                return $this->attachNodes($parent, $v->childNodes, $fn);

                // Algorithm 2:
                // return $this->attachNodes($parent, $v->documentElement, $fn);
        }

        protected function integerDomnodelistHandler($parent, $k, $v, $fn)
        {
                return $this->attachNodes($parent, $v, $fn);
        }

        protected function integerDomnodeHandler($parent, $k, $v, $fn)
        {
                return $this->attachNodes($parent, $v, $fn);
        }

        protected function integerSimplexmlHandler($parent, $k, $v, $fn)
        {
                return $this->attachNodes($parent, \dom_import_simplexml($v), $fn);
        }

        protected function integerFluidxmlHandler($parent, $k, $v, $fn)
        {
                return $this->attachNodes($parent, $v->dom()->documentElement, $fn);
        }

        protected function integerFluidcontextHandler($parent, $k, $v, $fn)
        {
                return $this->attachNodes($parent, $v->asArray(), $fn);
        }
}

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
                if (\is_array($id)) {
                        $args = $id;
                        $id   = $args[self::ID];
                        $uri  = $args[self::URI];

                        if (isset($args[self::MODE])) {
                                $mode = $args[self::MODE];
                        }
                }

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

        public function querify($xpath)
        {
                $id = $this->id();

                if ($id) {
                        $id .= ':';
                }

                // An XPath query may not start with a slash ('/').
                // Relative queries are an example '../target".
                $new_xpath = '';

                $nodes = \explode('/', $xpath);

                foreach ($nodes as $node) {
                        // An XPath query may have multiple slashes ('/')
                        // example: //target
                        if ($node) {
                                $new_xpath .= "{$id}{$node}";
                        }

                        $new_xpath .= '/';
                }

                // Removes the last appended slash.
                return \substr($new_xpath, 0, -1);
        }
}

class FluidRepeater
{
        private $document;
        private $context;
        private $times;

        public function __construct($document, $context, $times)
        {
                $this->document = $document;
                $this->context  = $context;
                $this->times    = $times;
        }

        public function __call($method, $arguments)
        {
                $nodes = [];
                $new_context = $this->context;

                for ($i = 0, $l = $this->times; $i < $l; ++$i) {
                        $new_context = $this->context->$method(...$arguments);
                        $nodes       = \array_merge($nodes, $new_context->asArray());
                }

                if ($new_context !== $this->context) {
                        return new FluidContext($this->document, $nodes);
                }

                return $this->context;
        }
}
