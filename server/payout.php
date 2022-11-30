<?php
require_once(__DIR__ . "/../vendor/autoload.php");
require_once(__DIR__ . "/../config.tests.php");

use AlgorithmicCash\PayOutRequest;

$merchantTxId = "TEST-" . time();
$requestAmount = "1000";
$baseUrl = "https://" . (!empty($_SERVER["HTTP_HOST"]) ? $_SERVER['HTTP_HOST'] : 'localhost');
$customerEmail = "test@test.com";
$beneficiaryName = "JOHN DOE";
$beneficiaryAccountNumber = "NR112233";
$beneficiaryIFSCCode = "IFSC112233";
$beneficiaryRemark = "TEST REMARK";
$handlerUrl = $baseUrl . "/handler.php";
$iframeSrc = $baseUrl . "/empty.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $payOutRequest = new PayOutRequest(getenv('ALGOCASH_MERCHANTID'), getenv('ALGOCASH_PRIVATEKEY'), getenv('ALGOCASH_RPCURL'));
    $payOutRequest
        ->setMerchantTxId($_POST['merchant_tx_id'])
        ->setCustomerEmail($_POST['customer_email'])
        ->setAmount($_POST['request_amount'])
        ->setBeneficiaryName($_POST['beneficiary_name'])
        ->setBeneficiaryAccountNumber($_POST['beneficiary_account_number'])
        ->setBeneficiaryIFSCCode($_POST['beneficiary_ifsc_code'])
        ->setRemark($_POST['beneficiary_remark'])
        ->setHandlerUrl($_POST['handler_url']);

    $payOutResponse = $payOutRequest->send();
    error_log('ResponseResult: ' . $payOutResponse->getResponse());
    $payOutResponse->send();
    exit();
}


?><!DOCTYPE html>
<html>
    <head>
        <title>PayOut Tester</title>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js" integrity="sha512-aVKKRRi/Q/YV+4mjoKBsE4x3H+BkegoM/em46NNlCqNTmUYADjBbeNefNxYV7giUp0VxICtqdrbqU7iVaeZNXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.2/js/bootstrap.min.js" integrity="sha512-5BqtYqlWfJemW5+v+TZUs22uigI8tXeVah5S/1Z6qBLVO7gakAOtkOzUtgq6dsIo5c0NJdmGPs0H9I+2OHUHVQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.2/css/bootstrap.min.css" integrity="sha512-CpIKUSyh9QX2+zSdfGP+eWLx23C8Dj9/XmHjZY2uDtfkdLGo0uY12jgcnkX9vXOgYajEKb/jiw67EYm+kBf+6g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
        }
        </style>
        <script type="text/javascript">

        
        function processSubmit(e) {
            console.log($("#action_form").serialize());
            e.preventDefault();
            let processButton = $('#process_button');
            console.log(processButton);
            processButton.attr('disabled','disabled');
            $('.spinner-border', processButton).removeClass('d-none');
            $('#processing_error').text('').addClass('d-none');
            $('#processing_result').text('Requesting ....');

            //processButton.disable();
            $.ajax({
                url: 'payout.php',
                type : "POST",
                data : $("#action_form").serialize(),
                success : function(result) {
                    console.log(result);
                    processButton.removeAttr('disabled');
                    $('.spinner-border', processButton).addClass('d-none');
                    /*
                    result = {
                        response: 'Ok',
                        redirect_url: 'https://app.algorithmic.cash/loading.html?id=81'
                    }
                    */

                    if (result.response != 'Ok') {
                        $('#processing_error').text(result.error).removeClass('d-none');
                    }
                    $('#processing_result').text(JSON.stringify(result, null, 2));
                },
                error: function(xhr, resp, text) {
                    console.log(xhr, resp, text);
                    processButton.removeAttr('disabled');
                    $('.spinner-border', processButton).addClass('d-none');
                    $('#processing_error').text(text).removeClass('d-none');
                }
            })
            return false;
        }
        </script>
    </head>
    <body>
        <div class="container-fluid h-100 pt-4">
            <div class="row h-100">
                <div class="col-4">
                    <div class="mb-3 row">
                        <div class="col">
                            <a href="index.php" class="btn btn-primary">Back</a>
                        </div>
                    </div>
                    <h3 class="text-center">Payout Variables</h3>
                    <form id="action_form" onsubmit="return processSubmit(event);">
                    <div class="mb-3 row">
                        <label for="merchant_tx_id" class="col-sm-4 col-form-label">Merchant Tx ID</label>
                        <div class="col-sm-8">
                        <input type="text" class="form-control" name="merchant_tx_id" id="merchant_tx_id" value="<?php echo $merchantTxId; ?>">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="customer_email" class="col-sm-4 col-form-label">Customer Email</label>
                        <div class="col-sm-8">
                        <input type="text" class="form-control" name="customer_email" id="customer_email" value="<?php echo $customerEmail; ?>">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="request_amount" class="col-sm-4 col-form-label">Deposit Amount</label>
                        <div class="col-sm-8">
                        <input type="text" class="form-control" name="request_amount" id="request_amount" value="<?php echo $requestAmount; ?>">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="beneficiary_name" class="col-sm-4 col-form-label">Beneficiary Name</label>
                        <div class="col-sm-8">
                        <input type="text" class="form-control" name="beneficiary_name" id="beneficiary_name" value="<?php echo $beneficiaryName; ?>">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="beneficiary_account_number" class="col-sm-4 col-form-label">Beneficiary AccNr</label>
                        <div class="col-sm-8">
                        <input type="text" class="form-control" name="beneficiary_account_number" id="beneficiary_account_number" value="<?php echo $beneficiaryAccountNumber; ?>">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="beneficiary_ifsc_code" class="col-sm-4 col-form-label">Beneficiary IFSC</label>
                        <div class="col-sm-8">
                        <input type="text" class="form-control" name="beneficiary_ifsc_code" id="beneficiary_ifsc_code" value="<?php echo $beneficiaryIFSCCode; ?>">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="beneficiary_remark" class="col-sm-4 col-form-label">Beneficiary Remark</label>
                        <div class="col-sm-8">
                        <input type="text" class="form-control" name="beneficiary_remark" id="beneficiary_remark" value="<?php echo $beneficiaryRemark; ?>">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="handler_url" class="col-sm-4 col-form-label">Handler URL</label>
                        <div class="col-sm-8">
                        <input type="text" class="form-control" name="handler_url" id="handler_url" value="<?php echo $handlerUrl; ?>">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <div class="col-auto"><button type="button" class="btn btn-danger" onclick="location.reload();">Reset values</button></div>
                        <div class="col text-right">
                            
                        </div>
                        <div class="col-auto text-right">
                        <button id="process_button" type="submit" class="btn btn-primary">
                            <span class="d-none spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Process PayOut
                        </button>
                        </div>
                    </div>
                    </form>
                </div>
                <div class="col-8 overflow-hidden">
                    <div id="processing_error" class="d-none alert alert-danger" role="alert"></div>
                    <div id="processing_result" class="w-100 h-100">Please send request</div>
                </div>

            </div>
        </div>
        
    </body>
</html>