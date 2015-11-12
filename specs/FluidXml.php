<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . ".common.php";

require_once 'FluidXml.php';

use FluidNamespace as Name;

function assert_is_context($actual)
{
        assert($actual instanceof FluidContext, __(
                \get_class($actual),
                FluidContext::class
        ));
}

function assert_equal_xml($actual, $expected)
{
        $xml_header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

        $actual   = \trim($actual->xml());
        $expected = \trim($xml_header . $expected);
        assert($actual === $expected, __($actual, $expected));
}

describe('fluidxml', function() {
        it('should return a new FluidXml instance', function() {
                $xml = fluidxml();

                assert($xml instanceof FluidXml, __(
                        \get_class($xml),
                        FluidXml::class
                ));
        });
});

describe('FluidXml', function() {
        it('should be an UTF-8 XML-1.0 document with one default root element', function() {
                $xml = new FluidXml();

                $expected = "<doc/>";
                assert_equal_xml($xml, $expected);
        });

        it('should be an UTF-8 XML-1.0 document with one custom root element', function() {
                $xml = new FluidXml(['root' => 'document']);

                $expected = "<document/>";
                assert_equal_xml($xml, $expected);
        });

        it('should be an UTF-8 XML-1.0 document with no root element', function() {
                $xml = new FluidXml(['root' => null]);

                $expected = "";
                assert_equal_xml($xml, $expected);
        });

        describe('.query', function() {
                it('should return the root nodes of the document:', function() {
                        // XPATH: /*
                        $xml = new FluidXml();
                        $cx = $xml->query('/*');

                        $actual   = $cx[0]->nodeName;
                        $expected = 'doc';
                        assert($actual === $expected, __($actual, $expected));

                        $xml->appendRoot('meta');
                        $cx = $xml->query('/*');

                        $actual   = $cx[0]->nodeName;
                        $expected = 'doc';
                        assert($actual === $expected, __($actual, $expected));

                        $actual   = $cx[1]->nodeName;
                        $expected = 'meta';
                        assert($actual === $expected, __($actual, $expected));
                });

                it('should support chained relative queries', function() {
                        // XPATH: //child subchild
                        $xml = new FluidXml();
                        $cx = $xml->appendChild('html', true);
                        $cx->appendChild(['head','body']);
                        $cx = $cx->query('body');

                        $actual   = $cx[0]->nodeName;
                        $expected = 'body';
                        assert($actual === $expected, __($actual, $expected));

                        $xml = new FluidXml();
                        $xml->appendChild('html', true)->appendChild(['head','body']);
                        $cx = $xml->query('//html')->query('head');

                        $actual   = $cx[0]->nodeName;
                        $expected = 'head';
                        assert($actual === $expected, __($actual, $expected));
                });

                it('should query the root of the document from a sub query', function() {
                        // XPATH: //child/subchild //child
                        $xml = new FluidXml();
                        $xml->appendChild('html', true)
                            ->appendChild(['head','body']);
                        $cx = $xml->query('//html/body')
                                  ->appendChild('h1')
                                  ->query('//head');

                        $actual   = $cx[0]->nodeName;
                        $expected = 'head';
                        assert($actual === $expected, __($actual, $expected));
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

                        $expected = "<doc>\n"       .
                                    "  <html>\n"    .
                                    "    <head/>\n" .
                                    "    <body>\n"  .
                                    "      <h1/>\n" .
                                    "    </body>\n" .
                                    "  </html>\n"   .
                                    "  <extra/>\n"  .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });
        });

        describe('.appendRoot', function() {
                it('should add more than one root nodes to the document', function() {
                        $xml = new FluidXml();
                        $xml->appendRoot('meta');
                        $xml->appendRoot('extra');
                        $cx = $xml->query('/*');

                        $actual   = $cx[0]->nodeName;
                        $expected = 'doc';
                        assert($actual === $expected, __($actual, $expected));

                        $actual   = $cx[1]->nodeName;
                        $expected = 'meta';
                        assert($actual === $expected, __($actual, $expected));

                        $actual   = $cx[2]->nodeName;
                        $expected = 'extra';
                        assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.appendChild', function() {
                it('should add to the document one child', function() {
                        $xml = new FluidXml();
                        $xml->appendChild('child');

                        $expected = "<doc>\n"      .
                                    "  <child/>\n" .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add to the document two children', function() {
                        $xml = new FluidXml();
                        $xml->appendChild(['child1', 'child2']);

                        $expected = "<doc>\n"           .
                                    "  <child1/>\n"     .
                                    "  <child2/>\n"     .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add to the document two children fluently', function() {
                        $xml = new FluidXml();
                        $xml->appendChild('child1')
                            ->appendChild('child2');

                            $expected = "<doc>\n"           .
                                        "  <child1/>\n"     .
                                        "  <child2/>\n"     .
                                        "</doc>";
                            assert_equal_xml($xml, $expected);
                });

                it('should add to the document one child with a value', function() {
                        $xml = new FluidXml();
                        $xml->appendChild(['child' => 'value']);

                        $expected = "<doc>\n"           .
                                    "  <child>value</child>\n"     .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add to the document two children with a value', function() {
                        $xml = new FluidXml();
                        $xml->appendChild(['child1' => 'value1', 'child2' => 'value2']);

                        $expected = "<doc>\n"                           .
                                    "  <child1>value1</child1>\n"       .
                                    "  <child2>value2</child2>\n"       .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add to the document one child with two attributes', function() {
                        $xml = new FluidXml();
                        $xml->appendChild('child', ['class' => 'Class attr', 'id' => 'Id attr']);

                        $expected = "<doc>\n"   .
                                    "  <child class=\"Class attr\" id=\"Id attr\"/>\n"  .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add to the document two children with two attributes both', function() {
                        $xml = new FluidXml();
                        $xml->appendChild(['child1', 'child2'], ['class' => 'Class Value', 'id' => 'Id Value']);

                        $expected = "<doc>\n"   .
                                    "  <child1 class=\"Class Value\" id=\"Id Value\"/>\n"       .
                                    "  <child2 class=\"Class Value\" id=\"Id Value\"/>\n"       .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should switch context', function() {
                        $xml = new FluidXml();
                        $cx = $xml->appendChild('child', true);

                        assert_is_context($cx);

                        $cx = $xml->appendChild(['child1', 'child2'], true);

                        assert_is_context($cx);
                });
        });

        describe('.appendChild switching context', function() {
                it('should add to the document one child and one subchild', function() {
                        $xml = new FluidXml();
                        $cx = $xml->appendChild('child', true)
                                  ->appendChild('subchild');

                        assert_is_context($cx);

                        $expected = "<doc>\n"           .
                                    "  <child>\n"       .
                                    "    <subchild/>\n" .
                                    "  </child>\n"      .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add to the document one child and two subchildren', function() {
                        $xml = new FluidXml();
                        $cx = $xml->appendChild('child', true)
                                  ->appendChild(['subchild1', 'subchild2']);

                        assert_is_context($cx);

                        $expected = "<doc>\n"                   .
                                    "  <child>\n"               .
                                    "    <subchild1/>\n"        .
                                    "    <subchild2/>\n"        .
                                    "  </child>\n"              .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add to the document one child and two subchildren fluently', function() {
                        $xml = new FluidXml();
                        $cx = $xml->appendChild('child', true)
                                  ->appendChild('subchild1')
                                  ->appendChild('subchild2');

                        assert_is_context($cx);

                        $expected = "<doc>\n"                   .
                                    "  <child>\n"               .
                                    "    <subchild1/>\n"        .
                                    "    <subchild2/>\n"        .
                                    "  </child>\n"              .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add to the document one child, one subchild and one subsubchild', function() {
                        $xml = new FluidXml();
                        $cx = $xml->appendChild('child', true)
                                  ->appendChild('subchild', true)
                                  ->appendChild('subsubchild');

                        assert_is_context($cx);

                        $expected = "<doc>\n"                   .
                                    "  <child>\n"               .
                                    "    <subchild>\n"          .
                                    "      <subsubchild/>\n"    .
                                    "    </subchild>\n"         .
                                    "  </child>\n"              .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add to the document one child, one subchild and one subsubchild with one attribute', function() {
                        $xml = new FluidXml();
                        $cx = $xml->appendChild('child', true)
                                  ->appendChild('subchild', true)
                                  ->appendChild('subsubchild', ['attr' => 'Attr Value']);

                        assert_is_context($cx);

                        $expected = "<doc>\n"           .
                                    "  <child>\n"       .
                                    "    <subchild>\n"  .
                                    "      <subsubchild attr=\"Attr Value\"/>\n"        .
                                    "    </subchild>\n" .
                                    "  </child>\n"      .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });
        });

        describe('.insertBefore', function() {
                it('should insert a sibling node before a node', function() {
                        $xml = new FluidXml();
                        $xml->appendChild('parent', true)
                            ->prependSibling('sibling1')
                            ->prependSibling('sibling2');

                        $expected = "<doc>\n"      .
                                    "  <sibling1/>\n" .
                                    "  <sibling2/>\n" .
                                    "  <parent/>\n" .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });
        });

        describe('.insertAfter', function() {
                it('should insert a sibling node before a node', function() {
                        $xml = new FluidXml();
                        $xml->appendChild('parent', true)
                            ->appendSibling('sibling1')
                            ->appendSibling('sibling2');

                        $expected = "<doc>\n"      .
                                    "  <parent/>\n" .
                                    "  <sibling2/>\n" .
                                    "  <sibling1/>\n" .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });
        });

        describe('.appendXml', function() {
                it('should populate the document with an xml document', function() {
                        $xml = new FluidXml(['root'=>null]);
                        $xml->appendXml('<root1/><root2/>', true);

                        $expected = "<root1/>\n" .
                                    "<root2/>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add to the document an xml document', function() {
                        $xml = new FluidXml();
                        $xml->appendXml('<child1/><child2/>');

                        $expected = "<doc>\n"      .
                                    "  <child1/>\n" .
                                    "  <child2/>\n" .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add to a node an xml document', function() {
                        $xml = new FluidXml();
                        $xml->appendChild('parent', true)
                            ->appendXml('<child1/><child2/>');

                        $expected = "<doc>\n"         .
                                    "  <parent>\n"    .
                                    "    <child1/>\n" .
                                    "    <child2/>\n" .
                                    "  </parent>\n"   .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
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

                        $xml->setAttribute('attr2', 'New Attr2 Value');

                        $expected = "<doc attr1=\"Attr1 Value\" attr2=\"New Attr2 Value\"/>";
                        assert_equal_xml($xml, $expected);

                        $xml->setAttribute('attr1', 'New Attr1 Value');

                        $expected = "<doc attr2=\"New Attr2 Value\" attr1=\"New Attr1 Value\"/>";
                        assert_equal_xml($xml, $expected);
                });

                it('should set the attributes of a node', function() {
                        $xml = new FluidXml();
                        $xml->appendChild('child', true)
                            ->setAttribute('attr1', 'Attr1 Value')
                            ->setAttribute('attr2', 'Attr2 Value');

                        $expected = "<doc>\n"   .
                                    "  <child attr1=\"Attr1 Value\" attr2=\"Attr2 Value\"/>\n" .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);

                        $xml = new FluidXml();
                        $xml->appendChild('child', true)
                            ->setAttribute(['attr1' => 'Attr1 Value',
                                            'attr2' => 'Attr2 Value']);

                        $expected = "<doc>\n"   .
                                    "  <child attr1=\"Attr1 Value\" attr2=\"Attr2 Value\"/>\n" .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should change the attributes of a node', function() {
                        $xml = new FluidXml();
                        $xml->appendChild('child', true)
                            ->setAttribute('attr1', 'Attr1 Value')
                            ->setAttribute('attr2', 'Attr2 Value')
                            ->setAttribute('attr2', 'New Attr2 Value');

                        $expected = "<doc>\n"   .
                                    "  <child attr1=\"Attr1 Value\" attr2=\"New Attr2 Value\"/>\n" .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);

                        $xml = new FluidXml();
                        $xml->appendChild('child', true)
                            ->setAttribute(['attr1' => 'Attr1 Value',
                                            'attr2' => 'Attr2 Value'])
                            ->setAttribute('attr1', 'New Attr1 Value');

                        $expected = "<doc>\n"   .
                                    "  <child attr2=\"Attr2 Value\" attr1=\"New Attr1 Value\"/>\n" .
                                    "</doc>";
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

                        $expected = "<doc>\n" .
                                    "  <p>Document Text First Line</p>\n" .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);

                        $cx->appendText('Document Text Second Line');

                        $expected = "<doc>\n" .
                                    "  <p>Document Text First LineDocument Text Second Line</p>\n" .
                                    "</doc>";
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

                        $expected = "<doc>\n" .
                                    "  <p>Document Text</p>\n" .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should change the text of a node', function() {
                        $xml = new FluidXml();
                        $cx = $xml->appendChild('p', true);
                        $cx->setText('Document Text');

                        $expected = "<doc>\n" .
                                    "  <p>Document Text</p>\n" .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);

                        $cx->setText('Document New Text');

                        $expected = "<doc>\n" .
                                    "  <p>Document New Text</p>\n" .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });
        });

        describe('.appendCdata', function() {
                it('should add CDATA to the root node', function() {
                        $xml = new FluidXml();
                        $xml->appendCdata('// <, > and & are characters that should be escaped in a XML context.');

                        $expected = "<doc>" .
                                    "<![CDATA[// <, > and & are characters that should be escaped in a XML context.]]>" .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);

                        $xml->appendCdata('// <second &cdata section>');

                        $expected = "<doc>" .
                                    "<![CDATA[// <, > and & are characters that should be escaped in a XML context.]]>" .
                                    "<![CDATA[// <second &cdata section>]]>" .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add CDATA to a node', function() {
                        $xml = new FluidXml();
                        $cx = $xml->appendChild('pre', true);
                        $cx->appendCdata('// <, > and & are characters that should be escaped in a XML context.');

                        $expected = "<doc>\n" .
                                    "  <pre><![CDATA[// <, > and & are characters that should be escaped in a XML context.]]></pre>\n" .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);

                        $cx->appendCdata('// <second &cdata section>');

                        $expected = "<doc>\n" .
                                    "  <pre><![CDATA[// <, > and & are characters that should be escaped in a XML context.]]>" .
                                       "<![CDATA[// <second &cdata section>]]>" .
                                       "</pre>\n" .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });
        });

        describe('.remove', function() {
                it('should remove a descending node from the root node using xpath', function() {
                        $xml = new FluidXml();
                        $xml->appendChild('parent', true)
                            ->appendChild('child');

                        $xml->remove('//parent/child');

                        $expected = "<doc>\n" .
                                    "  <parent/>\n" .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should remove a descending node from the root node using a context', function() {
                        $xml = new FluidXml();
                        $cx = $xml->appendChild('parent', true)
                                  ->appendChild('child', true);

                        $xml->remove($cx);

                        $expected = "<doc>\n" .
                                    "  <parent/>\n" .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should remove multiple descending nodes from the root node using xpath', function() {
                        $xml = new FluidXml();
                        $xml->appendChild('parent', true)
                            ->appendChild(['child1', 'child2'], ['class'=>'removable']);

                        $xml->remove('//parent/*[@class="removable"]');

                        $expected = "<doc>\n" .
                                    "  <parent/>\n" .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should remove multiple descending nodes from the root node using a context', function() {
                        $xml = new FluidXml();
                        $cx = $xml->appendChild('parent', true)
                                  ->appendChild(['child1', 'child2'], ['class'=>'removable'], true);

                        $xml->remove($cx);

                        $expected = "<doc>\n" .
                                    "  <parent/>\n" .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should remove a child node from a parent node using xpath', function() {
                        $xml = new FluidXml();
                        $cx = $xml->appendChild('parent', true);
                        $cx->appendChild('child');

                        $cx->remove('child');

                        $expected = "<doc>\n" .
                                    "  <parent/>\n" .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should remove a child node from a parent node using a context', function() {
                        $xml = new FluidXml();
                        $parent = $xml->appendChild('parent', true);
                        $cx     = $parent->appendChild('child', true);

                        $parent->remove($cx);

                        $expected = "<doc>\n" .
                                    "  <parent/>\n" .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should remove a descending node from an ancestor node using xpath', function() {
                        $xml = new FluidXml();
                        $cx = $xml->appendChild('parent', true);
                        $cx->appendChild('child', true)
                           ->appendChild('subchild');

                        $cx->remove('child/subchild');

                        $expected = "<doc>\n" .
                                    "  <parent>\n" .
                                    "    <child/>\n" .
                                    "  </parent>\n" .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should remove a descending node from an ancestor node using a context', function() {
                        $xml = new FluidXml();
                        $parent = $xml->appendChild('parent', true);
                        $cx = $parent->appendChild('child', true)
                                     ->appendChild('subchild', true);

                        $cx->remove($cx);

                        $expected = "<doc>\n" .
                                    "  <parent>\n" .
                                    "    <child/>\n" .
                                    "  </parent>\n" .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should remove multiple children nodes from a parent node using xpath', function() {
                        $xml = new FluidXml();
                        $cx = $xml->appendChild('parent', true);
                        $cx->appendChild(['child1', 'child2'], ['class'=>'removable']);

                        $cx->remove('*[@class="removable"]');

                        $expected = "<doc>\n" .
                                    "  <parent/>\n" .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should remove multiple children nodes from a parent node using a context', function() {
                        $xml = new FluidXml();
                        $parent = $xml->appendChild('parent', true);
                        $cx     = $parent->appendChild(['child1', 'child2'], ['class'=>'removable'], true);

                        $cx->remove($cx);

                        $expected = "<doc>\n" .
                                    "  <parent/>\n" .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should remove multiple descending nodes from an ancestor node using xpath', function() {
                        $xml = new FluidXml();
                        $cx = $xml->appendChild('parent', true);
                        $cx->appendChild('child', true)
                           ->appendChild(['subchild1', 'subchild2'], ['class'=>'removable']);

                        $cx->remove('child/*[@class="removable"]');

                        $expected = "<doc>\n" .
                                    "  <parent>\n" .
                                    "    <child/>\n" .
                                    "  </parent>\n" .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should remove multiple descending nodes from an ancestor node using a context', function() {
                        $xml = new FluidXml();
                        $parent = $xml->appendChild('parent', true);
                        $cx = $parent->appendChild('child', true)
                                     ->appendChild(['subchild1', 'subchild2'],
                                                   ['class'=>'removable'],
                                                   true);

                        $parent->remove($cx);

                        $expected = "<doc>\n" .
                                    "  <parent>\n" .
                                    "    <child/>\n" .
                                    "  </parent>\n" .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });
        });
});

describe('FluidContext', function() {
        it('should be iterable returning the represented DOMNode objects', function() {
                $xml = new FluidXml();
                $cx = $xml->appendChild(['head', 'body'], true);

                $actual = $cx;
                assert($actual instanceof \Iterator, __(
                        \get_class($actual),
                        \Iterator::class
                ));

                $representation = [];
                foreach ($cx as $k => $v) {
                        $actual = \is_int($k);
                        $expected = true;
                        assert($actual === $expected, __($actual, $expected));

                        $actual = $v;
                        assert($actual instanceof \DOMNode, __(
                                \get_class($actual),
                                \DOMNode::class
                        ));

                        $representation[$k] = $v->nodeName;
                }

                $actual = $representation;
                $expected = [0 => 'head', 1 => 'body'];
                assert($actual === $expected, __($actual, $expected));
        });

        describe('[]', function() {
                it('should access the nodes inside the context', function() {
                        $xml = new FluidXml();
                        $cx = $xml->appendChild(['head', 'body'], true);

                        $actual = $cx[0];
                        assert($actual instanceof \DOMElement, __(
                                \get_class($actual),
                                \DOMElement::class
                        ));
                });
        });

        describe('.asArray', function() {
                it('should return an array of nodes inside the context', function() {
                        $xml = new FluidXml();
                        $cx = $xml->appendChild(['head', 'body'], true);

                        $a = $cx->asArray();

                        $actual = $a;
                        assert(\is_array($actual));

                        $actual   = \count($a);
                        $expected = 2;
                        assert($actual === $expected, __($actual, $expected));
                });
        });

        describe('.length', function() {
                it('should return the number of nodes inside the context', function() {
                        $xml = new FluidXml();
                        $cx = $xml->query('/*');

                        $actual   = $cx->length();
                        $expected = 1;
                        assert($actual === $expected, __($actual, $expected));

                        $cx = $xml->appendChild(['child1', 'child2'], true);
                        $actual   = $cx->length();
                        $expected = 2;
                        assert($actual === $expected, __($actual, $expected));

                        $cx = $cx->appendChild(['subchild1', 'subchild2', 'subchild3']);
                        $actual   = $cx->length();
                        $expected = 2;
                        assert($actual === $expected, __($actual, $expected));

                        $cx = $cx->appendChild(['subchild4', 'subchild5', 'subchild6', 'subchild7'], true);
                        $actual   = $cx->length();
                        $expected = 8;
                        assert($actual === $expected, __($actual, $expected));

                        $expected = "<doc>\n"                   .
                                    "  <child1>\n"              .
                                    "    <subchild1/>\n"        .
                                    "    <subchild2/>\n"        .
                                    "    <subchild3/>\n"        .
                                    "    <subchild4/>\n"        .
                                    "    <subchild5/>\n"        .
                                    "    <subchild6/>\n"        .
                                    "    <subchild7/>\n"        .
                                    "  </child1>\n"             .
                                    "  <child2>\n"              .
                                    "    <subchild1/>\n"        .
                                    "    <subchild2/>\n"        .
                                    "    <subchild3/>\n"        .
                                    "    <subchild4/>\n"        .
                                    "    <subchild5/>\n"        .
                                    "    <subchild6/>\n"        .
                                    "    <subchild7/>\n"        .
                                    "  </child2>\n"             .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });
        });
});
