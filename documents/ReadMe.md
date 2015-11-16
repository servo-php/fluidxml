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
(remember the old _DOMDocument_/_DOMNode_/_DOMElement_...).

```php
$doc->appendChild('chapters', true)
       ->appendChild('chapter', 'Ideas About The Universe', ['id'=> 1])
       ->appendChild('chapter', 'The Expanding Universe',   ['id'=> 2]);
         
```

XPath is king.

```php
$doc->query('//chapter')
    ->setAttribute('lang', 'en')
    ->query('..')
    ->setAttribute('lang', 'en');
```

And sometimes string templates are the fastest way.

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


## Why trying it
Three great reasons, but you'll have the best answer trying it yourself.

FluidXML is **fun** to use, **concise** and **effective**. If it's not enough,  
it has a compreansive test suite with a **100% code coverage**.


## Installation

[api]: https://link/url

## License
FluidXML is licensed under the BSD 2-Clause License.  
See `documents/License.txt` for the details.
