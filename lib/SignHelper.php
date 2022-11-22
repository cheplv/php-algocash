<?php
namespace AlgorithmicCash;

use SWeb3\SWeb3;
// use kornrunner\Secp256k1;
use kornrunner\Solidity;
use AlgorithmicCash\Accounts;

class SignHelper {
    private $privateKey;
    private $account;
    public $rpcUrl;
    public $sweb3;
    // private $secp256k1;

    public function __construct(string $privateKey, string $rpcUrl = "") {
        $this->privateKey = $privateKey;
        $this->rpcUrl = $rpcUrl;
        $this->account = Accounts::privateKeyToAccount($this->privateKey);
        $this->sweb3 = new SWeb3($this->rpcUrl);
        // $this->secp256k1 = new Secp256k1();
    }

    public function hashParams(array $params) {
        return call_user_func_array('\kornrunner\Solidity::sha3', $params);
    }
    
    public function generateSignature(string $data) {
        return $this->account->sign($data)->signature;
    }
    
    public function verifySignature(string $message, string $signature, string $address) {
        return Accounts::verifySignatureWithAddress($message, $signature, $address);
    }

    public static function wei2eth($wei) {
        return bcdiv($wei,'1000000000000000000',2);
    }
}
