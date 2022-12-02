<?php
namespace AlgorithmicCash;

use AlgorithmicCash\PayBalanceResponse;
use AlgorithmicCash\SignHelper;
use AlgorithmicCash\PaymentUrl;
use Web3\Utils;

class PayBalanceRequest {
    private $privateKey = "";
    private $rpcUrl = "";
    private $signHelper;

    private $merchantId = "";

    private $request = [
        // Automatic generation
        'timestamp'=> "",
    ];

    private $payInUrl = "";

    public function __construct(string $merchantId, string $privateKey, string $rpcUrl = "") {
        $this->merchantId = $merchantId;
        $this->privateKey = $privateKey;
        $this->rpcUrl = $rpcUrl;
        $this->signHelper = new SignHelper($privateKey, $rpcUrl);
    }

    public function getRequestVars() : array {
        if (empty($this->request['timestamp'])) {
            $this->request['timestamp'] = time();
        }

        return $this->request;
    }

    public function getRequestSignature() : string {
        $request = $this->getRequestVars();

        $requestHash = "algorithmic-" . $this->signHelper->hashParams([
            'Get_Balance',
            $request['timestamp'],
        ]);

        return $this->signHelper->generateSignature($requestHash);
    }

    public function send(): PayBalanceResponse {
        $request = $this->getRequestVars();
        $requestSignature = $this->getRequestSignature();
        $result = json_encode([
            'response' => PaymentResult::FAIL,
            'error' => 'Unknown error',
        ]);

        $client = PayHTTPClient::getClient();
        try {
            $response = $client->request(
                'POST',
                PaymentUrl::buildPayBalanceUrl([
                    'merchant_id' => $this->merchantId
                ]),
                [
                    'verify' => false,
                    'timeout' => 15,
                    'headers' => [
                        'x-signature' => $requestSignature,
                        'content-type' => 'application/json',
                    ],
                    'body' => json_encode($request)
                ]
            );

            if ($response->getStatusCode() != 200) {
                $result = json_encode([
                    'response' => PaymentResult::FAIL,
                    'error' => 'Status code invalid: ' . $response->getStatusCode(),
                ]);
            } else {
                $responseData = $response->getBody()->getContents();
                if (empty($responseData)) {
                    $result = json_encode([
                        'response' => PaymentResult::FAIL,
                        'error' => 'Empty reponse received from server',
                    ]);
                }
                else {
                    $responseJson = @json_decode($responseData);
                    if (is_null($responseJson)) {
                        $result = json_encode([
                            'response' => PaymentResult::FAIL,
                            'error' => 'Response not json: ' . $responseData,
                        ]);
                    } else {
                        $result = $responseData;
                    }
                }
            }
        } catch (\Exception $ex) {
            $result = json_encode([
                'response' => PaymentResult::FAIL,
                'error' => $ex->getMessage(),
            ]);
        }

        return new PayBalanceResponse($result);
    }

    public function setTimestamp(int $timestamp) : PayBalanceRequest {
        $this->request['timestamp'] = $timestamp;
        return $this;
    }
}
