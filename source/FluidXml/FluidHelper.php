<?php

namespace FluidXml;

class FluidHelper
{
        public static function isAnXmlString($string)
        {
                if (is_null($string)) {
                        $string = '';
                }
                // Removes any empty new line at the beginning,
                // otherwise the first character check may fail.
                $string = \ltrim($string);

                return $string && $string[0] === '<';
        }

        public static function exportNode(\DOMDocument $dom, \DOMNode $node, $html = false)
        {
                // $delegate = $html ? 'saveHTML' : 'saveXML';
                // return $dom->$delegate($node);

                if ($html) {
                        return static::domnodeToHtml($node);
                }

                return $dom->saveXML($node);
        }

        public static function domdocumentToStringWithoutHeaders(\DOMDocument $dom, $html = false)
        {
                return static::exportNode($dom, $dom->documentElement, $html);
        }

        public static function domnodelistToString(\DOMNodeList $nodelist, $html = false)
        {
                $nodes = [];

                // Algorithm 1:
                foreach ($nodelist as $n) {
                        $nodes[] = $n;
                }

                // Algorithm 2:
                // $nodes = \iterator_to_array($nodelist);

                // Algorithm 1 is faster than Algorithm 2.

                return static::domnodesToString($nodes, $html);
        }

        public static function domnodesToString(array $nodes, $html = false)
        {
                $dom = $nodes[0]->ownerDocument;
                $xml = '';

                foreach ($nodes as $n) {
                        $xml .= static::exportNode($dom, $n, $html) . PHP_EOL;
                }

                return \rtrim($xml);
        }

        public static function simplexmlToStringWithoutHeaders(\SimpleXMLElement $element, $html = false)
        {
                $dom = \dom_import_simplexml($element);

                return static::exportNode($dom->ownerDocument, $dom, $html);
        }

        public static function domdocumentToHtml($dom, $clone = true)
        {
                if ($clone) {
                        $dom = $dom->cloneNode(true);
                }

                $voids = ['area',
                          'base',
                          'br',
                          'col',
                          'colgroup',
                          'command',
                          'embed',
                          'hr',
                          'img',
                          'input',
                          'keygen',
                          'link',
                          'meta',
                          'param',
                          'source',
                          'track',
                          'wbr'];

                // Every empty node. There is no reason to match nodes with content inside.
                $query = '//*[not(node())]';
                $nodes = (new \DOMXPath($dom))->query($query);

                foreach ($nodes as $n) {
                        if (! \in_array($n->nodeName, $voids)) {
                                // If it is not a void/empty tag,
                                // we need to leave the tag open.
                                $n->appendChild(new \DOMProcessingInstruction('X-NOT-VOID'));
                        }
                }

                $html = static::domdocumentToStringWithoutHeaders($dom);

                // Let's remove the placeholder.
                $html = \preg_replace('/\s*<\?X-NOT-VOID\?>\s*/', '', $html);

                return $html;
        }

        public static function domnodeToHtml(\DOMNode $node)
        {
                $dom = new \DOMDocument();
                $dom->formatOutput       = true;
                $dom->preserveWhiteSpace = false;
                $node = $dom->importNode($node, true);
                $dom->appendChild($node);

                return static::domdocumentToHtml($dom, false);
        }
}
