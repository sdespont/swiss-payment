<?php

namespace Z38\SwissPayment\Message;

use DOMDocument;
use InvalidArgumentException;
use Z38\SwissPayment\Text;
use DOMElement;
/**
 * CustomerCreditTransfer represents a Customer Credit Transfer Initiation (pain.001) message
 */
class CustomerCreditTransferSPS2021 extends AbstractCustomerCreditTransfer
{
    /**
     * Constructor
     *
     * @param string $id              Identifier of the message (should usually be unique over a period of at least 90 days)
     * @param string $initiatingParty Name of the initiating party
     *
     * @throws InvalidArgumentException When any of the inputs contain invalid characters or are too long.
     */
    public function __construct($id, $initiatingParty, $softwareName, $softwareVersion)
    {
        parent::__construct($id, $initiatingParty, $softwareName, $softwareVersion, AbstractCustomerCreditTransfer::SPS_2021);
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemaName()
    {
        return 'http://www.six-interbank-clearing.com/de/pain.001.001.03.ch.02.xsd';
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemaLocation()
    {
        return 'pain.001.001.03.ch.02.xsd';
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

        $root->appendChild(Text::xml($doc, 'Nm', $this->getSoftwareName()));
        $root->appendChild(Text::xml($doc, 'Othr', $this->getSoftwareVersion()));

        return $root;
    }
}
