<div id="flex-form" />

<script type="application/javascript" data-noptimize>
    (function ($) {
        "use strict";

        $( '#payment_method_splitit' ).change(function () {
            if ($(this).is( ':checked' )) {
                $( 'body' ).trigger( 'update_checkout' );
            }
        })

        localStorage.setItem( 'flex_fields_success', 'false' );
        localStorage.setItem( 'order_pay', 'false' );

        if ( window.isSplititPaymentFormInited ) {
            return; //do not init it couple times (for example, WooCommerce Smart COD plugin reinit form)
        }

        window.hideSplititLoader = function() {
            $( "#place_order" ).removeAttr( 'disabled' );
        }

        window.removeLoader = function () {
            setTimeout( () => $( '#order_review' ).unblock(), 1000 );
        };

        window.addSplititErrorMessage = function ( errorMessage ) {
            if ( !$( '#custom_splitit_error' ).length ) {
                $( '.payment_box.payment_method_splitit' ).prepend( '<p id="custom_splitit_error" style="color:red;">' + errorMessage + '</p>' );
            }
        };

        window.addWoocommerceErrorMessage = function ( errorMessage, form ) {
            cleanupWoocommerceErrorMessage();
            $( form ).prepend( '<ul class="woocommerce-error">' + errorMessage + '</ul>' );
        };

        window.cleanupWoocommerceErrorMessage = function () {
            $( '.woocommerce-error' ).remove();
        };

        window.scrollTopToBlock = function ( block ) {
            $( 'html, body' ).animate({
                scrollTop: ( $( block ).offset().top - 100)
            }, 1000);
        }

        window.getBillingAddressValue = function (code) {
            // In some cases city or state is optional, but it required in Splitit
            let value = $( '[name="' + code + '"]' ).val();
            if (!value) {
                value = $( 'input[name="billing_address_2"]' ).val();
            }
            if (!value) {
                value = $( 'input[name="billing_address_1"]' ).val();
            }
            if (!value) {
                value = $( '[name="billing_country"]' ).val();
            }
            if (!value) {
                value = $( 'input[name="billing_postcode"]' ).val();
            }
            return value;
        }

        window.setError = function (text) {
            var form = $( 'form.woocommerce-checkout' );
            var errMessage = "<li>"+text+"</li>";
            addWoocommerceErrorMessage( errMessage, form );
            scrollTopToBlock( 'form.woocommerce-checkout' );
            hideSplititLoader();
        }

        window.performPayment = function ( sender ) {
            if ( !isSplititPaymentSelected() ) {
                return;
            }
            $( sender ).attr( 'disabled', true );

            let d_3 = '<3ds>';

            if ( d_3 === '1' ) {
                setTimeout( function() { hideSplititLoader() }, 2000 );
            }

            flexFieldsInstance.updateDetails( {
                billingAddress: {
                    addressLine: $( 'input[name="billing_address_1"]' ).val(),
                    addressLine2: $( 'input[name="billing_address_2"]' ).val(),
                    city: getBillingAddressValue('billing_city'),
                    state: getBillingAddressValue('billing_state'),
                    country: $( '[name="billing_country"]' ).val(),
                    zip: getBillingAddressValue('billing_postcode')
                },
                consumerData: {
                    fullName: $( 'input[name="billing_first_name"]' ).val() + ' ' + $( 'input[name="billing_last_name"]' ).val(),
                    email: $( 'input[name="billing_email"]' ).val(),
                    phoneNumber: $( 'input[name="billing_phone"]' ).val(),
                    cultureName: "<culture>"
                }
            } );


            let result = {};
            $.each( $( 'form.checkout' ).serializeArray(), function () {
                result[this.name] = this.value;
            } );

            $.ajax( {
                url: getSplititAjaxURL('checkout_validate'),
                method: 'POST',
                dataType: 'json',
                async: false,
                data: {
                    action: 'checkout_validate',
                    fields: result,
                    ipn: localStorage.getItem( 'ipn' )
                },
                success: function ( data ) {
                    if ( data.result == 'success' ) {
                        cleanupWoocommerceErrorMessage();

                        $( sender ).attr( 'disabled', true );

                        //Check if flex fields has errors
                        if ( !flexFieldsInstance.isValid() ) {
                            flexFieldsInstance.triggerValidation()

                            hideSplititLoader();

                            return false;
                        } else {
                            if (window.location.href.includes("ride1up")) {
                                if (($( '[name="billing_state"]' ).length && !$( '[name="billing_state"]' ).val()) || $( '[name="billing_state"]' ).length && $( '[name="billing_state"]' ).val() == '') {
                                    setError('Billing State is a required field.');
                                    return;
                                }

                                if ($('input[name="ship_to_different_address"]').length && $('input[name="ship_to_different_address"]').is(':checked')) {
                                    if (($( '[name="shipping_state"]' ).length && !$( '[name="shipping_state"]' ).val()) || $( '[name="shipping_state"]' ).length && $( '[name="shipping_state"]' ).val() == '') {
                                        setError('Shipping State is a required field.');
                                        return;
                                    }
                                }
                            }

                            if ( typeof grecaptcha == 'undefined' || ( typeof grecaptcha != 'undefined' && grecaptcha.getResponse() != "" ) ) {
                                if ($('input[name="cf-turnstile-response"]').length) {
                                    if ($('input[name="cf-turnstile-response"]').val() != '') {
                                        flexFieldsInstance.pay();
                                    } else {
                                        setError('Verify you are human is a required field.');
                                    }
                                } else {
                                    flexFieldsInstance.pay();
                                }
                            } else {
                                setError('I`m not a robot is a required field.');
                            }
                        }
                    } else {
                        let $form = $('form.woocommerce-checkout');
                        let errorMessage = data.messages ? data.messages : data;

                        $form.find( '.input-text, select' ).blur();

                        addWoocommerceErrorMessage( errorMessage, $form );
                        scrollTopToBlock( 'form.woocommerce-checkout' );
                        hideSplititLoader();
                    }
                },
                error: function ( error ) {
                    scrollTopToBlock( 'form.woocommerce-checkout' );
                    hideSplititLoader();
                }
            } );
        }

        $( document ).ready( function () {
            if ( typeof flexFieldsInstance === 'undefined' ) {
                firstInitFlexFieldsInstance();
            } else {
                $( document ).trigger( 'update_checkout' );
            }
        } );

        $( 'body' ).on( 'updated_checkout' , function () {
            let ipn = localStorage.getItem( 'ipn' ) ?? false;
            if ( isSplititPaymentSelected() && flexFieldsInstance && ipn) {
                updateFlexFieldsTotal( ipn );
            }
        } );

        $( 'form[name="checkout"]' ).on( 'checkout_place_order' , function () {
            if ( isSplititPaymentSelected() ) {
                if ( localStorage.getItem( 'flex_fields_success' ) == 'true' ) {
                    removeLoader();
                    hideSplititLoader();
                    return true;
                }
                return false;
            }
        });

        $( 'form[name="checkout"]' ).on( 'change', 'input[name^="payment_method"]', function () {
            cleanupWoocommerceErrorMessage();
        } );

        //Order pay
        $( "form#order_review" ).submit( function ( e ) {
            if ( isSplititPaymentSelected() ) {
                if ( localStorage.getItem( 'order_pay' ) == 'false' ) {
                    e.preventDefault();
                    $( this ).remove( '#flex_field_hidden_checkout_field' );
                    $( this ).append( '<div id="flex_field_hidden_checkout_field"><input type="hidden" class="input-hidden" name="flex_field_ipn" id="flex_field_ipn" value=""> <input type="hidden" class="input-hidden" name="flex_field_num_of_inst" id="flex_field_num_of_inst" value=""> </div>' );

                    if ( !flexFieldsInstance.isValid() ) {
                        localStorage.setItem( 'order_pay', 'false' );

                        removeLoader();
                        hideSplititLoader()
                    } else {
                        localStorage.setItem( 'order_pay', 'true' );
                    }

                    let result = {};
                    $.each( $( this ).serializeArray(), function () {
                        result[ this.name ] = this.value;
                    } );

                    $.ajax( {
                        url: getSplititAjaxURL('order_pay_validate'),
                        method: 'POST',
                        dataType: 'json',
                        async: false,
                        data: {
                            action: 'order_pay_validate',
                            fields: result,
                            no_add_order_data_to_db: true
                        },
                        success: function ( data ) {
                            if ( data.result == 'success' ) {
                                cleanupWoocommerceErrorMessage();

                                if (localStorage.getItem( 'order_pay' ) == 'true' ) {
                                    flexFieldsInstance.pay();
                                } else {
                                    localStorage.setItem( 'order_pay', 'false' );
                                }
                            } else {
                                localStorage.setItem( 'order_pay', 'false' );

                                addWoocommerceErrorMessage(data.messages, $( 'form#order_review' ) );
                                scrollTopToBlock( 'form#order_review' );
                            }
                            hideSplititLoader();
                            removeLoader();
                        },
                        error: function ( error ) {
                            scrollTopToBlock( 'form#order_review' );
                            hideSplititLoader();
                            removeLoader();
                        }
                    } );
                }
            }
        } );

        $( document ).on( 'click', 'button#place_order', function () {
            // check is page is Pay for order
            flexFieldsInstance.triggerValidation();

            if(!(window.location.pathname.split('/')).includes('order-pay')) {
                //fix onclick if checkout button rewrited
                if ( 'performPayment(this)' !== $(this).attr('onclick') ) {
                    performPayment($(this));
                }
            }
        } );

        function firstInitFlexFieldsInstance() {
            $.ajax( {
                url: getSplititAjaxURL('flex_field_initiate_method'),
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'flex_field_initiate_method',
                    order_id: "<order_id>",
                    numberOfInstallments: '',
                    currency: getCurrencyCode()
                },
                success: function ( data ) {
                    if ( typeof data == 'undefined' || typeof data.installmentPlanNumber == 'undefined' ) {
                        if ( isSplititPaymentSelected() ) {
                            let form = $( 'form[name="checkout"]' ).length ? 'form[name="checkout"]' : '#order_review';
                            addWoocommerceErrorMessage( data.error.message, form) ;
                        }

                        addSplititErrorMessage( data.error.message );
                        removeLoader();
                    } else {
                        localStorage.setItem( 'ipn', data.installmentPlanNumber );
                        initFlexFieldsInstance( data )
                    }

                    //compatibility with some themes (empty fields in form)
                    setTimeout(function(){window.dispatchEvent(new Event('resize'));}, 100);
                    hideSplititLoader();
                },
                error: function ( error ) {
                    console.log(error.responseText);
                    removeLoader();
                    hideSplititLoader();
                }
            } );
        }

        function initFlexFieldsInstance( data ) {
            if(!data.shopper.fullName && $( 'input[name="billing_first_name"]' ).length && $( 'input[name="billing_last_name"]' ).length) {
                data.shopper.fullName = $( 'input[name="billing_first_name"]' ).val() + ' ' + $( 'input[name="billing_last_name"]' ).val();
            }

            flexFieldsInstance = window.Splitit.FlexForm.setup({
                showOnReady: true,
                nameField: {
                    hide: true
                },
                culture: "<culture>",
                ipn: data.installmentPlanNumber,
                container: "flex-form",
                numberOfInstallments: data.numberOfInstallments,
                billingAddress: data.billingAddress,
                consumerData: data.shopper,
                paymentButton: {
                    isCustom: true
                },
                onSuccess(data) {
                    //simulate error process for debug async flow
                    if (window.splititAsyncDebug === 'simulateError') {
                        console.log("planNumber: " + flexFieldsInstance.ipn);
                        return;
                    }

                    //add data to hidden input
                    if ( $( '#flex_field_hidden_checkout_field' ).length === 0 ) {
                        $( 'form[name="checkout"]' ).append( '<div id="flex_field_hidden_checkout_field"><input type="hidden" class="input-hidden" name="flex_field_ipn" id="flex_field_ipn" value=""> <input type="hidden" class="input-hidden" name="flex_field_num_of_inst" id="flex_field_num_of_inst" value=""> </div>' );
                    }

                    $( 'input[name="flex_field_ipn"]' ).val( flexFieldsInstance.ipn );
                    $( 'input[name="flex_field_num_of_inst"]' ).val( flexFieldsInstance.getSelectedNumInstallments() );

                    //Set item in local storage for inform about flex fields success
                    localStorage.setItem( 'flex_fields_success', 'true' );

                    $( 'form[name="checkout"]' ).submit(); //Submit checkout
                    $( "form#order_review" ).submit(); //Or submit pay order
                },
                onError( result ) {
                    console.log('onError:', result);

                    localStorage.setItem( 'flex_fields_success', 'false' );
                    removeLoader();
                    hideSplititLoader();
                },
                onEvent( ev ) {
                    if ( ev.component == "modal3ds" && ev.evType =="change" && ev.newValue == "closed" ) {
                        hideSplititLoader();
                    }
                }
            }).ready(
                function (manage) {
                    console.log('🚀 ~ READY CALLBACK', manage);
                }
            );
        }

        function updateFlexFieldsTotal( planNumber ) {
            if ( flexFieldsInstance !== undefined ) {
                $.ajax({
                    url: getSplititAjaxURL('flex_field_initiate_method'),
                    data: {
                        'action': 'flex_field_initiate_method',
                        'ipn': planNumber,
                        'order_id': "<order_id>",
                        'numberOfInstallments': '',
                        'function': 'updateFlexFieldsTotal',
                        'currency': getCurrencyCode()
                    },
                    method: "POST",
                    dataType: 'json',
                    success: function ( data ) {
                        cleanupWoocommerceErrorMessage();
                        $( '#custom_splitit_error' ).remove();
                        if ( typeof data.error != 'undefined' ) {
                            flexFieldsInstance.hide();
                            addSplititErrorMessage( data.error.message );
                        } else {
                            localStorage.setItem( 'ipn', data.installmentPlanNumber );
                            initFlexFieldsInstance( data )
                        }

                        hideSplititLoader();
                    },
                    error: function ( error ) {
                        console.log('~ updateFlexFieldsTotal error: ', error);
                        hideSplititLoader();
                    }
                });
            }
        }

        function getCurrencyCode() {
            //compatibility with WooCommerce Multilingual plugin
            if (typeof wcml_mc_settings != 'undefined') {
                return wcml_mc_settings.current_currency.code;
            }
            return '';
        }

        function isSplititPaymentSelected () {
            return $( '#payment_method_splitit' ).is( ':checked' );
        }

        //compatibility with some themes (empty fields in form)
        $(document.body).off("payment_method_selected");
        $(document.body).on("payment_method_selected", function() {
            $( 'body' ).trigger( 'update_checkout' );
            if (isSplititPaymentSelected()) {
                window.dispatchEvent(new Event('resize'));
            }
        });

        window.isSplititPaymentFormInited = true;
    }) ( jQuery );
</script>
