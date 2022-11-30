<?php
namespace AlgorithmicCash;

use GuzzleHttp\Client;

class PayHTTPClient {
    private static $client = null;

    public static function getClient($options = []) : Client {
        if (!is_null(self::$client)) {
            return self::$client;
        }

        return new Client($options);
    }

    public static function setClient($client) : bool {
        self::$client = $client;
        return true;
    }
}