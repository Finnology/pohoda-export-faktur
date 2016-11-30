<?php

require_once __DIR__ . "/../vendor/autoload.php";

$factory = new \Pohoda\InvoiceExport\Factory();

$pohoda = $factory->createPohoda('01508512');

// cislo faktury
$invoice = $factory->createInvoice(324342);

$item1 = $factory->createItem();
$item1->setAccounting('some_string');
$item1->setQuantity(5);
$item1->setUnitPrice(20000);

$item2 = $factory->createItem();
$item2->setAccounting('some_string');
$item2->setQuantity(10);
$item2->setUnitPrice(10000);

$invoice->addItem($item1);
$invoice->addItem($item2);

// variabilni cislo
$invoice->setVariableNumber('12345678');
// datum vytvoreni faktury
$invoice->setDateCreated('2014-01-24');
// datum zdanitelneho plneni
$invoice->setDateTax('2014-02-01');
// datum splatnosti
$invoice->setDateDue('2014-02-04');

// text faktury
$invoice->setText('faktura za prace ...');

// nastaveni identity dodavatele
$invoice->setProviderIdentity([
    "company" => "Firma s.r.o.",
    "city" => "Praha",
    "street" => "Nejaka ulice",
    "number" => "80/3",
    "zip" => "160 00",
    "ico" => "034234",
    "dic" => "CZ034234"]);

// nastaveni identity prijemce
$invoice->setPurchaserIdentity([
    "company" => "Firma s.r.o.",
    "city" => "Praha",
    "street" => "Nejaka ulice 80/3",
    "zip" => "160 00",
    "ico" => "034234"]);

$pohoda->addInvoice($invoice);

// ulozeni do souboru
$pohoda->exportToFile(time(), 'popis', date("Y-m-d_H-i-s"), 0);

// vypsani na obrazovku jako XML s hlavickou
$pohoda->exportAsXml(time(), 'popis', date("Y-m-d_H-i-s"));