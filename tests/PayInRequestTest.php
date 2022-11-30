<?php declare(strict_types=1);
namespace AlghorithmicCash\Tests;

use PHPUnit\Framework\TestCase;
use AlgorithmicCash\PayHTTPClient;
use AlgorithmicCash\PayInRequest;
use AlgorithmicCash\PaymentResult;
use Web3\Utils;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;

class PayInRequestTest extends TestCase {
    private $testPayInRequest = null;

    function setUp() : void {
        PayHTTPClient::setClient(null);

        $this->testPayInRequest = new PayInRequest(
            getenv('ALGOCASH_MERCHANTID'),
            getenv('ALGOCASH_PRIVATEKEY'),
            getenv('ALGOCASH_RPCURL')
        );

        $merchantTxId = "ACT-" . time();
        $requestEmail = "test@test.com";
        $requestAmount = "500";
        $successUrl = "https://test.case/success.html";
        $failureUrl = "https://test.case/failure.html";
        $handlerUrl = "https://test.case/request-status.handler";
        $supportUrl = "https://support.test.case";

        $this->testPayInRequest
            ->setMerchantTxId($merchantTxId)
            ->setCustomerEmail($requestEmail)
            ->setAmount($requestAmount)
            ->setSuccessUrl($successUrl)
            ->setFailureUrl($failureUrl)
            ->setHandlerUrl($handlerUrl)
            ->setSupportUrl($supportUrl);
    }

    public function testPayInRequestInit() {
        $payInRequest = new PayInRequest(
            getenv('ALGOCASH_MERCHANTID'),
            getenv('ALGOCASH_PRIVATEKEY'),
            getenv('ALGOCASH_RPCURL'));

        $this->assertIsObject($payInRequest);
    }

    public function testPayInRequestSetVariables() {
        $payInRequest = new PayInRequest(
            getenv('ALGOCASH_MERCHANTID'),
            getenv('ALGOCASH_PRIVATEKEY'),
            getenv('ALGOCASH_RPCURL'));

        $merchantTxId = "ACT-" . time();
        $requestEmail = "test@test.com";
        $requestAmount = "500";
        $successUrl = "https://test.case/success.html";
        $failureUrl = "https://test.case/failure.html";
        $handlerUrl = "https://test.case/request-status.handler";
        $supportUrl = "https://support.test.case";

        $payInRequest
            ->setMerchantTxId($merchantTxId)
            ->setCustomerEmail($requestEmail)
            ->setAmount($requestAmount)
            ->setSuccessUrl($successUrl)
            ->setFailureUrl($failureUrl)
            ->setHandlerUrl($handlerUrl)
            ->setSupportUrl($supportUrl);

        $requestVars = $payInRequest->getRequestVars();
        $requestSignature = $payInRequest->getRequestSignature();

        $this->assertEquals($merchantTxId, $requestVars['merchant_tx_id']);
        $this->assertEquals(Utils::toWei($requestAmount, 'ether')->toString(), $requestVars['amount']);
        $this->assertEquals($requestAmount, $requestVars['request_amount']);
        $this->assertEquals($successUrl, $requestVars['success_url']);
        $this->assertEquals($failureUrl, $requestVars['failure_url']);
        $this->assertEquals($handlerUrl, $requestVars['ipn_url']);
        $this->assertEquals($supportUrl, $requestVars['support_url']);
    }

    public function testRequestWithException() {
        $exceptionMessage = 'TEST_EXCEPTION';
        $mock = new MockHandler([
            function($request) use ($exceptionMessage) {
                throw new RequestException($exceptionMessage, $request);
            }
        ]);
        $handlerStack = HandlerStack::create($mock);

        $client = new Client(['handler' => $handlerStack]);
        PayHTTPClient::setClient($client);

        $response = $this->testPayInRequest->send();

        $this->assertEquals(PaymentResult::FAIL, $response->getResponse());
        $this->assertEquals($exceptionMessage, $response->getError());
    }

    public function testRequestWithNon200Code() {
        $mock = new MockHandler([
            new Response(201, ['X-Test' => 'Unit'], 'TEST_RESPONSE'),
        ]);
        $handlerStack = HandlerStack::create($mock);

        $client = new Client(['handler' => $handlerStack]);
        PayHTTPClient::setClient($client);

        $response = $this->testPayInRequest->send();

        $this->assertEquals(PaymentResult::FAIL, $response->getResponse());
        $this->assertEquals('Status code invalid: 201', $response->getError());
    }

    public function testRequestWith200EmptyResponse() {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], ''),
        ]);
        $handlerStack = HandlerStack::create($mock);

        $client = new Client(['handler' => $handlerStack]);
        PayHTTPClient::setClient($client);

        $response = $this->testPayInRequest->send();

        $this->assertEquals(PaymentResult::FAIL, $response->getResponse());
        $this->assertEquals('Empty reponse received from server', $response->getError());
    }

    public function testRequestWith200CodeNotJson() {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], 'TEST_RESPONSE'),
        ]);
        $handlerStack = HandlerStack::create($mock);

        $client = new Client(['handler' => $handlerStack]);
        PayHTTPClient::setClient($client);

        $response = $this->testPayInRequest->send();

        $this->assertEquals(PaymentResult::FAIL, $response->getResponse());
        $this->assertEquals('Response not json: TEST_RESPONSE', $response->getError());
    }

    public function testRequestWith200Success() {
        $redirect_url = "https://test.com/payment.html?id=1";
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                "response" => PaymentResult::OK,
                "redirect_url" => $redirect_url,
            ])),
        ]);
        $handlerStack = HandlerStack::create($mock);

        $client = new Client(['handler' => $handlerStack]);
        PayHTTPClient::setClient($client);

        $response = $this->testPayInRequest->send();

        $this->assertEquals(PaymentResult::OK, $response->getResponse());
        $this->assertEmpty($response->getError());
        $this->assertEquals($redirect_url, $response->getRedirectUrl());
    }

}
