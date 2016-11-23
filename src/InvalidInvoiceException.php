<?php
namespace Pohoda\InvoiceExport;

/**
 * Class InvalidInvoiceException
 */
class InvalidInvoiceException extends \Exception
{
    /** @var array */
    protected $_errors = [];

    /**
     * InvalidInvoiceException constructor.
     *
     * @param string $message
     * @param array $errors
     */
    public function __construct($message, array $errors)
    {
        $this->_errors = $errors;
        parent::__construct($message);
    }

    /**
     * @return array
     */
    public function getInvoiceErrors()
    {
        return $this->_errors;
    }
}