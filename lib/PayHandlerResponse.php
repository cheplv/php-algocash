<?php
namespace AlgorithmicCash;

class PayHandlerResponse {
    private $result;
    private $success;
    private $error;

    public function __construct($result, $success = 0, $error = "") {
        $this->result = $result;
        $this->success = $success;
        $this->error = $error;
    }

    public function setResult($result) {
        $this->result = $result;
        return $this;
    }

    public function setSuccess($success) {
        $this->success = $success;
        return $this;
    }

    public function setError($error) {
        $this->error = $error;
        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function send() {
        header('Content-Type: application/json');
        echo json_encode([
            'response' => $this->result,
            'success' => $this->success,
            'error' => $this->error,
        ]);
    }
}
