<?php

$ds = \DIRECTORY_SEPARATOR;
$source_dir = __DIR__ . "{$ds}..{$ds}source";
\set_include_path($source_dir . \PATH_SEPARATOR . \get_include_path());

use \FluidXml\FluidXml;
use \FluidXml\FluidInterface;

function __($actual, $expected)
{
        $v = [ 'actual'   => \var_export($actual, true),
               'expected' => \var_export($expected, true) ];

        $sep = ' ';
        $msg_l = \strlen($v['actual'] . $v['expected']);

        if ($msg_l > 60) {
                $sep = "\n";
        }

        return "expected " . $v['expected'] . ",$sep"
               . "given " . $v['actual'] . ".";
}

function assert_equal_xml($actual, $expected)
{
        $xml_header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

        $actual   = \trim($actual->xml());
        $expected = \trim($xml_header . $expected);
        \assert($actual === $expected, __($actual, $expected));
}

function assert_is_a($actual, $expected)
{
        \assert(\is_a($actual, $expected) === true, __(\get_class($actual), $expected));
}

function assert_is_fluid($method, ...$args)
{
        $instance = new FluidXml();

        if (\method_exists($instance, $method)) {
                $actual   = \call_user_func([$instance, $method], ...$args);
                $expected = FluidInterface::class;
                assert_is_a($actual, $expected);
        }

        $instance = $instance->query('/*');

        if (\method_exists($instance, $method)) {
                $actual   = \call_user_func([$instance, $method], ...$args);
                $expected = FluidInterface::class;
                assert_is_a($actual, $expected);
        }
}
