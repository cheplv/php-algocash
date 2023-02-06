<?php
namespace AlgorithmicCash;

use AlgorithmicCash\PayInResponse;
use AlgorithmicCash\SignHelper;
use AlgorithmicCash\PaymentUrl;
use Web3\Utils;

class PayInRequest {
    private $privateKey = "";
    private $rpcUrl = "";
    private $signHelper;

    private $merchantId = "";
    private $customerEmail = "";

    private $request = [
        'merchant_tx_id'=> "",
        'customerEmailHash' => "",

        'amount' => "0",
        'request_amount' => "0",

        'traderAddress'=> "",
        // Automatic generation
        'timestamp'=> "",

        // to be removed
        'return_url' => "",

        'support_url' => "",
        'ipn_url'=> "",
        'success_url' => "",
        'failure_url' => "",

        // Signature on request
        'signature'=> "",
    ];

    private $payInUrl = "";

    public function __construct(string $merchantId, string $privateKey, string $rpcUrl = "") {
        $this->merchantId = $merchantId;
        $this->privateKey = $privateKey;
        $this->rpcUrl = $rpcUrl;
        $this->signHelper = new SignHelper($privateKey, $rpcUrl);
    }

    public function getPayInUrl() : string {
        return $this->payInUrl;
    }

    public function setPayInUrl(string $url) : PayInRequest {
        $this->payInUrl = $url;
        return $this;
    }

    public function getRequestVars() : array {
        if (!$this->request['timestamp']) {
            $this->request['timestamp'] = time();
        }
        $this->request['customerEmailHash'] = "0x".hash('sha256', $this->merchantId."::".$this->customerEmail);

        $paramsHash = $this->signHelper->hashParams([
            $this->request['customerEmailHash'],
            $this->request['amount'],
            $this->request['merchant_tx_id'],
        ]);

        $paramsSignatureHash = $this->signHelper->hashParams([$this->merchantId, $paramsHash]);
        $paramsSignature = $this->signHelper->generateSignature($paramsSignatureHash);
        
        $this->request['signature'] = $paramsSignature;

        return $this->request;
    }

    public function getRequestSignature() : string {
        $request = $this->getRequestVars();

        $requestHash = "algorithmic-" . $this->signHelper->hashParams([
            $request['customerEmailHash'],
            $request['amount'],
            $request['traderAddress'],
            $request['merchant_tx_id'],
            $request['success_url'],
            $request['failure_url'],
            $request['return_url'],
            $request['support_url'],
            $request['ipn_url'],
            $request['signature'],
            $request['timestamp'],
        ]);

        return $this->signHelper->generateSignature($requestHash);
    }

    public function send(): PayInResponse {
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
                PaymentUrl::buildPayInUrl([
                    'merchant_id' => $this->merchantId
                ], $this->payInUrl),
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

        return new PayInResponse($result);
    }

    public function setMerchantTxId(string $txId) : PayInRequest {
        $this->request['merchant_tx_id'] = $txId;
        return $this;
    }

    public function setCustomerEmail(string $email): PayInRequest {
        $this->customerEmail = $email;
        return $this;
    }

    public function setAmount(string $amount) : PayInRequest {
        $this->request['request_amount'] = (string) $amount;
        $this->request['amount'] = Utils::toWei($amount, 'ether')->toString();
        return $this;
    }

    public function setSupportUrl(string $url) : PayInRequest {
        $this->request['support_url'] = $url;
        return $this;
    }

    public function setSuccessUrl(string $url) : PayInRequest {
        $this->request['success_url'] = $url;
        return $this;
    }

    public function setFailureUrl(string $url) : PayInRequest {
        $this->request['failure_url'] = $url;
        return $this;
    }

    public function setHandlerUrl(string $url) : PayInRequest {
        $this->request['ipn_url'] = $url;
        return $this;
    }
}
