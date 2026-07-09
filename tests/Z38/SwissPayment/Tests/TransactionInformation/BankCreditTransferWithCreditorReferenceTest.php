<?php

namespace Z38\SwissPayment\Tests\TransactionInformation;

use InvalidArgumentException;
use Z38\SwissPayment\BIC;
use Z38\SwissPayment\IBAN;
use Z38\SwissPayment\Money;
use Z38\SwissPayment\StructuredPostalAddress;
use Z38\SwissPayment\Tests\TestCase;
use Z38\SwissPayment\TransactionInformation\BankCreditTransferWithCreditorReference;

/**
 * @coversDefaultClass \Z38\SwissPayment\TransactionInformation\BankCreditTransferWithCreditorReference
 */
class BankCreditTransferWithCreditorReferenceTest extends TestCase
{
    private function build($creditorReference, $iban = 'CH31 8123 9000 0012 4568 9')
    {
        return new BankCreditTransferWithCreditorReference(
            'id000',
            'name',
            new Money\CHF(100),
            'name',
            new StructuredPostalAddress('foo', '99', '9999', 'bar'),
            new IBAN($iban),
            new BIC('PSETPD2SZZZ'),
            $creditorReference
        );
    }

    /**
     * @covers ::__construct
     */
    public function testValidCreditorReference()
    {
        $transaction = $this->build('RF18539007547034');
        $this->assertInstanceOf(BankCreditTransferWithCreditorReference::class, $transaction);
    }

    /**
     * @covers ::__construct
     */
    public function testValidCreditorReferenceWithSpaces()
    {
        $transaction = $this->build('RF18 5390 0754 7034');
        $this->assertInstanceOf(BankCreditTransferWithCreditorReference::class, $transaction);
    }

    /**
     * @covers ::__construct
     */
    public function testInvalidCheckDigit()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->build('RF19539007547034');
    }

    /**
     * @covers ::__construct
     */
    public function testMissingRfPrefix()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->build('539007547034');
    }

    /**
     * @covers ::__construct
     */
    public function testMustNotBeQrIban()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->build('RF18539007547034', 'CH44 3199 9123 0008 8901 2');
    }
}
