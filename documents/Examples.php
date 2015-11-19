<?php

$source = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'source';
\set_include_path($source . PATH_SEPARATOR . \get_include_path());

////////////////////////////////////////////////////////////////////////////////


require_once 'FluidXml.php';


/*****************************
 * Creating an XML document. *
******************************/

$options = ['root' => 'book'];
$book = new FluidXml($options);                         // It created an XML document with 'book' as root node.

// Possible options are:
$defaultOptions = [ 'version'    => '1.0',
                    'encoding'   => 'UTF-8',
                    'stylesheet' => null,               // The stylesheet URL.
                    'root'       => 'doc' ];



/************************************************************************
 * Customizing the root node with an attribute and some children nodes. *
*************************************************************************/

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
        ->appendChild('chapter', 'Ideas About The Universe', ['id' => 123, 'first' => ''])
        ->appendChild('chapter', 'The Expanding Universe',   ['id' => 321])
        ->appendChild('chapter', 'Black Holes',              ['id' => 432])
        ->appendChild('chapter', 'Black Holes Ain\'t So Black', ['id' =>234]);



/************************************************
 * Child name and child value accepted formats. *
*************************************************/

/*
* Inserting a node can be performed in different ways,
* each one with its pros and cons.
* In this examples, it is used the concise syntax, but the same concepts
* are applicable to the standard syntax.
*/


$food = fluidxml();
$food->add('fruit')                                             // A 'fruit' node with an empty content.
     ->add('fruit', 'orange')                                   // A 'fruit' node with 'Orange' as content.
     ->add('fruit', 'apple',        ['price' => 'expensive'])   // Apple is expensive. :D
     ->add([ 'fruit' => 'grapes' ], ['price' => 'cheap'])       // Grapes are cheap.
     ->add([ 'Tiramisu',
               'pizza' => 'Margherita' ])                       // Batch insertion of nodes.
     ->add([ ['egg'],
               ['egg'],
               ['egg'] ], ['price' => '0.25'])                  // Adding a bunch of 'egg's all with the same price.
     ->add([ 'fridge' => [
                    'omelette' => 'with potato',
                    'soupe'    => 'wit mashrooms']]);           // Deep tree structures are supported too.

echo $food->xml();
echo "————————————————————————————————————————————————————————————————————————————————\n";



/*********************
 * Chaining queries. *
**********************/

/*
* Chaining queries gives you great flexibility to traverse the document
* without loosing the manipulation flow.
*/

$book->query('//chapter')
     ->setAttribute('lang', 'en')
     ->query('..')
     ->setAttribute('lang', 'en')
     ->query('/book/title')
     ->setAttribute('lang', 'en');



/*****************************
 * Raw XML strings insertion.*
******************************/

/*
* Sometimes XML/XHTML comes from legacy templating systems
* or external sources. In those cases the raw XML string can be injected too.
*/

$book->appendChild('cover', true)
     ->appendXml(<<<TPL
        <h1>The Theory Of Everything</h1>
        <img src="http://goo.gl/kO3Iov"/>
TPL
);



/**********************************
 * Array access and DOMNode apis. *
***********************************/

/*
* The result of a query can be accessed even as array.
* Accessing the result of a query as array performs the unwrapping of the node
* and returns a raw instance of DOMNode.
* You loose the FluidXML interface but gain direct access to the DOMNode apis.
*/

$chaptersList = $book->query('//chapter');

$l = $chaptersList->length();

$lastChapterNode = $chaptersList[$l - 1];                // Returns a raw DOMNode.


/*
* This ->setAttribute is the DOMNode::setAttribute.
* Many other methods/properties are available as:
* - hasAttribute()
* - getAttribute()
* - nodeValue
* See http://php.net/manual/en/class.domnode.php
*/
$lastChapterNode->setAttribute('last', '');


/*
* To retrieve all DOMNode in one operation there is the ->asArray() method.
*/
$chaptersListNodes = $chaptersList->asArray();          // Returns an array of DOMNode.

echo $book->xml();
echo "————————————————————————————————————————————————————————————————————————————————\n";


/*
* Because the result of a query behaves like an array, can be iterated too.
*/
foreach ($chaptersList as $i => $chapter) {
        // $chapter is an instance of DOMNode.

        // This is a dummy example to explain how to access the content of a node.
        if ($chapter->hasAttribute('first')) {
                echo "The first chapter has title '{$chapter->nodeValue}'"
                     . " with id '{$chapter->getAttribute('id')}'.\n";
        } else {
                $ii = $i + 1;
                echo "Chapter {$ii} has title '{$chapter->nodeValue}'"
                     . " with id '{$chapter->getAttribute('id')}'.\n";
        }
}
echo "————————————————————————————————————————————————————————————————————————————————\n";
