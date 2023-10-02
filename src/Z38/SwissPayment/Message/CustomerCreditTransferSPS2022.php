<?php

namespace Z38\SwissPayment\Message;

use DOMElement;
use DOMDocument;
use InvalidArgumentException;
use Z38\SwissPayment\Text;

/**
 * CustomerCreditTransfer represents a Customer Credit Transfer Initiation (pain.001) message
 */
class CustomerCreditTransferSPS2022 extends AbstractCustomerCreditTransfer
{
    /**
     * Constructor
     *
     * @param string $id              Identifier of the message (should usually be unique over a period of at least 90 days)
     * @param string $initiatingParty Name of the initiating party
     *
     * @throws InvalidArgumentException When any of the inputs contain invalid characters or are too long.
     */
    public function __construct($id, $initiatingParty)
    {
        parent::__construct($id, $initiatingParty, AbstractCustomerCreditTransfer::SPS_2022);
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
     * Returns the name of the software used to create the message
     *
     * @return string
     */
    public function getSoftwareName()
    {
        return 'Z38_SwissPayment';
    }

    /**
     * Returns the version of the software used to create the message
     *
     * @return string
     */
    public function getSoftwareVersion()
    {
        return '0.7.0';
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

        $otherManufacturerName = $doc->createElement('Othr');
        $otherManufacturerName->appendChild($doc->createElement('ChanlTp', 'PRVD'));
        $otherManufacturerName->appendChild($doc->createElement('Id', $this->getSoftwareName()));
        $root->appendChild($otherManufacturerName);

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
