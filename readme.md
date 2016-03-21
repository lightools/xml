## Introduction

This library provides simple interface for loading XML or HTML strings to DomDocument object.
It prevents some known vulnerabilities and allows you to handle LibXML errors simply by catching XmlException as you can see below.

## Installation

```sh
$ composer require lightools/xml
```

## Simple usage

```php
$xml = '<?xml version="1.0"?><root>text</root>';
$html = '<!doctype html><title>Foo</title>';

$loader = new Lightools\Xml\XmlLoader();

try {
    $xmlDomDocument = $loader->loadXml($xml);
    $htmlDomDocument = $loader->loadHtml($html);

} catch (Lightools\Xml\XmlException $e) {
    // process exception
}
```

## How to run tests

```sh
$ vendor/bin/tester tests
```
