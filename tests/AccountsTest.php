<?php declare(strict_types=1);
namespace AlghorithmicCash\Tests;

use PHPUnit\Framework\TestCase;
use AlgorithmicCash\Accounts;
use AlgorithmicCash\SignHelper;

class AccountsTest extends TestCase {
    public function testAccountsCreate() {
        $account = Accounts::create();
        $this->assertIsObject($account);
        $this->assertIsString($account->privateKey);
        $this->assertIsString($account->publicKey);
        $this->assertIsString($account->address);
    }

    public function testAccountSignRaw() {
        $account = Accounts::create();
        $account->privateKey = '0xTESTEXCEPTION';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Private key must be length 64 + 2  (15 provided)');
        $account->signRaw('testhashsignature');
    }

    public function testAccountsPrivateKeyToAccount() {
        $account = Accounts::create();
        $account->privateKey = $account->privateKey;
        $resultAccount = Accounts::privateKeyToAccount($account->privateKey);
        $this->assertEquals($account->address, $resultAccount->address);
    }

    public function testAccountsPrivateKeyToAccountException() {
        $testPrivateKey = '0xTESTEXCEPTION1';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Private key must be 32 bytes long (7 provided)');
        Accounts::privateKeyToAccount($testPrivateKey);
    }

    public function testAccountsVerifySignatureWithPublicKey() {
        $account = Accounts::create();
        $signHelper = new SignHelper($account->privateKey);
        $message = "TEST_MESSAGE";
        $signature = $signHelper->generateSignature($message);
        $publicKey = Accounts::signedMessageToPublicKey($message, $signature);
        $publicAddress = Accounts::publicKeyToAddress($publicKey);

        $this->assertEquals($account->address, $publicAddress);
        $this->assertTrue(Accounts::verifySignatureWithPublicKey($message, $signature, $publicKey));
    }

    public function testHashMessage() {
        $message = "TEST_MESSAGE";
        $hash = Accounts::hashMessage($message);
        $this->assertEquals("f6bf13b7ad5a0ef5787a44122a3aaa01966876c37e58a33e7443f5267bc55ef6", $hash);
    }

    public function testHashMessageHex() {
        $message = "0x01";
        $hash = Accounts::hashMessage($message);
        $this->assertEquals("38fac6360805275eb58f90a0925a9c55477f73ceb68657f04a14ff9d16f9ea56", $hash);
    }

    public function testHashMessageHexInvalid() {
        $message = "0x01test";
        $hash = Accounts::hashMessage($message);
        // Invalid hex string produces empty hash
        $this->assertEquals('7763f62c3f41a2711e398650c4a50bf5b04d34a22bf6ec4876363c3354d18169', $hash);
    }
}
