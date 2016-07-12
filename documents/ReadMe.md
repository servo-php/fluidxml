[apis]:             https://github.com/servo-php/fluidxml/wiki/APIs
[gettingstarted]:   https://github.com/servo-php/fluidxml/wiki/Getting-Started
[examples]:         https://github.com/servo-php/fluidxml/blob/master/documents/Examples/
[specs]:            https://github.com/servo-php/fluidxml/blob/master/specs/FluidXml.php
[wiki]:             https://github.com/servo-php/fluidxml/wiki
[bsd]:              https://opensource.org/licenses/BSD-2-Clause
[license]:          https://github.com/servo-php/fluidxml/blob/master/documents/License.txt
[changelog]:        https://github.com/servo-php/fluidxml/blob/master/documents/Changelog.txt
[codecoverage]:     https://bytebucket.org/daniele_orlando/bithosting/raw/master/FluidXML_code_coverage.png
[donate-button]:    https://bytebucket.org/daniele_orlando/bithosting/raw/master/Donate_button.png
[donate-link]:      https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=UUBXYHQRVJE28
[donate-link-alt]:  https://www.paypal.me/danieleorlando
[ninja]:            http://1.viki.io/d/1863c/8b75dc48c9.gif
[thankyou]:         https://upload.wikimedia.org/wikipedia/commons/thumb/f/f1/Heart_corazón.svg/2000px-Heart_corazón.svg.png

[packagist]:        https://packagist.org/packages/servo/fluidxml
[travis]:           https://travis-ci.org/servo-php/fluidxml
[scrutinizer]:      https://scrutinizer-ci.com/g/servo-php/fluidxml/?branch=master
[coveralls]:        https://coveralls.io/github/servo-php/fluidxml?branch=master
[codeclimate]:      https://codeclimate.com/github/servo-php/fluidxml
[codeship]:         https://codeship.com/projects/129206
[circle]:           https://circleci.com/gh/servo-php/fluidxml

[packagist-license-badge]:        https://poser.pugx.org/servo/fluidxml/license
[packagist-release-badge]:        https://poser.pugx.org/servo/fluidxml/v/stable
[packagist-downloads-badge]:      https://poser.pugx.org/servo/fluidxml/downloads
[packagist-license-badge-alt]:    https://img.shields.io/packagist/l/servo/fluidxml.svg?style=flat-square
[packagist-release-badge-alt]:    https://img.shields.io/packagist/v/servo/fluidxml.svg?style=flat-square
[packagist-downloads-badge-alt]:  https://img.shields.io/packagist/dt/servo/fluidxml.svg?style=flat-square
[travis-build-badge]:             https://travis-ci.org/servo-php/fluidxml.svg?branch=master
[scrutinizer-coverage-badge]:     https://scrutinizer-ci.com/g/servo-php/fluidxml/badges/coverage.png?b=master
[scrutinizer-quality-badge]:      https://scrutinizer-ci.com/g/servo-php/fluidxml/badges/quality-score.png?b=master
[coveralls-coverage-badge]:       https://coveralls.io/repos/github/servo-php/fluidxml/badge.svg?branch=master
[codeclimate-quality-badge]:      https://codeclimate.com/github/servo-php/fluidxml/badges/gpa.svg
[codeship-build-badge]:           https://codeship.com/projects/8f977260-a359-0133-4946-1ac8bff03ae9/status?branch=master
[circle-build-badge]:             https://circleci.com/gh/servo-php/fluidxml.svg?style=svg


[![Travis Build][travis-build-badge]][travis]
[![Coveralls Coverage][coveralls-coverage-badge]][coveralls]
[![Scrutinizer Quality][scrutinizer-quality-badge]][scrutinizer]
[![Code Climate Quality][codeclimate-quality-badge]][codeclimate]

[![Packagist License][packagist-license-badge]][packagist]
[![Packagist Last Release][packagist-release-badge]][packagist]
[![Packagist Total Downloads][packagist-downloads-badge]][packagist]


## Changelog

**1.20.3** (2016-07-12):
_fixes wrong handling of null/empty node value._

**...**

[The full changes list.][changelog]

<br/>

<a href='https://ko-fi.com/2216WXOPLSZER' target='_blank'>
  <img height='32' src='https://az743702.vo.msecnd.net/cdn/kofi5.png?v=a' border='0' alt='Buy Me a Coffee at ko-fi.com'/>
</a>


# FluidXML
<img src="https://bytebucket.org/daniele_orlando/bithosting/raw/master/Servo_logo.png" height="64px" alt="Servo-PHP Logo"/>
<span>      </span>
<img src="https://bytebucket.org/daniele_orlando/bithosting/raw/master/Fluidxml_logo.png" height="64px" alt="FluidXML Logo"/>

FluidXML is a PHP library designed to manipulate XML documents with a **concise** and **fluent** API.<br/>
It leverages the fluent programming pattern to be **fun and effective**.

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

With FluidXML the DOM manipulation becomes **fast**, **clear** and **expressive**.

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

And **CSS Selectors** rock.

```php
$book->query('#id', '.class1.class2', 'div p > span')
        ->attr('lang', 'en');

// Many other selectors are available.
```

**XML/CSS Namespaces** are fully covered.

```php
$book->namespace('xhtml', 'http://www.w3.org/1999/xhtml')
     ->add('xhtml:h1')
     ->query('//xhtml:h1')  // XPath namespace.
     ->query('xhtml|h1');   // CSS namespace.
```

And sometimes **XML Fragments** are the fastest way.

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
        ->each(function ($i) {
             $this->attr('id', $i);
        });
```

```php
$book->query('//chapters')
        ->times(3)
            ->add('chapter')
        ->times(4, function ($i) {
            $this->add('chapter');
            $this->add('illustration');
        });
```

Whether some queries are too complex to express with XPath/CSS,<br/>
**filtering** is your friend.

```php
$book->query('//chapters')
        ->filter(function ($i, $node) {
            return $i % 2 === 0;
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
your immense gratitude **[♥][thankyou]**, _buy me a coffe_.

<a href='https://ko-fi.com/2216WXOPLSZER' target='_blank'>
    <img height='32' src='https://az743702.vo.msecnd.net/cdn/kofi5.png?v=a' border='0' alt='Buy Me a Coffee at ko-fi.com'/>
</a>

[//]: # ([![Donate][donate-button]][donate-link]<br/>)
[//]: # (**1$ or more**<span style="color: gray;">, due to the PayPal fees.</span>)

<a-off href='https://pledgie.com/campaigns/30607'>
    <img-off alt='Click here to lend your support to: FluidXML and make a donation at pledgie.com !' src='https://pledgie.com/campaigns/30607.png?skin_name=chrome' border='0'/>
</a-off>


## Roadmap
* [x] PHP 5.6 backport
* [ ] Extending the documentation
* [ ] Expanding the APIs

## Author
Daniele Orlando  [&lt;fluidxml@danieleorlando.com&gt;](mailto:fluidxml@danieleorlando.com)


## License
FluidXML is licensed under the [BSD 2-Clause License][bsd].

See [`documents/License.txt`][license] for the details.
