<?php declare(strict_types = 1);

namespace Lightools\Xml;

use LibXMLError;
use RuntimeException;
use function trim;
use const LIBXML_ERR_ERROR;
use const LIBXML_ERR_FATAL;
use const LIBXML_ERR_WARNING;

class XmlException extends RuntimeException
{

    private LibXMLError $error;

    public function __construct(LibXMLError $error)
    {
        $this->error = $error;
        $info = trim($error->message) . " on line $error->line and column $error->column";

        $errorMessage = match ($error->level) {
            LIBXML_ERR_WARNING => "XML Warning #$error->code: $info",
            LIBXML_ERR_ERROR => "XML Error #$error->code: $info",
            LIBXML_ERR_FATAL => "XML Fatal Error #$error->code: $info",
            default => "Unknown XML failure #$error->code: $info",
        };

        parent::__construct($errorMessage, $error->code);
    }

    public function getError(): LibXMLError
    {
        return $this->error;
    }

}
