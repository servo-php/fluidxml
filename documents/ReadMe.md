[example]: https://github.com/servo-php/fluidxml/wiki/Examples
[example-file]: https://github.com/servo-php/fluidxml/blob/master/documents/Examples.php
[specs-file]: https://github.com/servo-php/fluidxml/blob/master/specs/FluidXml.php
[license-file]: https://github.com/servo-php/fluidxml/blob/master/documents/License.txt
[gettingstarted]: https://github.com/servo-php/fluidxml/wiki/Getting-Started
[ninja]: http://1.viki.io/d/1863c/8b75dc48c9.gif
[donate-button]: https://bytebucket.org/daniele_orlando/hosting/raw/master/Donate_button.png?nocache=2
[donate-link]:   https://www.paypal.me/danieleorlando

# FluidXML
FluidXML is a PHP library, under the Servo PHP framework umbrella ☂,<br/>
specifically designed to manipulate XML documents with a concise<br/>
and fluent interface.

It leverages XPath and the fluent programming pattern to be fun and effective.

##### STOP _generating XML documents with template engines_.

##### STOP _using the boring and verbose DOMDocument_.

FluidXML has been specifically designed to bring XML manipulation to the next level.

```php
$book = new FluidXml();

$book->setAttribute('type', 'book')
     ->appendChild('title', 'The Theory Of Everything')
     ->appendChild('author', 'S. Hawking')
     ->appendChild('chapters', true)
         ->appendChild('chapter', 'Ideas About The Universe', ['id'=> 1])
         ->appendChild('chapter', 'The Expanding Universe',   ['id'=> 2])
     ->query('//chapter')
     ->setAttribute('lang', 'en');
```

Or, if you prefer, there is a **concise syntax**.

```php

$book = fluidxml();
$book->attr('type', 'book')
     ->add('title', 'The Theory Of Everything')
     ->add('author', 'S. Hawking')
     ->add('chapters', true)
         ->add('chapter', 'Ideas About The Universe', ['id'=> 1])
         ->add('chapter', 'The Expanding Universe',   ['id'=> 2])
     ->query('//chapter')
     ->attr('lang', 'en');
```

```php
echo $book->xml();
```
```xml
<?xml version="1.0" encoding="UTF-8"?>
<doc type="book">
  <title>The Theory Of Everything</title>
  <author>S. Hawking</author>
  <chapters>
    <chapter id="1" lang="en">Ideas About The Universe</chapter>
    <chapter id="2" lang="en">The Expanding Universe</chapter>
  </chapters>
</doc>
```

Creating **structured documents** is so easy that you'll never go back.

```php
$food = fluidxml();
$food->add('fruit')                                           // A 'fruit' node with an empty content.
     ->add('fruit', 'apple', ['price' => 'expensive'])        // A 'fruit' node with 'apple' as content.
     ->add([ 'Tiramisu',
             'pizza' => 'Margherita' ])                       // Batch insertion of nodes.
     ->add([ ['egg'],
             ['egg'],
             ['egg'] ], ['price' => '0.25'])                  // Adding a bunch of 'egg's all with the same price.
     ->add([ 'fridge' => [
                 'omelette' => 'with potato',
                 'soupe'    => 'wit mashrooms' ]]);           // Deep tree structures are supported too.
```

**XPath** is king.

```php
$book->query('//chapter')
     ->setAttribute('lang', 'en')
     ->query('..')
     ->setAttribute('lang', 'en')
     ->query('/book/title')
     ->setAttribute('lang', 'en');
```

And sometimes **string template** are the fastest way.

```php
$book->appendChild('cover', true)
     ->appendXml(<<<XML
        <h1>The Theory Of Everything</h1>
        <img src="http://goo.gl/kO3Iov"/>
XML
);
```

**XML namespaces** are fully covered and FluidXml it's great even there.

```php
$xhtml = new FluidNamespace('xhtml', 'http://www.w3.org/1999/xhtml');
$svg   = new FluidNamespace('svg',   'http://www.w3.org/2000/svg');
$book->namespace($xhtml)
     ->namespace($xsl)
     ->appendChild('xhtml:h1')
     ->appendChild('svg:shape')
     ->query('//xhtml:h1');
```

### Don't be shy, tell it: « IT'S SIMPLY AWESOME! »


## Still doubtful?
Other three great reasons to use FluidXML, but you'll have the best answer trying it yourself.

FluidXML is **fun** to use, **concise** and **effective**.

If it's not enough, it has a comprehensive test suite with a **100% code coverage**.

![100% Code Coverage](https://bytebucket.org/daniele_orlando/hosting/raw/master/FluidXML_code_coverage.png)


## Requirements
* PHP 7
* For _PHP 5.4_ see the [RoadMap](#roadmap)


## Installation
* **Cloning the repository**:
  ```sh
git clone https://github.com/servo-php/fluidxml.git
```

* **Using Composer**:
  ```sh
composer require servo/fluidxml
```


## Getting Started
* **Cloning the repository**:
  ```php
require_once 'FluidXml.php';
```

* **Using Composer**:
  ```php
require_once 'vendor/autoload.php';
```

See the [documentation](#documentation) to get started and becoming a [ninja][ninja].


## Documentation
_5 minutes reading_<br/>
Follow the [Getting Started tutorial][gettingstarted] to become a [ninja][ninja] in no time.

Many other examples are available:
- in the wiki [Examples][example] page
- inside the [`documents/Examples.php`][example-file] file
- inside the [`specs/FluidXml.php`][specs-file] file (as test cases)

All them cover from the simplest case to the most complex scenario.

The complete API documentation can be generated executing:
```sh
./support/tools/gendoc      # Generated under 'documents/api/'.
```


## APIs
```php
/*******************************************************************************
 * Functions
 ******************************************************************************/

function fluidxml($options);

function fluidns($id, $uri, $mode);


/*******************************************************************************
 * FluidXml interfaces
 ******************************************************************************/

class FluidXml

__construct($options);

->namespace(FluidNamespace $namespace);

->query($xpath);

->appendChild($child, ...$optionals);

->prependSibling($sibling, ...$optionals);

->appendSibling($sibling, ...$optionals);

->appendXml($xml);

->appendText($text);

->appendCdata($cdata);

->setText($text);

->setAttribute(...$arguments);

->remove($xpath);

->asArray();    // Available after a query or a node insertion with context switch.

->length();     // Available after a query or a node insertion with context switch.

->xml();

/*
 * Alias methods
 */

->add($child, ...$optionals);                       // ->appendChild

->prepend($sibling, ...$optionals);                 // ->prependSibling

->insertSiblingBefore($sibling, ...$optionals);     // ->prependSibling

->append($sibling, ...$optionals);                  // ->appendSibling

->insertSiblingAfter($sibling, ...$optionals);      // ->appendSibling

->attr(...$arguments);                              // ->setAttribute

->text($text);                                      // ->setText


/*******************************************************************************
 * FluidNamespace interfaces
 ******************************************************************************/

class FluidNamespace

__construct($id, $uri, $mode);

->id($value = null);

->uri($value = null);

->mode($value = null);

->querify($xpath);
```


## Donation
If you think this project is **awesome** or if you want to demonstrate<br/>
your immense gratitude **♡**, donate _1cent_.

[![Donate][donate-button]][donate-link]

#### Thanks You! :D ♥


## Roadmap
* [x] Porting the XML namespace implementation from the legacy FluidXML codebase
* [ ] Expanding the APIs with some other useful methods
* [ ] PHP 5.4 backport
* [ ] Extending the documentation

<a href='https://pledgie.com/campaigns/30607'>
    <img alt='Click here to lend your support to: FluidXML and make a donation at pledgie.com !' src='https://pledgie.com/campaigns/30607.png?skin_name=chrome' border='0' >
</a>


## Author
Daniele Orlando  [&lt;fluidxml@danieleorlando.com&gt;](mailto:fluidxml@danieleorlando.com)


## License
FluidXML is licensed under the BSD 2-Clause License.

See [`documents/License.txt`][license-file] for the details.
