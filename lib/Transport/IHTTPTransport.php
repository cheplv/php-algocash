<?php
namespace AlgorithmicCash\Transport;

interface IHTTPTransport {
    public function request($url, $method = "GET", $params = []);
}