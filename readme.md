## Introduction

This library provides simple interface for loading XML or HTML strings to `Dom\XMLDocument` or `Dom\HTMLDocument` objects (the DOM API introduced in PHP 8.4).
It prevents some known vulnerabilities and allows you to handle LibXML errors simply by catching XmlException as you can see below.

## Installation

```sh
$ composer require lightools/xml
```

## Simple usage

The loadXml method returns `Dom\XMLDocument` and the loadHtml method returns `Dom\HTMLDocument` (parsed by the spec-compliant HTML5 parser).
If you prefer working with SimpleXmlElement, you can use [simplexml_import_dom](https://secure.php.net/manual/en/function.simplexml-import-dom.php) function.

```php
$xml = '<?xml version="1.0"?><root>text</root>';
$html = '<!doctype html><title>Foo</title>';

$loader = new Lightools\Xml\XmlLoader();

try {
    $xmlDocument = $loader->loadXml($xml);
    $htmlDocument = $loader->loadHtml($html);

} catch (Lightools\Xml\XmlException $e) {
    // process exception
}
```

## How to run checks

```sh
$ composer check
```


## Versions

- v1.x is for PHP 5.4 and higher
- v2.x is for PHP 7.1 and higher
- v3.x is for PHP 8.0 and higher
- v4.x is for PHP 8.4 and higher (returns `Dom\XMLDocument` / `Dom\HTMLDocument` instead of `DOMDocument`)
