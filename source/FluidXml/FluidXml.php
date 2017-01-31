<?php

namespace FluidXml;

/**
 * @method array array()
 * @method FluidXml namespace(...$arguments)
 */
class FluidXml implements FluidInterface
{
        use FluidAliasesTrait,
            FluidSaveTrait,
            NewableTrait,
            ReservedCallTrait,          // For compatibility with PHP 5.6.
            ReservedCallStaticTrait;    // For compatibility with PHP 5.6.

        const ROOT_NODE = 'doc';

        private $defaults = [ 'root'       => self::ROOT_NODE,
                              'version'    => '1.0',
                              'encoding'   => 'UTF-8',
                              'stylesheet' => null ];

        private $document;
        private $handler;
        private $context;
        private $contextEl;

        public static function load($document)
        {
                $file     = $document;
                $document = \file_get_contents($file);

                // file_get_contents() returns false in case of error.
                if (! $document) {
                        throw new \Exception("File '$file' not accessible.");
                }

                return (new FluidXml(null))->addChild($document);
        }

        public function __construct(...$arguments)
        {
                // First, we parse the arguments detecting the options provided.
                // This options are needed to build the DOM, add the stylesheet
                // and to create the document root/structure.
                $options = $this->mergeOptions($arguments);

                // Having the options set, we can build the FluidDocument model
                // which incapsulates the DOM and the corresponding XPath instance.
                $document = new FluidDocument();
                $document->dom   = $this->newDom($options);
                $document->xpath = new \DOMXPath($document->dom);

                // After the FluidDocument model creation, we can proceed to build
                // the FluidInsertionHandler which requires the model to perform
                // its logics.
                $handler = new FluidInsertionHandler($document);

                // Ok, it's time to let them beeing visible along the instance.
                $this->document = $document;
                $this->handler  = $handler;

                // Now, we can further populate the DOM with any stylesheet or child.
                $this->initStylesheet($options)
                     ->initRoot($options);
        }

        protected function mergeOptions(&$arguments)
        {
                $options = $this->defaults;

                if (\count($arguments) > 0) {
                        // The root option can be specified as first argument
                        // because it is the most common.
                        $options['root'] = $arguments[0];
                }

                if (\count($arguments) > 1) {
                        // Custom options can be specified only as second argument,
                        // to avoid confusion with array to XML construction style.
                        $options = \array_merge($options, $arguments[1]);
                }

                return $options;
        }

        private function newDom(&$options)
        {
                $dom = new \DOMDocument($options['version'], $options['encoding']);
                $dom->formatOutput       = true;
                $dom->preserveWhiteSpace = false;

                return $dom;
        }

        private function initStylesheet(&$options)
        {
                if (! empty($options['stylesheet'])) {
                        $attrs = 'type="text/xsl" '
                               . "encoding=\"{$options['encoding']}\" "
                               . 'indent="yes" '
                               . "href=\"{$options['stylesheet']}\"";

                        $stylesheet = new \DOMProcessingInstruction('xml-stylesheet', $attrs);

                        $this->addChild($stylesheet);

                        // Algorithm 2:
                        // Used in case the order of the stylesheet and root creation is reversed.
                        // $this->document->dom->insertBefore($stylesheet, $this->document->dom->documentElement);
                }

                return $this;
        }

        private function initRoot(&$options)
        {
                if (! empty($options['root'])) {
                        $this->appendSibling($options['root']);
                }

                return $this;
        }

        public function length()
        {
                return \count($this->array());
        }

        public function dom()
        {
                return $this->document->dom;
        }

        // This method should be called 'array',
        // but for compatibility with PHP 5.6
        // it is shadowed by the __call() method.
        public function array_()
        {
                $el = $this->document->dom->documentElement;

                if ($el === null) {
                        $el = $this->document->dom;
                }

                return [ $el ];
        }

        public function __toString()
        {
                return $this->xml();
        }

        public function xml($strip = false)
        {
                if ($strip) {
                        return FluidHelper::domdocumentToStringWithoutHeaders($this->document->dom);
                }

                return $this->document->dom->saveXML();
        }

        public function html($strip = false)
        {
                $header = "<!DOCTYPE html>\n";

                if ($strip) {
                        $header = '';
                }

                $html = FluidHelper::domdocumentToStringWithoutHeaders($this->document->dom, true);

                return "{$header}{$html}";
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
                } else {
                        $namespaces = $arguments;
                }

                foreach ($namespaces as $n) {
                        $this->document->namespaces[$n->id()] = $n;
                        $this->document->xpath->registerNamespace($n->id(), $n->uri());
                }

                return $this;
        }

        public function query(...$query)                   { return $this->context()->query(...$query); }
        public function times($times, callable $fn = null) { return $this->context()->times($times, $fn); }
        public function each(callable $fn)                 { return $this->context()->each($fn); }
        public function map(callable $fn)                  { return $this->context()->map($fn); }
        public function filter(callable $fn)               { return $this->context()->filter($fn); }
        public function setAttribute($name, $value = null) { $this->context()->setAttribute($name, $value); return $this; }
        public function setText($text)                     { $this->context()->setText($text);    return $this; }
        public function addText($text)                     { $this->context()->addText($text);    return $this; }
        public function setCdata($text)                    { $this->context()->setCdata($text);   return $this; }
        public function addCdata($text)                    { $this->context()->addCdata($text);   return $this; }
        public function setComment($text)                  { $this->context()->setComment($text); return $this; }
        public function addComment($text)                  { $this->context()->addComment($text); return $this; }
        public function remove(...$query)                  { $this->context()->remove(...$query); return $this; }

        public function addChild($child, ...$optionals)
        {
                return $this->chooseContext(function($cx) use ($child, &$optionals) {
                        return $cx->addChild($child, ...$optionals);
                });
        }

        public function prependSibling($sibling, ...$optionals)
        {
                return $this->chooseContext(function($cx) use ($sibling, &$optionals) {
                        return $cx->prependSibling($sibling, ...$optionals);
                });
        }

        public function appendSibling($sibling, ...$optionals)
        {
                return $this->chooseContext(function($cx) use ($sibling, &$optionals) {
                        return $cx->appendSibling($sibling, ...$optionals);
                });
        }

        protected function context()
        {
                $el = $this->document->dom->documentElement;

                if ($el === null) {
                        // Whether there is not a root node
                        // the DOMDocument is promoted as root node.
                        $el = $this->document->dom;
                }

                if ($this->context === null || $el !== $this->contextEl) {
                        // The user can prepend a root node to the current root node.
                        // In this case we have to update the context with the new first root node.
                        $this->context   = new FluidContext($this->document, $this->handler, $el);
                        $this->contextEl = $el;
                }

                return $this->context;
        }

        protected function chooseContext(\Closure $fn)
        {
                // If the user has requested ['root' => null] at construction time
                // 'context()' promotes DOMDocument as root node.

                $context     = $this->context();
                $new_context = $fn($context);

                if ($context !== $new_context) {
                        // If the two contextes are diffent, the user has requested
                        // a switch of the context and we have to return it.
                        return $new_context;
                }

                return $this;
        }
}
