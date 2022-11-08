<?php
namespace AlgorithmicCash;

use SWeb3\SWeb3;
use SWeb3\Accounts;
use SWeb3\Account;
use kornrunner\Secp256k1;
use kornrunner\Solidity;

class SignHelper {
    private $privateKey = '';
    private $account;
    public $rpcUrl = '';
    public $sweb3;
    private $secp256k1;

    public function __construct($privateKey, $rpcUrl = "") {
        $this->privateKey = $privateKey;
        $this->rpcUrl = $rpcUrl;
        $this->account = Accounts::privateKeyToAccount($privateKey);
        $this->sweb3 = new SWeb3($this->rpcUrl);
        $this->secp256k1 = new Secp256k1();
    }

    public function hashMessage($order) {
        return call_user_func_array('\kornrunner\Solidity::sha3', $order);
    }
    
    public function generateSignature($data) {
        return $this->account->sign($data)->signature;
    }
    
    public function verifySignature($message, $signature, $address) {
        return Accounts::verifySignatureWithAddress($message, $signature, $address);
    }

    public function wei2eth($wei) {
        return bcdiv($wei,'1000000000000000000',2);
    }
}
