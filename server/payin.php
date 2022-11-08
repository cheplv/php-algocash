<?php
require_once("../vendor/autoload.php");
require_once("../tests/config.php");

use AlgorithmicCash\PayInRequest;

$merchantTxId = "TEST-" . time();
$requestAmount = "100";
$baseUrl = "https://" . $_SERVER["HTTP_HOST"];
$customerEmail = "test@test.com";
$successUrl = $baseUrl . "/client-result.php?status=success";
$failureUrl = $baseUrl . "/client-result.php?status=failure";
$handlerUrl = $baseUrl . "/handler.php";
$iframeSrc = $baseUrl . "/empty.html";

//error_log(json_encode($_SERVER));

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $payInRequest = new PayInRequest($GLOBALS['acTestVars']['merchantId'], $GLOBALS['acTestVars']['privateKey'], $GLOBALS['acTestVars']['rpcUrl']);
    $payInRequest
        ->setMerchantTxId($_POST['merchant_tx_id'])
        ->setCustomerEmail($_POST['customer_email'])
        ->setAmount($_POST['request_amount'])
        ->setSuccessUrl($_POST['success_url'])
        ->setFailureUrl($_POST['failure_url'])
        ->setHandlerUrl($_POST['handler_url']);

    $payInResponse = $payInRequest->send();
    error_log($payInResponse->getResponse());



    //echo json_encode(['test' => 'OK']);
    $payInResponse->send();
    exit();
}


?>

<!DOCTYPE html>
<html>
    <head>
        <title>PayIn Tester</title>
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

            //processButton.disable();
            $.ajax({
                url: 'payin.php',
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
                    } else {
                        $('#payment_frame').attr('src', result.redirect_url);
                    }
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
                    <h3 class="text-center">Payin Variables</h3>
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
                        <label for="success_url" class="col-sm-4 col-form-label">Success URL</label>
                        <div class="col-sm-8">
                        <input type="text" class="form-control" name="success_url" id="success_url" value="<?php echo $successUrl; ?>">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="failure_url" class="col-sm-4 col-form-label">Failure URL</label>
                        <div class="col-sm-8">
                        <input type="text" class="form-control" name="failure_url" id="failure_url" value="<?php echo $failureUrl; ?>">
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
                            Process PayIn
                        </button>
                        </div>
                    </div>
                    </form>
                    <div id="processing_error" class="d-none alert alert-danger" role="alert"></div>
                </div>
                <div class="col-8 overflow-hidden">
                    <iframe id="payment_frame" src="<?php echo $iframeSrc; ?>" class="w-100 h-100"></iframe>
                </div>

            </div>
        </div>
        
    </body>
</html>