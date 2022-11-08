<?php declare(strict_types=1);
namespace AlghorithmicCash\Tests;

use PHPUnit\Framework\TestCase;
use AlgorithmicCash\PaymentType;

class PaymentTypeTest extends TestCase {

    public function testIncomingTransaction() {
        $this->assertEquals("payin", PaymentType::TX_IN);
    }

    public function testOutgoingTransaction() {
        $this->assertEquals("payout", PaymentType::TX_OUT);
    }



}