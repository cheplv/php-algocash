<?php
namespace AlgorithmicCash;

use AlgorithmicCash\Transport\IHTTPTransport;
use AlgorithmicCash\Transport\CurlHTTPTransport;

class PayTransport {
    private $transport = null;

    public static function getTransport() : IHTTPTransport {
        if (is_null(self::$transport)) {
            self::$transport = new CurlHTTPTransport();
        }
        return self::$transport;
    }

    public static function setTransport(IHTTPTransport $transport) {
        return self::$transport = $transport;
    }
}