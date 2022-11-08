<?php
namespace AlgorithmicCash;

class PayHandlerRequest {
    private $signHelper;
    private $data;
    private $dataSignature;

    public function __construct( string $privateKey, string $dataSignature, string $data) {
        $this->signHelper = new SignHelper($privateKey);
        $this->data = trim($data);
        $this->dataSignature = trim($dataSignature);

    }

    public function isValidRequest() : bool {
        if (empty($this->dataSignature) || empty($this->data)) {
            return false;
        }

        return true;
    }

    
}