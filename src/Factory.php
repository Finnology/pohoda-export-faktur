<?php
namespace Pohoda\InvoiceExport;

/**
 * Class Factory
 */
class Factory
{
    /**
     * @param $text
     * @return Item
     */
    public function createItem($text)
    {
        return new Item($text);
    }

    /**
     * @param int $id
     * @return Invoice
     */
    public function createInvoice($id)
    {
        return new Invoice($id);
    }

    /**
     * @param string $ico
     * @return Pohoda
     */
    public function createPohoda($ico)
    {
        return new Pohoda($ico);
    }
}