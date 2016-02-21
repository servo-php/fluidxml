<?php

$DS = DIRECTORY_SEPARATOR;
$back = '..' . $DS;
$source = __DIR__ . $DS . $back . $back . 'source';
\set_include_path($source . PATH_SEPARATOR . \get_include_path());

////////////////////////////////////////////////////////////////////////////////


require_once 'FluidXml.php';

use \FluidXml\FluidXml;
use \FluidXml\FluidNamespace;
use function \FluidXml\fluidxml;
use function \FluidXml\fluidns;
use function \FluidXml\fluidify;

/*****************************
 * Creating An XML Document. *
******************************/

$book = new FluidXml('book');
// or
$book = new FluidXml(null, ['root' => 'book']);

// $book is an XML document with 'book' as root node.

// The default options are:
// [ 'root'       => 'doc',      // The root node of the document.
//   'version'    => '1.0',      // The version for the XML header.
//   'encoding'   => 'UTF-8',    // The encoding for the XML header.
//   'stylesheet' => null ];     // An url pointing to an XSL file.

$booksheet = new FluidXml('book', ['stylesheet' => 'http://domain.com/style.xsl']);
// With PHP 7 this is valid too:
// $booksheet = FluidXml::new('book', ['stylesheet' => 'http://domain.com/style.xsl']);

$book->setAttribute('type', 'science')                  // It sets an attribute of the root node ('book').
     ->addChild([ 'title'  => 'The Theory Of Everything',
                     'author' => 'S. Hawking' ]);       // It creates two nodes, each one with some text inside.

echo $book->xml();                                      // Exports the xml document as a string.
echo "————————————————————————————————————————————————————————————————————————————————\n";



/**********************
 * Context Switching. *
***********************/

/*
* Passing a 'true' boolean value to any method that performs an insertion of a node,
* returns the newly created node instead of the parent.
* This operation is called Context Switch.
* Methods that support this context switch are:
* - addChild($child, ...$optionals);
* - prependSibling($sibling, ...$optionals);
* - appendSibling($sibling, ...$optionals);
* and their alias methods (of course).
*/

$book->addChild('chapters', true)                     // true forces the return of the 'chapters' node.
        ->addChild('chapter', 'Ideas About The Universe',    ['id' => 123, 'first' => ''])
        ->addChild('chapter', 'The Expanding Universe',      ['id' => 321])
        ->addChild('chapter', 'Black Holes',                 ['id' => 432])
        ->addChild('chapter', 'Black Holes Ain\'t So Black', ['id' =>234]);



/********************
 * Appending Nodes. *
*********************/

/*
* Inserting a node can be performed in different ways,
* each one with its pros and cons.
*/

/*
* In this examples, it is used the concise syntax, but the same concepts
* are applicable to the standard syntax.
*/

$food = fluidxml('food');

$food->add('fruit')               // A 'fruit' node with an empty content.
     ->add('fruit', 'orange');    // A 'fruit' node with 'orange' as content.

// A node can have even a bunch of attributes.
$food->add('fruit', 'apple', [ 'price' => 'expensive',
                               'color' => 'red' ]);

// Batch insertion of nodes.
$food->add([ 'cake'  => 'Tiramisu',
             'pizza' => 'Margherita' ]);

// PHP arrays can't contain identical keys.
// But it's still possible to create, in a batch operation, nodes with the same tag.
$food->add([ [ 'pasta' => 'Carbonara' ],
             [ 'pasta' => 'Matriciana' ] ]);

// A bunch of egg's all with the same price.
$food->add([ ['egg'], ['egg'], ['egg'] ], ['price' => '0.25']);

// Complex array structures are supported too.
$food->add([ 'menu' => [
                 'pasta' => [
                     'spaghetti' => [
                         '@id'      => '123',
                         '@country' => 'Italy',
                         '@'        => 'Spaghetti are an Italian dish...',

                         'variants' => [
                             'tomato' => [ '@type' => 'vegan' ],
                             'egg'    => [ '@type' => 'vegetarian' ] ]]]]]);

echo $food->xml();
echo "————————————————————————————————————————————————————————————————————————————————\n";



/*************************
 * Importing XML strings.*
**************************/

/*
* Raw XML/XHTML strings che be injected at any point of the document too.
*/

$book->add('cover', true)
        ->add(<<<XML
                <h1>The Theory Of Everything</h1>
                <img src="http://goo.gl/kO3Iov"/>
XML
);


/*
* The document can be filled with a raw XML string.
*/
$html = fluidxml(null);
$html->add(<<<XML
<html>
    <head>
        <meta/>
    </head>
    <body>
        <p/>
    </body>
</html>
XML
);


/*
* Sometimes XML/XHTML comes from legacy templating systems or external sources.
*/

$string = $html->xml();
// XML string import.
$fluid = fluidxml($string);
echo $fluid->xml();
echo "————————————————————————————————————————————————————————————————————————————————\n";


$dom = new DOMDocument();
$dom->loadXML($fluid->xml());
$dom->formatOutput       = true;
$dom->preserveWhiteSpace = false;
// DOMDocument import.
$fluid = fluidxml($dom);
echo $fluid->xml();
echo "————————————————————————————————————————————————————————————————————————————————\n";


$simplexml = new SimpleXMLElement($fluid->xml());
// SimpleXMLElement import.
$fluid = fluidxml($simplexml);
echo $fluid->xml();
echo "————————————————————————————————————————————————————————————————————————————————\n";


// XML file import.
// $fluid = fluidify('path/to/file.xml');

$other = fluidxml($fluid->xml());
$fluid = fluidxml();

$fluid->add($other)                                     // Imports a FluidXml instance.
      ->add([ 'query'     => $fluid->query('//meta'),   // Imports a FluidXml query.
              'dom'       => $dom,                      // Imports a DOMDocument.
              'domnodes'  => $dom->childNodes,          // Imports a DOMNodeList.
              'simplexml' => $simplexml ]);             // Imports a SimpleXMLElement.



/******************
 * XPath Queries. *
*******************/

/*
* XPath queries can be absolute or relative to the context over they are executed.
*/

$eggs   = $food->query('//egg');
$fruits = $food->query('//fruit[@price="expensive"]');

echo "We have {$eggs->length()} eggs and {$fruits->length()} expensive fruit.\n";
echo "————————————————————————————————————————————————————————————————————————————————\n";

$book->query('//chapter')
        ->attr('lang', 'en')
     ->query('..')
        ->attr('lang', 'en')
     ->query('../title')
        ->attr('lang', 'en');

/*
* The previous code presents a repetition: all 'setAttribute' calls are identical.
* It can be refactored taking advantage of an advanced feature of 'query'.
*/
$book->query('//chapter', '//chapters', '/book/title')
        ->attr('lang', 'en');



/*******************************
 * Accessing The Node Content. *
********************************/

/*
* The result of a query can be accessed even as array.
* Accessing the result of a query as array performs the unwrapping of the node
* and returns a raw instance of DOMNode.
* You loose the FluidXML interface but gain direct access to the DOMNode apis.
*/

$chapters = $book->query('//chapter');

$l = $chapters->length();

// DOMNode access.
$chapters[0]->setAttribute('first', '');
$chapters[$l - 1]->setAttribute('last', '');

/*
* The previous ->setAttribute is the DOMNode::setAttribute.
* not the FluidXml::setAttribute().
* Many other methods/properties are available like:
* - hasAttribute()
* - getAttribute()
* - nodeValue
* See http://php.net/manual/en/class.domnode.php for the reference documentation.
*/

echo $book->xml();
echo "————————————————————————————————————————————————————————————————————————————————\n";

/*
* Because the result of a query behaves like an array, it can be iterated too.
*/
foreach ($chapters as $i => $chapter) {
        // $chapter is an instance of DOMNode.

        $title = $chapter->nodeValue;
        $id    = $chapter->getAttribute('id');
        $has_first_attr = $chapter->hasAttribute('first');

        if ($has_first_attr) {
                echo "The first chapter has title '{$title}' with id '{$id}'.\n";
        } else {
                $ii = $i + 1;
                echo "Chapter {$ii} has title '{$title}' with id '{$id}'.\n";
        }
}

/*
* To retrieve all DOMNode in one operation there is the ->asArray() method.
*/
$chapters_nodes = $chapters->array();          // Returns an array of DOMNode.

echo "————————————————————————————————————————————————————————————————————————————————\n";



/***************
 * Namespaces. *
 ***************/


/*
* To use a namespace you have to register its identifier together with its uri.
*/

$xhtml = fluidns('xhtml', 'http://www.w3.org/1999/xhtml');
$book->namespace($xhtml)
     ->namespace('svg', 'http://www.w3.org/2000/svg')
     ->namespace('xsl', 'http://www.w3.org/TR/xsl', FluidNamespace::MODE_IMPLICIT)
     ->add('xhtml:h1')
     ->add([ 'xsl:template'  => [ 'xsl:variable' ] ])
     ->query('//xhtml:h1')
     ->add('svg:shape');

echo $book->xml();
echo "————————————————————————————————————————————————————————————————————————————————\n";



/*******************
 * Removing Nodes. *
 *******************/

$food->remove('//egg');     // Removes all the eggs.

// Which is the same of
// $food->query('//egg')->remove();     // Removes all the eggs using a query.
// $food->query('/doc')->remove('egg'); // Removes all the eggs using a relative XPath.

/* ->remove(...$xpath)
 * accepts the same arguments of
 * ->query(...$xpath)
 * Remember that, like `->query()`, even `->remove()` accepts multiple XPath strings.
 */
$food->remove('//fruit', '//cake', '//pasta', '//pizza');

echo $food->xml();
echo "————————————————————————————————————————————————————————————————————————————————\n";
