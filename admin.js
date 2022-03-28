(function($) {

    var $payment_method = $('#_payment_method');
    var $fields = $('#gazchap_purchase_order_edit_fields');

    function show_fields() {
        $fields.show();
        $fields.find('input').prop('disabled', false);
    }

    function hide_fields() {
        $fields.hide();
        $fields.find('input').prop('disabled', true);
    }

    $( 'a.edit_address' ).on( 'click', function () {
        setTimeout( function () {
            $payment_method.trigger('change');
        }, 1 );
    });

    $payment_method.on( 'change', function () {
        var val = $( this ).val();
        if ( 'gazchap_wc_purchaseordergateway' === val ) {
            show_fields();
        } else {
            hide_fields();
        }
    } );

    hide_fields();
})(jQuery);
