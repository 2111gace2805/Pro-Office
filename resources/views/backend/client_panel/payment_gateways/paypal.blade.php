<!--PayPal Pay Now Button-->
<script
    src="https://www.paypal.com/sdk/js?client-id={{ get_option('paypal_client_id') }}&currency={{ get_option('currency') }}&disable-funding=credit,card">
</script>

<div id="paypal-button-container"></div>

<script>
paypal.Buttons({
    createOrder: function(data, actions) {
        return actions.order.create({
            purchase_units: [{
                amount: {
                    value: '{{ ($invoice->grand_total - $invoice->paid) }}'
                }
            }]
        });
    },
    onApprove: function(data, actions) {
        window.location.href = "{{ url('client/paypal_payment_authorize') }}/" + data.orderID +
            "/{{ $invoice->id }}";
    },
    onCancel: function(data) {
        alert("{{ _lang('Payment Cancelled') }}");
    }
}).render('#paypal-button-container');
</script>