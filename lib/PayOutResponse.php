<?php
namespace AlgorithmicCash;

class PayOutResponse {
    public $responseData;
    public $response;

    public function __construct(string $responseData) {
        $this->responseData = $responseData;
        $this->response = @json_decode($responseData, true);
    }

    public function getResponse() : string {
        return $this->getParam('response');
    }

    public function getResult() {
        return $this->getParam('result');
    }

    public function getReferenceNo() {
        return $this->getParam('reference_no');
    }

    public function getParam($name) {
        if (!is_array($this->response) || !isset($this->response[$name])) {
            return "";
        }
        return $this->response[$name];
    }

    public function getParams() {
        return $this->response;
    }

    public function getParamsKeys() {
        return array_keys($this->response);
    }

    public function getError() {
        return $this->getParam('error');
    }

    /**
    * @codeCoverageIgnore
    */
    public function send() {
        header('Content-Type: application/json');
        echo json_encode($this->response);
    }
}
