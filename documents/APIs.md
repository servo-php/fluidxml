# APIs

## Functions
```php
function fluidxml($root?, array $options?);

function fluidify($document);

function fluidns($id, $uri, $mode?);
```


## FluidXml interfaces
```php
class FluidXml

::load($document);

::new($root?, array $options?);                     // Alias of __construct(), requires PHP 7.

__construct($root?, array $options?);

->namespace(...$namespace);

->namespaces();

->query(...$xpath);

->appendChild($child, ...$optionals);
// alias:
->add($child, ...$optionals);

->prependSibling($sibling, ...$optionals);
// aliases:
->prepend($sibling, ...$optionals);
->insertSiblingBefore($sibling, ...$optionals);

->appendSibling($sibling, ...$optionals);
// aliases:
->append($sibling, ...$optionals);
->insertSiblingAfter($sibling, ...$optionals);

->appendXml($xml);

->appendText($text);

->appendCdata($text);
// alias:
->cdata($text);

->setText($text);
// alias:
->text($text);

->setAttribute(...$arguments);
// alias:
->attr(...$arguments);

->remove(...$xpath);

->asArray();    // Available after a query or a node insertion with context switch.

->length();     // Available after a query or a node insertion with context switch.

->xml($strip = false);
```


## FluidNamespace interfaces
```php
class FluidNamespace

__construct($id, $uri, $mode?);

->id();

->uri();

->mode();

->querify($xpath);
```

- - -

The complete API documentation can be generated executing:
```sh
./support/tools/gendoc      # Generated under 'documents/api/'.
```
