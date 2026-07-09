<?php

namespace Z38\SwissPayment\TransactionInformation;

use DOMDocument;
use DOMElement;
use InvalidArgumentException;
use Z38\SwissPayment\FinancialInstitutionInterface;
use Z38\SwissPayment\IBAN;
use Z38\SwissPayment\Money;

/**
 * BankCreditTransfer contains all the information about a type 3 transaction
 * for a QR-Bill with Creditor Reference (SCOR)
 */
class BankCreditTransferWithCreditorReference extends BankCreditTransfer
{
    /**
     * @var string
     */
    protected $creditorReference;

    /**
     * BankCreditTransferWithQRR constructor.
     * @param $instructionId
     * @param $endToEndId
     * @param Money\Money $amount
     * @param $creditorName
     * @param $creditorAddress
     * @param IBAN $creditorIBAN  IBAN of the creditor
     * @param FinancialInstitutionInterface $creditorAgent BIC or IID of the creditor's financial institution
     * @param string $creditorReference Creditor Reference (SCOR / ISO 11649)
     */
    public function __construct(
        $instructionId,
        $endToEndId,
        Money\Money $amount,
        $creditorName,
        $creditorAddress,
        IBAN $creditorIBAN,
        FinancialInstitutionInterface $creditorAgent,
        $creditorReference
    ) {
        $cleanedCreditorReference = str_replace(' ', '', strtoupper($creditorReference));
        if (!self::isValidIso11649($cleanedCreditorReference)) {
            throw new InvalidArgumentException('The creditor reference (SCOR) must be a valid ISO 11649 reference (RF followed by valid mod-97 check digits).');
        }
        $this->creditorReference = $cleanedCreditorReference;

        if (preg_match('/^CH[0-9]{2}3/', $creditorIBAN->normalize())) {
            throw new InvalidArgumentException('The IBAN must not be a QR-IBAN');
        }

        parent::__construct($instructionId, $endToEndId, $amount, $creditorName, $creditorAddress, $creditorIBAN, $creditorAgent);
    }

    /**
     * Checks whether a reference is a valid ISO 11649 Creditor Reference (SCOR):
     * "RF" followed by two check digits and up to 21 alphanumeric characters, with a
     * valid mod-97 check digit (letters mapped A=10..Z=35, and "RF" plus the check
     * digits moved to the end).
     *
     * @param string $reference The reference, already stripped of spaces and upper-cased
     *
     * @return bool
     */
    private static function isValidIso11649($reference)
    {
        if (!preg_match('/^RF[0-9]{2}[A-Z0-9]{1,21}$/', $reference)) {
            return false;
        }

        $rearranged = substr($reference, 4).substr($reference, 0, 4);
        $numeric = '';
        $length = strlen($rearranged);
        for ($i = 0; $i < $length; $i++) {
            $char = $rearranged[$i];
            $numeric .= ctype_digit($char) ? $char : (string) (ord($char) - 55);
        }

        // Compute mod 97 piecewise to avoid overflowing on the large number.
        $remainder = 0;
        $length = strlen($numeric);
        for ($i = 0; $i < $length; $i++) {
            $remainder = ($remainder * 10 + (int) $numeric[$i]) % 97;
        }

        return $remainder === 1;
    }

    /**
     * @param DOMDocument $doc
     * @param DOMElement $transaction
     */
    protected function appendRemittanceInformation(DOMDocument $doc, DOMElement $transaction)
    {
        $remittanceInformation = $doc->createElement('RmtInf');

        $structured = $doc->createElement('Strd');
        $remittanceInformation->appendChild($structured);

        $creditorReferenceInformation = $doc->createElement('CdtrRefInf');
        $structured->appendChild($creditorReferenceInformation);

        $codeOrProperty = $doc->createElement('CdOrPrtry');
        $codeOrProperty->appendChild($doc->createElement('Cd', 'SCOR'));
        $type = $doc->createElement('Tp');
        $type->appendChild($codeOrProperty);

        $creditorReferenceInformation->appendChild($type);
        $creditorReferenceInformation->appendChild($doc->createElement('Ref', $this->creditorReference));

        if (!empty($this->remittanceInformation)) {
            $structured->appendChild($doc->createElement('AddtlRmtInf', $this->remittanceInformation));
        }

        $transaction->appendChild($remittanceInformation);
    }
}
