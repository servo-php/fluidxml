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

trait FluidNamespaceTrait
{
        private $namespaces = [];

        public function namespaces()
        {
                return $this->namespaces;
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
                        $this->namespaces[$n->id()] = $n;
                }

                return $this;
        }
}

class FluidXml implements FluidInterface
{
        use FluidNamespaceTrait,
            NewableTrait,
            ReservedCallTrait,          // For compatibility with PHP 5.6.
            ReservedCallStaticTrait;    // For compatibility with PHP 5.6.

        const ROOT_NODE = 'doc';

        private $dom;

        public static function load($document)
        {
                if (\is_string($document) && ! \FluidXml\is_an_xml_string($document)) {
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

                $this->dom = new \DOMDocument($opts['version'], $opts['encoding']);
                $this->dom->formatOutput       = true;
                $this->dom->preserveWhiteSpace = false;

                if ($opts['root']) {
                        $this->appendSibling($opts['root']);
                }

                if ($opts['stylesheet']) {
                        $attrs = 'type="text/xsl" '
                               . "encoding=\"{$opts['encoding']}\" "
                               . 'indent="yes" '
                               . "href=\"{$opts['stylesheet']}\"";
                        $stylesheet = new \DOMProcessingInstruction('xml-stylesheet', $attrs);

                        $this->dom->insertBefore($stylesheet, $this->dom->documentElement);
                }
        }

        public function xml($strip = false)
        {
                if ($strip) {
                        return domdocument_to_string_without_headers($this->dom);
                }

                return $this->dom->saveXML();
        }

        public function dom()
        {
                return $this->dom;
        }

        public function query(...$xpath)
        {
                return $this->newContext()->query(...$xpath);
        }

        public function times($times, callable $fn = null)
        {
                return $this->newContext()->times($times, $fn);
        }

        public function each(callable $fn)
        {
                return $this->newContext()->each($fn);
        }

        public function appendChild($child, ...$optionals)
        {
                // If the user has requested ['root' => null] at construction time
                // 'newContext()' promotes DOMDocument as root node.
                $context     = $this->newContext();
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
                if ($this->dom->documentElement === null) {
                        // If the document doesn't have at least one root node,
                        // the sibling creation fails. In this case we replace
                        // the sibling creation with the creation of a generic node.
                        return $this->appendChild($sibling, ...$optionals);
                }

                $context     = $this->newContext();
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
                if ($this->dom->documentElement === null) {
                        // If the document doesn't have at least one root node,
                        // the sibling creation fails. In this case we replace
                        // the sibling creation with the creation of a generic node.
                        return $this->appendChild($sibling, ...$optionals);
                }

                $context     = $this->newContext();
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
                $this->newContext()->setAttribute(...$arguments);

                return $this;
        }

        // Alias of setAttribute().
        public function attr(...$arguments)
        {
                return $this->setAttribute(...$arguments);
        }

        public function appendText($text)
        {
                $this->newContext()->appendText($text);

                return $this;
        }

        public function appendCdata($text)
        {
                $this->newContext()->appendCdata($text);

                return $this;
        }

        public function setText($text)
        {
                $this->newContext()->setText($text);

                return $this;
        }

        // Alias of setText().
        public function text($text)
        {
                return $this->setText($text);
        }

        public function setCdata($text)
        {
                $this->newContext()->setCdata($text);

                return $this;
        }

        // Alias of setCdata().
        public function cdata($text)
        {
                return $this->setCdata($text);
        }

        public function remove(...$xpath)
        {
                $this->newContext()->remove(...$xpath);

                return $this;
        }

        protected function newContext($context = null)
        {
                if (! $context) {
                        $context = $this->dom->documentElement;
                }

                // If the user has requested ['root' => null] at construction time
                // the 'documentElement' property is null because we have not created
                // a root node yet.
                if (! $context) {
                        // Whether there is not a root node, the DOMDocument is
                        // promoted as root node.
                        $context = $this->dom;
                }

                return new FluidContext($context, $this->namespaces);
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
        use FluidNamespaceTrait,
            NewableTrait,
            ReservedCallTrait,          // For compatibility with PHP 5.6.
            ReservedCallStaticTrait;    // For compatibility with PHP 5.6.

        private $dom;
        private $nodes = [];
        private $seek = 0;

        public function __construct($context, array $namespaces = [])
        {
                if (! \is_array($context)) {
                        $context = [ $context ];
                }

                foreach ($context as $n) {
                        if ($n instanceof \DOMDocument) {
                                $this->dom     = $n;
                                $this->nodes[] = $n;
                        } else if ($n instanceof \DOMNode) {
                                $this->dom     = $n->ownerDocument;
                                $this->nodes[] = $n;
                        } else if ($n instanceof \DOMNodeList) {
                                $this->dom   = $n[0]->ownerDocument;
                                $this->nodes = \iterator_to_array($n);
                        } else if ($n instanceof FluidContext) {
                                $this->dom   = $n[0]->ownerDocument;
                                $this->nodes = \array_merge($this->nodes, $n->asArray());
                        } else {
                                throw new \Exception('Node type not recognized.');
                        }
                }

                if (! empty($namespaces)) {
                        $this->namespace(...\array_values($namespaces));
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

                $domxp = new \DOMXPath($this->dom);

                foreach ($this->namespaces as $n) {
                        $domxp->registerNamespace($n->id(), $n->uri());
                }

                $results = [];

                foreach ($this->nodes as $n) {
                        foreach ($xpaths as $x) {
                                // Returns a DOMNodeList.
                                $res = $domxp->query($x, $n);

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
                        return new FluidRepeater($this, $times);
                }

                for ($i = 0; $i < $times; ++$i) {
                        \call_user_func($fn, $this, $i);
                }

                return $this;
        }

        public function each(callable $fn)
        {
                foreach ($this->nodes as $k => $n) {
                        \call_user_func($fn, $this->newContext($n), $n, $k);
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
                return new FluidContext($context, $this->namespaces);
        }

        protected function insertElement($element, array $optionals, callable $fn)
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

                $new_context = [];

                foreach ($this->nodes as $n) {
                        foreach ($element as $k => $v) {
                                // I give up, it's a too complex job for only one method like me.
                                $cx = InsertionHandler::insert($n, $k, $v, $optionals, $fn, $this->dom, $this->namespaces);

                                $new_context = \array_merge($new_context, $cx);
                        }
                }

                $context = $this->newContext($new_context);

                // Setting the attributes is an help that the appendChild method
                // offers to the user and is the same of:
                // 1. appending a child switching the context
                // 2. setting the attributes over the new context.
                if (! empty($attributes)) {
                        $context->setAttribute($attributes);
                }

                if ($switch_context) {
                        return $context;
                }

                return $this;
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

class InsertionHandler
{
        private $parent;
        private $key;
        private $val;
        private $optionals;
        private $insert;

        private $dom;
        private $namespaces;

        public static function insert(...$arguments)
        {
                $handler = new static(...$arguments);

                return $handler();
        }

        // TODO
        // remove the dependency from namespaces and dom.
        public function __construct(\DOMNode $parent, &$key, &$val, array &$optionals, callable $insert, \DOMDocument $dom, array &$namespaces)
        {
                $this->parent    = $parent;
                $this->key       = $key;
                $this->val       = $val;
                $this->optionals = $optionals;
                $this->insert    = $insert;
                $this->dom       = $dom;
                $this->namespaces= $namespaces;
        }

        public function __invoke()
        {
                $check_sequence = [ 'specialContentHandler',
                                    'specialAttributeHandler',
                                    'stringStringHandler',
                                    'stringMixedHandler',
                                    'integerArrayHandler',
                                    'integerStringNotXmlHandler',
                                    'integerXmlHandler',
                                    'integerDomdocumentHandler',
                                    'integerDomnodelistHandler',
                                    'integerDomnodeHandler',
                                    'integerSimplexmlHandler',
                                    'integerFluidxmlHandler',
                                    'integerFluidcontextHandler' ];

                foreach ($check_sequence as $check) {
                        $ret = $this->$check();

                        if ($ret !== false) {
                                return $ret;
                        }
                }

                throw new \Exception('XML document not supported.');
        }

        protected function createElement($name, $value = null)
        {
                // The DOMElement instance must be different for every node,
                // otherwise only one element is attached to the DOM.

                $uri = null;

                // The node name can contain the namespace id prefix.
                // Example: xsl:template
                $name_parts = \explode(':', $name, 2);

                $name = \array_pop($name_parts);
                $id   = \array_pop($name_parts);

                if ($id) {
                        $ns  = $this->namespaces[$id];
                        $uri = $ns->uri();

                        if ($ns->mode() === FluidNamespace::MODE_EXPLICIT) {
                                $name = "{$id}:{$name}";
                        }
                }

                // Algorithm 1:
                $el = new \DOMElement($name, $value, $uri);

                // Algorithm 2:
                // $el = $this->dom->createElement($name, $value);

                return $el;
        }

        protected function attachNodes($nodes)
        {
                if (! \is_array($nodes) && ! $nodes instanceof \Traversable) {
                        $nodes = [ $nodes ];
                }

                $context = [];

                foreach ($nodes as $el) {
                        $el        = $this->dom->importNode($el, true);
                        $context[] = $this->insert->__invoke($this->parent, $el);
                }

                return $context;
        }

        protected function newContext($context)
        {
                return new FluidContext($context, $this->namespaces);
        }

        protected function specialContentHandler()
        {
                $k = $this->key;
                $v = $this->val;

                if (! \is_string($k) || $k !== '@'|| ! \is_string($v)) {
                        return false;
                }

                // The user has passed an element text content:
                // [ '@' => 'Element content.' ]

                // Algorithm 1:
                $this->newContext($this->parent)->appendText($v);

                // Algorithm 2:
                // $this->setText($v);

                // The user can specify multiple '@' special elements
                // so Algorithm 1 is the right choice.

                return [];
        }

        protected function specialAttributeHandler()
        {
                $k = $this->key;
                $v = $this->val;

                if (! \is_string($k) || $k[0] !== '@' || ! \is_string($v)) {
                        return false;
                }

                // The user has passed an attribute name and an attribute value:
                // [ '@attribute' => 'Attribute content' ]

                $attr = \substr($k, 1);
                $this->newContext($this->parent)->setAttribute($attr, $v);

                return [];
        }

        protected function stringStringHandler()
        {
                $k = $this->key;
                $v = $this->val;

                if (! \is_string($k) || ! \is_string($v)) {
                        return false;
                }

                // The user has passed an element name and an element value:
                // [ 'element' => 'Element content' ]

                $el = $this->createElement($k, $v);
                $el = \call_user_func($this->insert, $this->parent, $el);

                return [ $el ];
        }

        protected function stringMixedHandler()
        {
                $k = $this->key;
                $v = $this->val;

                if (! \is_string($k) || \is_string($v)) {
                        return false;
                }

                // The user has passed one of these two cases:
                // - [ 'element' => [...] ]
                // - [ 'element' => DOMNode|SimpleXMLElement|FluidXml ]

                $el = $this->createElement($k);
                $el = \call_user_func($this->insert, $this->parent, $el);

                // The new children elements must be created in the order
                // they are supplied, so 'appendChild' is the perfect operation.
                $this->newContext($el)->appendChild($v, ...$this->optionals);

                return [ $el ];
        }

        protected function integerArrayHandler()
        {
                $k = $this->key;
                $v = $this->val;

                if (! \is_int($k) || ! \is_array($v)) {
                        return false;
                }

                // The user has passed a wrapper array:
                // [ [...], ... ]

                $context = [];

                foreach ($v as $kk => $vv) {
                        $cx = InsertionHandler::insert($this->parent, $kk, $vv, $this->optionals, $this->insert, $this->dom, $this->namespaces);

                        $context = \array_merge($context, $cx);
                }

                return $context;
        }

        protected function integerStringNotXmlHandler()
        {
                $k = $this->key;
                $v = $this->val;

                if (! \is_int($k) || ! \is_string($v) || is_an_xml_string($v)) {
                        return false;
                }

                // The user has passed a node name without a node value:
                // [ 'element', ... ]

                $el = $this->createElement($v);
                $el = \call_user_func($this->insert, $this->parent, $el);

                return [ $el ];
        }

        protected function integerXmlHandler()
        {
                $k = $this->key;
                $v = $this->val;

                if (! \is_int($k) || ! is_an_xml_string($v)) {
                        return false;
                }

                // The user has passed an XML document instance:
                // [ '<tag></tag>', DOMNode, SimpleXMLElement, FluidXml ]

                $nodes = [];

                $dom = new \DOMDocument();
                $dom->formatOutput       = true;
                $dom->preserveWhiteSpace = false;

                $v = \ltrim($v);
                if ($v[1] === '?') {
                        $dom->loadXML($v);
                        $nodes = $dom->childNodes;
                } else {
                        // A way to import strings with multiple root nodes.
                        $dom->loadXML("<root>$v</root>");

                        // Algorithm 1:
                        $nodes = $dom->documentElement->childNodes;

                        // Algorithm 2:
                        // $dom_xp = new \DOMXPath($dom);
                        // $nodes = $dom_xp->query('/root/*');
                }

                return $this->attachNodes($nodes);
        }

        protected function integerDomdocumentHandler()
        {
                $k = $this->key;
                $v = $this->val;

                if (! \is_int($k) || ! $v instanceof \DOMDocument) {
                        return false;
                }

                // A DOMDocument can have multiple root nodes.

                // Algorithm 1:
                return $this->attachNodes($v->childNodes);

                // Algorithm 2:
                // return $this->attachNodes($v->documentElement);
        }

        protected function integerDomnodelistHandler()
        {
                $k = $this->key;
                $v = $this->val;

                if (! \is_int($k) || ! $v instanceof \DOMNodeList) {
                        return false;
                }

                return $this->attachNodes($v);
        }

        protected function integerDomnodeHandler()
        {
                $k = $this->key;
                $v = $this->val;

                if (! \is_int($k) || ! $v instanceof \DOMNode) {
                        return false;
                }

                return $this->attachNodes($v);
        }

        protected function integerSimplexmlHandler()
        {
                $k = $this->key;
                $v = $this->val;

                if (! \is_int($k) || ! $v instanceof \SimpleXMLElement) {
                        return false;
                }

                return $this->attachNodes(\dom_import_simplexml($v));
        }

        protected function integerFluidxmlHandler()
        {
                $k = $this->key;
                $v = $this->val;

                if (! \is_int($k) || ! $v instanceof FluidXml) {
                        return false;
                }

                return $this->attachNodes($v->dom()->documentElement);
        }

        protected function integerFluidcontextHandler()
        {
                $k = $this->key;
                $v = $this->val;

                if (! \is_int($k) || ! $v instanceof FluidContext) {
                        return false;
                }

                return $this->attachNodes($v->asArray());
        }
}

class FluidRepeater
{
        private $context;
        private $times;

        public function __construct($context, $times)
        {
                $this->context = $context;
                $this->times   = $times;
        }

        public function __call($method, $arguments)
        {
                $new_context = [];

                for ($i = 0, $l = $this->times; $i < $l; ++$i) {
                        $new_context[] = $this->context->$method(...$arguments);
                }

                if ($new_context[0] !== $this->context) {
                        return new FluidContext($new_context, $this->context->namespaces());
                }

                return $this->context;
        }
}
