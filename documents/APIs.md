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

->add($child, ...$optionals);
->appendChild($child, ...$optionals);

->prepend($sibling, ...$optionals);
->prependSibling($sibling, ...$optionals);
->insertSiblingBefore($sibling, ...$optionals);

->append($sibling, ...$optionals);
->appendSibling($sibling, ...$optionals);
->insertSiblingAfter($sibling, ...$optionals);

->attr(...$arguments);
->setAttribute(...$arguments);

->text($text);
->setText($text);

->appendText($text);

->cdata($text);
->setCdata($text);

->appendCdata($text);

->remove(...$xpath);

->asArray();    // Available after a query or a node insertion with context switch.

->length();     // Available after a query or a node insertion with context switch.

->dom();

->xml($strip = false);

->save($file, $strip = false);
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
