jQuery(function ($) {

    // The connection/merchant status is rendered server-side from the result of
    // the last settings save. Nothing is polled from here — the old health check
    // fired a request to the Paymento API on every settings page view.

    var confirmation = document.getElementById("woocommerce_paymento_gateway_confirmation");
    if (!confirmation) {
        return;
    }

    var handle_description = function (data) {
        var desc_to_change = document.getElementById("woocommerce_paymento_gateway_confirmation_description");
        if (!desc_to_change) {
            return;
        }
        if (data == 0)
            desc_to_change.innerHTML = '<br />Users will be redirected to your site immediately after making the payment. The invoice status will be set to "On Hold" until the transaction is confirmed. ';
        else if (data == 1)
            desc_to_change.innerHTML = '<br />Users will remain on the Paymento page until the transaction is confirmed. They will be redirected to your site once the payment is verified. ';
        else if (data == 2)
            desc_to_change.innerHTML = '<br />Users will be redirected to your site immediately after making the payment. The invoice status will be marked as "Paid" once the transaction is broadcasted before confirmation.';
        else
            desc_to_change.innerHTML = '';
    };

    confirmation.parentElement.innerHTML += '<div id="woocommerce_paymento_gateway_confirmation_description" style="width: 50%;text-align: justify;"></div>';

    handle_description($('select#woocommerce_paymento_gateway_confirmation').val());

    $('select#woocommerce_paymento_gateway_confirmation').change(function () {
        handle_description($(this).val());
    });

});
