[travis]: https://travis-ci.org/servo-php/fluidxml
[travis-badge]: https://travis-ci.org/servo-php/fluidxml.svg?branch=master
[apis]: https://github.com/servo-php/fluidxml/blob/master/documents/APIs.md
[gettingstarted]: https://github.com/servo-php/fluidxml/blob/master/documents/Getting-Started.md
[examples]: https://github.com/servo-php/fluidxml/blob/master/documents/Examples.php
[specs]: https://github.com/servo-php/fluidxml/blob/master/specs/FluidXml.php
[wiki]: https://github.com/servo-php/fluidxml/wiki
[license]: https://github.com/servo-php/fluidxml/blob/master/documents/License.txt
[codecoverage]: https://bytebucket.org/daniele_orlando/hosting/raw/master/FluidXML_code_coverage.png?nocache=1
[ninja]: http://1.viki.io/d/1863c/8b75dc48c9.gif
[donate-button]: https://bytebucket.org/daniele_orlando/hosting/raw/master/Donate_button.png?nocache=2
[donate-link]: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=UUBXYHQRVJE28
[donate-link-alt]: https://www.paypal.me/danieleorlando
[thankyou]: https://upload.wikimedia.org/wikipedia/commons/thumb/f/f1/Heart_corazón.svg/2000px-Heart_corazón.svg.png

[![Build Status][travis-badge]][travis]

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
         ->appendChild('chapter', 'The Expanding Universe',   ['id' => 2])
     ->query('//chapter')
         ->setAttribute('lang', 'en');
```

Or, if you prefer, there is a **concise syntax**.

```php
$book = fluidxml();

$book->add('title', 'The Theory Of Everything')
     ->add('author', 'S. Hawking')
     ->add('chapters', true)
         ->add('chapter', 'Ideas About The Universe', ['id' => 1])
         ->add('chapter', 'The Expanding Universe',   ['id' => 2])
     ->query('//chapter')
         ->attr('lang', 'en');
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
    <chapter id="1" lang="en">Ideas About The Universe</chapter>
    <chapter id="2" lang="en">The Expanding Universe</chapter>
  </chapters>
</doc>
```

Creating **structured documents** is so easy that you'll not believe.

```php
$food = fluidxml();

// Batch insertion of nodes.
$food->add([ 'cake'  => 'Tiramisu',
             'pizza' => 'Margherita' ]);

// A bunch of egg's all with the same attribute.
$food->add([ ['egg'],
             ['egg'],
             ['egg'] ], ['price' => '0.25']);

// Deep tree structures are supported too.
$food->add([ 'fridge' => [ 'omelette' => 'with potato',
                           'soupe'    => 'with mashrooms' ],
             'freezer' => [ 'meat' => 'beef' ] ]);
```

**XPath** is king.

```php
$book->query('//chapter')
     ->query('..')
        ->attr('lang', 'en')
     ->query('../title')
        ->attr('country', 'us');
```

And sometimes **string templates** are the fastest way.

```php
$book->add('cover', true)
        ->add(<<<XML
            <h1>The Theory Of Everything</h1>
            <img src="http://goo.gl/kO3Iov"/>
XML
);
```

**XML Namespaces** are fully covered too.

```php
$book->namespace('xhtml', 'http://www.w3.org/1999/xhtml')
     ->namespace('svg',   'http://www.w3.org/2000/svg')
     ->appendChild('xhtml:h1')
     ->appendChild('svg:shape')
     ->query('//xhtml:h1');
```

Existing **DOMDocument** and **SimpleXML** documents are not a problem, just import them.

```php
$fluidxml = fluidify($domdocument);

$fluidxml->query('/html/body')
         ->add($simplexmlelement);
```

Don't be shy and tell it: **« IT'S AWESOME! »** ^\_^


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

```php
use \FluidXml\FluidXml;
use \FluidXml\FluidNamespace;
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
* [ ] Expanding the APIs
* [ ] Extending the documentation

<a href='https://pledgie.com/campaigns/30607'>
    <img alt='Click here to lend your support to: FluidXML and make a donation at pledgie.com !' src='https://pledgie.com/campaigns/30607.png?skin_name=chrome' border='0' >
</a>


## Author
Daniele Orlando  [&lt;fluidxml@danieleorlando.com&gt;](mailto:fluidxml@danieleorlando.com)


## License
FluidXML is licensed under the BSD 2-Clause License.

See [`documents/License.txt`][license] for the details.
