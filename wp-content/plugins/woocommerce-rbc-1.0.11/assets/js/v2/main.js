/**
 * RBC Payplan v1.0.1
 *
 * @author Maritim, Kiprotich
 */

;(function ($, undefined) {

    $.fn.serializeObject = function () {
        "use strict";

        var result = {};
        var extend = function (i, element) {
            var node = result[element.name];

            // If node with same name exists already, need to convert it to an array as it
            // is a multi-value field (i.e., checkboxes)

            if ('undefined' !== typeof node && node !== null) {
                if ($.isArray(node)) {
                    node.push(element.value);
                } else {
                    result[element.name] = [node, element.value];
                }
            } else {
                result[element.name] = element.value;
            }
        };

        $.each(this.serializeArray(), extend);
        return result;
    };

    if (undefined === window.RBCPayPlan) {
        return false;
    }

    let rbc_sdk = window.RBCPayPlan;

    let rbcController = mwp.controller('woocommerce-gateway-rbcpayplan', {
        init: function () {
            switch (rbcController.local.page_type) {
                case 'category':
                    this.rbcCheckoutHandler = new CategoryHandler();
                    break;
                case 'product':
                    this.rbcCheckoutHandler = new ProductHandler();
                    break;
                case 'cart_summary':
                    this.rbcCheckoutHandler = new CartHandler();
                    break;
                case 'checkout':
                    this.rbcCheckoutHandler = new CheckoutHandler();
                    break;
                default:
                    this.rbcCheckoutHandler = new ProductHandler();
                    break;
            }
            ;
            rbcController.viewModel = this.rbcCheckoutHandler.getViewModel();
            this.rbcCheckoutHandler.init();
        }
    });

    var getConsoleFunc = function (level) {
        switch (level) {
            case 'fatal':
                return console.error;
            case 'error':
                return console.error;
            case 'warning':
                return console.warn;
            case 'info':
                return function(issue) {};
            case 'debug':
                return function(issue) {};
        }
    };

    let TRACKED_TAG_KEYS = [
        'plugin_version',
        'merchant_api_key',
        'tx_id'
    ];

    document.logRbcIssue = function (level, issueInfo, issue) {
        getConsoleFunc(level)(issue);
        var isSentryEnabled = rbcController.local.sentry_enabled;
        if (!isSentryEnabled) {
            return;
        }

        Sentry.withScope(
            function (scope) {
                scope.setExtra('issue_type', 'RbcIssue');
                scope.setLevel(level);

                Object.keys(issueInfo).forEach(
                    function (key) {
                        var value = JSON.stringify(issueInfo[key]);

                        if (TRACKED_TAG_KEYS.includes(key)) {
                            scope.setTag(key, value);
                        } else {
                            scope.setExtra(key, value);
                        }
                    }
                );

                if (typeof issue === 'string') {
                    Sentry.captureMessage(issue);
                } else {
                    Sentry.captureException(issue);
                }
            }
        );

    };
    
    function checkoutWithOpts() {
        
    };

    $.extend(ko.bindingHandlers, {
        /**
         * The `rbc` data binding attribute contains metadata and the immutable configuration/options for a button
         * instance.
         *
         *  {
         *      "productId": 99,
         *      "productType": "simple",
         *      "opts": {
         *          "buttonId": "rbc_checkout_button_99",
         *          "buttonLocation": "product"
         *      }
         *  }
         */
        rbc: {
            init: function (element, valueAccessor) {
                let el = $(element);
                let placeholder = el.html();

                element._reset = function () {
                    el.html(placeholder).removeAttr('data-loaded').css('visibility', 'visible');
                };

                $(document.body).trigger('rbc_button_bind', [element, valueAccessor]);
            }
        }
    });


    let CategoryHandler = function () {
        this.$buttons = {};
        this.configs = {};
        this.$button = $('div.rbc-checkout-button');
    };

    CategoryHandler.prototype.init = function () {
        let self = this;
        $(document.body).on('rbc_button_bind', function (e, element, valueAccessor) {
            rbcController.rbcCheckoutHandler.onButtonBind(e, element, valueAccessor);
        });

        $('div.rbc-checkout-button').each(function () {
            if (self.$buttons[this.id] === undefined) {
                self.$buttons[this.id] = $(this);
            }
        });
    };

    CategoryHandler.prototype.getViewModel = function () {
        return {};
    };

    CategoryHandler.prototype.onButtonBind = function (e, element, valueAccessor) {
        let config = ko.unwrap(valueAccessor());
        this.configs[config.opts.buttonId] = {config: config, loaded: false};
        // Avoid excessive ajax requests by fetching button options only after all buttons have been bound.
        if (Object.keys(this.configs).length === Object.keys(this.$buttons).length) {
            this.renderButtons();
        }
    };

    CategoryHandler.prototype.renderButtons = function () {
        let configs = [],
            self = this;

        /*
         * Ensure we only render the button once per item by setting a `loaded` property. This is needed
         * to support infinite-scrolling on category pages.
         */
        Object.keys(this.configs).forEach(function (key) {
            if (!self.configs[key].loaded) {
                configs[key] = self.configs[key].config;
                self.configs[key].config.loaded = true;
            }
        });

        let request = {
            action: 'rbc_get_options',
            source: rbcController.local.page_type,
            configs: Object.values(configs)
        };

        $.post(rbcController.local.ajaxurl, request)
            .done(function (response) {
                if (!response.success) {
                    var errorInfo = Object.assign(
                        request,
                        {response: response},
                    );
                    document.logRbcIssue('error', errorInfo, '(Category) Error in rbc_get_options response');
                    return;
                }

                let data = [];
                response.data.forEach(function (opts) {
                    if(opts.items.length > 0) {
                        let itemDetails = {
                            allowCheckout: false,
                            domID: opts.buttonId,
                            order: {
                                currency: opts.currency,
                                items: [
                                    {
                                        name: opts.items[0].name,
                                        quantity: opts.items[0].quantity,
                                        shippingCost: {value: 0, currency: opts.currency},
                                        shippingDescription: '',
                                        unitTax: {value: 0, currency: opts.currency},
                                        unitPrice: {
                                            currency: opts.currency,
                                            value: opts.items[0].price
                                        }
                                    }
                                ],
                                subTotal: { value: opts.items[0].price, currency: opts.currency },
                                totalPrice: { value: opts.items[0].price, currency: opts.currency },
                                totalDiscounts: { value: 0, currency: opts.currency },
                                totalShipping: { value: 0, currency: opts.currency },
                                totalTax: { value: 0, currency: opts.currency }
                            }
                        };
                        data.push(itemDetails);
                    } else {
                        //For variable products/composite/grouped the item count returned is 0
                        if(opts.customTotal > 0) {
                            var itemDetails = {
                                allowCheckout: false,
                                domID: opts.buttonId,
                                order: {
                                    currency: opts.currency,
                                    items: [],
                                    subTotal: {value: opts.customTotal, currency: opts.currency},
                                    totalPrice: {value: opts.customTotal, currency: opts.currency},
                                    totalDiscounts: {value: 0, currency: opts.currency},
                                    totalShipping: {value: 0, currency: opts.currency},
                                    totalTax: {value: 0, currency: opts.currency}
                                }
                            };

                            data.push(itemDetails);
                        }
                            
                    }

                });
                rbcController.rbcCheckoutHandler.prequalify(data);

            }).fail(function (xhr, status) {
            let errorInfo = Object.assign(
                request,
                {status: status, xhr: xhr.responseText},
            );
        });
    };

    CategoryHandler.prototype.prequalify = function (opts) {
        //Init RBC 2.0 SDK
        rbc_sdk.setup({
            integrationKey: rbcController.local.integration_key
        });

        rbc_sdk.on('INSTALLMENT:APPLICATION_DECISIONED', this.onApproved);
        rbc_sdk.on('INSTALLMENT:APPLICATION_CHECKOUT', this.onCheckout);

        rbc_sdk.registerPlacements(opts);
        rbc_sdk.setInitMode('manual');  
        rbc_sdk.init();
    };

    CategoryHandler.prototype.onApproved = function (application) {};
    CategoryHandler.prototype.onCheckout = function (application) {};

    //Product Handler
    let ProductHandler = function () {
        this.$form = $('form.cart');
        this.$button = $('div.rbc-checkout-button');
        this.config = {};   // placeholder for button config. populated on bind.
    };
    
    ProductHandler.prototype.getViewModel = function () {
        return {};
    };


    ProductHandler.prototype.init = function () {
        let self = this;
        $(document.body).on('rbc_button_bind', function (e, element, valueAccessor) {
            self.onButtonBind(e, element, valueAccessor);
        });
        
        $(document).ready(function () {
           self.$form.on('change', function (event) {
               self.onFormChange(event);
           });
        });
        
        $('#rbc-btn-cntnr').mouseover(function() {
            if (self.validateSelections()) $('.button-prevent').hide();
            else $('.button-prevent').show();
        });
        
        // Variable Products Only: Setup variable product event bindings.
        if ($('form.variations_form').length > 0) {
            self.setupBindingsVariable();
        }

        // Composite Products Only: Setup composite product event bindings.
        if ($('.composite_data').length > 0) {
            self.setupBindingsComposite();
        }
    };

    ProductHandler.prototype.onButtonBind = function (e, element, valueAccessor) {
        this.config = ko.unwrap(valueAccessor());
        this.toggleButton();
    };
    
    ProductHandler.prototype.setupBindingsVariable = function () {
        var self = this;
        this.$form.on('show_variation', function (variation) {
            self.variation = variation;
            self.toggleButton();
        });

        this.$form.on('reset_data', function () {
            delete self.variation;
            self.toggleButton();
        });
    };

    /**
     * Hook `component_selection_changed` action/event of a composite product and render the Bread
     * checkout button only when a valid configuration has been selected.
     */
    ProductHandler.prototype.setupBindingsComposite = function () {
        $(document).on('wc-composite-initializing', '.composite_data', function (event, composite) {
            rbcController.breadCheckoutHandler.composite = composite;

            composite.actions.add_action('component_selection_changed', function () {
                this.toggleButton();
            }, 100, rbcController.breadCheckoutHandler);
        });
    };
    
    ProductHandler.prototype.onFormChange = function (event) {
        let self = this;
        if (this.timeout) window.clearTimeout(this.timeout);

        this.timeout = window.setTimeout(function () {
            self.updateButton();
        }, 1000);

    };
    
    ProductHandler.prototype.validateSelections = function () {

        var self = this,
                validators = {
                    simple: function () {
                        return true;
                    },

                    grouped: function () {
                        return self.$form.find('input.qty').filter(function (index, element) {
                            return parseInt(element.value) > 0;
                        }).length > 0;
                    },

                    variable: function () {
                        return self.variation !== undefined;
                    },

                    composite: function () {
                        return (self.composite && self.composite.api.get_composite_validation_status() === 'pass');
                    }
                };

        if (!validators[rbcController.local.product_type]) {
            return false;
        }
        this.isValid = validators[rbcController.local.product_type]();

        return this.isValid;

    };
    
    ProductHandler.prototype.toggleButton = function () {
        
        if (!this.$button[0]) return;

        if (!this.validateSelections()) {
            return this.renderButtonForIncompleteProducts();
        }
        
        if (this.config.buttonType === 'composite' || this.config.buttonType === 'variable') {
            let iframe = this.$button.find('div > iframe');
            if (iframe.length > 0 && !iframe.parent().is(':visible')) {
                iframe.show();
            }
        }
        
        this.renderButton();
    };
    
    ProductHandler.prototype.updateButton = function () {
        if (this.$button[0]) {
            ko.cleanNode(this.$button[0]);
            ko.applyBindings(rbcController.viewModel, this.$button[0]);
        }
    };
    
    
    
    ProductHandler.prototype.renderButtonForIncompleteProducts = function () {
        let config = this.config;
        let self = rbcController.breadCheckoutHandler;

        $.post(rbcController.local.ajaxurl, {
            action: 'rbc_get_options',
            config: config,
            source: 'product'
        }).done(function (response) {
            if (response.success) {
                let opts = Object.assign(response.data, config.opts);
                let data = {
                    allowCheckout: opts.allowCheckout,
                    domID: opts.buttonId,
                    order: {
                        currency: opts.currency,
                        items: opts.items,
                        subTotal: {value: opts.customTotal, currency: opts.currency},
                        totalPrice: {value: opts.customTotal, currency: opts.currency},
                        totalDiscounts: {value: 0, currency: opts.currency},
                        totalShipping: {value: 0, currency: opts.currency},
                        totalTax: {value: 0, currency: opts.currency}
                    }
                };
                rbcController.rbcCheckoutHandler.prequalify(data);
            } else {
                rbcController.rbcCheckoutHandler.resetButton();
                if (typeof response === 'string')
                    return;
                let errorInfo = {response: response};
                document.logBreadIssue('error', errorInfo, '(PDP) Error in bread_get_options response');
            }
        }).fail(function (xhr, status) {
            self.resetButton();
        });

    };
    
    ProductHandler.prototype.renderButton = function () {
        let config = this.config,
            request = this.getPostData('rbc_get_options');

        $.post(rbcController.local.ajaxurl, request)
            .done(function (response) {
                if (response.success) {
                    let opts = Object.assign(response.data, config.opts);
                    let data = {
                        allowCheckout: opts.allowCheckout,
                        domID: opts.buttonId,
                        order: {
                            currency: opts.currency,
                            items: opts.items,
                            subTotal: { value: opts.customTotal, currency: opts.currency },
                            totalPrice: { value: opts.customTotal, currency: opts.currency },
                            totalDiscounts: { value: 0, currency: opts.currency },
                            totalShipping: { value: 0, currency: opts.currency },
                            totalTax: { value: 0, currency: opts.currency }
                        }
                    };
                    rbcController.rbcCheckoutHandler.prequalify(data);
                } else {
                    this.resetButton();
                    if (typeof response === 'string') return;
                    let errorInfo = Object.assign(
                        request,
                        {response: response},
                    );
                    document.logBreadIssue('error', errorInfo, '(PDP) Error in bread_get_options response');
                }
            }).fail(function (xhr, status) {

        });
    };
    
    ProductHandler.prototype.getPostData = function (breadAction, shippingContact, billingContact) {
        var data = this.$form.serializeObject();

        data['add-to-cart'] = this.$form[0]['add-to-cart'].value;
        data['action'] = breadAction;
        data['config'] = this.config;
        data['source'] = rbcController.local.page_type;

        if (shippingContact !== null) {
            data['shipping_contact'] = shippingContact;
        }

        if (billingContact !== null) {
            data['billing_contact'] = billingContact;
        }

        return data;
    };

    ProductHandler.prototype.resetButton = function () {
        if (this.$button.attr('data-loaded')) {
            this.$button[0]._reset();
        }
    };
    
    ProductHandler.prototype.prequalify = function (opts) {
        rbc_sdk.setup({
            integrationKey: rbcController.local.integration_key
        });

        rbc_sdk.on('INSTALLMENT:APPLICATION_DECISIONED', this.onApproved);
        rbc_sdk.on('INSTALLMENT:APPLICATION_CHECKOUT', this.onCheckout);
        rbc_sdk.registerPlacements([opts]);
        rbc_sdk.setInitMode('manual');
        rbc_sdk.init();
    };

    ProductHandler.prototype.onApproved = function (application) {};

    ProductHandler.prototype.onCheckout = function (application) {};

    //Cart page checkout
    let CartHandler = function () {
        this.$form = $('form.woocommerce-cart-form');
        this.$button = $('div.rbc-checkout-button');
    };

    CartHandler.prototype.init = function() {
        let self = this;

        $(document.body).on('rbc_button_bind', function (e, element, valueAccessor) {
            rbcController.rbcCheckoutHandler.onButtonBind(e, element, valueAccessor);
        });

        this.$form.on('change', function (event) {
            rbcController.rbcCheckoutHandler.onFormChange(event);
        });

        $(document.body).on('updated_wc_div', function (event) {
            rbcController.rbcCheckoutHandler.updateButton();
        });

        $(document.body).on('updated_shipping_method', function (event) {
            this.$button = $('div.rbc-checkout-button');
            rbcController.rbcCheckoutHandler.updateButton();
        });
    };

    CartHandler.prototype.getViewModel = function () {
        return {};
    };

    CartHandler.prototype.onButtonBind = function (e, element, valueAccessor) {
        this.config = ko.unwrap(valueAccessor());
        this.renderButton();
    };

    CartHandler.prototype.onFormChange = function (event) {

        if (this.timeout) window.clearTimeout(this.timeout);

        if ($(event.target).hasClass('qty')) {
            this.timeout = window.setTimeout(function () {
                rbcController.rbcCheckoutHandler.updateButton();
            }, 100);
        }

    };

    CartHandler.prototype.renderButton = function () {
        var self = rbcController.rbcCheckoutHandler,
            config = this.config,
            request = {
                action: 'rbc_get_options',
                source: 'cart_summary',
                config: config,
                form: this.$form.serializeArray()
            };
        $.post(rbcController.local.ajaxurl, request)
            .done(function (response) {
                if (response.success) {
                    let opts = Object.assign(response.data, config.opts);
                    let items = [];
                    response.data.items.forEach(function (item) {
                        let data = {
                            name: item.name,
                            quantity: item.quantity,
                            shippingCost: {
                                value: 0,
                                currency: opts.currency
                            },
                            shippingDescription: '',
                            unitTax: {
                                value: 0,
                                currency: opts.currency
                            },
                            unitPrice: {
                                currency: opts.currency,
                                value: item.price
                            }
                        };
                        items.push(data);
                    });

                    let data = {
                      allowCheckout: opts.allowCheckout,
                      domID: opts.buttonId,
                      order: {
                          currency: opts.currency,
                          items,
                          totalPrice: {
                              value: opts.customTotal,
                              currency: opts.currency
                          },
                          subTotal: {
                              value: opts.discounts === undefined ? opts.customTotal : (opts.customTotal + opts.discounts[0].amount),
                              currency: opts.currency
                          },
                          totalDiscounts: {
                              currency: opts.currency,
                              value: opts.discounts === undefined ? 0 : opts.discounts[0].amount
                          },
                          totalShipping: {
                              currency: opts.currency,
                              value: 0
                          },
                          totalTax: {
                              currency: opts.currency,
                              value: 0
                          }
                      }

                    };

                    rbcController.rbcCheckoutHandler.prequalify(data);

                } else {
                    self.resetButton();
                    let errorInfo = Object.assign(
                        request,
                        { response: response },
                    );
                    document.logRbcIssue('error', errorInfo, '(Cart) Error in rbc_get_options response');
                }
            })
            .fail(function (xhr, status) {
                self.resetButton();
                var errorInfo = Object.assign(
                    request,
                    { status: status, xhr: xhr.responseText },
                );
                document.logRbcIssue('error', errorInfo, '(Cart) Error in rbc_get_options call');
            });
    };

    CartHandler.prototype.prequalify = function (opts) {
        rbc_sdk.setup({
            integrationKey: rbcController.local.integration_key
        });

        rbc_sdk.on('INSTALLMENT:APPLICATION_DECISIONED', this.onApproved);
        rbc_sdk.on('INSTALLMENT:APPLICATION_CHECKOUT', this.onCheckout);

        rbc_sdk.registerPlacements([opts]);
        rbc_sdk.setInitMode('manual');  
        rbc_sdk.init();
    };

    CartHandler.prototype.updateButton = function () {
        if (this.$button[0]) {
            ko.cleanNode(this.$button[0]);
            ko.applyBindings(rbcController.viewModel, this.$button[0]);
        }
    };

    CartHandler.prototype.resetButton = function () {
        if (this.$button.attr('data-loaded')) {
            this.$button[0]._reset();
        }
    };

    CartHandler.prototype.onApproved = function (application) {};

    CartHandler.prototype.onCheckout = function (application) {};

    //Main Checkout page
    let CheckoutHandler = function () {
        this.$form = $('form.checkout, form#order_review');
    };

    CheckoutHandler.prototype.init = function () {
        var self = this,
            isOrderPayForm = $('form#order_review').length > 0;

        if (isOrderPayForm) {
            this.$form.on('submit', function() {
                if ($( '#payment_method_' + rbcController.local.gateway_token).is( ':checked' )) {
                    /*  If the hidden input `rbc_tx_token` exists, checkout has been completed and the form should be submitted */
                    var isCompletedRbcCheckout = self.$form.find('input[name="rbc_tx_token"]').length > 0;
                    if (isCompletedRbcCheckout) return true;

                    self.doRbcCheckoutForOrderPay();
                    return false;
                }
            });
        } else {
            this.$form.on('checkout_place_order_' + rbcController.local.gateway_token, function () {
                /*  If the hidden input `rbc_tx_token` exists, checkout has been completed and the form should be submitted */
                let isCompletedRbcCheckout = self.$form.find('input[name="rbc_tx_token"]').length > 0;
                if (isCompletedRbcCheckout) return true;

                self.doRbcCheckout();
                return false;
            });
        }

    };

    CheckoutHandler.prototype.getViewModel = function () {
        return {};
    };

    CheckoutHandler.prototype.doRbcCheckout = function () {

        let self = this,
            formIsValid = false,
            rbcdOpts = null,
            form = this.$form.serialize();
        self.addProcessingOverlay();    

        $.ajax({
                type: 'POST',
                url: wc_checkout_params.checkout_url + '&rbc_validate=true',
                data: form,
                dataType: 'json',
                async: false,
                success: function (result) {
                    if (result.result === 'success') {
                        formIsValid = true;
                        self.removeProcessingOverlay();
                    } else {
                        self.removeProcessingOverlay();
                        self.wc_submit_error(result.messages);
                        var errorInfo = {
                            form: form,
                            result: result
                        };
                        document.logRbcIssue('error', errorInfo, '(Checkout) Invalid checkout form');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    self.removeProcessingOverlay();
                    self.wc_submit_error('<div class="woocommerce-error">' + errorThrown + '</div>');
                    var errorInfo = {
                        form: form,
                        jqXHR: jqXHR.responseText,
                        textStatus: textStatus,
                        errorThrown: errorThrown
                    };
                    document.logRbcIssue('error', errorInfo, '(Checkout) Error in validate checkout form call');
                }
            }
        );

        if (formIsValid) {
            let data = {
                action: 'rbc_get_options',
                source: 'checkout'
            };

            self.$form.serializeArray().forEach(function (item) {
                data[item.name] = item.value;
            });

            $.ajax({
                type: 'POST',
                url: rbcController.local.ajaxurl,
                data: data,
                async: false,
                success: function (result) {
                    if (result.data.error) {
                        window.alert("Error completing checkout. " + result.data.error);
                        var errorInfo = {
                            data: data,
                            result: result
                        };
                        document.logRbcIssue('error', errorInfo, '(Checkout) Error in rbc_get_options result');
                    } else if (result.success) {
                        if(typeof(result.data.shippingCountry) !== 'undefined' && result.data.shippingCountry !== 'CA') {
                            window.alert('Error: Shipping address country must be CA');
                            var errorInfo = {
                                data: data,
                                result: result
                            };
                            document.logRbcIssue('error', errorInfo, '(Checkout) Error in rbc_get_options result');
                        } else {
                            rbcOpts = Object.assign(result.data);
                        }
                        
                    } else {
                        window.alert("Error completing checkout.");
                        var errorInfo = {
                            data: data,
                            result: result
                        };
                        document.logRbcIssue('error', errorInfo, '(Checkout) Error in rbc_get_options result');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    window.alert("Error completing checkout.");
                    var errorInfo = {
                        data: data,
                        jqXHR: jqXHR.responseText,
                        textStatus: textStatus,
                        errorThrown: errorThrown
                    };
                    document.logRbcIssue('error', errorInfo, '(Checkout) Error in rbc_get_options call');
                }
            });
        }
        if (rbcOpts !== null) {
            rbcController.rbcCheckoutHandler.checkoutWithOpts(rbcOpts);
        }
    };

    CheckoutHandler.prototype.checkoutWithOpts = function(opts) {
        //Init RBC SDK
        rbc_sdk.setup({
            integrationKey: rbcController.local.integration_key,
            buyer: {
                givenName: opts.billingContact.firstName,
                familyName: opts.billingContact.lastName,
                additionalName: "",
                birthDate: "",
                email: opts.billingContact.email,
                phone: opts.billingContact.phone,
                billingAddress: {
                    address1: opts.billingContact.address,
                    address2: opts.billingContact.address2 === null ? "" : opts.billingContact.address2,
                    country: opts.shippingCountry,
                    locality: opts.billingContact.city,
                    region: opts.billingContact.state,
                    postalCode: opts.billingContact.zip
                },
                shippingAddress: {
                    address1: opts.shippingContact.address,
                    address2: opts.shippingContact.address2 === null ? "" : opts.shippingContact.address2,
                    country: opts.shippingCountry,
                    locality: opts.shippingContact.city,
                    region: opts.shippingContact.state,
                    postalCode: opts.shippingContact.zip
                }
            }
        });

        rbc_sdk.on('INSTALLMENT:APPLICATION_DECISIONED', this.onApproved);
        rbc_sdk.on('INSTALLMENT:APPLICATION_CHECKOUT', this.onCheckout);

        let items = [];
        opts.items.forEach(function (item) {
            let data = {
                name: item.name,
                quantity: item.quantity,
                shippingCost: {
                    value: 0,
                    currency: opts.currency
                },
                shippingDescription: '',
                unitTax: {
                    value: 0,
                    currency: opts.currency
                },
                unitPrice: {
                    currency: opts.currency,
                    value: item.price
                }
            };
            items.push(data);
        });
        //Configure checkout options for sdk placements
        let data  = [{
            allowCheckout: opts.allowCheckout,
            domID: 'rbc_checkout_placeholder',
            order: {
                currency: opts.currency,
                items: items,
                totalPrice: {
                    value: opts.customTotal,
                    currency: opts.currency
                },
                subTotal: {
                    value: opts.subTotal,
                    currency: opts.currency
                },
                totalDiscounts: {
                    currency: opts.currency,
                    value: (opts.discounts === undefined) ? 0 : opts.discounts[0].amount
                },
                totalShipping: {
                    currency: opts.currency,
                    value: (opts.shippingOptions === undefined ) ? 0 : opts.shippingOptions[0].cost
                },
                totalTax: {
                    currency: opts.currency,
                    value: (opts.tax === undefined) ? 0 : opts.tax
                }
            }
        }];
        rbc_sdk.__internal__.setAutoRender(false);
        rbc_sdk.registerPlacements(data);
        rbc_sdk.setInitMode('manual');  
        rbc_sdk.init();
    };

    CheckoutHandler.prototype.onApproved = function (application) {};

    CheckoutHandler.prototype.onCheckout = function (application) {
        try {
            rbcController.rbcCheckoutHandler.addProcessingOverlay();
            $.post(rbcController.local.ajaxurl, {
                action: 'rbc_complete_checkout',
                tx_id: application.transactionID,
                form: rbcController.rbcCheckoutHandler.$form.serializeArray()
            }).done(function (response) {
                if (response.success && response.data.redirect) {
                    window.location.href = response.data.redirect;
                } else {
                    window.alert("Error completing checkout!");
                }
            }).fail(function (xhr, status) {
                window.alert("Error completing checkout!");
            });
        } catch(err) {
            window.alert("Error completing checkout!");
            var errorInfo = {   
                errorThrown: err
            };
            document.logRbcIssue('error', errorInfo, '(Checkout) Error in rbc_complete_checkout');
        }
        
    };

    CheckoutHandler.prototype.addProcessingOverlay = function() {
        /*
        * Borrowed from plugins/woocommerce/assets/js/frontend/checkout.js->submit()
        */
        this.$form.addClass('processing').block({
            message: null,
            overlayCSS: {
                background: '#aaa',
                opacity: 0.6
            }
        });
    };

    CheckoutHandler.prototype.removeProcessingOverlay = function() {
        this.$form.removeClass('processing').unblock();
    };

    CheckoutHandler.prototype.wc_submit_error = function (error_message) {
        $('.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message').remove();
        this.$form.prepend('<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout">' + error_message + '</div>');
        this.$form.removeClass('processing').unblock();
        this.$form.find('.input-text, select, input:checkbox').trigger('validate').blur();
        this.wc_scroll_to_notices();
        $(document.body).trigger('checkout_error');
    };

    CheckoutHandler.prototype.wc_scroll_to_notices = function () {
        var scrollElement = $('.woocommerce-NoticeGroup-updateOrderReview, .woocommerce-NoticeGroup-checkout'),
            isSmoothScrollSupported = 'scrollBehavior' in document.documentElement.style;

        if (!scrollElement.length) {
            scrollElement = $('.form.checkout');
        }

        if (scrollElement.length) {
            if (isSmoothScrollSupported) {
                scrollElement[0].scrollIntoView({
                    behavior: 'smooth'
                });
            } else {
                $('html, body').animate({
                    scrollTop: (scrollElement.offset().top - 100)
                }, 1000);
            }
        }
    };


})(jQuery);