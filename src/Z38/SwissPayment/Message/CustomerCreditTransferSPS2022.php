<?php

namespace Z38\SwissPayment\Message;

use DOMElement;
use DOMDocument;
use InvalidArgumentException;

/**
 * CustomerCreditTransfer represents a Customer Credit Transfer Initiation (pain.001) message
 */
class CustomerCreditTransferSPS2022 extends AbstractCustomerCreditTransfer
{
    /**
     * @var string
     */
    private $manufacturerName;

    /**
     * Constructor
     *
     * @param string $id              Identifier of the message (should usually be unique over a period of at least 90 days)
     * @param string $initiatingParty Name of the initiating party
     *
     * @throws InvalidArgumentException When any of the inputs contain invalid characters or are too long.
     */
    public function __construct($id, $initiatingParty, $softwareName, $softwareVersion, $manufacturerName = null)
    {
        $this->manufacturerName = $manufacturerName;
        parent::__construct($id, $initiatingParty, $softwareName, $softwareVersion, AbstractCustomerCreditTransfer::SPS_2022);
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemaName()
    {
        return 'urn:iso:std:iso:20022:tech:xsd:pain.001.001.09';
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemaLocation()
    {
        return 'pain.001.001.09.ch.03.xsd';
    }

    /**
     * Creates a DOM element which contains details about the software used to create the message
     *
     * @param DOMDocument $doc
     *
     * @return DOMElement
     */
    protected function buildContactDetails(DOMDocument $doc)
    {
        $root = $doc->createElement('CtctDtls');

        $otherProductName = $doc->createElement('Othr');
        $otherProductName->appendChild($doc->createElement('ChanlTp', 'NAME'));
        $otherProductName->appendChild($doc->createElement('Id', $this->getSoftwareName()));
        $root->appendChild($otherProductName);

        if (isset($this->manufacturerName)) {
            $otherManufacturerName = $doc->createElement('Othr');
            $otherManufacturerName->appendChild($doc->createElement('ChanlTp', 'PRVD'));
            $otherManufacturerName->appendChild($doc->createElement('Id', $this->manufacturerName));
            $root->appendChild($otherManufacturerName);
        }

        $otherSoftwareVersion = $doc->createElement('Othr');
        $otherSoftwareVersion->appendChild($doc->createElement('ChanlTp', 'VRSN'));
        $otherSoftwareVersion->appendChild($doc->createElement('Id', $this->getSoftwareVersion()));
        $root->appendChild($otherSoftwareVersion);

        $otherSpsIgVersion = $doc->createElement('Othr');
        $otherSpsIgVersion->appendChild($doc->createElement('ChanlTp', 'SPSV'));
        $otherSpsIgVersion->appendChild($doc->createElement('Id', '0200'));
        $root->appendChild($otherSpsIgVersion);

        return $root;
    }
}
