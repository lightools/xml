<?php

namespace Lightools\Xml;

use LibXMLError;
use RuntimeException;

class XmlException extends RuntimeException {

    /**
     * @var LibXMLError
     */
    private $error;

    public function __construct(LibXMLError $error) {
        $this->error = $error;
        $info = trim($error->message) . " on line $error->line and column $error->column";

        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $errorMessage = "XML Warning #$error->code: $info";
                break;
            case LIBXML_ERR_ERROR:
                $errorMessage = "XML Error #$error->code: $info";
                break;
            case LIBXML_ERR_FATAL:
                $errorMessage = "XML Fatal Error #$error->code: $info";
                break;
        }

        parent::__construct($errorMessage, $error->code);
    }

    /**
     * @return LibXMLError
     */
    public function getError() {
        return $this->error;
    }

}
