<?php

require_once __DIR__ . "/../vendor/autoload.php";

$factory = new \Pohoda\InvoiceExport\Factory();

$pohoda = $factory->createPohoda('01508512');

// cislo faktury
$invoice = $factory->createInvoice(324342);

// cena fakutry s DPH
$price = 1000;
$invoice->setPriceWithoutVAT($price);
$invoice->setPriceOnlyVAT($price*0.21);
$invoice->withVAT(true);

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

$errorsNo = 0; // pokud si pocitate chyby, projevi se to v nazvu souboru

// ulozeni do souboru
$pohoda->exportToFile(time(), 'popis', date("Y-m-d_H-i-s"), $errorsNo);

// vypsani na obrazovku jako XML s hlavickou
$pohoda->exportAsXml(time(), 'popis', date("Y-m-d_H-i-s"));