<?php
require_once(__DIR__ . "/vendor/autoload.php");

use Dotenv\Dotenv;
use AlgorithmicCash\Accounts;

$dotenv = Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->safeLoad();

if (getenv('ALGOCASH_MERCHANTID') === false) {
    putenv('ALGOCASH_MERCHANTID=0');
}

if (getenv('ALGOCASH_PRIVATEKEY') !== false) {
    $testAccount = Accounts::privateKeyToAccount(getenv('ALGOCASH_PRIVATEKEY'));
} else {
    $testAccount = Accounts::create();
    putenv('ALGOCASH_PRIVATEKEY='.$testAccount->privateKey);
}

if (getenv('ALGOCASH_PUBLICKEY') === false) {
    putenv('ALGOCASH_PUBLICKEY='.$testAccount->publicKey);
}

if (getenv('ALGOCASH_ADDRESS') === false) {
    putenv('ALGOCASH_ADDRESS='.$testAccount->address);
}

if (getenv('ALGOCASH_PGADDRESS') === false) {
    putenv('ALGOCASH_PGADDRESS=0xf743527f2e887903a94d090a2e743b015e7bf890');
}

if (getenv('ALGOCASH_RPCURL') === false) {
    putenv('ALGOCASH_RPCURL=https://cloudflare-eth.com');
}
