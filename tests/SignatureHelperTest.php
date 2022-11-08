<?php declare(strict_types=1);
namespace AlgorithmicCash\Tests;

use PHPUnit\Framework\TestCase;
use AlgorithmicCash\SignHelper;

class SignatureHelperTest extends TestCase {
    public function testSimple() {
        $this->assertTrue(true);
    }

    public function testObjectCreation() {
        $signHelper = new SignHelper($GLOBALS['acTestVars']['privateKey'], $GLOBALS['acTestVars']['rpcUrl']);
        $this->assertTrue(is_object($signHelper));
    }
}
