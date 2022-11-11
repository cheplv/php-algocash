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
}