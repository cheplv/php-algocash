<?php declare(strict_types=1);
namespace AlghorithmicCash\Tests;

use PHPUnit\Framework\TestCase;
use AlgorithmicCash\PayInRequest;
use AlgorithmicCash\SignHelper;
use Web3\Utils;

class PayInTest extends TestCase {
    public function testPayInRequestInit() {
        $payInRequest = new PayInRequest(
            $GLOBALS['acTestVars']['merchantId'],
            $GLOBALS['acTestVars']['privateKey'],
            $GLOBALS['acTestVars']['rpcUrl']);

        $this->assertIsObject($payInRequest);
    }

    public function testPayInRequestSetVariables() {
        $payInRequest = new PayInRequest(
            $GLOBALS['acTestVars']['merchantId'],
            $GLOBALS['acTestVars']['privateKey'],
            $GLOBALS['acTestVars']['rpcUrl']);

        $merchantTxId = "ACT-" . time();
        $requestAmount = "100";
        $successUrl = "https://test.case/success.html";
        $failureUrl = "https://test.case/failure.html";
        $handlerUrl = "https://test.case/request-status.handler";
        $supportUrl = "https://support.test.case";

        $payInRequest
            ->setMerchantTxId($merchantTxId)
            ->setAmount($requestAmount)
            ->setSuccessUrl($successUrl)
            ->setFailureUrl($failureUrl)
            ->setHandlerUrl($handlerUrl)
            ->setSupportUrl($supportUrl);

        $request = $payInRequest->getRequestVars();
        $requestSignature = $payInRequest->getRequestSignature();
        var_dump($request, $requestSignature);

        $this->assertEquals($merchantTxId, $request['merchant_tx_id']);
        $this->assertEquals($requestAmount, $request['request_amount']);
        $this->assertEquals(Utils::toWei($requestAmount, 'ether')->toString(), $request['amount']);
        $this->assertEquals($successUrl, $request['success_url']);
        $this->assertEquals($failureUrl, $request['failure_url']);
        $this->assertEquals($handlerUrl, $request['ipn_url']);
        $this->assertEquals($supportUrl, $request['support_url']);
    }
}
