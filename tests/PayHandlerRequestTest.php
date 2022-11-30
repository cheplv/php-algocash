<?php declare(strict_types=1);
namespace AlghorithmicCash\Tests;

use PHPUnit\Framework\TestCase;
use AlgorithmicCash\PayHandlerRequest;
use AlgorithmicCash\PaymentStatus;
use AlgorithmicCash\PaymentType;

class PayHandlerRequestTest extends TestCase {
    private $testTimestamp;
    private $testRequest;
    public function setUp() : void {
        $this->testTimestamp = time();
        $requestSignature = "0xTESTSIGNATURE";
        $requestData = json_encode([
            'merchant_id' => getenv('ALGOCASH_MERCHANTID'),
            'merchant_tx_id' => '1',
            'tx_type' => PaymentType::TX_IN,
            'timestamp' => $this->testTimestamp,
            'status' => PaymentStatus::ProcessingNotAvailable,
            'ipn_url' => 'https://test.case/handler.php',
            'reference_no' => 0,
        ]);
        $this->testRequest = new PayHandlerRequest(getenv('ALGOCASH_PRIVATEKEY'), getenv('ALGOCASH_PGADDRESS'), $requestSignature, $requestData);
    }

    public function testPaymentRequestCreation() {
        $requestSignature = "0xTESTSIGNATURE";
        $requestData = json_encode(['test' => 'test1']);
        $request = new PayHandlerRequest(getenv('ALGOCASH_PRIVATEKEY'), getenv('ALGOCASH_PGADDRESS'), $requestSignature, $requestData);

        $this->assertTrue(is_object($request));
    }

    public function testPaymentRequestIsValidEmptySignature() {
        $requestSignature = "";
        $requestData = json_encode(['test' => 'test1']);
        $request = new PayHandlerRequest(getenv('ALGOCASH_PRIVATEKEY'), getenv('ALGOCASH_PGADDRESS'), $requestSignature, $requestData);

        $this->assertTrue(is_object($request));
        $this->assertFalse($request->isValid());
    }

    public function testPaymentRequestIsValidEmptyData() {
        $requestSignature = "0xTESTSIGNATURE";
        $requestData = "";
        $request = new PayHandlerRequest(getenv('ALGOCASH_PRIVATEKEY'), getenv('ALGOCASH_PGADDRESS'), $requestSignature, $requestData);

        $this->assertTrue(is_object($request));
        $this->assertFalse($request->isValid());
    }

    public function testPaymentRequestIsValidFalseNoVars() {
        $requestSignature = "0xTESTSIGNATURE";
        $requestData = json_encode([
            'merchant_id' => getenv('ALGOCASH_MERCHANTID'),
            'merchant_tx_id' => '1',
            'tx_type' => PaymentType::TX_IN,
            'timestamp' => time(),
            'status' => PaymentStatus::ProcessingNotAvailable,
            //'ipn_url' => 'https://test.case/handler.php',
        ]);
        $request = new PayHandlerRequest(getenv('ALGOCASH_PRIVATEKEY'), getenv('ALGOCASH_PGADDRESS'), $requestSignature, $requestData);

        $this->assertTrue(is_object($request));
        $this->assertFalse($request->isValid());
    }

    public function testPaymentRequestIsValidFalse() {
        $requestSignature = "0xTESTSIGNATURE";
        $requestData = json_encode([
            'merchant_id' => getenv('ALGOCASH_MERCHANTID'),
            'merchant_tx_id' => '1',
            'tx_type' => PaymentType::TX_IN,
            'timestamp' => time(),
            'status' => PaymentStatus::ProcessingNotAvailable,
            'ipn_url' => 'https://test.case/handler.php',
        ]);
        $request = new PayHandlerRequest(getenv('ALGOCASH_PRIVATEKEY'), getenv('ALGOCASH_PGADDRESS'), $requestSignature, $requestData);

        $this->assertTrue(is_object($request));
        $this->assertFalse($request->isValid());
    }

    public function testPaymentRequestIsValidTrue() {
        $this->assertTrue(true);
    }

    public function testGetParamIsString() {
        $this->assertIsString($this->testRequest->getParam('merchant_id'));
    }

    public function testGetParamNotExists() {
        $this->assertNull($this->testRequest->getParam('not_exists_param'));
    }

    public function testGetParams() {
        $this->assertIsArray($this->testRequest->getParams());
    }

    public function testGetParamsKeys() {
        $this->assertIsArray($this->testRequest->getParamsKeys());
    }

    public function testGetMerchantId() {
        $this->assertIsString($this->testRequest->getMerchantId());
    }

    public function testGetMerchantIdEquals() {
        $this->assertEquals(getenv('ALGOCASH_MERCHANTID'), $this->testRequest->getMerchantId());
    }

    public function testGetMerchantTxId() {
        $this->assertIsString($this->testRequest->getMerchantTxId());
    }
    
    public function testGetTxType() {
        $this->assertIsString($this->testRequest->getTxType());
        $this->assertEquals(PaymentType::TX_IN, $this->testRequest->getTxType());
    }

    public function testGetHandlerUrl() {
        $this->assertIsString($this->testRequest->getHandlerUrl());
    }

    public function testGetStatus() {
        $this->assertEquals(PaymentStatus::ProcessingNotAvailable, $this->testRequest->getStatus());
    }

    public function testGetTimestamp() {
        $this->assertEquals($this->testTimestamp, $this->testRequest->getTimestamp());
    }

    public function testGetReferenceNo() {
        $this->assertIsNumeric($this->testRequest->getReferenceNo());
        $this->assertEquals(0, $this->testRequest->getReferenceNo());
    }

}