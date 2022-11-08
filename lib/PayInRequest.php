<?php
namespace AlgorithmicCash;

use AlgorithmicCash\PayInResponse;
use AlgorithmicCash\SignHelper;

class PayInRequest {
    private $privateKey = "";
    private $rpcUrl = "";
    private $signHelper;

    private $merchantId = "";
    private $customerEmail = "";

    private $request = [
        'merchant_tx_id'=> "",
        'customerEmailHash' => "",

        'amount' => 0,
        'request_amount' => 0,

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

    public function setPayInUrl(string $url) : PayInRequest {
        $this->payInUrl = $url;
        return $this;
    }

    public function send(): PayInResponse {
        $this->request['timestamp'] = time();
        $this->request['customerEmailHash'] = "0x".hash('sha256', $this->merchantId."::".$this->customerEmail);

        $response = "";
        return new PayInResponse($response);
    }

    public function setMerchantTxId(string $txId) : PayInRequest {
        $this->request['merchant_tx_id'] = $txId;
        return $this;
    }

    public function setCustomerEmail(string $email): PayInRequest {
        $this->customerEmail = $email;
        return $this;
    }

    public function setAmount(int $amount) : PayInRequest {
        $this->request['amount'] = $amount;
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

    public function setCallbackUrl(string $url) : PayInRequest {
        $this->request['ipn_url'] = $url;
        return $this;
    }
}