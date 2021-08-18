<?php declare(strict_types = 1);

namespace Lightools\Xml;

use DOMDocument;
use LibXMLError;
use function libxml_clear_errors;
use function libxml_get_last_error;
use function libxml_use_internal_errors;
use const LIBXML_ERR_FATAL;
use const LIBXML_NOBLANKS;
use const LIBXML_NONET;
use const XML_DOCUMENT_TYPE_NODE;

class XmlLoader
{

    private const LOAD_XML = 'xml';
    private const LOAD_HTML = 'html';

    /**
     * @throws XmlException When parsing fails
     */
    public function loadXml(string $xml): DOMDocument
    {
        $domDocument = $this->load($xml, self::LOAD_XML);
        $this->checkDomDocumentChildren($domDocument);
        return $domDocument;
    }

    /**
     * @throws XmlException When parsing fails
     */
    public function loadHtml(string $html): DOMDocument
    {
        return $this->load($html, self::LOAD_HTML);
    }

    /**
     * @throws XmlException
     */
    private function load(string $source, string $method): DOMDocument
    {
        if ($source === '') {
            throw new XmlException($this->getCustomError('Empty string supplied as input'));
        }

        $internalErrorsOld = libxml_use_internal_errors(true);

        $dom = new DOMDocument();

        if ($method === self::LOAD_XML) {
            $success = $dom->loadXML($source, LIBXML_NONET | LIBXML_NOBLANKS);
        } else {
            $success = $dom->loadHTML($source, LIBXML_NONET | LIBXML_NOBLANKS);
        }

        $error = libxml_get_last_error();

        libxml_clear_errors();
        libxml_use_internal_errors($internalErrorsOld);

        if ($success === false) {
            throw new XmlException($error !== false ? $error : $this->getCustomError('Unknown error'));
        }

        return $dom;
    }

    /**
     * @see http://stackoverflow.com/a/10218526/1542616
     * @throws XmlException
     */
    private function checkDomDocumentChildren(DOMDocument $dom): void
    {
        foreach ($dom->childNodes as $child) {
            if ($child->nodeType === XML_DOCUMENT_TYPE_NODE) {
                throw new XmlException($this->getCustomError('Document types are not allowed'));
            }
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
