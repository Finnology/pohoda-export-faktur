<?php
namespace Pohoda\InvoiceExport;

use SimpleXMLElement;

/**
 * Class Invoice
 */
class Invoice
{
    public $withVAT = false;

    public $type = 'issuedInvoice';

    public $varNum;
    public $specNum;
    public $date;
    public $dateTax;
    public $dateAccounting;
    public $dateDue;
    public $code;
    public $text;
    public $note;

    public $accounting = '2Fv';

    public $coefficient = '1.0';

    public $priceTotal = 0;

    public $myIdentity = [];

    public $partnerIdentity = [];

    private $id;
    private $errors = [];
    private $reqErrors = [];
    private $required = ['date', 'varNum', 'text'];

    /** @var Item[] */
    protected $items = [];

    /** @var string */
    protected $accountNo;

    /** @var string */
    protected $bankCode;

    /**
     * Invoice constructor.
     * @param string $id
     */
    public function __construct($id)
    {
        $this->id = $id;
        $this->setProviderIdentity([]);
        $this->setPurchaserIdentity([]);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Item $item
     */
    public function addItem(Item $item)
    {
        $this->items[] = $item;
        $this->priceTotal = $this->priceTotal + $item->getTotalPriceNone();
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->checkRequired() && empty($this->errors);
    }

    /**
     * @return bool
     */
    private function checkRequired()
    {
        $result = true;
        $this->reqErrors = [];

        foreach ($this->required as $param) {
            if (!isset($this->$param)) {
                $result = false;
                $this->reqErrors[] = 'Není nastaven povinný prvek ' . $param;
            }
        }

        return $result;
    }

    /**
     * @param $name
     * @param $value
     * @param bool $maxLength
     * @param bool $isNumeric
     * @param bool $isDate
     */
    private function validateItem($name, $value, $maxLength = false, $isNumeric = false, $isDate = false)
    {

        if ($maxLength !== false) {
            if (strlen($value) > $maxLength) {
                $this->errors[] = $name . '="' . $value . '" - překročilo maximální délku ' . $maxLength;
            }
        }

        if ($isNumeric) {
            if (!is_numeric($value)) {
                $this->errors[] = $name . '="' . $value . '" - není číslo';
            }
        }

        if ($isDate) {
            if (!date_create($value)) {
                $this->errors[] = $name . '="' . $value . '" - není datum';
            }
        }
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private function removeSpaces($value)
    {
        return preg_replace('/\s+/', '', $value);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        $arr = array_merge($this->errors, $this->reqErrors);

        $fce = function ($row) {
            return $this->id . ':' . $row;
        };
        $arr = array_map($fce, $arr);

        return $arr;
    }

    /**
     * @param mixed $value
     */
    public function withVAT($value)
    {
        $this->withVAT = $value;
    }

    /**
     * @param string $value
     */
    public function setType($value)
    {
        $this->type = $value;
    }

    /**
     * @param int $value
     */
    public function setVariableNumber($value)
    {
        $value = $this->removeSpaces($value);
        $this->validateItem('variable number', $value, 20, true);
        $this->varNum = $value;
    }

    /**
     * @param int $value
     */
    public function setSpecificNumber($value)
    {
        $value = $this->removeSpaces($value);
        $this->validateItem('variable number', $value, 20, true);
        $this->specNum = $value;
    }

    /**
     * @param mixed $value
     */
    public function setDateCreated($value)
    {
        $this->validateItem('date created', $value, false, false, true);
        $this->date = $value;
    }

    /**
     * @param mixed $value
     */
    public function setDateTax($value)
    {
        $this->validateItem('date tax', $value, false, false, true);
        $this->dateTax = $value;
    }

    /**
     * @param mixed $value
     */
    public function setDateAccounting($value)
    {
        $this->validateItem('date accounting', $value, false, false, true);
        $this->dateAccounting = $value;
    }

    /**
     * @param mixed $value
     */
    public function setDateDue($value)
    {
        $this->validateItem('date due', $value, false, false, true);
        $this->dateDue = $value;
    }

    /**
     * @param string $value
     */
    public function setCode($value)
    {
        $this->code = $value;
    }

    /**
     * @param $value
     */
    public function setText($value)
    {
        $this->validateItem('text', $value, 240);
        $this->text = $value;
    }

    /**
     * @param string $value
     */
    public function setBank($value)
    {
        $this->validateItem('bank shortcut', $value, 19);
        $this->bankShortcut = $value;
    }

    /**
     * @param string $value
     */
    public function setAccountNo($value)
    {
        $this->accountNo = $value;
    }

    /**
     * @param string $value
     */
    public function setBankCode($value)
    {
        $this->bankCode = $value;
    }

    /**
     * @param string $value
     */
    public function setAccounting($value)
    {
        $this->validateItem('accounting', $value, 19);
        $this->accounting = $value;
    }

    /**
     * @param string $value
     */
    public function setNote($value)
    {
        $this->note = $value;
    }

    /**
     * @param string $value
     */
    public function setContract($value)
    {
        $this->validateItem('contract', $value, 10);
        $this->contract = $value;
    }

    /**
     * @param int $value
     */
    public function setSymbolicNumber($value)
    {
        $value = $this->removeSpaces($value);
        $this->validateItem('symbolic number', $value, 20, true);
        $this->symbolicNumber = $value;
    }

    /**
     * @param string[] $value
     */
    public function setProviderIdentity(array $value)
    {
        if (isset($value['zip'])) {
            $value['zip'] = $this->removeSpaces($value['zip']);
        }
        if (isset($value['ico'])) {
            $value['ico'] = $this->removeSpaces($value['ico']);
        }

        if (isset($value['company'])) {
            $this->validateItem('provider - company', $value['company'], 96);
        }
        if (isset($value['street'])) {
            $this->validateItem('provider - street', $value['street'], 64);
        }
        if (isset($value['zip'])) {
            $this->validateItem('provider - zip', $value['zip'], 15, true);
        }
        if (isset($value['city'])) {
            $this->validateItem('provider - city', $value['city'], 45);
        }
        if (isset($value['ico'])) {
            $this->validateItem('provider - ico', $value['ico'], 15, true);
        }
        if (isset($value['number'])) {
            $this->validateItem('provider - number', $value['number'], 10);
        }

        $this->myIdentity = $value;
    }

    /**
     * @param string[] $value
     */
    public function setPurchaserIdentity(array $value)
    {
        if (isset($value['zip'])) {
            $value['zip'] = $this->removeSpaces($value['zip']);
        }
        if (isset($value['ico'])) {
            $value['ico'] = $this->removeSpaces($value['ico']);
        }

        if (isset($value['company'])) {
            $this->validateItem('purchaser - company', $value['company'], 96);
        }
        if (isset($value['division'])) {
            $this->validateItem('purchaser - division', $value['division'], 32);
        }
        if (isset($value['street'])) {
            $this->validateItem('purchaser - street', $value['street'], 64);
        }
        if (isset($value['zip'])) {
            $this->validateItem('purchaser - zip', $value['zip'], 15, true);
        }
        if (isset($value['city'])) {
            $this->validateItem('purchaser - city', $value['city'], 45);
        }
        if (isset($value['ico'])) {
            $this->validateItem('purchaser - ico', $value['ico'], 15, true);
        }
        if (isset($value['number'])) {
            $this->errors[] = 'purchaser nesmi mit nastaven type: number';
        }

        $this->partnerIdentity = $value;
    }

    /**
     * @param SimpleXMLElement $xml
     */
    public function export(SimpleXMLElement $xml)
    {
        $xmlInvoice = $xml->addChild("inv:invoice", null, Pohoda::$NS_INVOICE);
        $xmlInvoice->addAttribute('version', "2.0");


        $this->exportHeader($xmlInvoice->addChild("inv:invoiceHeader", null, Pohoda::$NS_INVOICE));
        $this->exportDetail($xmlInvoice->addChild("inv:invoiceDetail", null, Pohoda::$NS_INVOICE));
        $this->exportSummary($xmlInvoice->addChild("inv:invoiceSummary", null, Pohoda::$NS_INVOICE));
    }

    /**
     * @param SimpleXMLElement $header
     */
    private function exportHeader(SimpleXMLElement $header)
    {
        $header->addChild("inv:invoiceType", $this->type);
        $num = $header->addChild("inv:number");
        $num->addChild('typ:numberRequested', $this->code, Pohoda::$NS_TYPE);

        $header->addChild("inv:originalDocument", $this->id);

        $header->addChild("inv:symVar", $this->varNum);
        $header->addChild("inv:symSpec", $this->specNum);

        $header->addChild("inv:date", $this->date);
        $header->addChild("inv:dateTax", $this->dateTax);

        $header->addChild("inv:dateAccounting", $this->dateAccounting);

        $header->addChild("inv:dateDue", $this->dateDue);

        $accounting = $header->addChild("inv:accounting");
        $accounting->addChild('typ:ids', $this->accounting, Pohoda::$NS_TYPE);

        $classification = $header->addChild("inv:classificationVAT");
        $classification->addChild('typ:ids', 'PN', Pohoda::$NS_TYPE);

        $header->addChild("inv:text", $this->text);

        $partnerIdentity = $header->addChild("inv:partnerIdentity");
        $this->exportAddress($partnerIdentity, $this->partnerIdentity);

        $myIdentity = $header->addChild("inv:myIdentity");
        $this->exportAddress($myIdentity, $this->myIdentity);

        $paymentAccount = $header->addChild("inv:paymentAccount");
        $paymentAccount->addChild("typ:accountNo", $this->accountNo, Pohoda::$NS_TYPE);
        $paymentAccount->addChild("typ:bankCode", $this->bankCode, Pohoda::$NS_TYPE);

        $header->addChild("inv:note", $this->note);
        $header->addChild("inv:intNote", 'Tento doklad byl vytvořen importem přes XML.');
    }

    /**
     * @param SimpleXMLElement $detail
     */
    private function exportDetail(SimpleXMLElement $detail)
    {
        foreach ($this->items as $item) {
            $itemXML = $detail->addChild("inv:invoiceItem");
            $item->export($itemXML);
        }
    }

    /**
     * @param SimpleXMLElement $xml
     * @param array $data
     */
    private function exportAddress(SimpleXMLElement $xml, Array $data)
    {
        $address = $xml->addChild('typ:address', null, Pohoda::$NS_TYPE);

        if (isset($data['company'])) {
            $address->addChild('typ:company', $data['company']);
        }

        if (isset($data['division'])) {
            $address->addChild('typ:division', $data['division']);
        }

        if (isset($data['city'])) {
            $address->addChild('typ:city', $data['city']);
        }

        if (isset($data['street'])) {
            $address->addChild('typ:street', $data['street']);
        }

        if (isset($data['number'])) {
            $address->addChild('typ:number', $data['number']);
        }

        if (isset($data['zip'])) {
            $address->addChild('typ:zip', $data['zip']);
        }

        if (isset($data['ico'])) {
            $address->addChild('typ:ico', $data['ico']);
        }

        if (isset($data['dic'])) {
            $address->addChild('typ:dic', $data['dic']);
        }
    }

    /**
     * @param SimpleXMLElement $summary
     */
    private function exportSummary(SimpleXMLElement $summary)
    {
        $hc = $summary->addChild("inv:homeCurrency");
        $hc->addChild('typ:priceNone', $this->priceTotal, Pohoda::$NS_TYPE);
        $hc->addChild('typ:priceLow', 0, Pohoda::$NS_TYPE);
        $hc->addChild('typ:priceHighSum', 0, Pohoda::$NS_TYPE);
    }
}

