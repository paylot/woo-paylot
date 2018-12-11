jQuery( function( $ ) {

    var paylot_submit = false;

    

      //  wcPaylotEmbedFormHandler();

   

        jQuery( '#paylot-payment-button' ).click( function() {
            return wcPaylotFormHandler();
        });

        jQuery( '#paylot_form form#order_review' ).submit( function() {
            return wcPaylotFormHandler();
        });

    

    function wcPaylotCustomFields() {

        var custom_fields = [];

        if( wc_paylot_params.meta_order_id ) {

            custom_fields.push({
                display_name: "Order ID",
                variable_name: "order_id",
                value: wc_paylot_params.meta_order_id
            });

        }

        if( wc_paylot_params.meta_name ) {

            custom_fields.push({
                display_name: "Customer Name",
                variable_name: "customer_name",
                value: wc_paylot_params.meta_name
            });
        }

        if( wc_paylot_params.meta_email ) {

            custom_fields.push({
                display_name: "Customer Email",
                variable_name: "customer_email",
                value: wc_paylot_params.meta_email
            });
        }

        if( wc_paylot_params.meta_phone ) {

            custom_fields.push({
                display_name: "Customer Phone",
                variable_name: "customer_phone",
                value: wc_paylot_params.meta_phone
            });
        }


        if( wc_paylot_params.meta_products ) {

            custom_fields.push({
                display_name: "Products",
                variable_name: "products",
                value: wc_paylot_params.meta_products
            });
        }

        return custom_fields;
    }

    function wcPaylotCustomFilters() {

        var custom_filters = new Object();

        if( wc_paylot_params.banks_allowed ) {

            custom_filters['banks'] = wc_paylot_params.banks_allowed;

        }

        if( wc_paylot_params.cards_allowed ) {

            custom_filters['card_brands'] = wc_paylot_params.cards_allowed;
        }

        return custom_filters;
    }

    function wcPaylotFormHandler() {

        if ( paylot_submit ) {
            paylot_submit = false;
            return true;
        }

        var $form            = $( 'form#payment-form, form#order_review' ),
            paylot_txnref  = $form.find( 'input.paylot_txnref' ),
            bank             = "false",
            card             = "false";

        paylot_txnref.val( '' );

        

        var paylot_callback = function( response ) {
            $form.append( '<input type="hidden" class="paylot_txnref" name="paylot_txnref" value="' + response.reference + '"/>' );
            paylot_submit = true;

            $form.submit();

            $( 'body' ).block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                },
                css: {
                    cursor: "wait"
                }
            });
        };

        paylot({
            amount: wc_paylot_params.amount,
            key: wc_paylot_params.key,
            reference: wc_paylot_params.txnref,
            currency: wc_paylot_params.currency,
            payload: {
                email: wc_paylot_params.email,
                type: 'payment',
                subject: wc_paylot_params.subject || 'Payment'
            },
            onClose: function() {
                $( this.el ).unblock();
            }
        }, function(err, tx) {
            if(err){
                console.log('An error has occured');
            }else{
                console.log(tx);
                paylot_callback(tx);
            }
        });


        return false;

    }

} );