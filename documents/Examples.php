<?php

$source = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'source';
\set_include_path($source . PATH_SEPARATOR . \get_include_path());

////////////////////////////////////////////////////////////////////////////////


require_once 'FluidXml.php';


/*****************************
 * Creating an XML document. *
******************************/

$book = new FluidXml('book');
// or
$book = new FluidXml(['root' => 'book']);

// $book is an XML document with 'book' as root node.

// The default options are:
// [ 'root'       => 'doc',      // The root node of the document.
//   'version'    => '1.0',      // The version for the XML header.
//   'encoding'   => 'UTF-8',    // The encoding for the XML header.
//   'stylesheet' => null ];     // An url pointing to an XSL file.

$booksheet = new FluidXml('book', ['stylesheet' => 'http://domain.com/style.xsl']);


$book->setAttribute('type', 'science')                  // It sets an attribute of the root node ('book').
     ->appendChild([ 'title'  => 'The Theory Of Everything',
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
* - appendChild($child, ...$optionals);
* - prependSibling($sibling, ...$optionals);
* - appendSibling($sibling, ...$optionals);
* and their alias methods (of course).
*/

$book->appendChild('chapters', true)                     // true forces the return of the 'chapters' node.
        ->appendChild('chapter', 'Ideas About The Universe',    ['id' => 123, 'first' => ''])
        ->appendChild('chapter', 'The Expanding Universe',      ['id' => 321])
        ->appendChild('chapter', 'Black Holes',                 ['id' => 432])
        ->appendChild('chapter', 'Black Holes Ain\'t So Black', ['id' =>234]);



/********************
 * Appending nodes. *
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
$food->add([ ['egg'],
             ['egg'],
             ['egg'] ], ['price' => '0.25']);

// Complex array structures are supported too.
$food->add([ 'fridge' => [
                 'firstFloor' => [
                     'omelette' => 'with potato' ],
                 'secondFloor' => [
                     'soupe' => 'with mashrooms' ]
             ],
             'freezer' => [
                 'firstFloor' => [
                     'meat' => 'beef' ],
                 'secondFloor' => [
                     'fish' => 'tuna' ],
             ] ]);

echo $food->xml();
echo "————————————————————————————————————————————————————————————————————————————————\n";



/*****************************
 * Raw XML strings insertion.*
******************************/

/*
* Sometimes XML/XHTML comes from legacy templating systems
* or external sources. In those cases the raw XML string can be injected too.
*/

$book->appendChild('cover', true)
     ->appendXml(<<<XML
        <h1>The Theory Of Everything</h1>
        <img src="http://goo.gl/kO3Iov"/>
XML
);



/******************
 * XPath queries. *
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
$book->query('//chapter',
             '//chapters',
             '/book/title')
     ->attr('lang', 'en');



/**********************************
 * Array access and DOMNode apis. *
***********************************/

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
* This ->setAttribute is the DOMNode::setAttribute.
* not the FluidXml::setAttribute().
* Many other methods/properties are available like:
* - hasAttribute()
* - getAttribute()
* - nodeValue
* See http://php.net/manual/en/class.domnode.php for the reference documentation.
*/


/*
* To retrieve all DOMNode in one operation there is the ->asArray() method.
*/
$chapters_nodes = $chapters->asArray();          // Returns an array of DOMNode.

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
echo "————————————————————————————————————————————————————————————————————————————————\n";



/***************
 * Namespaces. *
****************/


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
