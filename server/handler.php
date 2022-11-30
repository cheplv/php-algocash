<?php
require_once(__DIR__ . "/../vendor/autoload.php");
require_once(__DIR__ . "/../config.tests.php");

use AlgorithmicCash\PayHandler;
use AlgorithmicCash\PayHandlerResponse;
use AlgorithmicCash\PaymentType;
use AlgorithmicCash\PaymentStatus;
use AlgorithmicCash\PaymentResult;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Only POST method allowed. Current method: ' . $_SERVER['REQUEST_METHOD']);
}

$requestSignature = !empty($_SERVER['HTTP_X_SIGNATURE']) ? $_SERVER['HTTP_X_SIGNATURE'] : '';
$requestData = file_get_contents('php://input');

error_log('Handler: ' . json_encode([$_SERVER, $_GET, $_POST]));
error_log('HandlerData: ' . $requestData);

if (empty($requestSignature)) {
    exit('Request signature is empty. No request header "X-Signature"');
}

$handler = new PayHandler(getenv('ALGOCASH_PRIVATEKEY'), getenv('ALGOCASH_PGADDRESS'), $requestSignature, $requestData);

$request = $handler->handleRequest();

error_log('IsValidHandlerRequest: ' . $request->isValid());
if (!$request->isValid()) {
    die('Request is invalid or signature mismatch');
}

error_log('N Merchant: ' . $request->getMerchantId());
error_log('N TxId: ' . $request->getMerchantTxId());
error_log('N TxType: ' . $request->getTxType());
error_log('N Status: ' . $request->getStatus());
error_log('N Timestamp: ' . $request->getTimestamp());

$responseResult = PaymentResult::OK;
$responseSuccess = 1;
$responseError = "";

switch($request->getTxType()) {
    // Processing incoming transaction status
    case PaymentType::TX_IN:
        switch($request->getStatus()) {
            case PaymentStatus::ProcessingNotAvailable:
                // Update transaction to failure status
                error_log('PayIN Processing not available this time');
                break;
            case PaymentStatus::InvalidRequest:
                // Update transaction to invalid status
                error_log('PayIN API Sent invalid request');
                break;
            case PaymentStatus::PaymentPending:
                // Update transaction to pending status
                error_log('PayIN Payment is in progress');
                break;
            case PaymentStatus::PaymentSuccess:
                error_log('PayIN Payment processed succesfuly');

                // Success payment additional variables
                $dataReferenceNo = $request->getReferenceNo();
                $dataCustomerHash = $request->getParam('customer_hash');
                break;
            case PaymentStatus::PaymentSettled:
                error_log('PayIN Payment settled to blockchain succesfuly');

                // Success settlement variables
                $dataReferenceNo = $request->getReferenceNo();
                $dataCustomerHash = $request->getParam('customer_hash');
                $dataAmount = $request->getParam('amount');
                $dataBlockchainAmount = $request->getParam('blockchain_request_amount');
                $dataFee = $request->getParam('fee_amount');
                $dataBlockchainFee = $request->getParam('blockchain_fee_amount');
                $dataRollingReserveAmount = $request->getParam('rolling_reserve_amount');
                $dataRollingReserveReleaseDT = $request->getParam('rolling_reserve_release_dt');
                break;
            default:
                $responseResult = PaymentResult::FAIL;
                $responseSuccess = 0;
                $responseError = 'Unknown transaction status';
                break;
        }
        break;
    
    // Processing outgoing transaction
    case PaymentType::TX_OUT:
        switch($request->getStatus()) {
            case PaymentStatus::ProcessingNotAvailable:
                // Set transaction to status failure
                error_log('PayOUT Processing not available this time');
                break;
            case PaymentStatus::InvalidRequest:
                // Set transaction to status invalid
                error_log('PayOUT API Sent invalid request');
                break;
            case PaymentStatus::PaymentPending:
                // Update transaction status
                error_log('PayOUT Payment is in progress');
                break;
            case PaymentStatus::PaymentSuccess:
                error_log('PayOUT Payment processed succesfuly');
                // Success payout variables
                $dataReferenceNo = $request->getReferenceNo();
                break;
            case PaymentStatus::PaymentSettled:
                error_log('PayOUT Payment settled to blockchain succesfuly');
                // Success payout settlements variables
                $dataReferenceNo = $request->getReferenceNo();
                break;
            default:
                $responseResult = PaymentResult::FAIL;
                $responseSuccess = 0;
                $responseError = 'Unknown transaction status';
                break;
        }
        break;

    default:
        $responseResult = PaymentResult::FAIL;
        $responseSuccess = 0;
        $responseError = 'Unknown transaction type';
        break;
}

$response = new PayHandlerResponse($responseResult, $responseSuccess, $responseError);
$response->send();
