<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . ".common.php";

require_once 'Servo/FluidXml.php';

use \Servo\FluidXml;
use \Servo\FluidContext;
use \Servo\FluidXmlNs as Name;
use function \Servo\fluidxml;


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
        $expected = $xml_header . $expected;
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

                it('should add to the document one child and switch context', function() {
                        $xml = new FluidXml();
                        $cx = $xml->appendChild('child', true);

                        assert_is_context($cx);

                        $expected = "<doc>\n"           .
                                    "  <child/>\n"      .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
                });

                it('should add to the document two children and switch context', function() {
                        $xml = new FluidXml();
                        $cx = $xml->appendChild(['child1', 'child2'], true);

                        assert_is_context($cx);

                        $expected = "<doc>\n"           .
                                    "  <child1/>\n"     .
                                    "  <child2/>\n"     .
                                    "</doc>";
                        assert_equal_xml($xml, $expected);
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
});

describe('FluidContext', function() {
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

        describe('.query', function() {
                it('should return the root node', function() {
                        $xml = new FluidXml();
                        $r = $xml->query('/*');

                        $actual   = $r[0]->tagName;
                        $expected = 'doc';
                        assert($actual === $expected, __($actual, $expected));
                });
        });
});
