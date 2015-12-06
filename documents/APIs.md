# APIs

## Functions
```php
function fluidxml($root?, array $options?);

function fluidns($id, $uri, $mode?);
```


## FluidXml interfaces
```php
class FluidXml

__construct($root?, array $options?);

->namespace(...$namespace);

->namespaces();

->query(...$xpath);

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

/*****************
 * Alias methods *
 *****************/

->add($child, ...$optionals);                       // ->appendChild

->prepend($sibling, ...$optionals);                 // ->prependSibling

->insertSiblingBefore($sibling, ...$optionals);     // ->prependSibling

->append($sibling, ...$optionals);                  // ->appendSibling

->insertSiblingAfter($sibling, ...$optionals);      // ->appendSibling

->attr(...$arguments);                              // ->setAttribute

->text($text);                                      // ->setText
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
