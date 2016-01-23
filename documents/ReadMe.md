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
[apis]: https://github.com/servo-php/fluidxml/blob/master/documents/APIs.md
[gettingstarted]: https://github.com/servo-php/fluidxml/blob/master/documents/Getting-Started.md
[examples]: https://github.com/servo-php/fluidxml/blob/master/documents/Examples.php
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

[![Donate][donate-button]][donate-link]


## Changelog

**1.12** (2016-01-08):
_introduces the `->times()` method._


**1.11**:
_introduces the `@`/`@<attribute>` special syntax._

* `->appendChild()`, `->prependSibling()` and `->appendSibling()` support the `@`/`@<attribute>` special syntax.


**...**

[See the full changes list.][changelog]


# FluidXML
<img src="https://bytebucket.org/daniele_orlando/hosting/raw/master/Servo_logo.png" height="64px" alt="Servo-PHP Logo"/>

FluidXML is a PHP library designed to manipulate XML documents with a **concise**
and **fluent** API.

It leverages XPath and the fluent programming pattern to be **fun and effective**.

**STOP generating XML documents with template engines.**<br/>
**STOP using the boring and verbose DOMDocument.**

FluidXML has been created to bring XML manipulation to the next level.

```php
$book = new FluidXml();

$book->appendChild('title', 'The Theory Of Everything')
     ->appendChild('author', 'S. Hawking')
     ->appendChild('chapters', true)
         ->appendChild('chapter', 'Ideas About The Universe', ['id' => 1])
         ->appendChild('chapter', 'The Expanding Universe',   ['id' => 2]);
```

Or, if you prefer, there is a **concise syntax**.

```php
$book = fluidxml();

$book->add('title', 'The Theory Of Everything')
     ->add('author', 'S. Hawking')
     ->add('chapters', true)
         ->add('chapter', 'Ideas About The Universe', ['id' => 1])
         ->add('chapter', 'The Expanding Universe',   ['id' => 2]);
```

Do you love **PHP Arrays**? Take a look at this. :D

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
echo $book->xml();
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

**XPath** is king. One method to rule them all.

```php
$book->query('//title', '//author', '//chapter')
        ->attr('lang', 'en');
```

**XML Namespaces** are fully covered.

```php
$book->namespace('xhtml', 'http://www.w3.org/1999/xhtml')
     ->add('xhtml:h1')
     ->query('//xhtml:h1');
```

And sometimes **string templates** are the fastest way.

```php
$book->add(<<<XML
    <cover>
        <h1>The Theory Of Everything</h1>
        <img src="http://goo.gl/kO3Iov"/>
    </cover>
XML
);
```

Everything is fluid, even **iterations**.

```php
$book->query('//chapter')
        ->each(function($idx) {
             $this->attr('id', $idx);
        });
```

```php
$book->query('//chapters')
        ->times(3)
            ->add('chapter')
        ->times(4, function($idx) {
            $this->add('chapter');
            $this->add('illustration');
        });
```

And interoperability with existing **DOMDocument** and **SimpleXML** is simply magic.<br/>
Import them or inject them in any point of the FluidXML document just like that.

```php
fluidify($domdocument)
    ->query('/html/body')
         ->add($simplexml);

// Yes, we merged a DOMDocument with a SimpleXMLElement
// and everything is still fluid.
```

Don't be shy and tell it: **« IT'S AWESOME! »** ^\_^

Many other [APIs][apis] are available:
- `load()`
- `remove()`
- `appendSibling()`/`append()`
- `prependSibling()`/`prepend()`
- `appendText()`
- `setText()`/`text()`
- `appendCdata()`
- `setCdata()`/`cdata()`
- `length()`
- `dom()`
- `asArray()`

and others to come.


## Still doubts?
Other three great reasons to use FluidXML, but you'll have the best answer trying it yourself.

FluidXML is **fun** to use, **concise** and **effective**.

If it's not enough, it has a comprehensive test suite with a **100% code coverage**.

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
use \FluidXml\FluidXml;
use \FluidXml\FluidNamespace;
```
```php
use function \FluidXml\fluidxml;
use function \FluidXml\fluidns;
use function \FluidXml\fluidify;
```
See the [documentation](#documentation) to get started and become a [ninja][ninja].


## Documentation
_10 minutes reading_<br/>
Follow the [Getting Started tutorial][gettingstarted] to become a [ninja][ninja] in no time.

Many other examples are available:
- inside the [`documents/Examples.php`][examples] file
- inside the [`specs/FluidXml.php`][specs] file (as test cases)

All them cover from the simplest case to the most complex scenario.

Take a look at the [APIs][apis] to discover all the available manipulation operations,<br/>
and go to the [Wiki Page][wiki] for more reading.


## Donation
If you think this project is **awesome** or if you want to demonstrate<br/>
your immense gratitude **♡**, donate _1cent_.

[![Donate][donate-button]][donate-link]

**Thank You! :D** [♥][thankyou]


## Roadmap
* [x] PHP 5.6 backport
* [ ] Expanding the APIs and the documentation

<a href='https://pledgie.com/campaigns/30607'>
    <img alt='Click here to lend your support to: FluidXML and make a donation at pledgie.com !' src='https://pledgie.com/campaigns/30607.png?skin_name=chrome' border='0' >
</a>


## Author
Daniele Orlando  [&lt;fluidxml@danieleorlando.com&gt;](mailto:fluidxml@danieleorlando.com)


## License
FluidXML is licensed under the [BSD 2-Clause License][bsd].

See [`documents/License.txt`][license] for the details.
