<?php
require_once(__DIR__ . "/../vendor/autoload.php");
require_once(__DIR__ . "/../config.tests.php");

use AlgorithmicCash\PayBalanceRequest;

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $payBalanceRequest = new PayBalanceRequest(getenv('ALGOCASH_MERCHANTID'), getenv('ALGOCASH_PRIVATEKEY'), getenv('ALGOCASH_RPCURL'));

    $payBalanceResponse = $payBalanceRequest->send();
    error_log('ResponseResult: ' . $payBalanceResponse->getResponse());
    $payBalanceResponse->send();
    exit();
}


?><!DOCTYPE html>
<html>
    <head>
        <title>PayBalance Tester</title>
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
                url: 'paybalance.php',
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
                    <h3 class="text-center">PayBalance Variables</h3>
                    <form id="action_form" onsubmit="return processSubmit(event);">
                    <div class="mb-3 row">
                        <div class="col-auto"><button type="button" class="btn btn-danger" onclick="location.reload();">Reset values</button></div>
                        <div class="col text-right">
                            
                        </div>
                        <div class="col-auto text-right">
                        <button id="process_button" type="submit" class="btn btn-primary">
                            <span class="d-none spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Process PayBalance
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