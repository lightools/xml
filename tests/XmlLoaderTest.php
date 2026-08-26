<?php declare(strict_types = 1);

namespace Lightools\Tests;

use Lightools\Xml\XmlException;
use Lightools\Xml\XmlLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use function str_repeat;
use function trim;

#[CoversClass(XmlLoader::class)]
#[CoversClass(XmlException::class)]
class XmlLoaderTest extends TestCase
{

    public function testBillionLaugh(): void
    {
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

        // message differs across libxml versions, see https://github.com/GNOME/libxml2/commit/3f69fc805c9bea48f9339b1ce6c9db7a10f03f63
        $this->expectException(XmlException::class);
        $this->expectExceptionMessageMatches('~^XML Fatal Error #89: .+ on line \d+ and column \d+$~');

        $loader = new XmlLoader();
        $loader->loadXml($source);
    }

    public function testQuadraticBlowup(): void
    {
        $source = trim('
        <?xml version="1.0"?>
        <!DOCTYPE kaboom [
            <!ENTITY a "' . str_repeat('a', 100_000) . '">
        ]>
        <kaboom>' . str_repeat('&a;', 100_000) . '</kaboom>
        ');

        $this->expectException(XmlException::class);
        $this->expectExceptionMessageMatches('~^XML Fatal Error #89: .+ on line \d+ and column \d+$~');

        $loader = new XmlLoader();
        $loader->loadXml($source);
    }

    public function testDoctype(): void
    {
        $source = '<?xml version="1.0"?><!DOCTYPE root><root/>';

        $this->expectException(XmlException::class);
        $this->expectExceptionMessage('XML Fatal Error #0: Document types are not allowed on line 0 and column 0');

        $loader = new XmlLoader();
        $loader->loadXml($source);
    }

    public function testExternalEntityInjection(): void
    {
        $source = trim('
        <?xml version="1.0"?>
        <!DOCTYPE root [
            <!ENTITY xxe SYSTEM "file://' . __FILE__ . '">
        ]>
        <root>&xxe;</root>
        ');

        $this->expectException(XmlException::class);
        $this->expectExceptionMessage('XML Fatal Error #0: Document types are not allowed on line 0 and column 0');

        $loader = new XmlLoader();
        $loader->loadXml($source);
    }

    public function testEmptySource(): void
    {
        $this->expectException(XmlException::class);
        $this->expectExceptionMessage('XML Fatal Error #0: Empty string supplied as input on line 0 and column 0');

        $loader = new XmlLoader();
        $loader->loadXml('');
    }

    public function testInvalidXml(): void
    {
        $source = trim('
        <?xml version="1.0"?>
        <invalid>
        ');

        $this->expectException(XmlException::class);
        $this->expectExceptionMessage('XML Fatal Error #77: Premature end of data in tag invalid line 2 on line 2 and column 18');

        $loader = new XmlLoader();
        $loader->loadXml($source);
    }

    public function testValidXml(): void
    {
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
        $from = $xml->getElementsByTagName('from')->item(0);
        self::assertNotNull($from);
        self::assertSame('Jack', $from->textContent);
    }

    public function testValidHtml(): void
    {
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
        $html = $loader->loadHtml($source);
        $title = $html->getElementsByTagName('title')->item(0);
        self::assertNotNull($title);
        self::assertSame('Foo', $title->textContent);
    }

    public function testMalformedHtml(): void
    {
        $loader = new XmlLoader();
        $html = $loader->loadHtml('<p><b>foo</p></b><table><tr>');
        $bold = $html->getElementsByTagName('b')->item(0);
        self::assertNotNull($bold);
        self::assertSame('foo', $bold->textContent);
    }

}
