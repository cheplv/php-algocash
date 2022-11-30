<?php declare(strict_types=1);
namespace AlgorithmicCash\Tests;

use PHPUnit\Framework\TestCase;
use AlgorithmicCash\SignHelper;
use AlgorithmicCash\Accounts;

class SignHelperTest extends TestCase {

    private $testAccount;
    private $testAccountSignHelper;

    public function setUp() : void {
        $this->testAccount = Accounts::privateKeyToAccount(getenv('ALGOCASH_PRIVATEKEY'));
        $this->testAccountSignHelper = new SignHelper($this->testAccount->privateKey);

    }
    public function testSignHelperCreation() {
        $signHelper = new SignHelper(getenv('ALGOCASH_PRIVATEKEY'), getenv('ALGOCASH_RPCURL'));
        $this->assertTrue(is_object($signHelper));
    }

    public function testHashParams() {
        $signHelper = new SignHelper(getenv('ALGOCASH_PRIVATEKEY'), getenv('ALGOCASH_RPCURL'));
        $params = ['a', 'b', 'c'];
        $paramsHash = $signHelper->hashParams($params);
        $testAccountHash = $this->testAccountSignHelper->hashParams($params);
        $this->assertEquals($testAccountHash, $paramsHash);
    }

    public function testGenerateSignature() {
        $signHelper = new SignHelper(getenv('ALGOCASH_PRIVATEKEY'), getenv('ALGOCASH_RPCURL'));
        $params = ['a', 'b', 'c'];
        $paramsHash = $signHelper->hashParams($params);
        $signature = $signHelper->generateSignature($paramsHash);
        $testSignature = $this->testAccountSignHelper->generateSignature($paramsHash);
        $this->assertEquals($testSignature, $signature);

    }

    public function testVerifySignature() {
        $params = ['a', 'b', 'c'];
        $paramsHash = $this->testAccountSignHelper->hashParams($params);
        $testSignature = $this->testAccountSignHelper->generateSignature($paramsHash);
        $this->assertTrue($this->testAccountSignHelper->verifySignature($paramsHash, $testSignature, $this->testAccount->address));
    }

    public function testVerifySignaturePartialHex() {
        $message = "0x12b";
        $testSignature = $this->testAccountSignHelper->generateSignature($message);
        $this->assertTrue($this->testAccountSignHelper->verifySignature($message, $testSignature, $this->testAccount->address));
    }

    public function testVerifySignatureNonHex() {
        $message = "0x12z";
        $testSignature = $this->testAccountSignHelper->generateSignature($message);
        $this->assertTrue($this->testAccountSignHelper->verifySignature($message, $testSignature, $this->testAccount->address));
    }

    public function testWei2Eth() {
        $eth = "100.00";
        $wei = bcmul($eth, '1000000000000000000', 2);
        $this->assertEquals($eth, SignHelper::wei2eth($wei));
    }
}
