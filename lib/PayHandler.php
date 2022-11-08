<?php
namespace AlgorithmicCash;

class PayHandler {
    private $privateKey;
    private $pgKey;
    private $requestData;
    private $requestSignature;

    public function __construct(string $privateKey, string $pgKey, string $requestSignature = "", string $requestData = "") {
        $this->privateKey = $privateKey;
        $this->pgKey = $pgKey;
        $this->requestData = $requestData;
        $this->requestSignature = $requestSignature;

        if (empty($this->requestData)) {
            $this->requestData = file_get_contents('php://input');
        }

        if (!empty($this->requestSignature)) {
            return;
        }

        if (!empty($_SERVER['HTTP_X_SIGNATURE'])) {
            $this->requestSignature = $_SERVER['HTTP_X_SIGNATURE'];
        } else {
            $headers = getallheaders();
            foreach ($headers as $header => $value) {
                if (strtoupper($header) == "X-SIGNATURE") {
                    $this->requestSignature = trim($value);
                }
            }
        }

    }

    public function handleRequest() {
        return new PayHandlerRequest($this->privateKey, $this->pgKey, $this->requestSignature, $this->requestData);
    }
}