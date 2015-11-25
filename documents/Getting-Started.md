FluidXML has been designed to give his best creating and manipulating XML documents.

It's quite common to see inside PHP projects the generation of XML documents through<br/>
template engines, PHP/XML mixing or, in the best case, using DOMDocument.

In every described situations FluidXML performs better in every way.


## Creating your first XML document

First of all, depending the way you have chosen to install FluidXML, you have two<br/>
options to include the library.
* If you have cloned the repository, copy the `source/FluidXml.php` in your PHP<br/>
project and include it:
  ```php
require_once 'FluidXml.php';
```

* If you have installed the library using Composer, include the autoloader:
  ```php
require_once 'vendor/autoload.php';
```

We can proceed to create our first XML document in the simplest way.

> Extended syntax
> ```php
> $book = new FluidXml();
> ```
> Concise syntax
> ```php
> $book = fluidxml();
> ```

It creates a new XML document with one root node that by default is called `<doc/>`.

```php
echo $book->xml();
```

```xml
<?xml version="1.0" encoding="UTF-8"?>
<doc/>
```

Whether there is the need to influence the document creation, the constructor supports<br/>
an array of options.

> Extended syntax
> ```php
> $book = new FluidXml(['root' => 'book']);
> ```
> Concise syntax
> ```php
> $book = fluidxml(['root' => 'book']);
> ```

Our XML document now has a root node called `<book/>`.

> **Pro Tip**:
> The constructor supports these options:
> ```php
> [ 'root'       => 'doc',    // The root node of the document.
>   'version'    => '1.0',    // The version for the XML header.
>   'encoding'   => 'UTF-8',  // The encoding for the XML header.
>   'stylesheet' => null ]    // An url pointing to an XSL file.
> ```


## Adding nodes

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

The `appendChild`/`add` method supports up to four arguments to achieve from the simplest<br/>
node insertion to nested trees creation.

> _Public API_:
> ```php
> ->appendChild($node, $value?, $attributes? = [], $switchContext? = false)
> ```
> **Pro Tip**:
> Except for the `$node` argument, all others arguments can be passed in any order.

One of the most important argument is the boolean flag `$switchContext`. Passing a `true`<br/>
boolean value returns the new node instead of the current one.

> Extended syntax
> ```php
> // true asks to return the 'chapters' node instead of the 'book' node.
> $book->appendChild('chapters', true)
>      ->appendChild('chapter', 'Ideas About The Universe')
>      ->appendChild('chapter', 'The Expanding Universe');
> ```
> Concise syntax
> ```php
> // true asks to return the 'chapters' node instead of the 'book' node.
> $book->add('chapters', true)
>      ->add('chapter', 'Ideas About The Universe')
>      ->add('chapter', 'The Expanding Universe');
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
> $food = new FluidXml(['root' => 'food']);
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
> $food = fluidxml(['root' => 'food']);
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

* * *
#### This document is a draft. It will be continued.
* * *

