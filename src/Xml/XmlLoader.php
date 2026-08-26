<?php declare(strict_types = 1);

namespace Lightools\Xml;

use Dom\HTMLDocument;
use Dom\XMLDocument;
use DOMException;
use LibXMLError;
use function libxml_clear_errors;
use function libxml_get_last_error;
use function libxml_use_internal_errors;
use const LIBXML_ERR_FATAL;
use const LIBXML_NOBLANKS;
use const LIBXML_NONET;

class XmlLoader
{

    /**
     * @throws XmlException When parsing fails
     */
    public function loadXml(string $xml): XMLDocument
    {
        $domDocument = $this->parse(static function () use ($xml): XMLDocument {
            return XMLDocument::createFromString($xml, LIBXML_NONET | LIBXML_NOBLANKS);
        }, $xml);

        if ($domDocument->doctype !== null) {
            throw new XmlException($this->getCustomError('Document types are not allowed'));
        }

        return $domDocument;
    }

    /**
     * @throws XmlException When parsing fails
     */
    public function loadHtml(string $html): HTMLDocument
    {
        return $this->parse(static function () use ($html): HTMLDocument {
            return HTMLDocument::createFromString($html);
        }, $html);
    }

    /**
     * @template T of XMLDocument|HTMLDocument
     * @param callable(): T $parser
     * @return T
     * @throws XmlException
     */
    private function parse(callable $parser, string $source): XMLDocument|HTMLDocument
    {
        if ($source === '') {
            throw new XmlException($this->getCustomError('Empty string supplied as input'));
        }

        $internalErrorsOld = libxml_use_internal_errors(true);

        try {
            return $parser();

        } catch (DOMException $e) {
            $error = libxml_get_last_error();
            throw new XmlException($error !== false ? $error : $this->getCustomError($e->getMessage()));

        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($internalErrorsOld);
        }
    }

    private function getCustomError(string $message): LibXMLError
    {
        $err = new LibXMLError();
        $err->level = LIBXML_ERR_FATAL;
        $err->message = $message;
        $err->code = 0;
        $err->column = 0;
        $err->line = 0;
        $err->file = '';
        return $err;
    }

}
