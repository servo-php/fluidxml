# FluidXML

FluidXML is a PHP library, under the Servo PHP framework umbrella,  
specifically designed to manipulate XML documents with a concise  
and fluent interface.

It leverages XPath and the fluent programming technique to be fun  
and effective.

```php
$doc = new FluidXml();

$doc->setAttribute('type', 'book')
    ->appendChild('title', 'The Theory Of Everything')
    ->appendChild('author', 'S. Hawking');

// Or, if you prefere, the concise syntax:

$doc = fluidxml();

$doc->attr('type', 'book')
    ->add('title', 'The Theory Of Everything')
    ->add('author', 'S. Hawking');

echo $doc->xml();
```

```xml
<?xml version="1.0" encoding="UTF-8"?>
<doc type="book">
  <title>The Theory Of Everything</title>
  <author>S. Hawking</author>
</doc>
```

Creating structured documents is so easy that you'll never go back  
to the old _DOMDocument/DOMNode/DOMElement_.

```php
$doc->appendChild('chapters', true)
       ->appendChild('chapter', 'Ideas About The Universe', ['id'=> 1])
       ->appendChild('chapter', 'The Expanding Universe',   ['id'=> 2]);
         
```

**XPath** is king.

```php
$doc->query('//chapter')
    ->setAttribute('lang', 'en')
    ->query('..')
    ->setAttribute('lang', 'en');
```

And sometimes **string templates** are the fastest way.

```php
$cover = '<h1>The Theory Of Everything</h1>'
        .'<img src="http://goo.gl/kO3Iov"/>';

$doc->appendChild('cover', true)
    ->appendXml($cover);
    
echo $doc->xml();
```

```xml
<?xml version="1.0" encoding="UTF-8"?>
<doc type="book">
  <title>The Theory Of Everything</title>
  <author>S. Hawking</author>
  <chapters lang="en">
    <chapter id="1" lang="en">Ideas About The Universe</chapter>
    <chapter id="2" lang="en">The Expanding Universe</chapter>
  </chapters>
  <cover>
    <h1>The Theory Of Everything</h1>
    <img src="http://goo.gl/kO3Iov"/>
  </cover>
</doc>
```

Take a look at the [API][api] section for more powerful manipulations.


## Why
Three great reasons to use it, but you'll have the best answer
trying it yourself.

FluidXML is **fun** to use, **concise** and **effective**. If it's not enough,
it has a compreansive test suite with a **100% code coverage**.


## Requirements
* PHP 7


## Installation
Cloning the repository:
```sh
git clone https://github.com/servo-php/fluidxml.git
```

Using Composer will follow soon.


## Usage
```php
require_once 'FluidXml.php';

$xml = fluidxml();
// is the same of
$xml = new FluidXml();

$xml = fluidxml([ 'version'    => '1.0',
                  'encoding'   => 'UTF-8',
                  'stylesheet' => null,
                  'root'       => 'doc' ]);
```


## API
```php
fluidxml();
new FluidXml();

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

->xml();
```

```php
// Aliases functions.

->add($child, ...$optionals);                       // ->appendChild

->prepend($sibling, ...$optionals);                 // ->prependSibling

->insertSiblingBefore($sibling, ...$optionals);     // ->prependSibling

->append($sibling, ...$optionals);                  // ->appendSibling

->insertSiblingAfter($sibling, ...$optionals);      // ->appendSibling

->attr(...$arguments);                              // ->setAttribute

->text($text);                                      // ->setText
```

## Documentation
Many examples are available insede the `specs/` folder, as test cases,  
which cover from the simplest case to the most complex scenario.

See the [Wiki](#wiki) (in progress).


## Author
Daniele Orlando <fluidxml@danieleorlando.com>


## License
FluidXML is licensed under the BSD 2-Clause License.  
See `documents/License.txt` for the details.


[api]: #api
[email]: mailto:fluidxml(at)danieleorlando.com
