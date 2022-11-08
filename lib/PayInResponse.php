<?php
namespace AlgorithmicCash;

class PayInResponse {
    public $responseData;
    public $response;

    public function __construct($responseData) {
        $this->responseData = $responseData;
        $this->response = json_decode($responseData, true);
    }

    public function getResponse() : string {
        return $this->response['response'];
    }

    public function getResult() {
        return $this->response['result'];
    }


    public function getParam($name) {
        if (!isset($this->response[$name])) {
            return null;
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
    
    public function getRedirectUrl() {
        return $this->getParam('redirect_url');
    }

    public function send() {
        header('Content-Type: application/json');
        echo json_encode($this->response);
    }
}