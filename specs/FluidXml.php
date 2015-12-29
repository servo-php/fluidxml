<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . ".common.php";

describe('fluidxml', function() {
        it('should behave like FluidXml::__construct', function() {
                $xml   = new FluidXml();
                $alias = fluidxml();

                $actual   = $alias->xml();
                $expected = $xml->xml();
                \assert($actual === $expected, __($actual, $expected));

                $options = [ 'root'       => 'root',
                             'version'    => '1.2',
                             'encoding'   => 'UTF-16',
                             'stylesheet' => 'stylesheet.xsl' ];

                $xml   = new FluidXml($options);
                $alias = fluidxml($options);

                $actual   = $alias->xml();
                $expected = $xml->xml();
                \assert($actual === $expected, __($actual, $expected));
        });
});

describe('fluidify', function() {
        it('should behave like FluidXml::load', function() {
                $doc   = '<tag>content</tag>';
                $xml   = FluidXml::load($doc);
                $alias = fluidify($doc);

                $actual   = $alias->xml();
                $expected = $xml->xml();
                \assert($actual === $expected, __($actual, $expected));
        });
});

describe('fluidns', function() {
        it('should behave like FluidNamespace::__construct', function() {
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

describe('FluidXml', function() {
        it('should be an UTF-8 XML-1.0 document with one default root element', function() {
                $xml = new FluidXml();

                $expected = "<doc/>";
                assert_equal_xml($xml, $expected);
        });

        it('should be an UTF-8 XML-1.0 document with one custom root element', function() {
                $xml = new FluidXml('document');

                $expected = "<document/>";
                assert_equal_xml($xml, $expected);

                $xml = new FluidXml(['root' => 'document']);

                $expected = "<document/>";
                assert_equal_xml($xml, $expected);
        });

        it('should be an UTF-8 XML-1.0 document with no root element', function() {
                $xml = new FluidXml(['root' => null]);

                $expected = "";
                assert_equal_xml($xml, $expected);
        });

        it('should be an UTF-8 XML-1.0 document with a stylesheet', function() {
                $xml = new FluidXml(['stylesheet' => 'http://servo-php.org/fluidxml']);

                $expected = "<?xml-stylesheet type=\"text/xsl\" encoding=\"UTF-8\" indent=\"yes\" href=\"http://servo-php.org/fluidxml\"?>\n<doc/>";
                assert_equal_xml($xml, $expected);

                $xml = new FluidXml(['root' => null, 'stylesheet' => 'http://servo-php.org/fluidxml']);
                $expected = "<?xml-stylesheet type=\"text/xsl\" encoding=\"UTF-8\" indent=\"yes\" href=\"http://servo-php.org/fluidxml\"?>";
                assert_equal_xml($xml, $expected);
        });

        describe(':load', function() {
                $doc = "<root>\n"
                     . "  <parent>content</parent>\n"
                     . "</root>";
                $dom = new \DOMDocument();
                $dom->loadXML($doc);

                it('should import an XML string', function() use ($doc, $dom) {
                        $exp = $dom->saveXML();
                        // This $exp has the XML header.

                        // The first empty line is used to test the trim of the string.
                        $xml = FluidXml::load("\n " . $exp);

                        $expected = $doc;
                        assert_equal_xml($xml, $expected);

                        // This $exp is deprived of the XML header.
                        $xml = FluidXml::load("\n " . \substr($exp, \strpos($exp, "\n") + 1));

                        $expected = $doc;
                        assert_equal_xml($xml, $expected);
                });

                it('should import an XML file', function() use ($doc) {
                        $ds   = \DIRECTORY_SEPARATOR;
                        $file = __DIR__ . "{$ds}..{$ds}sandbox{$ds}.fixture.xml";
                        \file_put_contents($file, $doc);
                        $xml = FluidXml::load($file);
                        \unlink($file);

                        $expected = $doc;
                        assert_equal_xml($xml, $expected);
                });

                it('should import a DOMDocument', function() use ($doc, $dom) {
                        $xml = FluidXml::load($dom);

                        $expected = $doc;
                        assert_equal_xml($xml, $expected);
                });

                it('should import a DOMNode', function() use ($dom) {
                        $domxp = new \DOMXPath($dom);
                        $nodes = $domxp->query('/root/parent');
                        $xml = FluidXml::load($nodes[0]);

                        $expected = "<parent>content</parent>";
                        assert_equal_xml($xml, $expected);
                });

                it('should import a DOMNodeList', function() use ($dom) {
                        $domxp = new \DOMXPath($dom);
                        $nodes = $domxp->query('/root/parent');
                        $xml = FluidXml::load($nodes);

                        $expected = "<parent>content</parent>";
                        assert_equal_xml($xml, $expected);
                });

                it('should import a SimpleXMLElement', function() use ($doc, $dom) {
                        $xml = FluidXml::load(\simplexml_import_dom($dom));

                        $expected = $doc;
                        assert_equal_xml($xml, $expected);
                });

                it('should import a FluidXml', function() use ($doc) {
                        $xml = FluidXml::load(FluidXml::load($doc));

                        $expected = $doc;
                        assert_equal_xml($xml, $expected);
                });

                it('should import a FluidContext', function() use ($doc) {
                        $cx  = FluidXml::load($doc)->query('/root');
                        $xml = FluidXml::load($cx);

                        $expected = $doc;
                        assert_equal_xml($xml, $expected);
                });

                it('should throw for not existing file', function() {
                        try {
                                $xml = FluidXml::load('.impossible.xml');
                        } catch (\Exception $e) {
                                $actual   = $e;
                        }

                        assert_is_a($actual, \Exception::class);
                });

                it('should throw for not supported documents', function() {
                        try {
                                $xml = FluidXml::load([]);
                        } catch (\Exception $e) {
                                $actual   = $e;
                        }

                        assert_is_a($actual, \Exception::class);
                });
        });

        if (\version_compare(\phpversion(), '7', '>=')) {
        describe(':new', function() {
                it('should behave like FluidXml::__construct', function() {
                        $xml   = new FluidXml();
                        eval('$alias = FluidXml::new();');

                        $actual   = $alias->xml();
                        $expected = $xml->xml();
                        \assert($actual === $expected, __($actual, $expected));

                        $options = [ 'root'       => 'root',
                                     'version'    => '1.2',
                                     'encoding'   => 'UTF-16',
                                     'stylesheet' => 'stylesheet.xsl' ];

                        $xml   = new FluidXml($options);
                        eval('$alias = FluidXml::new($options);');

                        $actual   = $alias->xml();
                        $expected = $xml->xml();
                        \assert($actual === $expected, __($actual, $expected));
                });
        });
        }

        describe('.dom', function() {
                it('should return the DOMDocument of the document', function() {
                        $xml = new FluidXml();

                        $actual = $xml->dom();
                        assert_is_a($actual, \DOMDocument::class);
                });
        });

        describe('.namespaces', function() {
                it('should return the registered namespaces', function() {
                        $xml  = new FluidXml();
                        $ns1  = new FluidNamespace('x', 'x.com');
                        $ns2  = fluidns('xx', 'xx.com', FluidNamespace::MODE_IMPLICIT);

                        $xml->namespace($ns1);
                        $xml->namespace($ns2);

                        $nss = $xml->namespaces();

                        $actual   = $nss[$ns1->id()];
                        $expected = $ns1;
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $nss[$ns2->id()];
                        $expected = $ns2;
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.namespace', function() {
                it('should accept a namespace', function() {
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

                it('should accept an id, an uri and an optional mode flag', function() {
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

                it('should accept variable namespaces arguments', function() {
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

                it('should accept an array of namespaces', function() {
                        $xml   = new FluidXml();
                        $x_ns  = new FluidNamespace('x', 'x.com');
                        $xx_ns = fluidns('xx', 'xx.com', FluidNamespace::MODE_IMPLICIT);

                        $nss = $xml->namespace([ $x_ns, $xx_ns ])
                                   ->namespaces();

                        $actual   = $nss[$x_ns->id()];
                        $expected = $x_ns;
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $nss[$xx_ns->id()];
                        $expected = $xx_ns;
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.query', function() {
                it('should return the root nodes of the document', function() {
                        // XPATH: /*
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

                it('should accept an array of queries', function() {
                        $xml = new FluidXml();
                        $xml->appendChild('html', true)
                            ->appendChild(['head','body']);
                        $xml->query(['//html', '//head', '//body'])
                            ->setAttribute('lang', 'en');

                        $expected = "<doc>\n"
                                  . "  <html lang=\"en\">\n"
                                  . "    <head lang=\"en\"/>\n"
                                  . "    <body lang=\"en\"/>\n"
                                  . "  </html>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);

                        $xml = new FluidXml();
                        $xml->appendChild('html', true)
                            ->appendChild(['head','body'])
                            ->query(['.', 'head', 'body'])
                            ->setAttribute('lang', 'en');

                        $expected = "<doc>\n"
                                  . "  <html lang=\"en\">\n"
                                  . "    <head lang=\"en\"/>\n"
                                  . "    <body lang=\"en\"/>\n"
                                  . "  </html>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should accept variable queries arguments', function() {
                        $xml = new FluidXml();
                        $xml->appendChild('html', true)
                            ->appendChild(['head','body']);
                        $xml->query('//html', '//head', '//body')
                            ->setAttribute('lang', 'en');

                        $expected = "<doc>\n"
                                  . "  <html lang=\"en\">\n"
                                  . "    <head lang=\"en\"/>\n"
                                  . "    <body lang=\"en\"/>\n"
                                  . "  </html>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);

                        $xml = new FluidXml();
                        $xml->appendChild('html', true)
                            ->appendChild(['head','body'])
                            ->query('.', 'head', 'body')
                            ->setAttribute('lang', 'en');

                        $expected = "<doc>\n"
                                  . "  <html lang=\"en\">\n"
                                  . "    <head lang=\"en\"/>\n"
                                  . "    <body lang=\"en\"/>\n"
                                  . "  </html>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should support relative queries', function() {
                        // XPATH: //child subchild
                        $xml = new FluidXml();
                        $cx = $xml->appendChild('html', true);
                        $cx->appendChild(['head','body']);
                        $cx = $cx->query('body');

                        $actual   = $cx[0]->nodeName;
                        $expected = 'body';
                        \assert($actual === $expected, __($actual, $expected));

                        $xml = new FluidXml();
                        $xml->appendChild('html', true)->appendChild(['head','body']);
                        $cx = $xml->query('/doc/html')->query('head');

                        $actual   = $cx[0]->nodeName;
                        $expected = 'head';
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should query the root of the document from a sub query', function() {
                        // XPATH: //child/subchild //child
                        $xml = new FluidXml();
                        $xml->appendChild('html', true)
                            ->appendChild(['head','body']);
                        $cx = $xml->query('/doc/html/body')
                                  ->appendChild('h1')
                                  ->query('/doc/html/head');

                        $actual   = $cx[0]->nodeName;
                        $expected = 'head';
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should perform relative queries ascending the DOM tree', function() {
                        // XPATH: //child/subchild ../..
                        $xml = new FluidXml();
                        $xml->appendChild('html', true)
                            ->appendChild(['head','body'], true)
                            ->query('../body')
                            ->appendChild('h1')
                            ->query('../..')
                            ->appendChild('extra');

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

                it('should query namespaced nodes', function() {
                        $xml   = new FluidXml();
                        $x_ns  = new FluidNamespace('x', 'x.com');
                        $xx_ns = fluidns('xx', 'xx.com', FluidNamespace::MODE_IMPLICIT);

                        $xml->namespace($x_ns, $xx_ns);

                        $xml->appendChild('x:a',  true)
                            ->appendChild('x:b',  true)
                            ->appendChild('xx:c', true)
                            ->appendChild('xx:d', true)
                            ->appendChild('e',    true)
                            ->appendChild('x:f',  true)
                            ->appendChild('g');

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
        });

        describe('.appendChild', function() {
                it('should add a child', function() {
                        $xml = new FluidXml();
                        $xml->appendChild('child1')
                            ->appendChild('child2')
                            ->appendChild('parent', true)
                            ->appendChild('child3')
                            ->appendChild('child4');

                        $expected = "<doc>\n"
                                  . "  <child1/>\n"
                                  . "  <child2/>\n"
                                  . "  <parent>\n"
                                  . "    <child3/>\n"
                                  . "    <child4/>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add many children', function() {
                        $xml = new FluidXml();
                        $xml->appendChild(['child1', 'child2'])
                            ->appendChild('parent', true)
                            ->appendChild(['child3', 'child4']);

                        $expected = "<doc>\n"
                                  . "  <child1/>\n"
                                  . "  <child2/>\n"
                                  . "  <parent>\n"
                                  . "    <child3/>\n"
                                  . "    <child4/>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add many children recursively', function() {
                        $xml = new FluidXml();
                        $xml->appendChild(['child1'=>['child11'=>['child111', 'child112'=>'value112'], 'child12'=>'value12'],
                                           'child2'=>['child21', 'child22'=>['child221', 'child222']]])
                            ->appendChild('parent', true)
                            ->appendChild(['child3'=>['child31'=>['child311', 'child312'=>'value312'], 'child32'=>'value32'],
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

                it('should add a child with a value', function() {
                        $xml = new FluidXml();
                        $xml->appendChild(['child1' => 'value1'])
                            ->appendChild('child2', 'value2')
                            ->appendChild('parent', true)
                            ->appendChild(['child3' => 'value3'])
                            ->appendChild('child4', 'value4');

                        $expected = "<doc>\n"
                                  . "  <child1>value1</child1>\n"
                                  . "  <child2>value2</child2>\n"
                                  . "  <parent>\n"
                                  . "    <child3>value3</child3>\n"
                                  . "    <child4>value4</child4>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add many children with a value', function() {
                        $xml = new FluidXml();
                        $xml->appendChild(['child1' => 'value1', 'child2' => 'value2'])
                             ->appendChild('parent', true)
                             ->appendChild(['child3' => 'value3', 'child4' => 'value4']);

                        $expected = "<doc>\n"
                                  . "  <child1>value1</child1>\n"
                                  . "  <child2>value2</child2>\n"
                                  . "  <parent>\n"
                                  . "    <child3>value3</child3>\n"
                                  . "    <child4>value4</child4>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);

                        $xml = new FluidXml();
                        $xml->appendChild([ 'child', ['child'], ['child' => 'value1'], ['child' => 'value2'] ])
                             ->appendChild('parent', true)
                             ->appendChild([ 'child', ['child'], ['child' => 'value3'], ['child' => 'value4'] ]);

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

                it('should add a child with some attributes', function() {
                        $xml = new FluidXml();
                        $xml->appendChild('child1', ['class' => 'Class attr', 'id' => 'Id attr1'])
                            ->appendChild('parent', true)
                            ->appendChild('child2', ['class' => 'Class attr', 'id' => 'Id attr2']);

                        $expected = "<doc>\n"
                                  . "  <child1 class=\"Class attr\" id=\"Id attr1\"/>\n"
                                  . "  <parent>\n"
                                  . "    <child2 class=\"Class attr\" id=\"Id attr2\"/>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add many children with some attributes both', function() {
                        $xml = new FluidXml();
                        $xml->appendChild(['child1', 'child2'], ['class' => 'Class attr', 'id' => 'Id attr1'])
                            ->appendChild('parent', true)
                            ->appendChild(['child3', 'child4'], ['class' => 'Class attr', 'id' => 'Id attr2']);

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

                it('should switch context', function() {
                        $xml = new FluidXml();

                        $actual = $xml->appendChild('child', true);
                        assert_is_a($actual, FluidContext::class);

                        $actual = $xml->appendChild('child', 'value', true);
                        assert_is_a($actual, FluidContext::class);

                        $actual = $xml->appendChild(['child1', 'child2'], true);
                        assert_is_a($actual, FluidContext::class);

                        $actual = $xml->appendChild(['child1' => 'value1', 'child2' => 'value2'], true);
                        assert_is_a($actual, FluidContext::class);

                        $actual = $xml->appendChild('child', ['attr' => 'value'], true);
                        assert_is_a($actual, FluidContext::class);

                        $actual = $xml->appendChild(['child1', 'child2'], ['attr' => 'value'], true);
                        assert_is_a($actual, FluidContext::class);
                });

                it('should add namespaced children', function() {
                        $xml = new FluidXml();
                        $xml->namespace(new FluidNamespace('x', 'x.com'));
                        $xml->namespace(fluidns('xx', 'xx.com', FluidNamespace::MODE_IMPLICIT));
                        $xml->appendChild('x:xTag1', true)
                            ->appendChild('x:xTag2');
                        $xml->appendChild('xx:xxTag1', true)
                            ->appendChild('xx:xxTag2')
                            ->appendChild('tag3');

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
        });

        describe('.prependSibling', function() {
                it('should add more than one root node to a document with one root node', function() {
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

                it('should add more than one root node to a document with no root node', function() {
                        $xml = new FluidXml(['root'=>null]);
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

                it('should insert a sibling node before a node', function() {
                        $xml = new FluidXml();
                        $xml->appendChild('parent', true)
                            ->prependSibling('sibling1')
                            ->prependSibling('sibling2');

                        $expected = "<doc>\n"
                                  . "  <sibling1/>\n"
                                  . "  <sibling2/>\n"
                                  . "  <parent/>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });
        });

        describe('.appendSibling', function() {
                it('should add more than one root node to a document with one root node', function() {
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

                it('should add more than one root node to a document with no root node', function() {
                        $xml = new FluidXml(['root'=>null]);
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

                it('should insert a sibling node after a node', function() {
                        $xml = new FluidXml();
                        $xml->appendChild('parent', true)
                            ->appendSibling('sibling1')
                            ->appendSibling('sibling2');

                        $expected = "<doc>\n"
                                  . "  <parent/>\n"
                                  . "  <sibling2/>\n"
                                  . "  <sibling1/>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });
        });

        describe('.appendXml', function() {
                $doc = "<doc>\n"
                     . "  <parent>content</parent>\n"
                     . "</doc>";
                $dom = new \DOMDocument();
                $dom->loadXML($doc);

                it('should fill the document with an XML string', function() {
                        $xml = new FluidXml(['root' => null]);
                        $xml->appendXml('<root/>');

                        $expected = "<root/>";
                        assert_equal_xml($xml, $expected);
                });

                it('should fill the document with an XML string with multiple root nodes', function() {
                        $xml = new FluidXml(['root' => null]);
                        $xml->appendXml('<root1/><root2/>');

                        $expected = "<root1/>\n"
                                  . "<root2/>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add an XML string with multiple root nodes', function() {
                        $xml = new FluidXml();
                        $xml->appendXml('<child1/><child2/>');

                        $expected = "<doc>\n"
                                  . "  <child1/>\n"
                                  . "  <child2/>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);

                        $xml = new FluidXml();
                        $xml->appendChild('parent', true)
                            ->appendXml('<child1/><child2/>');

                        $expected = "<doc>\n"
                                  . "  <parent>\n"
                                  . "    <child1/>\n"
                                  . "    <child2/>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add a DOMDocument', function() use ($doc) {
                        $dom = new DOMDocument();
                        $dom->loadXML('<parent>content</parent>');

                        $xml = new FluidXml();
                        $xml->appendXml($dom);

                        $expected = $doc;
                        assert_equal_xml($xml, $expected);
                });

                it('should add a DOMNode', function() use ($doc, $dom) {
                        $xp    = new \DOMXPath($dom);
                        $nodes = $xp->query('/doc/parent');
                        $xml   = new FluidXml();
                        $xml->appendXml($nodes[0]);

                        $expected = $doc;
                        assert_equal_xml($xml, $expected);
                });

                it('should add a DOMNodeList', function() use ($doc, $dom) {
                        $xp    = new \DOMXPath($dom);
                        $nodes = $xp->query('/doc/parent');
                        $xml   = new FluidXml();
                        $xml->appendXml($nodes);

                        $expected = $doc;
                        assert_equal_xml($xml, $expected);
                });

                it('should add a SimpleXMLElement', function() use ($doc, $dom) {
                        $sxml = \simplexml_import_dom($dom);
                        $xml  = new FluidXml();
                        $xml->appendXml($sxml->children());

                        $expected = $doc;
                        assert_equal_xml($xml, $expected);
                });

                it('should add a FluidXml', function() use ($doc, $dom) {
                        $nodes = $dom->documentElement->childNodes;
                        $fxml = FluidXml::load($nodes);
                        $xml  = new FluidXml();
                        $xml->appendXml($fxml);

                        $expected = $doc;
                        assert_equal_xml($xml, $expected);
                });

                it('should add a FluidContext', function() use ($doc, $dom) {
                        $fxml = FluidXml::load($dom)->query('/doc/parent');
                        $xml  = new FluidXml();
                        $xml->appendXml($fxml);

                        $expected = $doc;
                        assert_equal_xml($xml, $expected);
                });

                it('should throw for not supported input', function() {
                        $xml  = new FluidXml();
                        try {
                                $xml->appendXml([]);
                        } catch (\Exception $e) {
                                $actual   = $e;
                        }

                        assert_is_a($actual, \Exception::class);
                });
        });

        describe('.setAttribute', function() {
                it('should set the attributes of the root node', function() {
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

                it('should change the attributes of the root node', function() {
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

                it('should set the attributes of a node', function() {
                        $xml = new FluidXml();
                        $xml->appendChild('child', true)
                            ->setAttribute('attr1', 'Attr1 Value')
                            ->setAttribute('attr2', 'Attr2 Value');

                        $expected = "<doc>\n"
                                  . "  <child attr1=\"Attr1 Value\" attr2=\"Attr2 Value\"/>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);

                        $xml = new FluidXml();
                        $xml->appendChild('child', true)
                            ->setAttribute(['attr1' => 'Attr1 Value',
                                            'attr2' => 'Attr2 Value']);

                        $expected = "<doc>\n"
                                  . "  <child attr1=\"Attr1 Value\" attr2=\"Attr2 Value\"/>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should change the attributes of a node', function() {
                        $xml = new FluidXml();
                        $xml->appendChild('child', true)
                            ->setAttribute('attr1', 'Attr1 Value')
                            ->setAttribute('attr2', 'Attr2 Value')
                            ->setAttribute('attr2', 'Attr2 New Value');

                        $expected = "<doc>\n"
                                  . "  <child attr1=\"Attr1 Value\" attr2=\"Attr2 New Value\"/>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);

                        $xml = new FluidXml();
                        $xml->appendChild('child', true)
                            ->setAttribute(['attr1' => 'Attr1 Value',
                                            'attr2' => 'Attr2 Value'])
                            ->setAttribute('attr1', 'Attr1 New Value');

                        $expected = "<doc>\n"
                                  . "  <child attr1=\"Attr1 New Value\" attr2=\"Attr2 Value\"/>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });
        });

        describe('.appendText', function() {
                it('should add text to the root node', function() {
                        $xml = new FluidXml();
                        $xml->appendText('Document Text First Line');

                        $expected = "<doc>Document Text First Line</doc>";
                        assert_equal_xml($xml, $expected);

                        $xml->appendText('Document Text Second Line');

                        $expected = "<doc>Document Text First LineDocument Text Second Line</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add text to a node', function() {
                        $xml = new FluidXml();
                        $cx = $xml->appendChild('p', true);
                        $cx->appendText('Document Text First Line');

                        $expected = "<doc>\n"
                                  . "  <p>Document Text First Line</p>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);

                        $cx->appendText('Document Text Second Line');

                        $expected = "<doc>\n"
                                  . "  <p>Document Text First LineDocument Text Second Line</p>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });
        });

        describe('.setText', function() {
                it('should set the text of the root node', function() {
                        $xml = new FluidXml();
                        $xml->setText('Document Text');

                        $expected = "<doc>Document Text</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should change the text of the root node', function() {
                        $xml = new FluidXml();
                        $xml->setText('Document Text');

                        $expected = "<doc>Document Text</doc>";
                        assert_equal_xml($xml, $expected);

                        $xml->setText('Document New Text');

                        $expected = "<doc>Document New Text</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should set the text of a node', function() {
                        $xml = new FluidXml();
                        $cx = $xml->appendChild('p', true);
                        $cx->setText('Document Text');

                        $expected = "<doc>\n"
                                  . "  <p>Document Text</p>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should change the text of a node', function() {
                        $xml = new FluidXml();
                        $cx = $xml->appendChild('p', true);
                        $cx->setText('Document Text');

                        $expected = "<doc>\n"
                                  . "  <p>Document Text</p>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);

                        $cx->setText('Document New Text');

                        $expected = "<doc>\n"
                                  . "  <p>Document New Text</p>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });
        });

        describe('.appendCdata', function() {
                it('should add CDATA to the root node', function() {
                        $xml = new FluidXml();
                        $xml->appendCdata('// <, > and & are characters that should be escaped in a XML context.');

                        $expected = "<doc>"
                                  . "<![CDATA[// <, > and & are characters that should be escaped in a XML context.]]>"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);

                        $xml->appendCdata('// <second &cdata section>');

                        $expected = "<doc>"
                                  . "<![CDATA[// <, > and & are characters that should be escaped in a XML context.]]>"
                                  . "<![CDATA[// <second &cdata section>]]>"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add CDATA to a node', function() {
                        $xml = new FluidXml();
                        $cx = $xml->appendChild('pre', true);
                        $cx->appendCdata('// <, > and & are characters that should be escaped in a XML context.');

                        $expected = "<doc>\n"
                                  . "  <pre><![CDATA[// <, > and & are characters that should be escaped in a XML context.]]></pre>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);

                        $cx->appendCdata('// <second &cdata section>');

                        $expected = "<doc>\n"
                                  . "  <pre><![CDATA[// <, > and & are characters that should be escaped in a XML context.]]>"
                                  .    "<![CDATA[// <second &cdata section>]]>"
                                  .    "</pre>\n"
                                  . "</doc>";
                        assert_equal_xml($xml, $expected);
                });
        });

        describe('.remove', function() {
                $expected = "<doc>\n"
                          . "  <parent/>\n"
                          . "</doc>";

                $new_doc = function() {
                        $xml = new FluidXml();
                        $xml->appendChild('parent', true)
                            ->appendChild(['child1', 'child2'], ['class'=>'removable']);

                        return $xml;
                };

                it('should remove the root node', function() use ($new_doc) {
                        $xml = $new_doc();
                        $xml->remove();

                        assert_equal_xml($xml, '');
                });

                it('should remove the results of a query', function() use ($new_doc, $expected) {
                        $xml = $new_doc();
                        $xml->query('//*[@class="removable"]')->remove();

                        assert_equal_xml($xml, $expected);
                });

                it('should remove the absolute and relative targets of an XPath', function() use ($new_doc, $expected) {
                        $xml = $new_doc();
                        $xml->remove('//*[@class="removable"]');

                        assert_equal_xml($xml, $expected);

                        $xml = $new_doc();
                        $xml->query('/doc')->remove('//*[@class="removable"]');

                        assert_equal_xml($xml, $expected);

                        $xml = $new_doc();
                        $xml->query('/doc/parent')->remove('*[@class="removable"]');

                        assert_equal_xml($xml, $expected);
                });

                it('should remove the absolute and relative targets of an array of XPaths', function() use ($new_doc, $expected) {
                        $xml = $new_doc();
                        $xml->remove(['//child1', '//child2']);

                        assert_equal_xml($xml, $expected);

                        $xml = $new_doc();
                        $xml->query('/doc')->remove(['//child1', '//child2']);

                        assert_equal_xml($xml, $expected);

                        $xml = $new_doc();
                        $xml->query('/doc/parent')->remove(['child1', 'child2']);

                        assert_equal_xml($xml, $expected);
                });

                it('should remove the absolute and relative targets of a variable list of XPaths', function() use ($new_doc, $expected) {
                        $xml = $new_doc();
                        $xml->remove('//child1', '//child2');

                        assert_equal_xml($xml, $expected);

                        $xml = $new_doc();
                        $xml->query('/doc')->remove('//child1', '//child2');

                        assert_equal_xml($xml, $expected);

                        $xml = $new_doc();
                        $xml->query('/doc/parent')->remove('child1', 'child2');

                        assert_equal_xml($xml, $expected);
                });
        });

        describe('.xml', function() {
                it('should return the document as XML string', function() {
                        $xml = new FluidXml();
                        $xml->appendChild('parent', true)
                                ->appendChild('child', 'content');

                        $expected = "<doc>\n"
                                  . "  <parent>\n"
                                  . "    <child>content</child>\n"
                                  . "  </parent>\n"
                                  . "</doc>";

                        assert_equal_xml($xml, $expected);
                });

                it('should return the document as XML string without the XML headers (declaration and stylesheet)', function() {
                        $xml = new FluidXml(['stylesheet' => 'x.com/style.xsl']);
                        $xml->appendChild('parent', true)
                                ->appendChild('child', 'content');

                        $actual   = $xml->xml(true);
                        $expected = "<doc>\n"
                                  . "  <parent>\n"
                                  . "    <child>content</child>\n"
                                  . "  </parent>\n"
                                  . "</doc>";
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should return a node and the inner XML as XML string', function() {
                        $xml = new FluidXml();
                        $xml->appendChild('parent', true)
                                ->appendText('parent content')
                                ->appendChild('child', 'content');

                        $actual   = $xml->query('//parent')->xml();
                        $expected = "<parent>parent content<child>content</child></parent>";
                        \assert($actual === $expected, __($actual, $expected));

                        $xml = new FluidXml();
                        $xml->appendChild('parent', true)
                                ->appendChild('child', 'content1')
                                ->appendChild('child', 'content2');

                        $actual   = $xml->query('//child')->xml();
                        $expected = "<child>content1</child>\n"
                                  . "<child>content2</child>";
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.add', function() {
                it('should behave like .appendChild', function() {
                        $xml = new FluidXml();
                        $xml->appendChild('parent', true)
                            ->appendChild(['child1', 'child2'], ['class'=>'child']);

                        $alias = new FluidXml();
                        $alias->add('parent', true)
                              ->add(['child1', 'child2'], ['class'=>'child']);

                        $actual   = $xml->xml();
                        $expected = $alias->xml();
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.prepend', function() {
                it('should behave like .prependSibling', function() {
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

        describe('.insertSiblingBefore', function() {
                it('should behave like .prependSibling', function() {
                        $xml = new FluidXml();
                        $xml->prependSibling('sibling1', true)
                            ->prependSibling(['sibling2', 'sibling3'], ['class'=>'sibling']);

                        $alias = new FluidXml();
                        $alias->insertSiblingBefore('sibling1', true)
                              ->insertSiblingBefore(['sibling2', 'sibling3'], ['class'=>'sibling']);

                        $actual   = $xml->xml();
                        $expected = $alias->xml();
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.append', function() {
                it('should behave like .appendSibling', function() {
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

        describe('.insertSiblingAfter', function() {
                it('should behave like .appendSibling', function() {
                        $xml = new FluidXml();
                        $xml->appendSibling('sibling1', true)
                            ->appendSibling(['sibling2', 'sibling3'], ['class'=>'sibling']);

                        $alias = new FluidXml();
                        $alias->insertSiblingAfter('sibling1', true)
                              ->insertSiblingAfter(['sibling2', 'sibling3'], ['class'=>'sibling']);

                        $actual   = $xml->xml();
                        $expected = $alias->xml();
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.attr', function() {
                it('should behave like .setAttribute', function() {
                        $xml = new FluidXml();
                        $xml->setAttribute('attr1', 'Attr1 Value')
                            ->setAttribute(['attr2' => 'Attr2 Value', 'attr3' => 'Attr3 Value'])
                            ->appendChild('child', true)
                            ->setAttribute('attr4', 'Attr4 Value')
                            ->setAttribute(['attr5' => 'Attr5 Value', 'attr6' => 'Attr6 Value']);

                        $alias = new FluidXml();
                        $alias->attr('attr1', 'Attr1 Value')
                              ->attr(['attr2' => 'Attr2 Value', 'attr3' => 'Attr3 Value'])
                              ->appendChild('child', true)
                              ->attr('attr4', 'Attr4 Value')
                              ->attr(['attr5' => 'Attr5 Value', 'attr6' => 'Attr6 Value']);

                        $actual   = $xml->xml();
                        $expected = $alias->xml();
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.text', function() {
                it('should behave like .setText', function() {
                        $xml = new FluidXml();
                        $xml->setText('Text1')
                            ->appendChild('child', true)
                            ->setText('Text2');

                        $alias = new FluidXml();
                        $alias->text('Text1')
                              ->appendChild('child', true)
                              ->text('Text2');

                        $actual   = $xml->xml();
                        $expected = $alias->xml();
                        \assert($actual === $expected, __($actual, $expected));
                });
        });
});

describe('FluidContext', function() {
        it('should be iterable returning the represented DOMNode objects', function() {
                $xml = new FluidXml();
                $cx = $xml->appendChild(['head', 'body'], true);

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

        describe('()', function() {
                it('should accept a DOMNodeList', function() {
                        $xml = new FluidXml();
                        $cx = $xml->appendChild(['head', 'body'], true);
                        $dom = $xml->dom();

                        $domxp = new \DOMXPath($dom);
                        $nodes = $domxp->query('/doc/*');

                        $newCx = new FluidContext($nodes);

                        $actual   = $cx->asArray() === $newCx->asArray();
                        $expected = true;
                        \assert($actual === $expected, __($actual, $expected));
                });

                it('should accept a FluidContext', function() {
                        $xml = new FluidXml();
                        $cx = $xml->appendChild(['head', 'body'], true);

                        $newCx = new FluidContext($cx);

                        $actual   = $cx->asArray() === $newCx->asArray();
                        $expected = true;
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('[]', function() {
                it('should access the nodes inside the context', function() {
                        $xml = new FluidXml();
                        $cx = $xml->appendChild(['head', 'body'], true);

                        $actual = $cx[0];
                        assert_is_a($actual, \DOMElement::class);

                        $actual = $cx[1];
                        assert_is_a($actual, \DOMElement::class);
                });

                it('should behave like an array', function() {
                        $xml = new FluidXml();
                        $cx = $xml->appendChild(['head', 'body', 'extra'], true);

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
                                $actual   = $e;
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

        describe('.asArray', function() {
                it('should return an array of nodes inside the context', function() {
                        $xml = new FluidXml();
                        $cx = $xml->appendChild(['head', 'body'], true);

                        $a = $cx->asArray();

                        $actual = $a;
                        \assert(\is_array($actual));

                        $actual   = \count($a);
                        $expected = 2;
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.length', function() {
                it('should return the number of nodes inside the context', function() {
                        $xml = new FluidXml();
                        $cx = $xml->query('/*');

                        $actual   = $cx->length();
                        $expected = 1;
                        \assert($actual === $expected, __($actual, $expected));

                        $cx = $xml->appendChild(['child1', 'child2'], true);
                        $actual   = $cx->length();
                        $expected = 2;
                        \assert($actual === $expected, __($actual, $expected));

                        $cx = $cx->appendChild(['subchild1', 'subchild2', 'subchild3']);
                        $actual   = $cx->length();
                        $expected = 2;
                        \assert($actual === $expected, __($actual, $expected));

                        $cx = $cx->appendChild(['subchild4', 'subchild5', 'subchild6', 'subchild7'], true);
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
});

describe('FluidNamespace', function() {
        describe('()', function() {
                it('should accept an id, an uri and an optional mode flag', function() {
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

                it('should accept an array with an id, an uri and an optional mode flag', function() {
                        $ns_id   = 'x';
                        $ns_uri  = 'x.com';
                        $ns_mode = FluidNamespace::MODE_EXPLICIT;
                        $args    = [ FluidNamespace::ID  => $ns_id,
                                     FluidNamespace::URI => $ns_uri ];
                        $ns      = new FluidNamespace($args);

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
                        $args[FluidNamespace::MODE] = $ns_mode;
                        $ns = new FluidNamespace($args);

                        $actual   = $ns->mode();
                        $expected = $ns_mode;
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.id', function() {
                it('should return the namespace id', function() {
                        $ns_id  = 'x';
                        $ns_uri = 'x.com';
                        $ns     = new FluidNamespace($ns_id, $ns_uri);

                        $actual   = $ns->id();
                        $expected = $ns_id;
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.uri', function() {
                it('should return the namespace uri', function() {
                        $ns_id  = 'x';
                        $ns_uri = 'x.com';
                        $ns     = new FluidNamespace($ns_id, $ns_uri);

                        $actual   = $ns->uri();
                        $expected = $ns_uri;
                        \assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.mode', function() {
                it('should return the namespace mode', function() {
                        $ns_id   = 'x';
                        $ns_uri  = 'x.com';
                        $ns_mode = FluidNamespace::MODE_EXPLICIT;
                        $ns      = new FluidNamespace($ns_id, $ns_uri);

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

        describe('.querify', function() {
                it('should format an XPath query to use the namespace id', function() {
                        $ns = new FluidNamespace('x', 'x.com');

                        $actual   = $ns->querify('current/child');
                        $expected = 'x:current/x:child';
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $ns->querify('//current/child');
                        $expected = '//x:current/x:child';
                        \assert($actual === $expected, __($actual, $expected));

                        $ns = new FluidNamespace('x', 'x.com', FluidNamespace::MODE_IMPLICIT);

                        $actual   = $ns->querify('current/child');
                        $expected = 'x:current/x:child';
                        \assert($actual === $expected, __($actual, $expected));

                        $actual   = $ns->querify('//current/child');
                        $expected = '//x:current/x:child';
                        \assert($actual === $expected, __($actual, $expected));
                });
        });
});
