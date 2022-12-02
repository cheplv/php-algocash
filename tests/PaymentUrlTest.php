<?php declare(strict_types=1);
namespace AlghorithmicCash\Tests;

use PHPUnit\Framework\TestCase;
use AlgorithmicCash\PaymentUrl;

class PaymentUrlTest extends TestCase {
    public function testBuildPayInUrl() {
        $this->assertStringStartsWith(PaymentUrl::PAYIN_URL, PaymentUrl::buildPayInUrl(['test'=>'payin']));
    }

    public function testBuildPayOutUrl() {
        $this->assertStringStartsWith(PaymentUrl::PAYOUT_URL, PaymentUrl::buildPayOutUrl(['test'=>'payout']));
    }

    public function testBuildPayBalanceUrl() {
        $this->assertStringStartsWith(PaymentUrl::PAYBALANCE_URL, PaymentUrl::buildPayBalanceUrl(['test'=>'payout']));
    }
}
