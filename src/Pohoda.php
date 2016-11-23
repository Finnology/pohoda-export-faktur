<?php
namespace Pohoda\InvoiceExport;

use SimpleXMLElement;

/**
 * Class Pohoda
 */
class Pohoda
{
    public static $NS_INVOICE = 'http://www.stormware.cz/schema/version_2/invoice.xsd';
    public static $NS_TYPE = 'http://www.stormware.cz/schema/version_2/type.xsd';

    /** @var Invoice[] */
    protected $invoices = [];

    /** @var int */
    protected $lastId = 0;

    /** @var string */
    protected $ico;

    /**
     * Pohoda constructor.
     *
     * @param string $ico
     */
    public function __construct($ico)
    {
        $this->ico = $ico;
    }

    /**
     * @param Invoice $invoice
     * @throws InvalidInvoiceException
     */
    public function addInvoice(Invoice $invoice)
    {
        if (!$invoice->isValid()) {
            throw new InvalidInvoiceException("Invalid Invoice", $invoice->getErrors());
        }

        $this->invoices[] = $invoice;
    }

    /**
     * @param string $exportId
     * @param string $application
     * @param string $fileName
     * @param int $errorsNo
     * @param string $note
     */
    public function exportToFile($exportId, $application, $fileName, $errorsNo, $note = '') {

        $xml = $this->export($exportId, $application, $note);
        $incomplete = '';
        if ($errorsNo > 0) {
            $incomplete = '_incomplete';
        }
        $xml->asXML(dirname(__FILE__).'/'.$fileName.'_lastId-'.$this->lastId.$incomplete.'.xml');
    }

    /**
     * @param string $exportId
     * @param string $application
     * @param string $note
     */
    public function exportAsXml($exportId, $application, $note = '') {
        header ("Content-Type:text/xml; charset=utf-8");
        $xml = $this->export($exportId, $application, $note);
        echo $xml->asXML();
    }

    /**
     * @param string $exportId
     * @param string $application
     * @param string $note
     */
    public function exportAsString($exportId, $application, $note = '') {
        $xml = $this->export($exportId, $application, $note);
        echo $xml->asXML();
    }

    /**
     * @param string $exportId
     * @param string $application
     * @param string $note
     * @return SimpleXMLElement
     */
    private function export($exportId, $application, $note = '') {
        $xmlText = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<dat:dataPack id=\"".$exportId."\" ico=\"".$this->ico."\" application=\"".$application."\" version = \"2.0\" note=\"".$note."\" xmlns:dat=\"http://www.stormware.cz/schema/version_2/data.xsd\"></dat:dataPack>";
        $xml = simplexml_load_string($xmlText);

        $i = 0;
        foreach ($this->invoices as $item) {
            $i++;
            $dataItem = $xml->addChild("dat:dataPackItem");
            $dataItem->addAttribute('version', "2.0");
            $dataItem->addAttribute('id', $exportId . '-' . $i);

            $item->export($dataItem);

            if ($item->varNum > $this->lastId) {
                $this->lastId = $item->varNum;
            }
        }

        return $xml;
    }
}