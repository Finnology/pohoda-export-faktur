<?php
namespace Pohoda\InvoiceExport;

use SimpleXMLElement;

/**
 * Class Item
 */
class Item
{
    /** @var string */
    protected $text;

    /** @var int */
    protected $quantity;

    /** @var float */
    protected $rateVAT = 0;

    /** @var float */
    protected $unitPrice;

    /** @var string */
    protected $accounting;

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @param float $rateVAT
     */
    public function setRateVAT($rateVAT)
    {
        $this->rateVAT = $rateVAT;
    }

    /**
     * @param float $unitPrice
     */
    public function setUnitPrice($unitPrice)
    {
        $this->unitPrice = $unitPrice;
    }

    /**
     * @param string $accounting
     */
    public function setAccounting($accounting)
    {
        $this->accounting = $accounting;
    }

    /**
     * @return float
     */
    public function getTotalPriceNone()
    {
        return $this->quantity * $this->unitPrice;
    }

    /**
     * @return float
     */
    public function getTotalPriceVAT()
    {
        $priceNone = $this->getTotalPriceNone();
        $VAT = $this->rateVAT * $priceNone;
        return $priceNone + $VAT;
    }

    /**
     * @param SimpleXMLElement $itemXML
     */
    public function export(SimpleXMLElement $itemXML)
    {
        $itemXML->addChild("inv:text", $this->text);
        $itemXML->addChild("inv:quantity", $this->quantity);
        $itemXML->addChild("inv:rateVAT", $this->rateVAT == 0 ? 'none' : $this->rateVAT);

        $itemXML->addChild("inv:homeCurrency")
            ->addChild('typ:unitPrice', $this->unitPrice)
        ;

        $itemXML->addChild("inv:accounting")
            ->addChild("typ:ids", $this->accounting)
        ;
    }
}