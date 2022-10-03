<?php

namespace Lightools\Tests;

use Lightools\Xml\XmlException;
use Lightools\Xml\XmlLoader;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;

require __DIR__ . '/../vendor/autoload.php';

Environment::setup();

/**
 * @testCase
 */
class XmlLoaderTest extends TestCase {

    public function testBillionLaugh(): void {
        $source = trim('
        <?xml version="1.0"?>
        <!DOCTYPE lolz [
            <!ENTITY lol "lol">
            <!ENTITY lol1 "&lol;&lol;&lol;&lol;&lol;&lol;&lol;&lol;&lol;&lol;">
            <!ENTITY lol2 "&lol1;&lol1;&lol1;&lol1;&lol1;&lol1;&lol1;&lol1;&lol1;&lol1;">
            <!ENTITY lol3 "&lol2;&lol2;&lol2;&lol2;&lol2;&lol2;&lol2;&lol2;&lol2;&lol2;">
            <!ENTITY lol4 "&lol3;&lol3;&lol3;&lol3;&lol3;&lol3;&lol3;&lol3;&lol3;&lol3;">
            <!ENTITY lol5 "&lol4;&lol4;&lol4;&lol4;&lol4;&lol4;&lol4;&lol4;&lol4;&lol4;">
            <!ENTITY lol6 "&lol5;&lol5;&lol5;&lol5;&lol5;&lol5;&lol5;&lol5;&lol5;&lol5;">
            <!ENTITY lol7 "&lol6;&lol6;&lol6;&lol6;&lol6;&lol6;&lol6;&lol6;&lol6;&lol6;">
            <!ENTITY lol8 "&lol7;&lol7;&lol7;&lol7;&lol7;&lol7;&lol7;&lol7;&lol7;&lol7;">
            <!ENTITY lol9 "&lol8;&lol8;&lol8;&lol8;&lol8;&lol8;&lol8;&lol8;&lol8;&lol8;">
        ]>
        <lolz>&lol9;</lolz>
        ');

        Assert::exception(function () use ($source): void {
            $loader = new XmlLoader();
            $loader->loadXml($source);
        }, XmlException::class, 'XML Fatal Error #89: Detected an entity reference loop on line 14 and column 21');
    }

    public function testQuadraticBlowup(): void {
        $source = trim('
        <?xml version="1.0"?>
        <!DOCTYPE kaboom [
            <!ENTITY a "' . str_repeat('a', 100000) . '">
        ]>
        <kaboom>' . str_repeat('&a;', 100000) . '</kaboom>
        ');

        Assert::exception(function () use ($source): void {
            $loader = new XmlLoader();
            (string) $loader->loadXml($source);
        }, XmlException::class, 'XML Fatal Error #0: Document types are not allowed on line 0 and column 0');
    }

    public function testEmptySource(): void {
        Assert::exception(function () {
            $loader = new XmlLoader();
            $loader->loadXml('');
        }, XmlException::class, 'XML Fatal Error #0: Empty string supplied as input on line 0 and column 0');
    }

    public function testInvalidXml(): void {
        $source = trim('
        <?xml version="1.0"?>
        <invalid>
        ');

        Assert::exception(function () use ($source): void {
            $loader = new XmlLoader();
            $loader->loadXml($source);
        }, XmlException::class, 'XML Fatal Error #74: EndTag: \'</\' not found on line 2 and column 18');
    }

    public function testValidXml(): void {
        $source = trim('
        <?xml version="1.0"?>
        <note>
            <to>John</to>
            <from>Jack</from>
            <heading>Reminder</heading>
            <body>Don\'t forget me this weekend!</body>
        </note>
        ');

        $loader = new XmlLoader();
        $xml = $loader->loadXml($source);
        Assert::same('Jack', $xml->getElementsByTagName('from')->item(0)->nodeValue);
    }

    public function testValidHtml(): void {
        $source = trim('
        <!doctype html>
        <html lang=en>
            <head>
                <meta charset=utf-8>
                <title>Foo</title>
            </head>
            <body>
                <p>I\'m the content</p>
            </body>
        </html>
        ');

        $loader = new XmlLoader();
        $xml = $loader->loadHtml($source);
        Assert::same('Foo', $xml->getElementsByTagName('title')->item(0)->nodeValue);
    }

}

(new XmlLoaderTest)->run();
