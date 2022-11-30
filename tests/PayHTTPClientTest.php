<?php declare(strict_types=1);
namespace AlghorithmicCash\Tests;

use PHPUnit\Framework\TestCase;
use AlgorithmicCash\PayHTTPClient;
use GuzzleHttp\Client;
use Psr\Http\Client\ClientInterface;

class PayHTTPClientTest extends TestCase {
    function setUp() : void {
        PayHTTPClient::setClient(null);
    }

    function testDefaultClient() {
        $client = PayHTTPClient::getClient();
        $this->assertTrue(is_object($client));
        $this->assertTrue($client instanceof Client);
    }

    function testSetDefaultTransportClient() {
        $client = new Client();
        PayHTTPClient::setClient($client);
        $this->assertEquals($client, PayHTTPClient::getClient());
    }


    function testIsPsr7Client() {
        $client = new Client();
        PayHTTPClient::setClient($client);
        $this->assertTrue(PayHTTPClient::getClient() instanceof ClientInterface);
    }
}
