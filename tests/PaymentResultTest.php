<?php declare(strict_types=1);
namespace AlghorithmicCash\Tests;

use PHPUnit\Framework\TestCase;
use AlgorithmicCash\PaymentResult;

class PaymentResultTest extends TestCase {

    public function testResultOk() {
        $this->assertEquals("Ok", PaymentResult::OK);
    }

    public function testResultFail() {
        $this->assertEquals("Fail", PaymentResult::FAIL);
    }



}