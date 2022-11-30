<?php declare(strict_types=1);
namespace AlghorithmicCash\Tests;

use PHPUnit\Framework\TestCase;
use AlgorithmicCash\PayOutResponse;
use AlgorithmicCash\PaymentResult;

class PayOutResponseTest extends TestCase {
    private $testResponseData;
    private $testResponse;

    public function setUp() : void {
        $this->testResponseData = [
            'response' => PaymentResult::OK,
            'error' => '',
            'result' => 'test_result',
        ];

        $this->testResponse = new PayOutResponse(json_encode($this->testResponseData));
    }

    public function testResponseCreation() {
        $this->assertIsObject($this->testResponse);
    }

    public function testResponseValue() {
        $this->assertEquals(PaymentResult::OK, $this->testResponse->getResponse());
    }

    public function testResultValue() {
        $this->assertEquals($this->testResponseData['result'], $this->testResponse->getResult());
    }

    public function testErrorValue() {
        $this->assertEquals($this->testResponseData['error'], $this->testResponse->getError());
    }

    public function testParamValueExists() {
        $this->assertEquals(PaymentResult::OK, $this->testResponse->getParam('response'));
    }

    public function testParamValueNotExists() {
        $this->assertEmpty($this->testResponse->getParam('not_exists'));
    }

    public function testParams() {
        $this->assertIsArray($this->testResponse->getParams());
        $this->assertEquals(sizeof(array_keys($this->testResponseData)), sizeof(array_keys($this->testResponse->getParams())));
    }

    public function testParamsKeys() {
        $this->assertIsArray($this->testResponse->getParamsKeys());
    }
}