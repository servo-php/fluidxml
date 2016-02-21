[travis-build]: https://travis-ci.org/servo-php/fluidxml
[travis-build-badge]: https://travis-ci.org/servo-php/fluidxml.svg?branch=master
[codeship-build]: https://codeship.com/projects/129206
[codeship-build-badge]: https://codeship.com/projects/8f977260-a359-0133-4946-1ac8bff03ae9/status?branch=master
[coveralls-coverage]: https://coveralls.io/github/servo-php/fluidxml?branch=master
[coveralls-coverage-badge]: https://coveralls.io/repos/github/servo-php/fluidxml/badge.svg?branch=master
[scrutinizer-coverage]: https://scrutinizer-ci.com/g/servo-php/fluidxml/?branch=master
[scrutinizer-coverage-badge]: https://scrutinizer-ci.com/g/servo-php/fluidxml/badges/coverage.png?b=master
[scrutinizer-quality]: https://scrutinizer-ci.com/g/servo-php/fluidxml/?branch=master
[scrutinizer-quality-badge]: https://scrutinizer-ci.com/g/servo-php/fluidxml/badges/quality-score.png?b=master
[codeclimate-quality]: https://codeclimate.com/github/servo-php/fluidxml
[codeclimate-quality-badge]: https://codeclimate.com/github/servo-php/fluidxml/badges/gpa.svg
[coveralls]: https://coveralls.io/github/servo-php/fluidxml?branch=master
[coveralls-badge]: https://coveralls.io/repos/servo-php/fluidxml/badge.svg?branch=master&service=github
[apis]: https://github.com/servo-php/fluidxml/wiki/APIs
[gettingstarted]: https://github.com/servo-php/fluidxml/wiki/Getting-Started
[examples]: https://github.com/servo-php/fluidxml/blob/master/documents/Examples/
[specs]: https://github.com/servo-php/fluidxml/blob/master/specs/FluidXml.php
[wiki]: https://github.com/servo-php/fluidxml/wiki
[bsd]: https://opensource.org/licenses/BSD-2-Clause
[license]: https://github.com/servo-php/fluidxml/blob/master/documents/License.txt
[changelog]: https://github.com/servo-php/fluidxml/blob/master/documents/Changelog.txt
[codecoverage]: https://bytebucket.org/daniele_orlando/hosting/raw/master/FluidXML_code_coverage.png?nocache=1
[ninja]: http://1.viki.io/d/1863c/8b75dc48c9.gif
[donate-button]: https://bytebucket.org/daniele_orlando/hosting/raw/master/Donate_button.png?nocache=2
[donate-link]: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=UUBXYHQRVJE28
[donate-link-alt]: https://www.paypal.me/danieleorlando
[thankyou]: https://upload.wikimedia.org/wikipedia/commons/thumb/f/f1/Heart_corazón.svg/2000px-Heart_corazón.svg.png

[![Travis Build][travis-build-badge]][travis-build]
[![Codeship Build][codeship-build-badge]][codeship-build]
[![Coveralls Coverage][coveralls-coverage-badge]][coveralls-coverage]
[![Scrutinizer Coverage][scrutinizer-coverage-badge]][scrutinizer-coverage]
[![Scrutinizer Quality][scrutinizer-quality-badge]][scrutinizer-quality]
[![Code Climate][codeclimate-quality-badge]][codeclimate-quality]


## Changelog

**1.20** (2016-02-21):
_is a major step forward breaking compatibility with the past.<br/>
Under the hood there is a general reorganization to be `PSR-0/4` compliant,<br/>
a general cleanup of the API for being consistent and allowing future clean APIs<br/>
growth, some notable additions like the `->filter()` method to programmatically<br/>
filter the query results and `->html()` to output the document as valid HTML 5<br/>
string. Just because awesomenesses are never enough, `->query()` supports **CSS Selectors**._

### New:
* `->html()`                is part of the family.
* `->filter()`              is part of the family.
* `->comment()`             is part of the family.
* `->setComment()`          is part of the family.
* `->addComment()`          is part of the family.
* `->size()`                is an alias of `->length()`.
* `->__invoke()`            is an alias of `->query()`.
* `->__toString()`          is an alias of `->xml()`.
* `->array()`               replaces `->asArray()`.
* `->addChild()`            replaces `->appendChild()`.
* `->addText()`             replaces `->appendText()`.
* `->addCdata()`            replaces `->appendCdata()`.

### Changed:
* `->query()`               supports CSS Selectors.
* `fluidxml()`              has gained the super powers of `fluidify()`.
* `FluidXml->__construct()` has gained the super powers of `FluidXml::load()`.
* `::load()/fluidify()`     can be ONLY used to load an XML file.

### Removed:
* `->asArray()`             has been removed superseded by `->array()`.
* `->appendText()`          has been removed superseded by `->addText()`.
* `->appendCdata()`         has been removed superseded by `->addCdata()`.
* `->appendChild()`         has been removed superseded by `->addChild()`.
* `->insertSiblingBefore()` has been removed superseded by `->prependSibling()`.
* `->insertSiblingAfter()`  has been removed superseded by `->appendSibling()`.

### Internal:
* `PSR-0/4` compliance.
* `FluidXml.php` is still there for people NOT using Composer/PSR-0/4 loaders.
* `FluidInsertionHandler` refactoring.

**...**

[See the full changes list.][changelog]

<br/>

[![Donate][donate-button]][donate-link]<br/>
**1$ or more**<span style="color: gray;">, due to the PayPal fees.</span>


# FluidXML
<img src="https://bytebucket.org/daniele_orlando/hosting/raw/master/Servo_logo.png?nocache=1" height="64px" alt="Servo-PHP Logo"/>
<img src="https://bytebucket.org/daniele_orlando/hosting/raw/master/Fluidxml_logo.png?nocache=1" height="64px" alt="FluidXML Logo" style="margin-left: 32px;"/>

FluidXML is a PHP library designed to manipulate XML documents with a **concise** and **fluent** API.<br/>
It leverages the fluent programming pattern to be **fun and effective**.

With FluidXML the DOM manipulation becomes **fast**, **clear** and **expressive**.

```php
$book = fluidxml();

$book->add('title', 'The Theory Of Everything')
     ->add('author', 'S. Hawking')
     ->add('chapters', true)
         ->add('chapter', 'Ideas About The Universe', ['id' => 1])
         ->add('chapter', 'The Expanding Universe',   ['id' => 2]);
```

Or, if you prefer, there is an **extended syntax**.

```php
$book = new FluidXml();

$book->addChild('title', 'The Theory Of Everything')
     ->addChild('author', 'S. Hawking')
     ->addChild('chapters', true)
         ->addChild('chapter', 'Ideas About The Universe', ['id' => 1])
         ->addChild('chapter', 'The Expanding Universe',   ['id' => 2]);
```

**PHP Arrays** are first class citizens.

```php
$book->add([ 'title'  => 'The Theory Of Everything',
             'author' => 'S. Hawking',
             'chapters' => [
                    [ 'chapter' => [
                            '@id' => '1',
                            '@'   => 'Ideas About The Universe' ] ],
                    [ 'chapter' => [
                            '@id' => '2',
                            '@'   => 'The Expanding Universe' ] ],
           ]]);
```

```php
echo $book;
```

```xml
<?xml version="1.0" encoding="UTF-8"?>
<doc>
  <title>The Theory Of Everything</title>
  <author>S. Hawking</author>
  <chapters>
    <chapter id="1">Ideas About The Universe</chapter>
    <chapter id="2">The Expanding Universe</chapter>
  </chapters>
</doc>
```

**XPath** is king.

```php
$book->query('//title', '//author', '//chapter')
        ->attr('lang', 'en');
```

But **CSS Selectors** rocks.

```php
$book->query('title', 'author', 'chapters > chapter')
        ->attr('lang', 'en');
```

**XML/CSS Namespaces** are fully covered.

```php
$book->namespace('xhtml', 'http://www.w3.org/1999/xhtml')
     ->add('xhtml:h1')
     ->query('//xhtml:h1')  // XPath namespace.
     ->query('xhtml|h1');   // CSS namespace.
```

And sometimes **string fragments** are the fastest way.

```php
$book->add(<<<XML
    <cover class="front">
        <img src="http://goo.gl/kO3Iov"/>
    </cover>
    <cover class="back">
        <img src="http://goo.gl/kO3Iov"/>
    </cover>
XML
);
```

Everything is fluent, even **iterations**.

```php
$book->query('//chapter')
        ->each(function($i) {
             $this->attr('id', $i);
        });
```

```php
$book->query('//chapters')
        ->times(3)
            ->add('chapter')
        ->times(4, function($i) {
            $this->add('chapter');
            $this->add('illustration');
        });
```

Whether some queries are too complex to express with XPath/CSS,<br/>
filtering is your friend.

```php
$book->query('//chapters')
        ->filter(function($i, $node) {
            return $node->getAttribute('id') % 2 === 0;
        })
        ->attr('even');
```

Interoperability with existing **DOMDocument** and **SimpleXML** is simply magic.<br/>
Import them or inject them in any point of the FluidXML flow just like that.

```php
fluidxml($domdocument)
    ->query('/html/body')
         ->add($simplexml);

// Yes, we merged a DOMDocument with a SimpleXMLElement
// and everything is still fluid.
```

Don't be shy and tell it: **« IT'S AWESOME! »** ^\_^

Many other [APIs][apis] are available:
- `__invoke()`
- `append()`/`appendSibling()`
- `prepend()`/`prependSibling()`
- `addText()`
- `text()`/`setText()`
- `addCdata()`
- `cdata()`/`setCdata()`
- `addComment()`
- `comment()`/`setComment()`
- `remove()`
- `size()`/`length()`
- `load()`
- `save()`
- `dom()`
- `xml()`
- `html()`
- `__toString()`
- `array()`
- ...


## Still doubts?
FluidXML is **fun** to use, **concise** and **effective**.

If it's not enough, it has a comprehensive test suite with a **100% code coverage**.

But you'll have the best answer trying it yourself.

![100% Code Coverage][codecoverage]


## Requirements
* PHP 5.6


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

`use` classes and functions as you need.
```php
use function \FluidXml\fluidxml;
use function \FluidXml\fluidns;
use function \FluidXml\fluidify;
```
```php
use \FluidXml\FluidXml;
use \FluidXml\FluidNamespace;
```
See the [documentation](#documentation) to get started and become a [ninja][ninja].


## Documentation
_10 minutes reading_<br/>
Follow the [Getting Started tutorial][gettingstarted] to become a [ninja][ninja] in no time.

Many other examples are available:
- inside the [`documents/Examples/`][examples] folder
- inside the [`specs/FluidXml.php`][specs] file (as test cases)

All them cover from the simplest case to the most complex scenario.

Take a look at the [APIs][apis] to discover all the available manipulation operations,<br/>
and go to the [Wiki Page][wiki] for more reading.


## Donation
If you think this code is **awesome** or if you want to demonstrate<br/>
your immense gratitude **[♥][thankyou]**, donate _1cent_.

[![Donate][donate-button]][donate-link]
**1$ or more**<span style="color: gray;">, due to the PayPal fees.</span>

## Roadmap
* [x] PHP 5.6 backport
* [ ] Extending the documentation
* [ ] Expanding the APIs

<a href='https://pledgie.com/campaigns/30607'>
    <img alt='Click here to lend your support to: FluidXML and make a donation at pledgie.com !' src='https://pledgie.com/campaigns/30607.png?skin_name=chrome' border='0' >
</a>


## Author
Daniele Orlando  [&lt;fluidxml@danieleorlando.com&gt;](mailto:fluidxml@danieleorlando.com)


## License
FluidXML is licensed under the [BSD 2-Clause License][bsd].

See [`documents/License.txt`][license] for the details.
