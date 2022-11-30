<?php
namespace AlgorithmicCash;

use AlgorithmicCash\PayOutResponse;
use AlgorithmicCash\SignHelper;
use AlgorithmicCash\PaymentUrl;
use Web3\Utils;

class PayOutRequest {
    private $privateKey = "";
    private $rpcUrl = "";
    private $signHelper;

    private $merchantId = "";
    private $customerEmail = "";

    private $request = [
        'customerEmailHash' => "",

        'amount' => "0",
        'request_amount' => "0",

        'merchant_tx_id'=> "",

        'beneficiary_name' => "",
        'beneficiary_account_no' => "",
        'beneficiary_ifsc_code' => "",

        'info_hash' => '',
        // Signature on request
        'signature' => '',

        'remark' => '',

        // Automatic generation
        'timestamp'=> "",

        //'support_url' => "",
        'ipn_url'=> "",
    ];

    public function __construct(string $merchantId, string $privateKey, string $rpcUrl = "") {
        $this->merchantId = $merchantId;
        $this->privateKey = $privateKey;
        $this->rpcUrl = $rpcUrl;
        $this->signHelper = new SignHelper($privateKey, $rpcUrl);
    }

    public function getRequestVars() : array {
        if (empty($this->request['timestamp'])) {
            $this->request['timestamp'] = "" . time();
        }
        $this->request['customerEmailHash'] = "0x".hash('sha256', $this->merchantId."::".$this->customerEmail);

        $this->request['info_hash'] = "0x".hash('sha256', implode('::', [
            $this->merchantId,
            $this->request['request_amount'],
            $this->request['customerEmailHash'],
            $this->request['remark'],
            $this->request['merchant_tx_id'],
            $this->request['beneficiary_name'],
            $this->request['beneficiary_account_no'],
            $this->request['beneficiary_ifsc_code'],
            $this->request['ipn_url'],
        ]));

        $paramsHash = $this->signHelper->hashParams([
            $this->request['customerEmailHash'],
            $this->request['amount'],
            $this->request['info_hash'],
            $this->request['remark'],
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
            $request['merchant_tx_id'],
            $request['beneficiary_name'],
            $request['beneficiary_account_no'],
            $request['beneficiary_ifsc_code'],
            $request['ipn_url'],
            $request['signature'],
            $request['timestamp'],
        ]);

        return $this->signHelper->generateSignature($requestHash);
    }

    public function send(): PayOutResponse {
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
                PaymentUrl::buildPayOutUrl([
                    'merchant_id' => $this->merchantId
                ]),
                [
                    'verify' => false,
                    'timeout' => 15,
                    'headers' => [
                        'x-signature' => $requestSignature,
                        'content-type' => 'application/json'
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

        return new PayOutResponse($result);
    }

    public function setMerchantTxId(string $txId) : PayOutRequest {
        $this->request['merchant_tx_id'] = $txId;
        return $this;
    }

    public function setCustomerEmail(string $email): PayOutRequest {
        $this->customerEmail = $email;
        return $this;
    }

    public function setAmount(string $amount) : PayOutRequest {
        $this->request['request_amount'] = (string) $amount;
        $this->request['amount'] = Utils::toWei($amount, 'ether')->toString();
        return $this;
    }

    public function setSupportUrl(string $url) : PayOutRequest {
        $this->request['support_url'] = $url;
        return $this;
    }

    public function setHandlerUrl(string $url) : PayOutRequest {
        $this->request['ipn_url'] = $url;
        return $this;
    }

    public function setRemark(string $remark) : PayOutRequest {
        $this->request['remark'] = $remark;
        return $this;
    }

    public function setBeneficiaryName(string $name) : PayOutRequest {
        $this->request['beneficiary_name'] = $name;
        return $this;
    }

    public function setBeneficiaryAccountNumber(string $accountNumber) : PayOutRequest {
        $this->request['beneficiary_account_no'] = $accountNumber;
        return $this;
    }

    public function setBeneficiaryIFSCCode(string $ifscCode) : PayOutRequest {
        $this->request['beneficiary_ifsc_code'] = $ifscCode;
        return $this;
    }
}
