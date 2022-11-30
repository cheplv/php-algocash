<?php declare(strict_types=1);
namespace AlghorithmicCash\Tests;

use PHPUnit\Framework\TestCase;
use AlgorithmicCash\PaymentStatus;

class PaymentStatusTest extends TestCase {

    public function testProcessingNotAvailable() {
        $this->assertEquals(-2, PaymentStatus::ProcessingNotAvailable);
    }

    public function testInvalidRequest() {
        $this->assertEquals(-1, PaymentStatus::InvalidRequest);
    }

    public function testPaymentPending() {
        $this->assertEquals(0, PaymentStatus::PaymentPending);
    }

    public function testPaymentSuccess() {
        $this->assertEquals(1, PaymentStatus::PaymentSuccess);
    }

    public function testPaymentSettled() {
        $this->assertEquals(2, PaymentStatus::PaymentSettled);
    }


}