<?php
namespace AlgorithmicCash;

class PayHandlerRequest {
    private $signHelper;
    private $signPgKey;
    private $requestData;
    private $requestSignature;

    public function __construct( string $privateKey, string $signPgKey, string $requestSignature, string $requestData) {
        $this->signHelper = new SignHelper($privateKey);
        $this->signPgKey = $signPgKey;
        $this->requestSignature = trim($requestSignature);
        $this->requestData = trim($requestData);
        $this->request = @json_decode($this->requestData, true);
    }

    public function isValid() : bool {
        if (empty($this->requestSignature) || empty($this->requestData) || !is_array($this->request)) {
            return false;
        }

        $requiredParams = [
            'merchant_id',
            'merchant_tx_id',
            'tx_type',
            'timestamp',
            'status',
            'ipn_url',
        ];

        foreach($requiredParams as $paramName) {
            if (!isset($this->request[$paramName])) {
                return false;
            }
        }

        $merchantIdHash = hash('sha256',$this->request['merchant_id']);
        $requestHash = "algorithmic-".$this->signHelper->hashParams([
            $merchantIdHash,
            $this->request['ipn_url'],
            $this->request['merchant_tx_id'],
            $this->request['tx_type'],
            $this->request['timestamp']
        ]);

        $requestIsValid = $this->signHelper->verifySignature($requestHash, $this->requestSignature, $this->signPgKey);
        return $requestIsValid;
    }

    public function getParam($name) {
        if (!isset($this->request[$name])) {
            return null;
        }
        return $this->request[$name];
    }

    public function getParams() {
        return $this->request;
    }

    public function getParamsKeys() {
        return array_keys($this->request);
    }

    public function getMerchantId() {
        return $this->getParam('merchant_id');
    }

    public function getMerchantTxId() {
        return $this->getParam('merchant_tx_id');
    }

    public function getTxType() {
        return $this->getParam('tx_type');
    }

    public function getHandlerUrl() {
        return $this->getParam('ipn_url');
    }

    public function getReferenceNo() {
        return $this->getParam('reference_no');
    }

    public function getStatus() {
        return $this->getParam('status');
    }

    public function getTimestamp() {
        return $this->getParam('timestamp');
    }

    
}