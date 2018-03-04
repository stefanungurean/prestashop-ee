<div class="container">
    <form id="payment-form" method="post" action="{$action}">

        <div class="form-group">
            <label for="account-holder-firstname">Firstname :</label>
            <input type="text" name="account-holder-firstname" placeholder="Firstname" id="account-holder-firstname" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="account-holder-lastname">Lastname :</label>
            <input type="text" name="account-holder-lastname" placeholder="Lastname" id="account-holder-lastname" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="account">Account :</label>
            <input type="text" name="account" id="account" placeholder="DE42512308000000060004" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="bic">Bic :</label>
            <input type="text" name="bic" id="bic" placeholder="WIREDEMMXXX" class="form-control" required>
        </div>
        <input type="hidden" name="payment-type" id="payment-type" value="Sepa">

    </form>

</div>
