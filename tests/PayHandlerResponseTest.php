<?php declare(strict_types=1);
namespace AlghorithmicCash\Tests;

use PHPUnit\Framework\TestCase;
use AlgorithmicCash\PayHandlerResponse;
use AlgorithmicCash\PaymentResult;
use AlgorithmicCash\PaymentStatus;

class PayHandlerResponseTest extends TestCase {
    private $result;
    private $success;
    private $error;
    private $testResponse;

    public function setUp() : void {
        $this->result = PaymentResult::OK;
        $this->success = 1;
        $this->error = "";

        $this->testResponse = new PayHandlerResponse($this->result, $this->success, $this->error);

    }
    public function testObjectCreation() {
        $this->assertIsObject($this->testResponse);
    }

    public function testSetResult() {
        $this->assertEquals($this->testResponse, $this->testResponse->setResult($this->result));
    }

    public function testSetSuccess() {
        $this->assertEquals($this->testResponse, $this->testResponse->setSuccess($this->success));
    }

    public function testSetError() {
        $this->assertEquals($this->testResponse, $this->testResponse->setError($this->error));
    }
}
