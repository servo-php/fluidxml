[wiki]: https://github.com/servo-php/fluidxml/wiki
[apis]: https://github.com/servo-php/fluidxml/blob/master/documents/APIs.md

# Getting Started

FluidXML has been designed to give his best creating and manipulating XML documents.

It's quite common to see inside PHP projects the generation of XML documents through<br/>
template engines, PHP/XML mixing or, in the best case, using DOMDocument.

In every described situation FluidXML performs better in every way.


## Creating Your First XML Document

First of all, depending the method you have chosen to install FluidXML, you have two<br/>
options to include the library.
* If you have cloned the repository, copy the `source/FluidXml.php*` files in your PHP<br/>
project and include it:
  ```php
require_once 'FluidXml.php';
```

* If you have installed the library using Composer, include the autoloader:
  ```php
require_once 'vendor/autoload.php';
```

Now `use` classes and functions you need.
> Extended syntax
> ```php
> use \FluidXml\FluidXml;
> use \FluidXml\FluidNamespace;
> ```
> Concise syntax
> ```php
> use function \FluidXml\fluidxml;
> use function \FluidXml\fluidns;
> use function \FluidXml\fluidify;
> ```

We can proceed to create our first XML document in the simplest way.

> Extended syntax
> ```php
> $book = new FluidXml();
> ```
> Concise syntax
> ```php
> $book = fluidxml();
> ```

It creates a new XML document with one root node by default called `<doc/>`.

```php
echo $book->xml();
```

```xml
<?xml version="1.0" encoding="UTF-8"?>
<doc/>
```

Whether there is the need to influence the document creation, the constructor supports<br/>
some options.

> Extended syntax
> ```php
> $book = new FluidXml('book', ['stylesheet' => 'http://domain.com/style.xsl']);
>
> // Which is the same of
>
> $book = new FluidXml(['root' => 'book', 'stylesheet' => 'http://domain.com/style.xsl']);
> ```
> Concise syntax
> ```php
> $book = fluidxml('book', ['stylesheet' => 'http://domain.com/style.xsl']);
>
> // Which is the same of
>
> $book = fluidxml(['root' => 'book', 'stylesheet' => 'http://domain.com/style.xsl']);
> ```

Our XML document now has a root node called `<book/>`.

> **Pro Tip**:<br/>
> Supported options:
> ```php
> [ 'root'       => 'doc',    // The root node of the document.
>   'version'    => '1.0',    // The version for the XML header.
>   'encoding'   => 'UTF-8',  // The encoding for the XML header.
>   'stylesheet' => null ]    // An url pointing to an XSL file.
> ```

> **Pro Tip**:<br/>
> The Ruby object construction style is supported too with PHP 7.
> ```php
> $doc = FluidXml::new('book', [/* options */]);
> ```


## Adding Nodes

Adding a node is super easy. Because FluidXML implements the fluid OOP pattern, multiple<br/>
operations can be performed chaining methods calls.

> Extended syntax
> ```php
> $book->appendChild('title',  'The Theory Of Everything')
>      ->appendChild('author', 'S. Hawking')
>      ->appendChild('description');
> ```
> Concise syntax
> ```php
> $book->add('title',  'The Theory Of Everything')
>      ->add('author', 'S. Hawking')
>      ->add('description');
> ```

```php
echo $book->xml();
```

```xml
<?xml version="1.0" encoding="UTF-8"?>
<book>
  <title>The Theory Of Everything</title>
  <author>S. Hawking</author>
  <description/>
</book>
```

The `appendChild()`/`add()` method supports up to four arguments to achieve from the simplest<br/>
node insertion to nested trees creation.

> _Public API_
> ```php
> ->appendChild($node, $value?, $attributes? = [], $switchContext? = false)
> ```
> **Pro Tip**:<br/>
> Except for the `$node` argument, all others arguments can be passed in any order.

One of the most important argument is the boolean flag `$switchContext`. Passing a `true`<br/>
boolean value returns the new node instead of the current one.

> Extended syntax
> ```php
> $book->appendChild('chapters', true)
>          ->appendChild('chapter', 'Ideas About The Universe')
>          ->appendChild('chapter', 'The Expanding Universe');
>
> // true asks to return the 'chapters' node instead of the 'book' node.
> ```
> Concise syntax
> ```php
> $book->add('chapters', true)
>          ->add('chapter', 'Ideas About The Universe')
>          ->add('chapter', 'The Expanding Universe');
>
> // true asks to return the 'chapters' node instead of the 'book' node.
> ```

```php
echo $book->xml();
```

```xml
<?xml version="1.0" encoding="UTF-8"?>
<book>
  <title>The Theory Of Everything</title>
  <author>S. Hawking</author>
  <description/>
  <chapters>
    <chapter>Ideas About The Universe</chapter>
    <chapter>The Expanding Universe</chapter>
  </chapters>
</book>
```

Chaining methods calls is nice, but sometimes it's more convenient creating nodes<br/>
in a batch operation, for example when the nodes' structure is defined using an array.<br/>
To demonstrate this concept, we create a new document that will be filled with food.

> Extended syntax
> ```php
> $food = new FluidXml('food');
>
> $food->appendChild('fruit')               // A 'fruit' node with an empty content.
>      ->appendChild('fruit', 'orange');    // A 'fruit' node with 'orange' as content.
>
>
> // Batch insertion of nodes.
>
> $food->appendChild([ 'cake'  => 'Tiramisu',
>                      'pizza' => 'Margherita' ]);
>
>
> // PHP arrays can't contain identical keys.
> // But it's still possible to create, in a batch operation, nodes with the same tag.
>
> $food->appendChild([ [ 'pasta' => 'Carbonara' ],
>                      [ 'pasta' => 'Matriciana' ] ]);
> ```
> Concise syntax
> ```php
> $food = fluidxml('food');
>
> $food->add('fruit')               // A 'fruit' node with an empty content.
>      ->add('fruit', 'orange');    // A 'fruit' node with 'orange' as content.
>
>
> // Batch insertion of nodes.
>
> $food->add([ 'cake'  => 'Tiramisu',
>              'pizza' => 'Margherita' ]);
>
>
> // PHP arrays can't contain identical keys.
> // But it's still possible to create, in a batch operation, nodes with the same tag.
>
> $food->add([ [ 'pasta' => 'Carbonara' ],
>              [ 'pasta' => 'Matriciana' ] ]);
> ```

```xml
<?xml version="1.0" encoding="UTF-8"?>
<food>
  <fruit/>
  <fruit>orange</fruit>
  <cake>Tiramisu</cake>
  <pizza>Margherita</pizza>
  <pasta>Carbonara</pasta>
  <pasta>Matriciana</pasta>
</food>
```

Another important argument is `$attributes`, which allows to set the attributes<br/>
of a node contextually to its creation.

> Extended syntax
> ```php
> $food->appendChild('fruit', 'apple', [ 'price' => 'expensive',
>                                        'color' => 'red' ]);
>
> // which is identical to
>
> $food->appendChild('fruit', 'apple', true)        // Remember, passing 'true' returns the created node.
>          ->setAttribute([ 'price' => 'expensive',
>                           'color' => 'red' ]);
>
> // The advantage comes when multiple nodes have the same attributes.
>
> // A bunch of eggs all with the same price.
> $food->appendChild([ ['egg'], ['egg'], ['egg'] ], ['price' => '0.25']);
> ```
> Concise syntax
> ```php
> $food->add('fruit', 'apple', [ 'price'=> 'expensive',
>                                'color' => 'red' ]);
>
> // which is identical to
>
> $food->add('fruit', 'apple', true)        // Remember, passing 'true' returns the created node.
>          ->attr([ 'price' => 'expensive',
>                   'color' => 'red' ]);
>
> // The advantage comes when multiple nodes have the same attributes.
>
> // A bunch of eggs all with the same price.
> $food->add([ ['egg'], ['egg'], ['egg'] ], ['price' => '0.25']);
> ```

Creating arbitrarily complex structures is possible too nesting arrays.

> Extended syntax
> ```php
> $food->appendChild([ 'fridge' => [
>                          'firstFloor' => [
>                              'omelette' => 'with potato' ],
>                          'secondFloor' => [
>                              'soupe' => 'with mashrooms' ]
>                      ],
>                      'freezer' => [
>                          'firstFloor' => [
>                              'meat' => 'beef' ],
>                          'secondFloor' => [
>                              'fish' => 'tuna' ],
>                      ] ]);
> ```
> Concise syntax
> ```php
> $food->add([ 'fridge' => [
>                  'firstFloor' => [
>                      'omelette' => 'with potato' ],
>                  'secondFloor' => [
>                      'soupe' => 'with mashrooms' ]
>              ],
>              'freezer' => [
>                  'firstFloor' => [
>                      'meat' => 'beef' ],
>                  'secondFloor' => [
>                      'fish' => 'tuna' ],
>              ] ]);
> ```

```php
echo $food->xml();
```

```xml
<?xml version="1.0" encoding="UTF-8"?>
<food>
  <fruit/>
  <fruit>orange</fruit>
  <fruit price="expensive" color="red">apple</fruit>
  <cake>Tiramisu</cake>
  <pizza>Margherita</pizza>
  <pasta>Carbonara</pasta>
  <pasta>Matriciana</pasta>
  <egg price="0.25"/>
  <egg price="0.25"/>
  <egg price="0.25"/>
  <fridge>
    <firstFloor>
      <omelette>with potato</omelette>
    </firstFloor>
    <secondFloor>
      <soupe>with mashrooms</soupe>
    </secondFloor>
  </fridge>
  <freezer>
    <firstFloor>
      <meat>beef</meat>
    </firstFloor>
    <secondFloor>
      <fish>tuna</fish>
    </secondFloor>
  </freezer>
</food>
```


## Adding XML Strings

Sometimes XML/XHTML comes from legacy templating systems or external sources.<br/>
In those cases the raw XML string can be injected directly into the document.

```php
$book->add('cover', true)
        ->add(<<<XML
            <h1>The Theory Of Everything</h1>
            <img src="http://goo.gl/kO3Iov"/>
XML
);
```


## Executing XPath Queries

The possibility to execute XPath queries very easily is another feature of FluidXML.

```php
$eggs   = $food->query('//egg');
$fruits = $food->query('//fruit[@price="expensive"]');

echo "We have {$eggs->length()} eggs and {$fruits->length()} expensive fruit.\n";
```

Chaining queries together with the usage of relative XPath queries gives an immense<br/>
flexibility.

> Extended syntax
> ```php
> $book->query('//chapter')
>           ->setAttribute('lang', 'en')
>      ->query('..')
>           ->setAttribute('lang', 'en')
>      ->query('../title')
>           ->setAttribute('lang', 'en');
> ```
> Concise syntax
> ```php
> $book->query('//chapter')
>           ->attr('lang', 'en')
>      ->query('..')
>           ->attr('lang', 'en')
>      ->query('../title')
>           ->attr('lang', 'en');
> ```

> **Pro Tip**:<br/>
> `query()` supports quering multiple XPaths. The previous example can be refactored<br/>
> using this feature.
> ```php
> $book->query('//chapter', '//chapters','/book/title')
>          ->attr('lang', 'en');
> ```


## Exporting The Document

The document can be exported as XML string.

```php
$xml = $book->xml();
```

```xml
<?xml version="1.0" encoding="UTF-8"?>
<book>
  <title>The Theory Of Everything</title>
  <author>S. Hawking</author>
  <description/>
  <chapters>
    <chapter>Ideas About The Universe</chapter>
    <chapter>The Expanding Universe</chapter>
  </chapters>
  <cover>
    <h1>The Theory Of Everything</h1>
    <img src="http://goo.gl/kO3Iov"/>
  </cover>
</book>
```

The XML declaration can be removed from the output string too.
```php
$xml = $book->xml(true);
```

```xml
<book>
  <title>The Theory Of Everything</title>
  <author>S. Hawking</author>
  <description/>
  <chapters>
    <chapter>Ideas About The Universe</chapter>
    <chapter>The Expanding Universe</chapter>
  </chapters>
  <cover>
    <h1>The Theory Of Everything</h1>
    <img src="http://goo.gl/kO3Iov"/>
  </cover>
</book>
```

Not only the entire document but even only specific nodes (with their content)<br/>
can be exported.

```php
$xml = $book->query('//chapter')->xml();
```

```xml
<chapter>Ideas About The Universe</chapter>
<chapter>The Expanding Universe</chapter>
```


## Accessing The Node Content

FluidXML wraps the DOMNode native APIs extending them but without reimplementing<br/>
what is already convenient to use.

To access the node content after a query we can use the DOMNode own methods.

```php
$book->query('//chapter')
     ->each(function($fluid, $domnode, $index) {
            $fluid->attr('uuid', random_uuid());

            echo $domnode->nodeValue;
     });
```

Accessing the query result as array or iterating it returns the DOMNode unwrapped.

```php
$chapters = $book->query('//chapter');

$last = $chapters->length() - 1;

// Raw DOMNode access.
$chapters[0]->setAttribute('first', '');
$chapters[$last]->setAttribute('last', '');

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
```

> **Pro Tip**:<br/>
> Many DOMNode methods and properties are available like:
> - `hasAttribute()`
> - `getAttribute()`
> - `nodeValue`
> - `childNodes`
>
> See http://php.net/manual/en/class.domnode.php for the reference documentation.

Another way to retrieve the DOMNode instances is using the `asArray()` method.

```php
$nodes = $chapters->asArray();          // Returns an array of DOMNode instances.
```


## Namespaces

XML namespaces are an important part of documents manipulation and FluidXML makes<br/>
so easy to use them that you will not believe.

Start registering the namespace identifier together with the namespace uri.

```php
$book->namespace('xhtml', 'http://www.w3.org/1999/xhtml')
     ->namespace('svg',   'http://www.w3.org/2000/svg')
     ->namespace('xsl',   'http://www.w3.org/TR/xsl', FluidNamespace::MODE_IMPLICIT);
```

At this point you are ready to use it.

> Extended syntax
> ```php
> $book->appendChild('xhtml:h1')
>      ->appendChild([ 'xsl:template'  => [ 'xsl:variable' ] ])
>      ->query('//xhtml:h1')
>           ->appendChild('svg:shape');
> ```
> Concise syntax
> ```php
> $book->add('xhtml:h1')
>      ->add([ 'xsl:template'  => [ 'xsl:variable' ] ])
>      ->query('//xhtml:h1')
>           ->add('svg:shape');
> ```

```php
echo $book->xml();
```

```xml
<?xml version="1.0" encoding="UTF-8"?>
<book type="science">
  <title lang="en">The Theory Of Everything</title>
  <author>S. Hawking</author>
  <chapters lang="en">
    <chapter id="123" first="" lang="en">Ideas About The Universe</chapter>
    <chapter id="321" lang="en">The Expanding Universe</chapter>
    <chapter id="432" lang="en">Black Holes</chapter>
    <chapter id="234" lang="en" last="">Black Holes Ain't So Black</chapter>
  </chapters>
  <cover>
    <h1>The Theory Of Everything</h1>
    <img src="http://goo.gl/kO3Iov"/>
  </cover>
  <xhtml:h1 xmlns:xhtml="http://www.w3.org/1999/xhtml">
    <svg:shape xmlns:svg="http://www.w3.org/2000/svg"/>
  </xhtml:h1>
  <template xmlns="http://www.w3.org/TR/xsl">
    <variable/>
  </template>
</book>
```

That's it! Even XML namespaces can be easy and fun to use.

> **Pro Tip**:<br/>
> A namespace can be defined even as a `FluidNamespace` instance,<br/>
> to make easy to share namespaces between different documents.
>
> Extended syntax
> ```php
> $xhtml = new FluidNamespace('xhtml', 'http://www.w3.org/1999/xhtml');
> $svg   = new FluidNamespace('svg',   'http://www.w3.org/2000/svg', FluidNamespace::MODE_IMPLICIT);
> $book->namespace($xhtml, $svg);
> ```
> Concise syntax
> ```php
> $xhtml = fluidns('xhtml', 'http://www.w3.org/1999/xhtml');
> $svg   = fluidns('svg',   'http://www.w3.org/2000/svg', FluidNamespace::MODE_IMPLICIT);
> $book->namespace($xhtml, $svg);
> ```
>
> `namespace()` accepts a variable number of `FluidNamespace` instances,<br/>
> so that multiple namespaces can be registered in one method call.


## Removing Nodes

Removing a node is just a matter of quering an XPath.

```php
$food->remove('//egg');     // Removes all the eggs.
```

Which is the same of

```php
$food->query('//egg')->remove();        // Removes all the eggs.
```

Quering and removing with relative XPath can be used too.

```php
$food->query('/doc')->remove('egg');    // Removes all the eggs.
```

> **Pro Tip**:<br/>
> `->remove(...$xpath)` accepts the same arguments of `->query(...$xpath)`.<br/>
> This means that, like `->query()`, even `->remove()` accepts multiple XPath strings.<br/>
> ```php
> $food->remove('//fruit', '//pasta', '//pizza');
> ```


## Importing Existing Documents

FluidXML provides an easy way to import existing XML documents from a variety of formats.

The resulting object is a `FluidXml` instance filled with the XML of the imported document.

* **XML String**<br/>
  ```php
$xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<html>
    <head/>
    <body/>
</html>
XML;
```
> Extended syntax
> ```php
> $doc = FluidXml::load($xml);
> ```
> Concise syntax
> ```php
> $doc = fluidify($xml);
> ```

* **XML File**<br/>
> Extended syntax
> ```php
> $doc = FluidXml::load('path/to/file.xml');
> ```
> Concise syntax
> ```php
> $doc = fluidify('path/to/file.xml');
> ```

* **DOMDocument**<br/>
> Extended syntax
> ```php
> $doc = FluidXml::load($domdocument);  // $domdocument is an instance of DOMDocument.
> ```
> Concise syntax
> ```php
> $doc = fluidify($domdocument);        // $domdocument is an instance of DOMDocument.
> ```

* **SimpleXMLElement**<br/>
> Extended syntax
> ```php
> $doc = FluidXml::load($simplexml);    // $simplexml is an instance of SimpleXMLElement.
> ```
> Concise syntax
> ```php
> $doc = fluidify($simplexml);          // $simplexml is an instance of SimpleXMLElement.
> ```

> **Pro Tip**:<br/>
> `fluidify()`/`FluidXml::load()` methods support the following input documents:
> - XML string
> - XML file path
> - `FluidXml` instance
> - `DOMDocument`/`DOMNode`/`DOMNodeList` instance
> - `SimpleXMLElement` instance
> - `FluidXml` instance

Existing XML documents instances can be injected in any point of the FluidXML document.

```php
$doc = fluidxml();

$doc->add($dom)                     // A DOMDocument/DOMNode/DOMNodeList instance.
    ->add($simplexml)               // A SimpleXMLElement instance.
    ->add($fluidxml)                // A FluidXml instance.
    ->add($fluidxml->query('//p')); // A FluidXml query instance.
```

Crazy things are possible too and I will not stop you from doing them.

```php
$doc->add([ 'aNode',
            'domDoc' => $dom,
            'file'   => fluidify('path/to/file.xml'),
            'simple' => $simplexml ]);
```


## Where To Go Next

We have concluded our brief but complete tour of FluidXML and you are ready to<br/>
start manipulating XML like a real ninja.

The dark ages of DOMDocument have come to the end. Long life to FluidXML.

Open the `documents/Examples.php` file to start experimenting with FluidXML.

Take a look at the [APIs][apis] to discover all the available manipulation operations,<br/>
and go to the [Wiki Page][wiki] for more reading.

Thanks and most important, have fun with XML.
