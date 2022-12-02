<?php declare(strict_types=1);
namespace AlghorithmicCash\Tests;

use PHPUnit\Framework\TestCase;
use AlgorithmicCash\PayHTTPClient;
use AlgorithmicCash\PayBalanceRequest;
use AlgorithmicCash\PaymentResult;
use Web3\Utils;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;

class PayBalanceRequestTest extends TestCase {
    private $testPayBalanceRequest = null;

    function setUp() : void {
        PayHTTPClient::setClient(null);

        $this->testPayBalanceRequest = new PayBalanceRequest(
            getenv('ALGOCASH_MERCHANTID'),
            getenv('ALGOCASH_PRIVATEKEY'),
            getenv('ALGOCASH_RPCURL')
        );

        $timestamp = time();

        $this->testPayBalanceRequest
            ->setTimestamp($timestamp);
    }

    public function testPayBalanceRequestInit() {
        $payBalanceRequest = new PayBalanceRequest(
            getenv('ALGOCASH_MERCHANTID'),
            getenv('ALGOCASH_PRIVATEKEY'),
            getenv('ALGOCASH_RPCURL'));

        $this->assertIsObject($payBalanceRequest);
    }

    public function testPayBalanceRequestSetVariables() {
        $payBalanceRequest = new PayBalanceRequest(
            getenv('ALGOCASH_MERCHANTID'),
            getenv('ALGOCASH_PRIVATEKEY'),
            getenv('ALGOCASH_RPCURL'));

        $timestamp = time();

        $payBalanceRequest
            ->setTimestamp($timestamp);

        $requestVars = $payBalanceRequest->getRequestVars();
        $requestSignature = $payBalanceRequest->getRequestSignature();

        $this->assertEquals($timestamp, $requestVars['timestamp']);
    }

    public function testPayBalanceRequestEmptyTimestamp() {
        $payBalanceRequest = new PayBalanceRequest(
            getenv('ALGOCASH_MERCHANTID'),
            getenv('ALGOCASH_PRIVATEKEY'),
            getenv('ALGOCASH_RPCURL'));

        $requestVars = $payBalanceRequest->getRequestVars();

        $this->assertNotEmpty($requestVars['timestamp']);
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

        $response = $this->testPayBalanceRequest->send();

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

        $response = $this->testPayBalanceRequest->send();

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

        $response = $this->testPayBalanceRequest->send();

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

        $response = $this->testPayBalanceRequest->send();

        $this->assertEquals(PaymentResult::FAIL, $response->getResponse());
        $this->assertEquals('Response not json: TEST_RESPONSE', $response->getError());
    }

    public function testRequestWith200Success() {
        $balance = 1000.00;
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                "response" => PaymentResult::OK,
                "balance" => $balance,
            ])),
        ]);
        $handlerStack = HandlerStack::create($mock);

        $client = new Client(['handler' => $handlerStack]);
        PayHTTPClient::setClient($client);

        $response = $this->testPayBalanceRequest->send();

        $this->assertEquals(PaymentResult::OK, $response->getResponse());
        $this->assertEmpty($response->getError());
        $this->assertEquals($balance, $response->getBalance());
    }

}
