# FluidXML

FluidXML is a PHP library, under the Servo PHP framework umbrella,  
specifically designed to manipulate XML documents with a concise  
and fluent interface.

It leverages XPath and the fluent programming pattern to be fun  
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
$doc->appendChild('cover', true)
    ->appendXml(<<<TPL
        <h1>The Theory Of Everything</h1>
        <img src="http://goo.gl/kO3Iov"/>
TPL
);
    
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

FluidXML is **fun** to use, **concise** and **effective**. If it's not enough, it has  
a compreansive test suite with a **100% code coverage**.

![100% Code Coverage](https://bytebucket.org/daniele_orlando/hosting/raw/a4a7f61de3793ec143ddcb75037da11abe434e23/FluidXML_code_coverage.png)


## Requirements
* PHP 7


## Installation
Cloning the repository:
```sh
git clone https://github.com/servo-php/fluidxml.git
```

> Composer installation will follow soon.


## Usage
```php
require_once 'FluidXml.php';

$xml = new FluidXml();
// or
$xml = new FluidXml([ 'version'    => '1.0',
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

->asArray();

->length();

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
Many examples are available inside the `specs/` folder, as test cases,  
and inside the `documents/Examples.php` file. Both cover from the  
simplest case to the most complex scenario.

The complete API documentation can be generated, after cloning the  
repository, executing
```sh
./support/tools/gendoc
```
and can be found under `documents/api`.


## Roadmap
* [ ] Porting the XML namespace implementation from the legacy FluidXML codebase
* [ ] Expanding the API with some other useful methods


## Author
Daniele Orlando <fluidxml@danieleorlando.com>


## License
FluidXML is licensed under the BSD 2-Clause License.  
See `documents/License.txt` for the details.


[api]: #api
[email]: mailto:fluidxml(at)danieleorlando.com
