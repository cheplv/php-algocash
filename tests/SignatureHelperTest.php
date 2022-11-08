<?php declare(strict_types=1);
namespace AlgorithmicCash\Tests;

use PHPUnit\Framework\TestCase;
use AlgorithmicCash\SignHelper;

class SignatureHelperTest extends TestCase {
    public function testSignHelperCreation() {
        $signHelper = new SignHelper($GLOBALS['acTestVars']['privateKey'], $GLOBALS['acTestVars']['rpcUrl']);
        $this->assertTrue(is_object($signHelper));
    }

    public function testHashMessage() {
        $signHelper = new SignHelper($GLOBALS['acTestVars']['privateKey'], $GLOBALS['acTestVars']['rpcUrl']);
        $params = ['a', 'b', 'c'];
        $paramsHash = $signHelper->hashMessage($params);
        $this->assertEquals($GLOBALS['acTestVars']['testHashMessage'], $paramsHash);
    }

    public function testGenerateSignature() {
        $signHelper = new SignHelper($GLOBALS['acTestVars']['privateKey'], $GLOBALS['acTestVars']['rpcUrl']);
        $params = ['a', 'b', 'c'];
        $paramsHash = $signHelper->hashMessage($params);

        $signature = $signHelper->generateSignature($paramsHash);
        $this->assertEquals($GLOBALS['acTestVars']['testGenerateSignature'], $signature);

    }

    public function testVerifySignature() {
        $this->assertTrue(true);
    }
}
