<?php

namespace Z38\SwissPayment\TransactionInformation;

use Z38\SwissPayment\Money\Money;
use Z38\SwissPayment\PostalAddressInterface;

/**
 * CreditTransfer contains all the information about the beneficiary and further information about the transaction.
 */
abstract class CreditTransfer
{
    /**
     * @var string
     */
    protected $instructionId;

    /**
     * @var string
     */
    protected $endToEndId;

    /**
     * @var string
     */
    protected $creditorName;

    /**
     * @var PostalAddressInterface
     */
    protected $creditorAddress;

    /**
     * @var Money
     */
    protected $amount;

    /**
     * @var string|null
     */
    protected $remittanceInformation;

    /**
     * Constructor
     *
     * @param string                 $instructionId   Identifier of the instruction (should be unique within the message)
     * @param string                 $endToEndId      End-To-End Identifier of the instruction (passed unchanged along the complete processing chain)
     * @param Money                  $amount          Amount of money to be transferred
     * @param string                 $creditorName    Name of the creditor
     * @param PostalAddressInterface $creditorAddress Address of the creditor
     */
    public function __construct($instructionId, $endToEndId, Money $amount, $creditorName, PostalAddressInterface $creditorAddress)
    {
        $this->instructionId = (string) $instructionId;
        $this->endToEndId = (string) $endToEndId;
        $this->amount = $amount;
        $this->creditorName = (string) $creditorName;
        $this->creditorAddress = $creditorAddress;
        $this->remittanceInformation = null;
    }

    /**
     * Sets the unstructured remittance information
     *
     * @param string|null $remittanceInformation
     *
     * @return CreditTransfer This credit transfer
     */
    public function setRemittanceInformation($remittanceInformation)
    {
        $this->remittanceInformation = $remittanceInformation;

        return $this;
    }

    /**
     * Gets the instructed amount of this transaction
     *
     * @return Money The instructed amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Builds a DOM tree of this transaction
     *
     * @param \DOMDocument $doc
     *
     * @return \DOMElement The built DOM tree
     */
    abstract public function asDom(\DOMDocument $doc);

    /**
     * Builds a DOM tree of this transaction and adds header nodes
     *
     * @param \DOMDocument $doc
     * @param string|null  $localInstrument Local Instrument
     * @param string|null  $serviceLevel
     *
     * @return \DOMNode The built DOM node
     */
    protected function buildHeader(\DOMDocument $doc, $localInstrument = null, $serviceLevel = null)
    {
        $root = $doc->createElement('CdtTrfTxInf');

        $id = $doc->createElement('PmtId');
        $id->appendChild($doc->createElement('InstrId', $this->instructionId));
        $id->appendChild($doc->createElement('EndToEndId', $this->endToEndId));
        $root->appendChild($id);

        if (!empty($localInstrument) || !empty($serviceLevel)) {
            $paymentType = $doc->createElement('PmtTpInf');
            if (!empty($localInstrument)) {
                $localInstrumentNode = $doc->createElement('LclInstrm');
                $localInstrumentNode->appendChild($doc->createElement('Prtry', $localInstrument));
                $paymentType->appendChild($localInstrumentNode);
            }
            if (!empty($serviceLevel)) {
                $serviceLevelNode = $doc->createElement('SvcLvl');
                $serviceLevelNode->appendChild($doc->createElement('Cd', $serviceLevel));
                $paymentType->appendChild($serviceLevelNode);
            }
            $root->appendChild($paymentType);
        }

        $amount = $doc->createElement('Amt');
        $instdAmount = $doc->createElement('InstdAmt', $this->amount->format());
        $instdAmount->setAttribute('Ccy', $this->amount->getCurrency());
        $amount->appendChild($instdAmount);
        $root->appendChild($amount);

        return $root;
    }

    /**
     * Builds a DOM node of the Creditor field
     *
     * @param \DOMDocument $doc
     *
     * @return \DOMNode The built DOM node
     */
    protected function buildCreditor(\DOMDocument $doc)
    {
        $creditor = $doc->createElement('Cdtr');
        $creditor->appendChild($doc->createElement('Nm', $this->creditorName));
        $creditor->appendChild($this->creditorAddress->asDom($doc));

        return $creditor;
    }

    /**
     * Indicates whether remittance information is set
     *
     * @return bool true if remittance information is set
     */
    protected function hasRemittanceInformation()
    {
        return !empty($this->remittanceInformation);
    }

    /**
     * Builds a DOM node of the Remittance Information field
     *
     * @param \DOMDocument $doc
     *
     * @return \DOMNode The built DOM node
     *
     * @throws \LogicException When no remittance information is set
     */
    protected function buildRemittanceInformation(\DOMDocument $doc)
    {
        if ($this->hasRemittanceInformation()) {
            $remittance = $doc->createElement('RmtInf');
            $remittance->appendChild($doc->createElement('Ustrd', $this->remittanceInformation));

            return $remittance;
        } else {
            throw new \LogicException('Can not build node without data.');
        }
    }
}
