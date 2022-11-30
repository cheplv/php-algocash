<?php declare(strict_types=1);
namespace AlghorithmicCash\Tests;

use PHPUnit\Framework\TestCase;
use AlgorithmicCash\PayHTTPClient;
use AlgorithmicCash\PayOutRequest;
use AlgorithmicCash\PaymentResult;
use Web3\Utils;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;


class PayOutRequestTest extends TestCase {
    private $testPayOutRequest = null;

    function setUp() : void {
        PayHTTPClient::setClient(null);

        $this->testPayOutRequest = new PayOutRequest(
            getenv('ALGOCASH_MERCHANTID'),
            getenv('ALGOCASH_PRIVATEKEY'),
            getenv('ALGOCASH_RPCURL')
        );

        $merchantTxId = "ACT-" . time();
        $requestEmail = "test@test.com";
        $requestAmount = "500";
        $testRemark = "TEST REMARK";
        $beneficiaryName = "John Doe";
        $beneficiaryAccountNumber = "11223344";
        $beneficiaryIFSCCode = "TESTIFSCCODE";

        $handlerUrl = "https://test.case/request-status.handler";
        $supportUrl = "https://support.test.case";

        $this->testPayOutRequest
            ->setMerchantTxId($merchantTxId)
            ->setCustomerEmail($requestEmail)
            ->setAmount($requestAmount)
            ->setBeneficiaryName($beneficiaryName)
            ->setBeneficiaryAccountNumber($beneficiaryAccountNumber)
            ->setBeneficiaryIFSCCode($beneficiaryIFSCCode)
            ->setRemark($testRemark)
            ->setHandlerUrl($handlerUrl)
            ->setSupportUrl($supportUrl);
    }

    public function testPayOutRequestInit() {
        $request = new PayOutRequest(
            getenv('ALGOCASH_MERCHANTID'),
            getenv('ALGOCASH_PRIVATEKEY'),
            getenv('ALGOCASH_RPCURL'));

        $this->assertIsObject($request);
    }

    public function testPayOutRequestSetVariables() {
        $request = new PayOutRequest(
            getenv('ALGOCASH_MERCHANTID'),
            getenv('ALGOCASH_PRIVATEKEY'),
            getenv('ALGOCASH_RPCURL')
        );

        $merchantTxId = "ACT-" . time();
        $requestEmail = "test@test.com";
        $requestAmount = "500";
        $testRemark = "TEST REMARK";
        $beneficiaryName = "John Doe";
        $beneficiaryAccountNumber = "11223344";
        $beneficiaryIFSCCode = "TESTIFSCCODE";

        $handlerUrl = "https://test.case/request-status.handler";
        $supportUrl = "https://support.test.case";

        $request
            ->setMerchantTxId($merchantTxId)
            ->setCustomerEmail($requestEmail)
            ->setAmount($requestAmount)
            ->setBeneficiaryName($beneficiaryName)
            ->setBeneficiaryAccountNumber($beneficiaryAccountNumber)
            ->setBeneficiaryIFSCCode($beneficiaryIFSCCode)
            ->setRemark($testRemark)
            ->setHandlerUrl($handlerUrl)
            ->setSupportUrl($supportUrl);

        $requestVars = $request->getRequestVars();
        $requestSignature = $request->getRequestSignature();

        $this->assertEquals($merchantTxId, $requestVars['merchant_tx_id']);
        $this->assertEquals($requestAmount, $requestVars['request_amount']);
        $this->assertEquals(Utils::toWei($requestAmount, 'ether')->toString(), $requestVars['amount']);
        $this->assertEquals($testRemark, $requestVars['remark']);
        $this->assertEquals($beneficiaryName, $requestVars['beneficiary_name']);
        $this->assertEquals($beneficiaryAccountNumber, $requestVars['beneficiary_account_no']);
        $this->assertEquals($beneficiaryIFSCCode, $requestVars['beneficiary_ifsc_code']);
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

        $response = $this->testPayOutRequest->send();

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

        $response = $this->testPayOutRequest->send();

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

        $response = $this->testPayOutRequest->send();

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

        $response = $this->testPayOutRequest->send();

        $this->assertEquals(PaymentResult::FAIL, $response->getResponse());
        $this->assertEquals('Response not json: TEST_RESPONSE', $response->getError());
    }

    public function testRequestWith200Success() {
        $reference_number = time();
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                "response" => PaymentResult::OK,
                "reference_no" => $reference_number,
            ])),
        ]);
        $handlerStack = HandlerStack::create($mock);

        $client = new Client(['handler' => $handlerStack]);
        PayHTTPClient::setClient($client);

        $response = $this->testPayOutRequest->send();

        $this->assertEquals(PaymentResult::OK, $response->getResponse());
        $this->assertEmpty($response->getError());
        $this->assertEquals($reference_number, $response->getReferenceNo());
    }
}
