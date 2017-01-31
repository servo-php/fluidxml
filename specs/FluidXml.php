<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . '.common.php';

use \FluidXml\FluidXml;
use \FluidXml\FluidNamespace;
use \FluidXml\FluidHelper;
use \FluidXml\FluidContext;
use \FluidXml\FluidDocument;
use \FluidXml\FluidInsertionHandler;
use \FluidXml\FluidRepeater;
use const \FluidXml\FLUIDXML_VERSION;
use function \FluidXml\fluidxml;
use function \FluidXml\fluidns;
use function \FluidXml\fluidify;


describe('FLUIDXML_VERSION', function () {
        it('should be defined', function () {
                // Require for the codecoverage analysis.
                require \dirname(__DIR__).'/source/FluidXml.php';

                $actual   = \defined('FLUIDXML_VERSION');
                $expected = true;
                \assert($actual === $expected, __($actual, $expected));
        });
});

describe('fluidxml()', function () {
        it('should behave like FluidXml::__construct()', function () {
                $xml   = new FluidXml();
                $alias = fluidxml();

                $actual   = $alias->xml();
                $expected = $xml->xml();
                \assert($actual === $expected, __($actual, $expected));

                $options = [ 'root'       => 'root',
                             'version'    => '1.2',
                             'encoding'   => 'UTF-16',
                             'stylesheet' => 'stylesheet.xsl' ];

                $xml   = new FluidXml(null, $options);
                $alias = fluidxml(null, $options);

                $actual   = $alias->xml();
                $expected = $xml->xml();
                \assert($actual === $expected, __($actual, $expected));
        });
});

describe('fluidify()', function () {
        it('should behave like FluidXml::load()', function () {
                $ds = \DIRECTORY_SEPARATOR;
                $file = __DIR__ . "{$ds}..{$ds}sandbox{$ds}.test_fluidify.xml";
                $doc = "<root>\n"
                     . "  <parent>content</parent>\n"
                     . "</root>";

                \file_put_contents($file, $doc);
                $xml   = FluidXml::load($file);
                $alias = fluidify($file);
                \unlink($file);

                $actual   = $alias->xml();
                $expected = $xml->xml();
                \assert($actual === $expected, __($actual, $expected));
        });
});

describe('fluidns()', function () {
        it('should behave like FluidNamespace::__construct()', function () {
                $ns    = new FluidNamespace('x', 'x.com');
                $alias = fluidns('x', 'x.com');

                $actual   = $ns->id();
                $expected = $alias->id();
                \assert($actual === $expected, __($actual, $expected));

                $actual   = $ns->uri();
                $expected = $alias->uri();
                \assert($actual === $expected, __($actual, $expected));

                $actual   = $ns->mode();
                $expected = $alias->mode();
                \assert($actual === $expected, __($actual, $expected));

                $ns    = new FluidNamespace('x', 'x.com', FluidNamespace::MODE_IMPLICIT);
                $alias = fluidns('x', 'x.com', FluidNamespace::MODE_IMPLICIT);

                $actual   = $ns->id();
                $expected = $alias->id();
                \assert($actual === $expected, __($actual, $expected));

                $actual   = $ns->uri();
                $expected = $alias->uri();
                \assert($actual === $expected, __($actual, $expected));

                $actual   = $ns->mode();
                $expected = $alias->mode();
                \assert($actual === $expected, __($actual, $expected));
        });
});

describe('FluidXml', function () {
        $ds = \DIRECTORY_SEPARATOR;
        $this->out_dir = __DIR__ . "{$ds}..{$ds}sandbox{$ds}";

        it('should throw invoking not existing staic method', function () {
                try {
                        FluidXml::lload();
                } catch (\Exception $e) {
                        $actual = $e;
                }

                assert_is_a($actual, \Exception::class);
        });

        describe(':load()', function () {
                it('should import an XML file', function () {
                        $file = "{$this->out_dir}.test_load.xml";
                        $doc = "<root>\n"
                                . "  <parent>content</parent>\n"
                                . "</root>";

                        \file_put_contents($file, $doc);
                        $xml = FluidXml::load($file);
                        \unlink($file);

                        $expected = $doc;
                        assert_equal_xml($xml, $expected);
                });

                it('should throw for not existing file', function () {
                        $err_handler = \set_error_handler(function () {});
                        try {
                                $xml = FluidXml::load('.impossible.xml');
                        } catch (\Exception $e) {
                                $actual = $e;
                        }
                        \set_error_handler($err_handler);

                        assert_is_a($actual, \Exception::class);
                });
        });

        if (\version_compare(\phpversion(), '7', '>=')) {
        describe(':new()', function () {
                it('should behave like FluidXml::__construct()', function () {
                        $xml   = new FluidXml();
                        eval('$alias = \FluidXml\FluidXml::new();');

                        $actual   = $alias->xml();
                        $expected = $xml->xml();
                        \assert($actual === $expected, __($actual, $expected));

                        $options = [ 'root'       => 'root',
                                     'version'    => '1.2',
                                     'encoding'   => 'UTF-16',
                                     'stylesheet' => 'stylesheet.xsl' ];

                        $xml   = new FluidXml($options);
                        eval('$alias = \FluidXml\FluidXml::new($options);');

                        $actual   = $alias->xml();
                        $expected = $xml->xml();
                        \assert($actual === $expected, __($actual, $expected));
                });
        });
        }

        describe('.__construct()', function () {
                $doc = "<root>\n"
                     . "  <parent>content</parent>\n"
                     . "</root>";
                $dom = new \DOMDocument();
                $dom->loadXML($doc);
                $stylesheet = "<?xml-stylesheet type=\"text/xsl\" "
                                              . "encoding=\"UTF-8\" "
                                              . "indent=\"yes\" "
                                              . "href=\"http://servo-php.org/fluidxml\"?>";

                it('should create an UTF-8 XML-1.0 document with one default root element', function () {
                        $xml = new FluidXml();

                        $expected = "<doc/>";
                        assert_equal_xml($xml, $expected);
                });

                it('should create an UTF-8 XML-1.0 document with one custom root element as first or second argument', function () {
                        $xml = new FluidXml('document');

                        $expected = "<document/>";
                        assert_equal_xml($xml, $expected);

                        $xml = new FluidXml(null, ['root' => 'document']);

                        assert_equal_xml($xml, $expected);
                });

                it('should create an UTF-8 XML-1.0 document with no root element as first or second argument', function () {
                        $xml = new FluidXml(null);

                        $expected = "";
                        assert_equal_xml($xml, $expected);

                        $xml = new FluidXml(null, ['root' => null]);
                        assert_equal_xml($xml, $expected);

                        $xml = new FluidXml('doc', ['root' => null]);
                        assert_equal_xml($xml, $expected);
                });

                it('should create an UTF-8 XML-1.0 document with a stylesheet and a root element', function () use ($stylesheet) {
                        $xml = new FluidXml('doc', ['stylesheet' => 'http://servo-php.org/fluidxml']);

                        $expected = $stylesheet . "\n"
                                  . "<doc/>";
                        assert_equal_xml($xml, $expected);
                });

                it('should create an UTF-8 XML-1.0 document with a stylesheet and no root element', function () use ($stylesheet) {
                        $xml = new FluidXml(null, ['stylesheet' => 'http://servo-php.org/fluidxml']);

                        $expected = $stylesheet;
                        assert_equal_xml($xml, $expected);
                });

                it('should import an XML string', function () use ($doc, $dom) {
                        $exp = $dom->saveXML();
                        // This $exp has the XML header.

                        // The first empty line is used to test the trim of the string.
                        $xml = new FluidXml("\n " . $exp);

                        $expected = $doc;
                        assert_equal_xml($xml, $expected);

                        // This $exp is deprived of the XML header.
                        $xml = new FluidXml("\n " . \substr($exp, \strpos($exp, "\n") + 1));

                        $expected = $doc;
                        assert_equal_xml($xml, $expected);
                });

                it('should import an array of elements with the @ syntax', function () {
                        $xml = new FluidXml(['root' => [ 'child1' => [ '@id' => 1 ],
                                                         'child2' => 'Text 2'        ] ]);

                        $expected = "<root>\n"
                                  . "  <child1 id=\"1\"/>\n"
                                  . "  <child2>Text 2</child2>\n"
                                  . "</root>";
                        assert_equal_xml($xml, $expected);
                });

                it('should import a DOMDocument', function () use ($doc, $dom) {
                        $xml = new FluidXml($dom);

                        $expected = $doc;
                        assert_equal_xml($xml, $expected);
                });

                it('should import a DOMNode', function () use ($dom) {
                        $domxp = new \DOMXPath($dom);
                        $nodes = $domxp->query('/root/parent');
                        $xml = new FluidXml($nodes[0]);

                        $expected = "<parent>content</parent>";
                        assert_equal_xml($xml, $expected);
                });

                it('should import a DOMNodeList', function () use ($dom) {
                        $domxp = new \DOMXPath($dom);
                        $nodes = $domxp->query('/root/parent');
                        $xml = new FluidXml($nodes);

                        $expected = "<parent>content</parent>";
                        assert_equal_xml($xml, $expected);
                });

                it('should import a SimpleXMLElement', function () use ($doc, $dom) {
                        $xml = new FluidXml(\simplexml_import_dom($dom));

                        $expected = $doc;
                        assert_equal_xml($xml, $expected);
                });

                it('should import a FluidXml', function () use ($doc) {
                        $xml = new FluidXml(new FluidXml($doc));

                        $expected = $doc;
                        assert_equal_xml($xml, $expected);
                });

                it('should import a FluidContext', function () use ($doc) {
                        $cx  = (new FluidXml($doc))->query('/root');
                        $xml = new FluidXml($cx);

                        $expected = $doc;
                        assert_equal_xml($xml, $expected);
                });

                it('should throw for not supported documents', function () {
                        try {
                                $xml = new FluidXml(1);
                        } catch (\Exception $e) {
                                $actual = $e;
                        }

                        assert_is_a($actual, \Exception::class);
                });

                it('should throw invoking not existing method', function () {
                        $xml = new FluidXml();
                        try {
                                $xml->qquery();
                        } catch (\Exception $e) {
                                $actual = $e;
                        }

                        assert_is_a($actual, \Exception::class);
                });
        });

        describe('.namespace()', function () {
                it('should be fluid', function () {
                        assert_is_fluid('namespace', 'a', 'b');
                });

                it('should accept a namespace', function () {
                        $xml   = new FluidXml();
                        $x_ns  = new FluidNamespace('x', 'x.com');
                        $xx_ns = fluidns('xx', 'xx.com', FluidNamespace::MODE_IMPLICIT);
                        $nss = $xml->namespace($x_ns)
                                   ->namespace($xx_ns)
                                   ->namespaces();

                        $actual   = $nss[$x_ns->id()];
                        $expected = $x_ns;
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $nss[$xx_ns->id()];
                        $expected = $xx_ns;
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should accept an id, an uri and an optional mode flag', function () {
                        $xml = new FluidXml();

                        $nss = $xml->namespace('x', 'x.com')
                                   ->namespace('xx', 'xx.com', FluidNamespace::MODE_IMPLICIT)
                                   ->namespaces();

                        $actual   = $nss['x']->uri();
                        $expected = 'x.com';
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $nss['x']->mode();
                        $expected = FluidNamespace::MODE_EXPLICIT;
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $nss['xx']->uri();
                        $expected = 'xx.com';
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $nss['xx']->mode();
                        $expected = FluidNamespace::MODE_IMPLICIT;
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should accept variable namespaces arguments', function () {
                        $xml   = new FluidXml();
                        $x_ns  = new FluidNamespace('x', 'x.com');
                        $xx_ns = fluidns('xx', 'xx.com', FluidNamespace::MODE_IMPLICIT);

                        $nss = $xml->namespace($x_ns, $xx_ns)
                                   ->namespaces();

                        $actual   = $nss[$x_ns->id()];
                        $expected = $x_ns;
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $nss[$xx_ns->id()];
                        $expected = $xx_ns;
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.query()', function () {
                it('should be fluid', function () {
                        assert_is_fluid('query', '.');
                });

                it('should accept a query that return the root nodes of the document (XPath)', function () {
                        $xml = new FluidXml();
                        $cx = $xml->query('/*');

                        $actual   = $cx[0]->nodeName;
                        $expected = 'doc';
                        \assert($actual === $expected, __($actual, $expected));

                        $xml->appendSibling('meta');
                        $cx = $xml->query('/*');

                        $actual   = $cx[0]->nodeName;
                        $expected = 'doc';
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $cx[1]->nodeName;
                        $expected = 'meta';
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should accept a query that return the root nodes of the document (CSS)', function () {
                        $xml = new FluidXml();
                        $cx = $xml->query(':root');

                        $actual   = $cx[0]->nodeName;
                        $expected = 'doc';
                        \assert($actual === $expected, __($actual, $expected));

                        $xml->appendSibling('meta');
                        $cx = $xml->query(':root');

                        $actual   = $cx[0]->nodeName;
                        $expected = 'doc';
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $cx[1]->nodeName;
                        $expected = 'meta';
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should accept an array of queries (XPath)', function () {
                        $xml = new FluidXml();
                        $xml->addChild('html', true)
                            ->addChild(['head','body'])
                            ->query(['//html', 'head', '//body'])
                            ->setAttribute('lang', 'en');

                        $expected = "<doc>\n"
                                  . "  <html lang=\"en\">\n"
                                  . "    <head lang=\"en\"/>\n"
                                  . "    <body lang=\"en\"/>\n"
                                  . "  </html>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should accept an array of queries (XPath and CSS)', function () {
                        $xml = new FluidXml();
                        $xml->addChild('html', true)
                            ->addChild(['head','body'])
                            ->query(['//html', 'head', '//body'])
                            ->setAttribute('lang', 'en');

                        $expected = "<doc>\n"
                                  . "  <html lang=\"en\">\n"
                                  . "    <head lang=\"en\"/>\n"
                                  . "    <body lang=\"en\"/>\n"
                                  . "  </html>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should accept a variable number of queries (XPath and CSS)', function () {
                        $xml = new FluidXml();
                        $xml->addChild('html', true)
                            ->addChild(['head','body'])
                            ->query('//html', 'head', '//body')
                            ->setAttribute('lang', 'en');

                        $expected = "<doc>\n"
                                  . "  <html lang=\"en\">\n"
                                  . "    <head lang=\"en\"/>\n"
                                  . "    <body lang=\"en\"/>\n"
                                  . "  </html>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should support relative queries (XPath)', function () {
                        $xml = new FluidXml();
                        $cx = $xml->addChild('html', true)
                                  ->addChild(['head','body'])
                                  ->query('./body');

                        $actual   = $cx[0]->nodeName;
                        $expected = 'body';
                        \assert($actual === $expected, __($actual, $expected));

                        $xml = new FluidXml();
                        $xml->addChild('html', true)->addChild(['head','body']);
                        $cx = $xml->query('/doc/html')->query('./head');

                        $actual   = $cx[0]->nodeName;
                        $expected = 'head';
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should query the root of the document from a sub query (XPath)', function () {
                        $xml = new FluidXml();
                        $xml->addChild('html', true)
                            ->addChild(['head','body']);
                        $cx = $xml->query('/doc/html/body')
                                  ->addChild('h1')
                                  ->query('/doc/html/head');

                        $actual   = $cx[0]->nodeName;
                        $expected = 'head';
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should query the root of the document from a sub query (CSS)', function () {
                        $xml = new FluidXml();
                        $xml->addChild('html', true)
                            ->addChild(['head','body']);
                        $cx = $xml->query('body')
                                  ->addChild('h1')
                                  ->query(':root head');

                        $actual   = $cx[0]->nodeName;
                        $expected = 'head';
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should perform relative queries (XPath) ascending the DOM tree', function () {
                        $xml = new FluidXml();
                        $xml->addChild('html', true)
                            ->addChild(['head','body'], true)
                            ->query('../body')
                            ->addChild('h1')
                            ->query('../..')
                            ->addChild('extra');

                        $expected = "<doc>\n"
                                  . "  <html>\n"
                                  . "    <head/>\n"
                                  . "    <body>\n"
                                  . "      <h1/>\n"
                                  . "    </body>\n"
                                  . "  </html>\n"
                                  . "  <extra/>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should query namespaced nodes (XPath)', function () {
                        $xml   = new FluidXml();
                        $x_ns  = new FluidNamespace('x', 'x.com');
                        $xx_ns = fluidns('xx', 'xx.com', FluidNamespace::MODE_IMPLICIT);

                        $xml->namespace($x_ns, $xx_ns);

                        $xml->addChild('x:a',  true)
                            ->addChild('x:b',  true)
                            ->addChild('xx:c', true)
                            ->addChild('xx:d', true)
                            ->addChild('e',    true)
                            ->addChild('x:f',  true)
                            ->addChild('g');

                        $r = $xml->query('/doc/a');

                        $actual   = $r->length();
                        $expected = 0;
                        \assert($actual === $expected, __($actual, $expected));

                        $r = $xml->query('/doc/x:a');

                        $actual   = $r[0]->nodeName;
                        $expected = 'x:a';
                        \assert($actual === $expected, __($actual, $expected));

                        $r = $xml->query('/doc/x:a/x:b');

                        $actual   = $r[0]->nodeName;
                        $expected = 'x:b';
                        \assert($actual === $expected, __($actual, $expected));

                        $r = $xml->query('/doc/x:a/x:b/c');

                        $actual   = $r->length();
                        $expected = 0;
                        \assert($actual === $expected, __($actual, $expected));

                        $r = $xml->query('/doc/x:a/x:b/xx:c');

                        $actual   = $r[0]->nodeName;
                        $expected = 'c';
                        \assert($actual === $expected, __($actual, $expected));

                        $r = $xml->query('/doc/x:a/x:b/xx:c/xx:d');

                        $actual   = $r[0]->nodeName;
                        $expected = 'd';
                        \assert($actual === $expected, __($actual, $expected));

                        $r = $xml->query('/doc/x:a/x:b/xx:c/xx:d/e');

                        $actual   = $r[0]->nodeName;
                        $expected = 'e';
                        \assert($actual === $expected, __($actual, $expected));

                        $r = $xml->query('/doc/x:a/x:b/xx:c/xx:d/e/f');

                        $actual   = $r->length();
                        $expected = 0;
                        \assert($actual === $expected, __($actual, $expected));

                        $r = $xml->query('/doc/x:a/x:b/xx:c/xx:d/e/x:f');

                        $actual   = $r[0]->nodeName;
                        $expected = 'x:f';
                        \assert($actual === $expected, __($actual, $expected));

                        $r = $xml->query('/doc/x:a/x:b/xx:c/xx:d/e/x:f/g');

                        $actual   = $r[0]->nodeName;
                        $expected = 'g';
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should query namespaced nodes (CSS)', function () {
                        $xml   = new FluidXml();
                        $x_ns  = new FluidNamespace('x', 'x.com');
                        $xx_ns = fluidns('xx', 'xx.com', FluidNamespace::MODE_IMPLICIT);

                        $xml->namespace($x_ns, $xx_ns);

                        $xml->addChild('x:a',  true)
                            ->addChild('x:b',  true)
                            ->addChild('xx:c', true)
                            ->addChild('xx:d', true)
                            ->addChild('e',    true)
                            ->addChild('x:f',  true)
                            ->addChild('g');

                        $r = $xml->query('a');

                        $actual   = $r->length();
                        $expected = 0;
                        \assert($actual === $expected, __($actual, $expected));

                        $r = $xml->query('x|a');

                        $actual   = $r[0]->nodeName;
                        $expected = 'x:a';
                        \assert($actual === $expected, __($actual, $expected));

                        $r = $xml->query('x|a > x|b');

                        $actual   = $r[0]->nodeName;
                        $expected = 'x:b';
                        \assert($actual === $expected, __($actual, $expected));

                        $r = $xml->query('x|a > x|b > c');

                        $actual   = $r->length();
                        $expected = 0;
                        \assert($actual === $expected, __($actual, $expected));

                        $r = $xml->query('x|a > x|b > xx|c');

                        $actual   = $r[0]->nodeName;
                        $expected = 'c';
                        \assert($actual === $expected, __($actual, $expected));

                        $r = $xml->query('x|a > x|b > xx|c > xx|d');

                        $actual   = $r[0]->nodeName;
                        $expected = 'd';
                        \assert($actual === $expected, __($actual, $expected));

                        $r = $xml->query('x|a > x|b > xx|c > xx|d > e');

                        $actual   = $r[0]->nodeName;
                        $expected = 'e';
                        \assert($actual === $expected, __($actual, $expected));

                        $r = $xml->query('x|a > x|b > xx|c > xx|d > e > f');

                        $actual   = $r->length();
                        $expected = 0;
                        \assert($actual === $expected, __($actual, $expected));

                        $r = $xml->query('x|a > x|b > xx|c > xx|d > e > x|f');

                        $actual   = $r[0]->nodeName;
                        $expected = 'x:f';
                        \assert($actual === $expected, __($actual, $expected));

                        $r = $xml->query('x|a > x|b > xx|c > xx|d > e > x|f > g');

                        $actual   = $r[0]->nodeName;
                        $expected = 'g';
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.__invoke()', function () {
                it('should be fluid', function () {
                        assert_is_fluid('__invoke', '/*');
                });

                it('should behave like .query()', function () {
                        $xml = new FluidXml();

                        $actual   = $xml('/*');
                        $expected = $xml->query('/*');
                        \assert($actual == $expected, __($actual, $expected));
                });
        });

        describe('.each()', function () {
                it('should be fluid', function () {
                        assert_is_fluid('each', function (){});
                });

                it('should iterate the nodes inside the context', function () {
                        $xml = new FluidXml();

                        $xml->each(function ($i, $n) {
                                assert_is_a($this, FluidContext::class);
                                assert_is_a($n, \DOMNode::class);
                                $actual   = $i;
                                $expected = 0;
                                \assert($actual === $expected, __($actual, $expected));
                        });

                        function eachassert($cx, $i, $n)
                        {
                                assert_is_a($cx, FluidContext::class);
                                assert_is_a($n,  \DOMNode::class);
                                $actual   = $i;
                                $expected = 0;
                                \assert($actual === $expected, __($actual, $expected));
                        }

                        $xml->each('eachassert');

                        $xml->addChild('child1')
                            ->addChild('child2');

                        $nodes = [];
                        $index = 0;
                        $xml->query('/doc/*')
                            ->each(function ($i, $n) use (&$nodes, &$index) {
                                $idx = $i + 1;
                                $this->setText($n->nodeName . $idx);
                                $nodes[] = $n;

                                $actual   = $i;
                                $expected = $index;
                                \assert($actual === $expected, __($actual, $expected));

                                ++$index;
                        });

                        $actual   = $nodes;
                        $expected = $xml->query('/doc/*')->array();
                        \assert($actual === $expected, __($actual, $expected));

                        $expected = "<doc>\n"
                                  . "  <child1>child11</child1>\n"
                                  . "  <child2>child22</child2>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);

                        $xml = new FluidXml();
                        $xml->addChild('child1')
                            ->addChild('child2');

                        function eachsettext($cx, $i, $n)
                        {
                                $idx = $i + 1;
                                $cx->setText($n->nodeName . $idx);
                        }

                        $xml->query('/doc/*')
                                ->each('eachsettext');

                        $expected = "<doc>\n"
                                  . "  <child1>child11</child1>\n"
                                  . "  <child2>child22</child2>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });
        });

        describe('.map()', function () {
                it('should map over the nodes inside the context', function () {
                        $xml = new FluidXml();

                        $xml->map(function ($i, $n) {
                                assert_is_a($this, FluidContext::class);
                                assert_is_a($n, \DOMNode::class);
                                $actual   = $i;
                                $expected = 0;
                                \assert($actual === $expected, __($actual, $expected));
                        });

                        function mapassert($cx, $i, $n)
                        {
                                assert_is_a($cx, FluidContext::class);
                                assert_is_a($n,  \DOMNode::class);
                                $actual   = $i;
                                $expected = 0;
                                \assert($actual === $expected, __($actual, $expected));
                        }

                        $xml->map('mapassert');

                        $xml->addChild(['child1' => 'child1'])
                            ->addChild(['child2' => 'child2']);

                        $actual = $xml->query('/doc/*')
                            ->map(function ($i, $n) {
                                    $idx = $i + 1;
                                    return $n->nodeValue . $idx;
                            });

                        $expected = ['child11', 'child22'];

                        \assert($actual === $expected, __($actual, $expected));

                        function mapfn($cx, $i, $n)
                        {
                                $idx = $i + 1;
                                return $n->nodeValue . $idx;
                        }

                        $actual = $xml->query('/doc/*')->map('mapfn');

                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.filter()', function () {
                it('should be fluid', function () {
                        assert_is_fluid('filter', function (){});
                });

                it('should filter the nodes inside the context', function () {
                        $xml = new FluidXml();

                        $xml->filter(function ($i, $n) {
                                assert_is_a($this, FluidContext::class);
                                assert_is_a($n, \DOMNode::class);
                                $actual   = $i;
                                $expected = 0;
                                \assert($actual === $expected, __($actual, $expected));
                        });

                        function filterassert($cx, $i, $n)
                        {
                                assert_is_a($cx, FluidContext::class);
                                assert_is_a($n,  \DOMNode::class);
                                $actual   = $i;
                                $expected = 0;
                                \assert($actual === $expected, __($actual, $expected));
                        }

                        $xml->each('filterassert');
                        $xml->times(4)->addChild('child');

                        $index = 0;
                        $children = $xml->query('//child');

                        $cx = $children->filter(function ($i, $n) use (&$index) {
                                $actual   = $i;
                                $expected = $index;
                                \assert($actual === $expected, __($actual, $expected));

                                ++$index;

                                if ($i === 0) {
                                        return true;
                                }

                                if (($i % 2) === 0) {
                                        return false;
                                }
                        });

                        $actual   = $cx->array();
                        $expected = [ $children[0], $children[1], $children[3] ];
                        \assert($actual === $expected, __($actual, $expected));

                        $cx->setText('not filtered');

                        $expected = "<doc>\n"
                                  . "  <child>not filtered</child>\n"
                                  . "  <child>not filtered</child>\n"
                                  . "  <child/>\n"
                                  . "  <child>not filtered</child>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });
        });

        describe('.times()', function () {
                it('should be fluid', function () {
                        assert_is_a((new FluidXml())->times(4), FluidRepeater::class);
                        assert_is_fluid('times', 4, function () {});
                });

                it('should repeat the following one method call (if no callable is passed)', function () {
                        $xml = new FluidXml();

                        $xml->times(2)
                                ->add('child')
                            ->add('lastchild');

                        $expected = "<doc>\n"
                                  . "  <child/>\n"
                                  . "  <child/>\n"
                                  . "  <lastchild/>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should switch context', function () {
                        $xml = new FluidXml();

                        $xml->times(2)
                                ->add('child', true)
                                        ->add('subchild');

                        $expected = "<doc>\n"
                                  . "  <child>\n"
                                  . "    <subchild/>\n"
                                  . "  </child>\n"
                                  . "  <child>\n"
                                  . "    <subchild/>\n"
                                  . "  </child>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should repeat a closure bound to $this of the context', function () {
                        $xml = new FluidXml();

                        $xml->add('parent', true)
                                ->times(2, function ($i) {
                                        $this->add("child{$i}");
                                });

                        $expected = "<doc>\n"
                                  . "  <parent>\n"
                                  . "    <child0/>\n"
                                  . "    <child1/>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should repeat a callable', function () {
                        $xml = new FluidXml();

                        function addchild($parent, $i)
                        {
                                $parent->add("child{$i}");
                        }

                        $xml->add('parent', true)
                                ->times(2, 'addchild');

                        $expected = "<doc>\n"
                                  . "  <parent>\n"
                                  . "    <child0/>\n"
                                  . "    <child1/>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should repeat a callable without repeating the following method call', function () {
                        $xml = new FluidXml();

                        $xml->add('parent', true)
                                ->times(2, function ($i) {
                                        $this->add("child{$i}");
                                })
                                ->add('lastchild');

                        $expected = "<doc>\n"
                                  . "  <parent>\n"
                                  . "    <child0/>\n"
                                  . "    <child1/>\n"
                                  . "    <lastchild/>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });
        });

        describe('.addChild()', function () {
                it('should be fluid', function () {
                        assert_is_fluid('addChild', 'a');
                });

                it('should add a child using the argument syntax', function () {
                        $xml = new FluidXml();
                        $xml->addChild('child1')
                            ->addChild('parent', true)
                            ->addChild('child2');

                        $expected = "<doc>\n"
                                  . "  <child1/>\n"
                                  . "  <parent>\n"
                                  . "    <child2/>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add a child using the array syntax', function () {
                        $xml = new FluidXml();
                        $xml->addChild(['child1'])
                            ->addChild(['parent'], true)
                            ->addChild(['child2']);

                        $expected = "<doc>\n"
                                  . "  <child1/>\n"
                                  . "  <parent>\n"
                                  . "    <child2/>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add a child with a string value using the argument syntax', function () {
                        $xml = new FluidXml();
                        $xml->addChild('child1', 'value1')
                            ->addChild('parent', true)
                                ->addChild('child2', 'value2');

                        $expected = "<doc>\n"
                                  . "  <child1>value1</child1>\n"
                                  . "  <parent>\n"
                                  . "    <child2>value2</child2>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add a child with a string value using the array syntax', function () {
                        $xml = new FluidXml();
                        $xml->addChild(['child1' => 'value1'])
                            ->addChild('parent', true)
                                ->addChild(['child2' => 'value2']);

                        $expected = "<doc>\n"
                                  . "  <child1>value1</child1>\n"
                                  . "  <parent>\n"
                                  . "    <child2>value2</child2>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add a child with an empty string value using the argument syntax', function () {
                        $xml = new FluidXml();
                        $xml->addChild('child1', '')
                            ->addChild('parent', true)
                                ->addChild('child2', '');

                        $expected = "<doc>\n"
                                  . "  <child1/>\n"
                                  . "  <parent>\n"
                                  . "    <child2/>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add a child with an empty string value using the array syntax', function () {
                        $xml = new FluidXml();
                        $xml->addChild(['child1' => ''])
                            ->addChild('parent', true)
                                ->addChild(['child2' => '']);

                        $expected = "<doc>\n"
                                  . "  <child1/>\n"
                                  . "  <parent>\n"
                                  . "    <child2/>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add a child with a null value using the argument syntax', function () {
                        $xml = new FluidXml();
                        $xml->addChild('child1', null)
                            ->addChild('parent', true)
                                ->addChild('child2', null);

                        $expected = "<doc>\n"
                                  . "  <child1/>\n"
                                  . "  <parent>\n"
                                  . "    <child2/>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add a child with a null value using the array syntax', function () {
                        $xml = new FluidXml();
                        $xml->addChild(['child1' => null])
                            ->addChild('parent', true)
                                ->addChild(['child2' => null]);

                        $expected = "<doc>\n"
                                  . "  <child1/>\n"
                                  . "  <parent>\n"
                                  . "    <child2/>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add a child with an integer value using the argument syntax', function () {
                        $xml = new FluidXml();
                        $xml->addChild('child1', 1)
                            ->addChild('parent', true)
                                ->addChild('child2', 1);

                        $expected = "<doc>\n"
                                  . "  <child1>1</child1>\n"
                                  . "  <parent>\n"
                                  . "    <child2>1</child2>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add a child with an integer value using the array syntax', function () {
                        $xml = new FluidXml();
                        $xml->addChild(['child1' => 1])
                            ->addChild('parent', true)
                                ->addChild(['child2' => 1]);

                        $expected = "<doc>\n"
                                  . "  <child1>1</child1>\n"
                                  . "  <parent>\n"
                                  . "    <child2>1</child2>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add a child with a 0 value using the argument syntax', function () {
                        $xml = new FluidXml();
                        $xml->addChild('child1', 0)
                            ->addChild('parent', true)
                                ->addChild('child2', 0);

                        $expected = "<doc>\n"
                                  . "  <child1>0</child1>\n"
                                  . "  <parent>\n"
                                  . "    <child2>0</child2>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add a child with a 0 value using the array syntax', function () {
                        $xml = new FluidXml();
                        $xml->addChild(['child1' => 0])
                            ->addChild('parent', true)
                                ->addChild(['child2' => 0]);

                        $expected = "<doc>\n"
                                  . "  <child1>0</child1>\n"
                                  . "  <parent>\n"
                                  . "    <child2>0</child2>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add many children with and without a value', function () {
                        $xml = new FluidXml();
                        $xml->addChild(['child1', 'child2', 'child3' => 'value3', 'child4' => 'value4'])
                            ->addChild('parent', true)
                            ->addChild(['child5', 'child6', 'child7' => 'value7', 'child8' => 'value8']);

                        $expected = "<doc>\n"
                                  . "  <child1/>\n"
                                  . "  <child2/>\n"
                                  . "  <child3>value3</child3>\n"
                                  . "  <child4>value4</child4>\n"
                                  . "  <parent>\n"
                                  . "    <child5/>\n"
                                  . "    <child6/>\n"
                                  . "    <child7>value7</child7>\n"
                                  . "    <child8>value8</child8>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add many children of the same name with and without a value', function () {
                        $xml = new FluidXml();
                        $xml->addChild(['child', ['child'], ['child' => 'value1'], ['child' => 'value2']])
                            ->addChild('parent', true)
                            ->addChild(['child', ['child'], ['child' => 'value3'], ['child' => 'value4']]);

                        $expected = "<doc>\n"
                                  . "  <child/>\n"
                                  . "  <child/>\n"
                                  . "  <child>value1</child>\n"
                                  . "  <child>value2</child>\n"
                                  . "  <parent>\n"
                                  . "    <child/>\n"
                                  . "    <child/>\n"
                                  . "    <child>value3</child>\n"
                                  . "    <child>value4</child>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add many children with nested arrays', function () {
                        $xml = new FluidXml();
                        $xml->addChild(['child1'=>['child11'=>['child111', 'child112'=>'value112'], 'child12'=>'value12'],
                                           'child2'=>['child21', 'child22'=>['child221', 'child222']]])
                            ->addChild('parent', true)
                            ->addChild(['child3'=>['child31'=>['child311', 'child312'=>'value312'], 'child32'=>'value32'],
                                           'child4'=>['child41', 'child42'=>['child421', 'child422']]]);

                        $expected = <<<EOF
<doc>
  <child1>
    <child11>
      <child111/>
      <child112>value112</child112>
    </child11>
    <child12>value12</child12>
  </child1>
  <child2>
    <child21/>
    <child22>
      <child221/>
      <child222/>
    </child22>
  </child2>
  <parent>
    <child3>
      <child31>
        <child311/>
        <child312>value312</child312>
      </child31>
      <child32>value32</child32>
    </child3>
    <child4>
      <child41/>
      <child42>
        <child421/>
        <child422/>
      </child42>
    </child4>
  </parent>
</doc>
EOF;
                        assert_equal_xml($xml, $expected);
                });

                it('should add a child with some attributes', function () {
                        $xml = new FluidXml();
                        $xml->addChild('child1', ['class' => 'Class attr', 'id' => 'Id attr1'])
                            ->addChild('parent', true)
                            ->addChild('child2', ['class' => 'Class attr', 'id' => 'Id attr2']);

                        $expected = "<doc>\n"
                                  . "  <child1 class=\"Class attr\" id=\"Id attr1\"/>\n"
                                  . "  <parent>\n"
                                  . "    <child2 class=\"Class attr\" id=\"Id attr2\"/>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add many children with some attributes', function () {
                        $xml = new FluidXml();
                        $xml->addChild(['child1', 'child2'], ['class' => 'Class attr', 'id' => 'Id attr1'])
                            ->addChild('parent', true)
                            ->addChild(['child3', 'child4'], ['class' => 'Class attr', 'id' => 'Id attr2']);

                        $expected = "<doc>\n"
                                  . "  <child1 class=\"Class attr\" id=\"Id attr1\"/>\n"
                                  . "  <child2 class=\"Class attr\" id=\"Id attr1\"/>\n"
                                  . "  <parent>\n"
                                  . "    <child3 class=\"Class attr\" id=\"Id attr2\"/>\n"
                                  . "    <child4 class=\"Class attr\" id=\"Id attr2\"/>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add children with some attributes and text using the @ syntax', function () {
                        $xml = new FluidXml();
                        $attrs = [ '@class' => 'Class attr',
                                   '@'      => 'Text content',
                                   '@id'    => 'Id attr' ];
                        $xml->addChild(['child1' => $attrs ])
                            ->addChild(['child2' => $attrs ], true)
                                ->addChild(['child3' => $attrs ]);

                        $expected = "<doc>\n"
                                  . "  <child1 class=\"Class attr\" id=\"Id attr\">Text content</child1>\n"
                                  . "  <child2 class=\"Class attr\" id=\"Id attr\">"
                                  .      "Text content"
                                  .      "<child3 class=\"Class attr\" id=\"Id attr\">Text content</child3>"
                                  .    "</child2>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should switch context', function () {
                        $xml = new FluidXml();

                        $actual = $xml->addChild('child', true);
                        assert_is_a($actual, FluidContext::class);

                        $actual = $xml->addChild('child', 'value', true);
                        assert_is_a($actual, FluidContext::class);

                        $actual = $xml->addChild(['child1', 'child2'], true);
                        assert_is_a($actual, FluidContext::class);

                        $actual = $xml->addChild(['child1' => 'value1', 'child2' => 'value2'], true);
                        assert_is_a($actual, FluidContext::class);

                        $actual = $xml->addChild('child', ['attr' => 'value'], true);
                        assert_is_a($actual, FluidContext::class);

                        $actual = $xml->addChild(['child1', 'child2'], ['attr' => 'value'], true);
                        assert_is_a($actual, FluidContext::class);
                });

                it('should add namespaced children', function () {
                        $xml = new FluidXml();
                        $xml->namespace(new FluidNamespace('x', 'x.com'));
                        $xml->namespace(fluidns('xx', 'xx.com', FluidNamespace::MODE_IMPLICIT));
                        $xml->addChild('x:xTag1', true)
                            ->addChild('x:xTag2');
                        $xml->addChild('xx:xxTag1', true)
                            ->addChild('xx:xxTag2')
                            ->addChild('tag3');

                        $expected = "<doc>\n"
                                  . "  <x:xTag1 xmlns:x=\"x.com\">\n"
                                  . "    <x:xTag2/>\n"
                                  . "  </x:xTag1>\n"
                                  . "  <xxTag1 xmlns=\"xx.com\">\n"
                                  . "    <xxTag2/>\n"
                                  . "    <tag3/>\n"
                                  . "  </xxTag1>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                $doc = "<doc>\n"
                     . "  <parent>content</parent>\n"
                     . "</doc>";
                $dom = new \DOMDocument();
                $dom->loadXML($doc);

                it('should fill the document with an XML string', function () {
                        $xml = new FluidXml(null);
                        $xml->addChild('<root/>');

                        $expected = "<root/>";
                        assert_equal_xml($xml, $expected);
                });

                it('should fill the document with an XML string with multiple root nodes', function () {
                        $xml = new FluidXml(null);
                        $xml->addChild('<root1/><root2/>');

                        $expected = "<root1/>\n"
                                  . "<root2/>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add an XML string with multiple root nodes', function () {
                        $xml = new FluidXml();
                        $xml->addChild('<child1/><child2/>');

                        $expected = "<doc>\n"
                                  . "  <child1/>\n"
                                  . "  <child2/>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);

                        $xml = new FluidXml();
                        $xml->addChild('parent', true)
                            ->addChild('<child1/><child2/>');

                        $expected = "<doc>\n"
                                  . "  <parent>\n"
                                  . "    <child1/>\n"
                                  . "    <child2/>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add a DOMDocument', function () use ($doc) {
                        $dom = new DOMDocument();
                        $dom->loadXML('<parent>content</parent>');

                        $xml = new FluidXml();
                        $xml->addChild($dom);

                        $expected = $doc;
                        assert_equal_xml($xml, $expected);
                });

                it('should add a DOMNode', function () use ($doc, $dom) {
                        $xp    = new \DOMXPath($dom);
                        $nodes = $xp->query('/doc/parent');
                        $xml   = new FluidXml();
                        $xml->addChild($nodes[0]);

                        $expected = $doc;
                        assert_equal_xml($xml, $expected);
                });

                it('should add a DOMNodeList', function () use ($doc, $dom) {
                        $xp    = new \DOMXPath($dom);
                        $nodes = $xp->query('/doc/parent');
                        $xml   = new FluidXml();
                        $xml->addChild($nodes);

                        $expected = $doc;
                        assert_equal_xml($xml, $expected);
                });

                it('should add a SimpleXMLElement', function () use ($doc, $dom) {
                        $sxml = \simplexml_import_dom($dom);
                        $xml  = new FluidXml();
                        $xml->addChild($sxml->children());

                        $expected = $doc;
                        assert_equal_xml($xml, $expected);
                });

                it('should add a FluidXml', function () use ($doc, $dom) {
                        $nodes = $dom->documentElement->childNodes;
                        $fxml = new FluidXml($nodes);
                        $xml  = new FluidXml();
                        $xml->addChild($fxml);

                        $expected = $doc;
                        assert_equal_xml($xml, $expected);
                });

                it('should add a FluidContext', function () use ($doc, $dom) {
                        $fxml = (new FluidXml($dom))->query('/doc/parent');
                        $xml  = new FluidXml();
                        $xml->addChild($fxml);

                        $expected = $doc;
                        assert_equal_xml($xml, $expected);
                });

                it('should add many instances', function () use ($doc, $dom) {
                        $fxml = (new FluidXml($dom))->query('/doc/parent');
                        $xml  = new FluidXml();
                        $xml->addChild([ $fxml,
                                            'imported' => $fxml ]);

                        $expected = "<doc>\n"
                                  . "  <parent>content</parent>\n"
                                  . "  <imported>\n"
                                  . "    <parent>content</parent>\n"
                                  . "  </imported>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should throw for not supported input', function () {
                        $xml  = new FluidXml();
                        try {
                                $xml->addChild(0);
                        } catch (\Exception $e) {
                                $actual = $e;
                        }

                        assert_is_a($actual, \Exception::class);
                });
        });

        describe('.add()', function () {
                it('should be fluid', function () {
                        assert_is_fluid('add', 'a');
                });

                it('should behave like .addChild()', function () {
                        $xml = new FluidXml();
                        $xml->addChild('parent', true)
                            ->addChild(['child1', 'child2'], ['class'=>'child']);

                        $alias = new FluidXml();
                        $alias->add('parent', true)
                              ->add(['child1', 'child2'], ['class'=>'child']);

                        $actual   = $xml->xml();
                        $expected = $alias->xml();
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.prependSibling()', function () {
                it('should be fluid', function () {
                        assert_is_fluid('prependSibling', 'a');
                });

                it('should add more than one root node to a document with one root node', function () {
                        $xml = new FluidXml();
                        $xml->prependSibling('meta');
                        $xml->prependSibling('extra');
                        $cx = $xml->query('/*');

                        $actual   = $cx[0]->nodeName;
                        $expected = 'extra';
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $cx[1]->nodeName;
                        $expected = 'meta';
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $cx[2]->nodeName;
                        $expected = 'doc';
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should add more than one root node to a document with no root node', function () {
                        $xml = new FluidXml(null);
                        $xml->prependSibling('meta');
                        $xml->prependSibling('extra');
                        $cx = $xml->query('/*');

                        $actual   = $cx[0]->nodeName;
                        $expected = 'extra';
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $cx[1]->nodeName;
                        $expected = 'meta';
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should add a sibling node before a node', function () {
                        $xml = new FluidXml();
                        $xml->addChild('parent', true)
                            ->prependSibling('sibling1')
                            ->prependSibling('sibling2');

                        $expected = "<doc>\n"
                                  . "  <sibling1/>\n"
                                  . "  <sibling2/>\n"
                                  . "  <parent/>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add an XML document instance before a node', function () {
                        $dom = new DOMDocument();
                        $dom->loadXML('<parent>content</parent>');

                        $xml = new FluidXml();
                        $xml->prependSibling($dom);

                        $expected = "<parent>content</parent>\n"
                                  . "<doc/>";
                        assert_equal_xml($xml, $expected);

                        $xml = new FluidXml();
                        $xml->addChild('sibling', true)
                            ->prependSibling($dom);

                        $expected = "<doc>\n"
                                  . "  <parent>content</parent>\n"
                                  . "  <sibling/>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });
        });

        describe('.prepend()', function () {
                it('should be fluid', function () {
                        assert_is_fluid('prepend', 'a');
                });

                it('should behave like .prependSibling()', function () {
                        $xml = new FluidXml();
                        $xml->prependSibling('sibling1', true)
                            ->prependSibling(['sibling2', 'sibling3'], ['class'=>'sibling']);

                        $alias = new FluidXml();
                        $alias->prepend('sibling1', true)
                              ->prepend(['sibling2', 'sibling3'], ['class'=>'sibling']);

                        $actual   = $xml->xml();
                        $expected = $alias->xml();
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.appendSibling()', function () {
                it('should be fluid', function () {
                        assert_is_fluid('appendSibling', 'a');
                });

                it('should add more than one root node to a document with one root node', function () {
                        $xml = new FluidXml();
                        $xml->appendSibling('meta');
                        $xml->appendSibling('extra');
                        $cx = $xml->query('/*');

                        $actual   = $cx[0]->nodeName;
                        $expected = 'doc';
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $cx[1]->nodeName;
                        $expected = 'extra';
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $cx[2]->nodeName;
                        $expected = 'meta';
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should add more than one root node to a document with no root node', function () {
                        $xml = new FluidXml(null);
                        $xml->appendSibling('meta');
                        $xml->appendSibling('extra');
                        $cx = $xml->query('/*');

                        $actual   = $cx[0]->nodeName;
                        $expected = 'meta';
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $cx[1]->nodeName;
                        $expected = 'extra';
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should add a sibling node after a node', function () {
                        $xml = new FluidXml();
                        $xml->addChild('parent', true)
                            ->appendSibling('sibling1')
                            ->appendSibling('sibling2');

                        $expected = "<doc>\n"
                                  . "  <parent/>\n"
                                  . "  <sibling2/>\n"
                                  . "  <sibling1/>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add an XML document instance after a node', function () {
                        $dom = new DOMDocument();
                        $dom->loadXML('<parent>content</parent>');

                        $xml = new FluidXml();
                        $xml->appendSibling($dom);

                        $expected = "<doc/>\n"
                                  . "<parent>content</parent>";
                        assert_equal_xml($xml, $expected);

                        $xml = new FluidXml();
                        $xml->addChild('sibling', true)
                            ->appendSibling($dom);

                        $expected = "<doc>\n"
                                  . "  <sibling/>\n"
                                  . "  <parent>content</parent>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });
        });

        describe('.append()', function () {
                it('should be fluid', function () {
                        assert_is_fluid('append', 'a');
                });

                it('should behave like .appendSibling()', function () {
                        $xml = new FluidXml();
                        $xml->appendSibling('sibling1', true)
                            ->appendSibling(['sibling2', 'sibling3'], ['class'=>'sibling']);

                        $alias = new FluidXml();
                        $alias->append('sibling1', true)
                              ->append(['sibling2', 'sibling3'], ['class'=>'sibling']);

                        $actual   = $xml->xml();
                        $expected = $alias->xml();
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.setAttribute()', function () {
                it('should be fluid', function () {
                        assert_is_fluid('setAttribute', 'a', 'b');
                });

                it('should set the attributes of the root node', function () {
                        $xml = new FluidXml();
                        $xml->setAttribute('attr1', 'Attr1 Value')
                            ->setAttribute('attr2', 'Attr2 Value');

                        $expected = "<doc attr1=\"Attr1 Value\" attr2=\"Attr2 Value\"/>";
                        assert_equal_xml($xml, $expected);

                        $xml = new FluidXml();
                        $xml->setAttribute(['attr1' => 'Attr1 Value',
                                            'attr2' => 'Attr2 Value']);

                        $expected = "<doc attr1=\"Attr1 Value\" attr2=\"Attr2 Value\"/>";
                        assert_equal_xml($xml, $expected);
                });

                it('should change the attributes of the root node', function () {
                        $xml = new FluidXml();
                        $xml->setAttribute('attr1', 'Attr1 Value')
                            ->setAttribute('attr2', 'Attr2 Value');

                        $xml->setAttribute('attr2', 'Attr2 New Value');

                        $expected = "<doc attr1=\"Attr1 Value\" attr2=\"Attr2 New Value\"/>";
                        assert_equal_xml($xml, $expected);

                        $xml->setAttribute('attr1', 'Attr1 New Value');

                        $expected = "<doc attr1=\"Attr1 New Value\" attr2=\"Attr2 New Value\"/>";
                        assert_equal_xml($xml, $expected);
                });

                it('should set the attributes of a node', function () {
                        $xml = new FluidXml();
                        $xml->addChild('child', true)
                            ->setAttribute('attr1', 'Attr1 Value')
                            ->setAttribute('attr2', 'Attr2 Value');

                        $expected = "<doc>\n"
                                  . "  <child attr1=\"Attr1 Value\" attr2=\"Attr2 Value\"/>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);

                        $xml = new FluidXml();
                        $xml->addChild('child', true)
                            ->setAttribute(['attr1' => 'Attr1 Value',
                                            'attr2' => 'Attr2 Value']);

                        $expected = "<doc>\n"
                                  . "  <child attr1=\"Attr1 Value\" attr2=\"Attr2 Value\"/>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should set the attributes, without values, of a node', function () {
                        $xml = new FluidXml();
                        $xml->addChild('child', true)
                            ->setAttribute('attr1')
                            ->setAttribute('attr2');

                        $expected = "<doc>\n"
                                  . "  <child attr1=\"\" attr2=\"\"/>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);

                        $xml = new FluidXml();
                        $xml->addChild('child', true)
                            ->setAttribute(['attr1', 'attr2']);

                        $expected = "<doc>\n"
                                  . "  <child attr1=\"\" attr2=\"\"/>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should change the attributes of a node', function () {
                        $xml = new FluidXml();
                        $xml->addChild('child', true)
                            ->setAttribute('attr1', 'Attr1 Value')
                            ->setAttribute('attr2', 'Attr2 Value')
                            ->setAttribute('attr2', 'Attr2 New Value');

                        $expected = "<doc>\n"
                                  . "  <child attr1=\"Attr1 Value\" attr2=\"Attr2 New Value\"/>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);

                        $xml = new FluidXml();
                        $xml->addChild('child', true)
                            ->setAttribute(['attr1' => 'Attr1 Value',
                                            'attr2' => 'Attr2 Value'])
                            ->setAttribute('attr1', 'Attr1 New Value');

                        $expected = "<doc>\n"
                                  . "  <child attr1=\"Attr1 New Value\" attr2=\"Attr2 Value\"/>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });
        });

        describe('.attr()', function () {
                it('should be fluid', function () {
                        assert_is_fluid('attr', 'a', 'b');
                });

                it('should behave like .setAttribute()', function () {
                        $xml = new FluidXml();
                        $xml->setAttribute('attr1', 'Value 1')
                            ->setAttribute('attr2')
                            ->setAttribute(['attr3' => 'Value 3', 'attr4' => 'Value 4'])
                            ->setAttribute(['attr5', 'attr6'])
                            ->addChild('child', true)
                            ->setAttribute('attr1', 'Value 1')
                            ->setAttribute('attr2')
                            ->setAttribute(['attr3' => 'Value 3', 'attr4' => 'Value 4'])
                            ->setAttribute(['attr5', 'attr6']);

                        $alias = new FluidXml();
                        $alias->attr('attr1', 'Value 1')
                              ->attr('attr2')
                              ->attr(['attr3' => 'Value 3', 'attr4' => 'Value 4'])
                              ->attr(['attr5', 'attr6'])
                              ->addChild('child', true)
                              ->attr('attr1', 'Value 1')
                              ->attr('attr2')
                              ->attr(['attr3' => 'Value 3', 'attr4' => 'Value 4'])
                              ->attr(['attr5', 'attr6']);

                        $actual   = $xml->xml();
                        $expected = $alias->xml();
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.setText()', function () {
                it('should be fluid', function () {
                        assert_is_fluid('setText', 'a');
                });

                it('should set/change the text of the root node', function () {
                        $xml = new FluidXml();
                        $xml->setText('First Text')
                            ->setText('Second Text');

                        $expected = "<doc>Second Text</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should set/change the text of a node', function () {
                        $xml = new FluidXml();
                        $cx = $xml->addChild('p', true);
                        $cx->setText('First Text')
                           ->setText('Second Text');

                        $expected = "<doc>\n"
                                  . "  <p>Second Text</p>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });
        });

        describe('.text()', function () {
                it('should be fluid', function () {
                        assert_is_fluid('text', 'a');
                });

                it('should behave like .setText()', function () {
                        $xml = new FluidXml();
                        $xml->setText('Text1')
                            ->addChild('child', true)
                            ->setText('Text2');

                        $alias = new FluidXml();
                        $alias->text('Text1')
                              ->addChild('child', true)
                              ->text('Text2');

                        $actual   = $xml->xml();
                        $expected = $alias->xml();
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.addText()', function () {
                it('should be fluid', function () {
                        assert_is_fluid('addText', 'a');
                });

                it('should add text to the root node', function () {
                        $xml = new FluidXml();
                        $xml->addText('First Line')
                            ->addText('Second Line');

                        $expected = "<doc>First LineSecond Line</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add text to a node', function () {
                        $xml = new FluidXml();
                        $cx = $xml->addChild('p', true);
                        $cx->addText('First Line')
                           ->addText('Second Line');

                        $expected = "<doc>\n"
                                  . "  <p>First LineSecond Line</p>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });
        });

        describe('.setCdata()', function () {
                it('should be fluid', function () {
                        assert_is_fluid('setCdata', 'a');
                });

                it('should set/change the CDATA of the root node', function () {
                        $xml = new FluidXml();
                        $xml->setCdata('First Data')
                            ->setCdata('Second Data');

                        $expected = "<doc><![CDATA[Second Data]]></doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should set/change the CDATA of a node', function () {
                        $xml = new FluidXml();
                        $cx = $xml->addChild('p', true);
                        $cx->setCdata('First Data')
                           ->setCdata('Second Data');

                        $expected = "<doc>\n"
                                  . "  <p><![CDATA[Second Data]]></p>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });
        });

        describe('.cdata()', function () {
                it('should be fluid', function () {
                        assert_is_fluid('cdata', 'a');
                });

                it('should behave like .setCdata()', function () {
                        $xml = new FluidXml();
                        $xml->setCdata('Text1')
                            ->addChild('child', true)
                            ->setCdata('Text2');

                        $alias = new FluidXml();
                        $alias->cdata('Text1')
                              ->addChild('child', true)
                              ->cdata('Text2');

                        $actual   = $xml->xml();
                        $expected = $alias->xml();
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.addCdata()', function () {
                it('should be fluid', function () {
                        assert_is_fluid('addCdata', 'a');
                });

                it('should add CDATA to the root node', function () {
                        $xml = new FluidXml();
                        $xml->addCdata('// <, > are characters that should be escaped in a XML context.')
                            ->addCdata('// Even & is a characters that should be escaped in a XML context.');

                        $expected = "<doc>"
                                  . "<![CDATA[// <, > are characters that should be escaped in a XML context.]]>"
                                  . "<![CDATA[// Even & is a characters that should be escaped in a XML context.]]>"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add CDATA to a node', function () {
                        $xml = new FluidXml();
                        $cx = $xml->addChild('pre', true);
                        $cx->addCdata('// <, > are characters that should be escaped in a XML context.')
                           ->addCdata('// Even & is a characters that should be escaped in a XML context.');

                        $expected = "<doc>\n"
                                  . "  <pre>"
                                  . "<![CDATA[// <, > are characters that should be escaped in a XML context.]]>"
                                  . "<![CDATA[// Even & is a characters that should be escaped in a XML context.]]>"
                                  .    "</pre>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });
        });

        describe('.setComment()', function () {
                it('should be fluid', function () {
                        assert_is_fluid('setComment', 'a');
                });

                it('should set/change the comment of the root node', function () {
                        $xml = new FluidXml();
                        $xml->setComment('First')
                            ->setComment('Second');

                        $expected = "<doc><!--Second--></doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should set/change the comment of a node', function () {
                        $xml = new FluidXml();
                        $cx = $xml->addChild('p', true);
                        $cx->setComment('First')
                           ->setComment('Second');

                        $expected = "<doc>\n"
                                  . "  <p><!--Second--></p>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });
        });

        describe('.comment()', function () {
                it('should be fluid', function () {
                        assert_is_fluid('comment', 'a');
                });

                it('should behave like .setComment()', function () {
                        $xml = new FluidXml();
                        $xml->setComment('Text1')
                            ->addChild('child', true)
                            ->setComment('Text2');

                        $alias = new FluidXml();
                        $alias->comment('Text1')
                              ->addChild('child', true)
                              ->comment('Text2');

                        $actual   = $xml->xml();
                        $expected = $alias->xml();
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.addComment()', function () {
                it('should be fluid', function () {
                        assert_is_fluid('addComment', 'a');
                });

                it('should add comments to the root node', function () {
                        $xml = new FluidXml();
                        $xml->addComment('First')
                            ->addComment('Second');

                        $expected = "<doc>\n"
                                  . "  <!--First-->\n"
                                  . "  <!--Second-->\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add comments to a node', function () {
                        $xml = new FluidXml();
                        $cx = $xml->addChild('pre', true);
                        $cx->addComment('First')
                           ->addComment('Second');

                        $expected = "<doc>\n"
                                  . "  <pre>\n"
                                  . "    <!--First-->\n"
                                  . "    <!--Second-->\n"
                                  . "  </pre>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });
        });

        describe('.remove()', function () {
                $expected = "<doc>\n"
                          . "  <parent/>\n"
                          . "</doc>";

                $new_doc = function () {
                        $xml = new FluidXml();
                        $xml->addChild('parent', true)
                            ->addChild(['child1', 'child2'], ['class'=>'removable']);

                        return $xml;
                };

                it('should be fluid', function () {
                        assert_is_fluid('remove', 'a');
                });

                it('should remove the root node', function () use ($new_doc) {
                        $xml = $new_doc();
                        $xml->remove();

                        assert_equal_xml($xml, '');
                });

                it('should remove the results of the previous query', function () use ($new_doc, $expected) {
                        $xml = $new_doc();
                        $xml->query('//*[@class="removable"]')->remove();

                        assert_equal_xml($xml, $expected);
                });

                it('should remove the absolute and relative targets of a query (XPath)', function () use ($new_doc, $expected) {
                        $xml = $new_doc();
                        $xml->remove('//*[@class="removable"]');

                        assert_equal_xml($xml, $expected);

                        $xml = $new_doc();
                        $xml->query('/doc')->remove('//*[@class="removable"]');

                        assert_equal_xml($xml, $expected);

                        $xml = $new_doc();
                        $xml->query('/doc/parent')->remove('./*[@class="removable"]');

                        assert_equal_xml($xml, $expected);
                });

                it('should remove the absolute and relative targets of a query (CSS)', function () use ($new_doc, $expected) {
                        $xml = $new_doc();
                        $xml->remove('.removable');

                        assert_equal_xml($xml, $expected);

                        $xml = $new_doc();
                        $xml->query('/doc')->remove(':root .removable');

                        assert_equal_xml($xml, $expected);

                        $xml = $new_doc();
                        $xml->query('/doc/parent')->remove('.removable');

                        assert_equal_xml($xml, $expected);
                });

                it('should remove the absolute and relative targets of an array of queries (XPath and CSS)', function () use ($new_doc, $expected) {
                        $xml = $new_doc();
                        $xml->remove(['//child1', ':root child2']);

                        assert_equal_xml($xml, $expected);

                        $xml = $new_doc();
                        $xml->query('/doc')->remove(['//child1', ':root child2']);

                        assert_equal_xml($xml, $expected);

                        $xml = $new_doc();
                        $xml->query('/doc/parent')->remove(['./child1', 'child2']);

                        assert_equal_xml($xml, $expected);
                });

                it('should remove the absolute and relative targets of a variable list of queries (XPath and CSS)', function () use ($new_doc, $expected) {
                        $xml = $new_doc();
                        $xml->remove('//child1', ':root child2');

                        assert_equal_xml($xml, $expected);

                        $xml = $new_doc();
                        $xml->query('/doc')->remove('//child1', ':root child2');

                        assert_equal_xml($xml, $expected);

                        $xml = $new_doc();
                        $xml->query('/doc/parent')->remove('./child1', 'child2');

                        assert_equal_xml($xml, $expected);
                });
        });

        describe('.dom()', function () {
                it('should return the associated DOMDocument instace', function () {
                        $xml = new FluidXml();

                        $actual = $xml->dom();
                        assert_is_a($actual, \DOMDocument::class);

                        $actual = $xml->query('/*')->dom();
                        assert_is_a($actual, \DOMDocument::class);
                });
        });

        describe('.xml()', function () {
                it('should return the document as XML string', function () {
                        $xml = new FluidXml();
                        $xml->addChild('parent', true)
                                ->addChild('child', 'content');

                        $expected = "<doc>\n"
                                  . "  <parent>\n"
                                  . "    <child>content</child>\n"
                                  . "  </parent>\n"
                                  . "</doc>";

                        assert_equal_xml($xml, $expected);
                });

                it('should return the document as XML string without the XML headers (declaration and stylesheet)', function () {
                        $xml = new FluidXml('doc', ['stylesheet' => 'x.com/style.xsl']);
                        $xml->addChild('parent', true)
                                ->addChild('child', 'content');

                        $actual   = $xml->xml(true);
                        $expected = "<doc>\n"
                                  . "  <parent>\n"
                                  . "    <child>content</child>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should return a node and the descendants as XML string', function () {
                        $xml = new FluidXml();
                        $xml->addChild('parent', true)
                                ->addText('parent content')
                                ->addChild('child', 'content');

                        $actual   = $xml->query('//parent')->xml();
                        $expected = "<parent>parent content<child>content</child></parent>";
                        \assert($actual === $expected, __($actual, $expected));

                        $xml = new FluidXml();
                        $xml->addChild('parent', true)
                                ->addChild('child', 'content1')
                                ->addChild('child', 'content2');

                        $actual   = $xml->query('//child')->xml();
                        $expected = "<child>content1</child>\n"
                                  . "<child>content2</child>";
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.__toString()', function () {
                it('should behave like .xml()', function () {
                        $xml = new FluidXml();
                        $cx = $xml->addChild('parent', true)
                                      ->addChild(['child1', 'child2']);

                        $actual   = \trim("$xml");
                        $expected = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
                                  . "<doc>\n"
                                  . "  <parent>\n"
                                  . "    <child1/>\n"
                                  . "    <child2/>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = "$cx";
                        $expected = "<parent>\n"
                                  . "  <child1/>\n"
                                  . "  <child2/>\n"
                                  . "</parent>";
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.html()', function () {
                it('should return the document as valid HTML 5 string', function () {
                        $xml = new FluidXml([
                                'html' => [ 'body' => [ 'input', // Void.
                                                        'div',   /* Not void. */ ] ] ]);

                        $actual   = $xml->html();
                        $expected = "<!DOCTYPE html>\n"
                                  . "<html>\n"
                                  . "  <body>\n"
                                  . "    <input/>\n"
                                  . "    <div></div>\n"
                                  . "  </body>\n"
                                  . "</html>";
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should return the document as valid HTML 5 string without the doctype', function () {
                        $xml = new FluidXml([
                                'html' => [ 'body' => [ 'input', // Void.
                                                        'div',   /* Not void. */ ] ] ]);

                        $actual   = $xml->html(true);
                        $expected = "<html>\n"
                                  . "  <body>\n"
                                  . "    <input/>\n"
                                  . "    <div></div>\n"
                                  . "  </body>\n"
                                  . "</html>";
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should return a node and the descendants as HTML string', function () {
                        $xml = new FluidXml([
                                'html' => [ 'body' => [ 'input', // Void.
                                                        'div',   /* Not void. */ ] ] ]);

                        $actual   = $xml->query('//body/*')->html();
                        $expected = "<input/>\n"
                                  . "<div></div>";
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.save()', function () {
                it('should be fluid', function () {
                        $file = "{$this->out_dir}.test_save0.xml";
                        assert_is_fluid('save', $file);
                        \unlink($file);
                });

                it('should store the entire XML document in a file', function () {
                        $xml = new FluidXml();
                        $xml->addChild('parent', true)
                                ->addChild('child', 'content');

                        $file = "{$this->out_dir}.test_save1.xml";
                        $xml->save($file);

                        $actual   = \trim(\file_get_contents($file));
                        $expected = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
                                  . "<doc>\n"
                                  . "  <parent>\n"
                                  . "    <child>content</child>\n"
                                  . "  </parent>\n"
                                  . "</doc>";

                        \unlink($file);

                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should store a fragment of the XML document in a file', function () {
                        $xml = new FluidXml();
                        $xml->addChild('parent', true)
                                ->addChild('child', 'content');

                        $file = "{$this->out_dir}.test_save2.xml";
                        $xml->query('//child')->save($file);

                        $actual   = \trim(\file_get_contents($file));
                        $expected = "<child>content</child>";

                        \unlink($file);

                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should throw for not writable file', function () {
                        $xml = new FluidXml();

                        $err_handler = \set_error_handler(function () {});
                        try {
                                $xml->save('/.impossible/tmp/out.xml');
                        } catch (\Exception $e) {
                                $actual = $e;
                        }
                        \set_error_handler($err_handler);

                        assert_is_a($actual, \Exception::class);
                });
        });
});

describe('FluidContext', function () {
        it('should be iterable returning the represented DOMNode objects', function () {
                $xml = new FluidXml();
                $cx = $xml->addChild(['head', 'body'], true);

                $actual = $cx;
                assert_is_a($actual, \Iterator::class);

                $representation = [];
                foreach ($cx as $k => $v) {
                        $actual = \is_int($k);
                        $expected = true;
                        \assert($actual === $expected, __($actual, $expected));

                        $actual = $v;
                        assert_is_a($actual, \DOMNode::class);

                        $representation[$k] = $v->nodeName;
                }

                $actual = $representation;
                $expected = [0 => 'head', 1 => 'body'];
                \assert($actual === $expected, __($actual, $expected));
        });

        describe('.__construct()', function () {
                it('should accept a DOMDocument', function () {
                        $xml = new FluidXml();

                        $doc     = new FluidDocument();
                        $handler = new FluidInsertionHandler($doc);
                        $new_cx  = new FluidContext($doc, $handler, $xml->dom());

                        $actual   = $new_cx[0];
                        $expected = $xml->dom();
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should accept a DOMNode', function () {
                        $xml = new FluidXml();
                        $cx = $xml->addChild(['head'], true);

                        $doc     = new FluidDocument();
                        $handler = new FluidInsertionHandler($doc);
                        $new_cx  = new FluidContext($doc, $handler, $cx[0]);

                        $actual   = $new_cx->array();
                        $expected = $cx->array();
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should accept an array of DOMNode', function () {
                        $xml = new FluidXml();
                        $cx = $xml->addChild(['head', 'body'], true);

                        $doc     = new FluidDocument();
                        $handler = new FluidInsertionHandler($doc);
                        $new_cx  = new FluidContext($doc, $handler, $cx->array());

                        $actual   = $new_cx->array();
                        $expected = $cx->array();
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should accept a DOMNodeList', function () {
                        $xml = new FluidXml();
                        $cx = $xml->addChild(['head', 'body'], true);
                        $dom = $xml->dom();

                        $domxp = new \DOMXPath($dom);
                        $nodes = $domxp->query('/doc/*');

                        $doc     = new FluidDocument();
                        $handler = new FluidInsertionHandler($doc);
                        $new_cx  = new FluidContext($doc, $handler, $nodes);

                        $actual   = $new_cx->array();
                        $expected = $cx->array();
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should accept a FluidContext', function () {
                        $xml = new FluidXml();
                        $cx = $xml->addChild(['head', 'body'], true);

                        $doc     = new FluidDocument();
                        $handler = new FluidInsertionHandler($doc);
                        $new_cx  = new FluidContext($doc, $handler, $cx);

                        $actual   = $new_cx->array();
                        $expected = $cx->array();
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should throw for not supported document', function () {
                        $doc     = new FluidDocument();
                        $handler = new FluidInsertionHandler($doc);

                        try {
                                new FluidContext($doc, $handler, 'node');
                        } catch (\Exception $e) {
                                $actual = $e;
                        }

                        assert_is_a($actual, \Exception::class);
                });
        });

        describe('[]', function () {
                it('should access the nodes inside the context', function () {
                        $xml = new FluidXml();
                        $cx = $xml->addChild(['head', 'body'], true);

                        $actual = $cx[0];
                        assert_is_a($actual, \DOMElement::class);

                        $actual = $cx[1];
                        assert_is_a($actual, \DOMElement::class);
                });

                it('should behave like an array', function () {
                        $xml = new FluidXml();
                        $cx = $xml->addChild(['head', 'body', 'extra'], true);

                        $actual   = isset($cx[0]);
                        $expected = true;
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = isset($cx[3]);
                        $expected = false;
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $cx[3];
                        $expected = null;
                        \assert($actual === $expected, __($actual, $expected));

                        try {
                                $cx[] = "value";
                        } catch (\Exception $e) {
                                $actual = $e;
                        }
                        assert_is_a($actual, \Exception::class);

                        unset($cx[1]);

                        $actual   = $cx[0]->nodeName;
                        $expected = 'head';
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $cx[1]->nodeName;
                        $expected = 'extra';
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.array()', function () {
                it('should return an array of nodes inside the context', function () {
                        $xml = new FluidXml(null);

                        $a = $xml->array();

                        $actual   = \is_array($a);
                        $expected = True;
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = \count($a);
                        $expected = 1;
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $a;
                        $expected = [ $xml->dom() ];
                        \assert($actual === $expected, __($actual, $expected));

                        $xml = new FluidXml();

                        $a = $xml->array();

                        $actual   = \is_array($a);
                        $expected = True;
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = \count($a);
                        $expected = 1;
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $a;
                        $expected = [ $xml->dom()->documentElement ];
                        \assert($actual === $expected, __($actual, $expected));

                        $cx = $xml->addChild(['head', 'body'], true);

                        $a = $cx->array();

                        $actual   = \is_array($a);
                        $expected = True;
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = \count($a);
                        $expected = 2;
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.length()', function () {
                it('should return the number of nodes inside the context', function () {
                        $xml = new FluidXml();
                        $cx = $xml->query('/*');

                        $actual   = $xml->length();
                        $expected = 1;
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $cx->length();
                        $expected = 1;
                        \assert($actual === $expected, __($actual, $expected));

                        $cx = $xml->addChild(['child1', 'child2'], true);
                        $actual   = $cx->length();
                        $expected = 2;
                        \assert($actual === $expected, __($actual, $expected));

                        $cx = $cx->addChild(['subchild1', 'subchild2', 'subchild3']);
                        $actual   = $cx->length();
                        $expected = 2;
                        \assert($actual === $expected, __($actual, $expected));

                        $cx = $cx->addChild(['subchild4', 'subchild5', 'subchild6', 'subchild7'], true);
                        $actual   = $cx->length();
                        $expected = 8;
                        \assert($actual === $expected, __($actual, $expected));

                        $expected = "<doc>\n"
                                  . "  <child1>\n"
                                  . "    <subchild1/>\n"
                                  . "    <subchild2/>\n"
                                  . "    <subchild3/>\n"
                                  . "    <subchild4/>\n"
                                  . "    <subchild5/>\n"
                                  . "    <subchild6/>\n"
                                  . "    <subchild7/>\n"
                                  . "  </child1>\n"
                                  . "  <child2>\n"
                                  . "    <subchild1/>\n"
                                  . "    <subchild2/>\n"
                                  . "    <subchild3/>\n"
                                  . "    <subchild4/>\n"
                                  . "    <subchild5/>\n"
                                  . "    <subchild6/>\n"
                                  . "    <subchild7/>\n"
                                  . "  </child2>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });
        });

        describe('.size()', function () {
                it('should behave like .length()', function () {
                        $xml = new FluidXml();

                        $actual   = $xml->size();
                        $expected = $xml->length();
                        \assert($actual === $expected, __($actual, $expected));

                        $cx = $xml->addChild('parent', true)
                                      ->addChild(['child1', 'child2']);

                        $actual   = $cx->size();
                        $expected = $cx->length();
                        \assert($actual === $expected, __($actual, $expected));
                });
        });
});

describe('FluidNamespace', function () {
        describe('.__construct()', function () {
                it('should accept an id, an uri and an optional mode flag', function () {
                        $ns_id   = 'x';
                        $ns_uri  = 'x.com';
                        $ns_mode = FluidNamespace::MODE_EXPLICIT;
                        $ns      = new FluidNamespace($ns_id, $ns_uri);

                        $actual   = $ns->id();
                        $expected = $ns_id;
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $ns->uri();
                        $expected = $ns_uri;
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $ns->mode();
                        $expected = $ns_mode;
                        \assert($actual === $expected, __($actual, $expected));

                        $ns_mode = FluidNamespace::MODE_IMPLICIT;
                        $ns = new FluidNamespace($ns_id, $ns_uri, $ns_mode);

                        $actual   = $ns->mode();
                        $expected = $ns_mode;
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.id()', function () {
                it('should return the namespace id', function () {
                        $ns_id  = 'x';
                        $ns_uri = 'x.com';
                        $ns     = new FluidNamespace($ns_id, $ns_uri);

                        $actual   = $ns->id();
                        $expected = $ns_id;
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.uri()', function () {
                it('should return the namespace uri', function () {
                        $ns_id  = 'x';
                        $ns_uri = 'x.com';
                        $ns     = new FluidNamespace($ns_id, $ns_uri);

                        $actual   = $ns->uri();
                        $expected = $ns_uri;
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.mode()', function () {
                it('should return the namespace mode', function () {
                        $ns_id   = 'x';
                        $ns_uri  = 'x.com';
                        $ns      = new FluidNamespace($ns_id, $ns_uri);
                        $ns_mode = FluidNamespace::MODE_EXPLICIT;

                        $actual   = $ns->mode();
                        $expected = $ns_mode;
                        \assert($actual === $expected, __($actual, $expected));

                        $ns_mode = FluidNamespace::MODE_IMPLICIT;
                        $ns      = new FluidNamespace($ns_id, $ns_uri, $ns_mode);

                        $actual   = $ns->mode();
                        $expected = $ns_mode;
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.querify()', function () {
                it('should format an XPath query to use the namespace id', function () {
                        $ns = new FluidNamespace('x', 'x.com');

                        $actual   = $ns('current/child');
                        $expected = 'x:current/x:child';
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $ns('//current/child');
                        $expected = '//x:current/x:child';
                        \assert($actual === $expected, __($actual, $expected));

                        $ns = new FluidNamespace('x', 'x.com', FluidNamespace::MODE_IMPLICIT);

                        $actual   = $ns('current/child');
                        $expected = 'x:current/x:child';
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $ns('//current/child');
                        $expected = '//x:current/x:child';
                        \assert($actual === $expected, __($actual, $expected));
                });
        });
});

describe('FluidHelper', function () {
        describe(':isAnXmlString()', function () {
                it('should understand if a string is an XML document', function () {
                        $xml = new FluidXml();

                        $actual   = FluidHelper::isAnXmlString($xml->xml());
                        $expected = true;
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = FluidHelper::isAnXmlString(" \n \n \t" . $xml->xml());
                        $expected = true;
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = FluidHelper::isAnXmlString('item');
                        $expected = false;
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe(':domdocumentToHtml()', function () {
                it('should convert a DOMDocument instance to an HTML string without respecting void and not void tags.', function () {
                        // This is only to analyze a condition (not used) for the code coverage reporter.
                        FluidHelper::domdocumentToHtml((new FluidXml())->dom(), true);
                });
        });

        describe(':domdocumentToStringWithoutHeaders()', function () {
                it('should convert a DOMDocument instance to an XML string without the XML headers (declaration and stylesheets)', function () {
                        $xml = new FluidXml();

                        $actual   = FluidHelper::domdocumentToStringWithoutHeaders($xml->dom());
                        $expected = "<doc/>";
                        \assert($actual === $expected, __($actual, $expected));

                        $xml = new FluidXml('doc', ['stylesheet' => 'x.com/style.xsl']);

                        $actual   = FluidHelper::domdocumentToStringWithoutHeaders($xml->dom());
                        $expected = "<doc/>";
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe(':domnodelistToString()', function () {
                it('should convert a DOMNodeList instance to an XML string', function () {
                        $xml   = new FluidXml();
                        $nodes = $xml->dom()->childNodes;

                        $actual   = FluidHelper::domnodelistToString($nodes);
                        $expected = "<doc/>";
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe(':domnodesToString()', function () {
                it('should convert an array of DOMNode instances to an XML string', function () {
                        $xml   = new FluidXml();
                        $nodes = [ $xml->dom()->documentElement ];

                        $actual   = FluidHelper::domnodesToString($nodes);
                        $expected = "<doc/>";
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('simplexmlToStringWithoutHeaders()', function () {
                it('should convert a SimpleXMLElement instance to an XML string without the XML headers (declaration and stylesheets)', function () {
                        $xml = \simplexml_import_dom((new FluidXml())->dom());

                        $actual   = FluidHelper::simplexmlToStringWithoutHeaders($xml);
                        $expected = "<doc/>";
                        \assert($actual === $expected, __($actual, $expected));
                });
        });
});

describe('CssTranslator', function () {
        describe('.xpath()', function () {
                $hml = new FluidXml([ 'html' => [
                        'body' => [
                                'div' => [
                                        [ 'p'  => [ '@class' => 'a', '@id' => '123', [ 'span' ] ] ],
                                        [ 'h1' => [ '@class' => 'b' ] ],
                                        [ 'shape' => [ '@class' => 'c' ] ],
                                        [ 'p'  => [ '@class' => 'a b' ] ],
                                        [ 'p'  => [ '@class' => 'a' ] ],
                                        [ 'span'  => [ '@class' => 'b' ] ],
                ]]]]);

                $hml->namespace('svg', 'http://svg.org');
                $hml->query('//body')
                        ->add('svg:svg', true)
                            ->add('svg:shape')
                            ->add('svg:shape');

                it('should support the CSS selector A', function () use ($hml) {
                        $actual   = $hml->query('p')->array();
                        $expected = $hml->query('//p')->array();
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $hml->query('p')->size();
                        $expected = 3;
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should support the CSS selector ns|A', function () use ($hml) {
                        $actual   = $hml->query('svg|shape')->array();
                        $expected = $hml->query('//svg:shape')->array();
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $hml->query('svg|shape')->size();
                        $expected = 2;
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should support the CSS selector *|A', function () use ($hml) {
                        $actual   = $hml->query('*|shape')->array();
                        $expected = $hml->query('[local-name() = "shape"]')->array();
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $hml->query('*|shape')->size();
                        $expected = 3;
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should support the CSS selector :root', function () use ($hml) {
                        $actual   = $hml->query(':root')->array();
                        $expected = $hml->query('/*')->array();
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $hml->query(':root')->size();
                        $expected = 1;
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should support the CSS selector #id', function () use ($hml) {
                        $actual   = $hml->query('#123')->array();
                        $expected = $hml->query('//*[@id="123"]')->array();
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $hml->query('#123')->size();
                        $expected = 1;
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should support the CSS selector .class.class', function () use ($hml) {
                        $actual   = $hml->query('.a')->array();
                        $expected = $hml->query('//p')->array();
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $hml->query('.a')->size();
                        $expected = 3;
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $hml->query('.a.b')->array();
                        $expected = $hml->query('//p[2]')->array();
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $hml->query('.a.b')->size();
                        $expected = 1;
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $hml->query('h1.b')->array();
                        $expected = $hml->query('//h1')->array();
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $hml->query('h1.b')->size();
                        $expected = 1;
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should support the CSS selector [attr]', function () use ($hml) {
                        $actual   = $hml->query('[class]')->array();
                        $expected = $hml->query('//div/*')->array();
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $hml->query('[class]')->size();
                        $expected = 6;
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $hml->query('[id]')->array();
                        $expected = $hml->query('//*[@id]')->array();
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $hml->query('[id]')->size();
                        $expected = 1;
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should support the CSS selector [attr="val"]', function () use ($hml) {
                        $actual   = $hml->query('p[id="123"]')->array();
                        $expected = $hml->query('//p[@id]')->array();
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $hml->query('p[id="123"]')->size();
                        $expected = 1;
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $hml->query('[class="a"]')->array();
                        $expected = $hml->query('//*[@class="a"]')->array();
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $hml->query('[class="a"]')->size();
                        $expected = 2;
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should support the CSS selector A B', function () use ($hml) {
                        $actual   = $hml->query('div span')->array();
                        $expected = $hml->query('//div//span')->array();
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $hml->query('div span')->size();
                        $expected = 2;
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should support the CSS selector A > B', function () use ($hml) {
                        $actual   = $hml->query('div > p')->array();
                        $expected = $hml->query('//div/p')->array();
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $hml->query('div > p')->size();
                        $expected = 3;
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should support the CSS selector A, B', function () use ($hml) {
                        $actual   = $hml->query('p, div')->array();
                        $expected = $hml->query('//p|//div')->array();
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $hml->query('p, div')->size();
                        $expected = 4;
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should support the CSS selector A + B', function () use ($hml) {
                        $actual   = $hml->query('p + p')->array();
                        $expected = $hml->query('//p[3]')->array();
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $hml->query('p + p')->size();
                        $expected = 1;
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should support the CSS selector A ~ B', function () use ($hml) {
                        $actual   = $hml->query('h1 ~ p')->array();
                        $expected = $hml->query('//p[2]|//p[3]')->array();
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $hml->query('h1 ~ p')->size();
                        $expected = 2;
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should support mixing CSS selectors :root #123 span, div, :root .a', function () use ($hml) {
                        $actual   = $hml->query(':root #123 span, div, :root .a')->array();
                        $expected = $hml->query('//p/span|//div|//*[@class="a"]|//*[@class="a b"]')->array();
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $hml->query(':root #123 span, div, :root .a')->size();
                        $expected = 5;
                        \assert($actual === $expected, __($actual, $expected));
                });
        });
});
