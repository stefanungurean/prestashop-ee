<div class="container">
    <script src="https://code.jquery.com/jquery-3.1.1.min.js" type="application/javascript"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <?php
    // This library is needed to generate the UI and to get a valid token ID.
    ?>
    <script src="https://api-test.wirecard.com/engine/hpp/paymentPageLoader.js" type="text/javascript"></script>
    <style>
        #creditcard-form-div {
            height: 300px;
        }

        #overrides h1 {
            margin: 0px;
            font-size: 2vw;
        }

        #overrides h2 {
            padding-bottom: 10px;
            border-bottom: 1px solid #dedede;
        }

        #overrides h3 {
            min-height: 52.8px;
        }

        #overrides img {
            height: 40px;
            margin: 0px 20px;
        }

        #overrides .align-baseline {
            position: relative;
        }

        #overrides .bottom-align-text {
            position: absolute;
            bottom: -0.35vw;
            left: 235px;
        }

        #overrides .page-header {
            background-color: #002846;
            margin-top: 0px;
            padding: 40px 20px;
            color: white;
        }

        #overrides .list-group-item {
            border-radius: 0px;
            text-transform: uppercase;
            font-size: 12px;
        }

        #overrides .list-group-item:hover {
            color: #ff2014;
            background-color: #F7F7F8;
        }

        #overrides .btn-primary {
            background-color: #002846;
        }

        #overrides .btn-primary:hover {
            background-color: #414B56;
        }

    </style>
    <form id="payment-form-cc" onsubmit="return getTokenIdFromWirecard();" method="post" action="{$action}">

        <input type="hidden" name="tokenId" id="tokenId" value="">
        <input type="hidden" name="payment-type" id="payment-type" value="creditcard">

        <div id="creditcard-form-div"></div>
    </form>
</div>
<script type="application/javascript">

    // This function will render the credit card UI in the specified div.
    WirecardPaymentPage.seamlessRenderForm({

        // We fill the _requestData_ with the return value
        // from the `getDataForCreditCardUi` method of the `transactionService`.
        requestData: {$requestData|@json_encode nofilter},
        wrappingDivId: "creditcard-form-div",
        onSuccess: logCallback,
        onError: logCallback
    });

    function logCallback(response) {
        console.log(response);
    }


    function getTokenIdFromWirecard(event) {

        // We check if the field for the token ID already got a value.
        if ($('#tokenId').val() == '') {

            // If not, we will prevent the submission of the form and submit the form of credit card UI instead.

            WirecardPaymentPage.seamlessSubmitForm({
                onSuccess: setParentTransactionId,
                onError: logCallback
            })
            return false;
        }
        return true;

    }

    // If the submit to Wirecard is successful, `seamlessSubmitForm` will set the field for the token ID
    // and submit your form to your server.
    function setParentTransactionId(response) {
        console.log(response);
        $('#tokenId').val(response.token_id);
        $('#payment-form-cc').submit();
    }

</script>
