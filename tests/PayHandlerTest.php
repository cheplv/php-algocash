<?php declare(strict_types=1);
namespace AlghorithmicCash\Tests;

use PHPUnit\Framework\TestCase;
use AlgorithmicCash\PayHandler;

class PayHandlerTest extends TestCase {
    public function testPayHandlerCreation() {
        $requestSignature = "0xTESTSIGNATURE";
        $requestData = json_encode(['test' => 'test1']);
        $handler = new PayHandler(getenv('ALGOCASH_PRIVATEKEY'), getenv('ALGOCASH_PGADDRESS'), $requestSignature, $requestData);

        $this->assertTrue(is_object($handler));
    }

    public function testPayHandlerCreationWithoutData() {
        $requestSignature = "0xTESTSIGNATURE";
        $requestData = "";
        $handler = new PayHandler(getenv('ALGOCASH_PRIVATEKEY'), getenv('ALGOCASH_PGADDRESS'), $requestSignature, $requestData);

        $this->assertTrue(is_object($handler));
    }

    public function testPayHandlerCreationWithoutSignatureHeader() {
        $requestSignature = "";
        $requestData = "";
        $_SERVER['HTTP_X_SIGNATURE'] = "0xTESTSIGNATURE";
        $handler = new PayHandler(getenv('ALGOCASH_PRIVATEKEY'), getenv('ALGOCASH_PGADDRESS'), $requestSignature, $requestData);
        unset($_SERVER['HTTP_X_SIGNATURE']);

        $this->assertTrue(is_object($handler));
    }

    public function testPayHandlerCreationWithoutServerSignatureHeader() {
        $requestSignature = "";
        $requestData = json_encode(['test' => 'test1']);
        $handler = new PayHandler(getenv('ALGOCASH_PRIVATEKEY'), getenv('ALGOCASH_PGADDRESS'), $requestSignature, $requestData);

        $this->assertTrue(is_object($handler));
    }

    public function testPayHandlerHandleRequest() {
        $requestSignature = "0xTESTSIGNATURE";
        $requestData = json_encode(['test' => 'test1']);
        $handler = new PayHandler(getenv('ALGOCASH_PRIVATEKEY'), getenv('ALGOCASH_PGADDRESS'), $requestSignature, $requestData);
        $request = $handler->handleRequest();

        $this->assertTrue(is_object($request));
    }
}