<?php

require_once 'source/FluidXml.php';

$doc = new FluidXml();

$doc->setAttribute('type', 'book')
    ->appendChild('title', 'The Theory Of Everything')
    ->appendChild('author', 'S. Hawking');

$doc->appendChild('chapters', true)
       ->appendChild('chapter', 'Ideas About The Universe', ['id'=> 1])
       ->appendChild('chapter', 'The Expanding Universe',   ['id'=> 2]);

$doc->query('//chapter')
    ->setAttribute('lang', 'en')
    ->query('..')
    ->setAttribute('lang', 'en');

$cover = '<h1>The Theory Of Everything</h1>'
        .'<img src="http://goo.gl/kO3Iov"/>';

$doc->appendChild('cover', true)
    ->appendXml($cover);

echo $doc->xml();
