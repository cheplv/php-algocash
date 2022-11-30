<!DOCTYPE html>
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
        <div class="container h-100 pt-4">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="text-center">Algorithmic.Cash Utils</h3>
                </div>
            </div>
            <div class="row align-items-center pt-4">
                <div class="col text-center">
                    <a href="payin.php" class="w-25 btn btn-primary">PayIn Tester</a>
                </div>
            </div>
            <div class="row align-items-center pt-4">
                <div class="col text-center">
                    <a href="payout.php" class="w-25 btn btn-primary">PayOut Tester</a>
                </div>
            </div>
        </div>
        
    </body>
</html>