<?php

namespace Lightools\Xml;

use DOMDocument;
use LibXMLError;

/**
 * @author Jan Nedbal
 */
class XmlLoader {

    /**
     * @param string $xml XML string
     * @return DOMDocument Root element
     * @throws XmlException When parsing fails
     */
    public function loadXml($xml) {
        $domDocument = $this->load($xml, 'loadXml');
        $this->checkDomDocumentChildren($domDocument);
        return $domDocument;
    }

    /**
     * @param string $html HTML string
     * @return DOMDocument Root element
     * @throws XmlException When parsing fails
     */
    public function loadHtml($html) {
        return $this->load($html, 'loadHtml');
    }

    /**
     * @param string $source
     * @param string $method DomDocument loading method (loadXml or loadHtml)
     * @return DOMDocument
     * @throws XmlException
     */
    private function load($source, $method) {
        if (!$source) {
            throw new XmlException($this->getCustomError('Empty string supplied as input'));
        }

        $internalErrorsOld = libxml_use_internal_errors(TRUE);
        $entityLoaderOld = libxml_disable_entity_loader(TRUE);

        $dom = new DOMDocument();
        $success = $dom->$method($source, LIBXML_NONET | LIBXML_NOBLANKS);

        if ($success === FALSE) {
            $error = libxml_get_last_error();
            throw new XmlException($error ? : $this->getCustomError('Unknown error'));
        }

        libxml_clear_errors();
        libxml_use_internal_errors($internalErrorsOld);
        libxml_disable_entity_loader($entityLoaderOld);

        return $dom;
    }

    /**
     * @see http://stackoverflow.com/a/10218526/1542616
     * @param DOMDocument $dom
     * @throws XmlException
     */
    private function checkDomDocumentChildren(DOMDocument $dom) {
        foreach ($dom->childNodes as $child) {
            if ($child->nodeType === XML_DOCUMENT_TYPE_NODE) {
                throw new XmlException($this->getCustomError('Document types are not allowed'));
            }
        }
    }

    /**
     * @param string $message
     * @return LibXMLError
     */
    private function getCustomError($message) {
        $err = new LibXMLError();
        $err->level = LIBXML_ERR_FATAL;
        $err->message = $message;
        $err->code = 0;
        $err->column = 0;
        $err->line = 0;
        $err->file = NULL;
        return $err;
    }

}
