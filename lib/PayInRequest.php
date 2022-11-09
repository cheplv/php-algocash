<?php
namespace AlgorithmicCash;

use AlgorithmicCash\PayInResponse;
use AlgorithmicCash\SignHelper;
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

        $paramsHash = $this->signHelper->hashMessage([
            $this->request['customerEmailHash'],
            $this->request['amount'],
            $this->request['merchant_tx_id'],
        ]);

        $paramsSignatureHash = $this->signHelper->hashMessage([$this->merchantId, $paramsHash]);
        $paramsSignature = $this->signHelper->generateSignature($paramsSignatureHash);
        
        $this->request['signature'] = $paramsSignature;

        return $this->request;
    }

    public function getRequestSignature() : string {
        $request = $this->getRequestVars();

        $requestHash = "algorithmic-" . $this->signHelper->hashMessage([
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

        $c = curl_init();

        curl_setopt_array($c, array(
            CURLOPT_URL => "https://api.algorithmic.cash".'/request_payin.php?merchant_id='.$this->merchantId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($request),
            CURLOPT_HTTPHEADER => array(
                'x-signature: '.$requestSignature,
                'content-type: application/json'
            ),
        ));

        $response = curl_exec($c);
        $responseInfo = curl_getinfo($c);
        curl_close($c);

        if (!$response) {
            $response = json_encode([
                'response' => PaymentResult::FAIL,
                'error' => 'Server timeout waiting for response'
            ]);
        } else {
            $responseJson = @json_decode($response);
            if (is_null($responseJson)) {
                $response = json_encode([
                    'response' => PaymentResult::FAIL,
                    'error' => 'Response not json: ' . $response,
                ]);
            }
        }

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
