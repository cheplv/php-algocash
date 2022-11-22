<?php declare(strict_types=1);
namespace AlgorithmicCash\Tests;

use PHPUnit\Framework\TestCase;
use AlgorithmicCash\SignHelper;
use AlgorithmicCash\Accounts;

class SignHelperTest extends TestCase {

    private $testAccount;
    private $testAccountSignHelper;

    public function setUp() : void {
        $this->testAccount = $GLOBALS['acTestVars']['testAccount'];
        $this->testAccountSignHelper = new SignHelper($this->testAccount->privateKey);

    }
    public function testSignHelperCreation() {
        $signHelper = new SignHelper($GLOBALS['acTestVars']['privateKey'], $GLOBALS['acTestVars']['rpcUrl']);
        $this->assertTrue(is_object($signHelper));
    }

    public function testHashParams() {
        $signHelper = new SignHelper($GLOBALS['acTestVars']['privateKey'], $GLOBALS['acTestVars']['rpcUrl']);
        $params = ['a', 'b', 'c'];
        $paramsHash = $signHelper->hashParams($params);
        $testAccountHash = $this->testAccountSignHelper->hashParams($params);
        $this->assertEquals($testAccountHash, $paramsHash);
    }

    public function testGenerateSignature() {
        $signHelper = new SignHelper($GLOBALS['acTestVars']['privateKey'], $GLOBALS['acTestVars']['rpcUrl']);
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

    public function testWei2Eth() {
        $eth = "100.00";
        $wei = bcmul($eth, '1000000000000000000', 2);
        $this->assertEquals($eth, SignHelper::wei2eth($wei));
    }
}
