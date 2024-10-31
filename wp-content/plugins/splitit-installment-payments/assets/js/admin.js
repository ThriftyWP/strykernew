(function ($) {
	'use strict';

	function initSplititSettings() {
		getPluginUrl();
		initInstalmentTable();
		initUpstreamMessagingSelection();
		$( '[name="save"]' ).removeAttr( 'disabled' );
	}

	function initInstalmentTable() {
		let firstInstallmentsSelect = $( '#woocommerce_splitit_ic_installment_0' );
		if (firstInstallmentsSelect.val() === null || firstInstallmentsSelect.val().length === 0) {
			let $options = firstInstallmentsSelect.find( 'option' );
			if ( $options.length > 0 ) {
				firstInstallmentsSelect.val( [ $options.first().val() ] );
			} else {
				firstInstallmentsSelect.val( function( index, currentValue ) {
					let values = currentValue ? currentValue.split( ',' ) : [];
					values.push( '2' );
					return values;
				} ).trigger( 'change' );
			}
		}

		$( 'html' )
			.on(
				'click',
				'#add_instalment',
				function (e) {
					e.preventDefault();
					let count   = $( '#ic_container div.ic_tr' ).length - 1;
					let prevNum = count - 1;

					let prevIcFromValue       = $( '#woocommerce_splitit_ic_from_' + prevNum ).val()
					let prevIcToValue         = $( '#woocommerce_splitit_ic_to_' + prevNum ).val()
					let prevInstallmentsValue = $( '#woocommerce_splitit_ic_installment_' + prevNum ).val()
					let prevInstallmentsCurrencySymbol = $( '#woocommerce_splitit_ic_from_' + prevNum + '_currency_symbol' ).text()
					let inst_from = $( '#woocommerce_splitit_ic_installment_' + prevNum ).find( "option:first-child" ).val()
					let inst_to   = $( '#woocommerce_splitit_ic_installment_' + prevNum ).find( "option:last-child" ).val()

					let inst_range_options = ''
					for (let i = +inst_from; i <= inst_to; i++) {
						inst_range_options += '<option value="' + i + '">' + i + '</option>\n'
					}

					if (prevIcFromValue == '' || prevIcToValue == '' || (prevInstallmentsValue && ! prevInstallmentsValue.length)) {
						$( '#installment-error-message' ).show( 200 )
						setTimeout(
							function () {
								$( '#installment-error-message' ).hide( 1000 )
							},
							2000
						)
					} else {
						let html = '<div style="display: flex" class="ic_tr mb-3" id="ic_tr_' + count + '">' +
									'<div class="forminp mr-3">\n' +
										'<fieldset>\n' +
											'<legend class="screen-reader-text"><span>from</span></legend>\n' +
											'<p class="description">Starting price*</p>\n' +
											'<div style="position: relative">\n' +
												'<span id="woocommerce_splitit_ic_from_' + count + '_currency_symbol" style="position: absolute; left: 11px; top: 14px; font-weight: 500; font-size: 16px;">' + prevInstallmentsCurrencySymbol + '</span>\n' +
												'<input class="input-text regular-input from" type="number" name="woocommerce_splitit_ic_from[]" id="woocommerce_splitit_ic_from_' + count + '" style="" value="" placeholder="">\n' +
											'</div>\n' +
										'</fieldset>\n' +
									'</div>\n' +
									'<div class="forminp mr-3">\n' +
										'<fieldset>\n' +
											'<legend class="screen-reader-text"><span>to</span></legend>\n' +
											'<p class="description">Ending price*</p>\n' +
											'<div style="position: relative">\n' +
												'<span id="woocommerce_splitit_ic_to_' + count + '_currency_symbol" style="position: absolute; left: 11px; top: 14px; font-weight: 500; font-size: 16px;">' + prevInstallmentsCurrencySymbol + '</span>\n' +
												'<input class="input-text regular-input to" type="number" name="woocommerce_splitit_ic_to[]" id="woocommerce_splitit_ic_to_' + count + '" style="" value="" placeholder="">\n' +
											'</div>\n' +
										'</fieldset>\n' +
									'</div>\n' +
									'<div class="forminp mr-3">\n' +
										'<fieldset>\n' +
											'<legend class="screen-reader-text"><span>installment</span></legend>\n' +
											'<p class="description">Installments*</p>\n' +
											'<select multiple class="is-select select installments" name="woocommerce_splitit_ic_installment[' + count + '][]" id="woocommerce_splitit_ic_installment_' + count + '" style="" value="">\n' +
												inst_range_options +
											'</select>\n' +
										'</fieldset>\n' +
									'</div>\n' +
									'<div class="titledesc ic_action">\n' +
										'<label for="woocommerce_splitit_ic_action"><span class="delete_instalment"><span class="trash-icon-new"></span></span></label>\n' +
									'</div>\n' +
								'</div>';

						$( '#ic_container div.ic_tr:last' )
							.after( html );

						let currentIcFromInput = $( '#woocommerce_splitit_ic_from_' + count )

						/* set previous To value to current row From */
						currentIcFromInput.val( +prevIcToValue + 1 )
						currentIcFromInput.attr( 'min', +prevIcToValue + 1 )

						$( '#woocommerce_splitit_ic_installment_' + count ).select2(
							{
								placeholder: "2, 3, 4, 6",
								closeOnSelect : false,
								allowHtml: true,
								tags: false,
								minimumResultsForSearch: -1
							}
						);
						$( ".select2-search input" ).prop( "readonly", true );
					}
				}
			);

		// Find and remove selected rows.
		$( 'html' )
			.on(
				'click',
				'.delete_instalment',
				function (e) {
					e.preventDefault();
					if ($( '#ic_container div.ic_tr' ).length <= 2) {
						$( '#installment-remove-error-message' ).show( 200 )
						setTimeout(
							function () {
								$( '#installment-remove-error-message' ).hide( 1000 )
							},
							2000
						)
					} else {
						$( this )
							.closest( 'div.ic_tr' )
							.remove();
					}
				}
			);

		$( 'html' )
			.on(
				'click',
				'#checkApiCredentials',
				function (e) {
					e.preventDefault();
					$( 'body' )
						.append( '<div class="loading">Loading&#8230;</div>' );
					$.ajax(
						{
							type: 'POST',
							url: ajaxurl_admin,
							data: {
								action: 'check_api_credentials'
							},
							success: function (response) {
								$( 'body' )
									.find( '.loading' )
									.remove();
								$( this ).closest( 'tr' )
									.find( 'td' )
									.append( '<div class="response">' + response + '</div>' );
								setTimeout(
									function () {
										$( this ).closest( 'tr' )
											.find( 'td' )
											.find( '.response' )
											.remove();
									},
									5000
								);
							},
							error: function (error) {
								$( 'body' )
									.find( '.loading' )
									.remove();
								$( this ).closest( 'tr' )
									.find( 'td' )
									.append( '<div class="error">' + error.statusText + '</div>' );
								setTimeout(
									function () {
										$( this ).closest( 'tr' )
											.find( 'td' )
											.find( '.error' )
											.remove();
									},
									5000
								);
							}
						}
					);

				}
			);

		$( 'html' )
			.on(
				'change',
				'#woocommerce_splitit_splitit_environment',
				function () {
					$.ajax(
						{
							type: 'GET',
							url: ajaxurl_admin,
							data: {
								action: 'splitit_merchant_logout',
							},
							success: function (response) {
								$( '[name="save"]' ).trigger( "click" );
							},
							error: function (error) {
								console.log( 'error:', error )
							}
						}
					);
				}
			);

		$( 'html' )
			.on(
				'click',
				'#merchant_logout',
				function (e) {
					window.onbeforeunload = function () {
						return null
					}
					window.location.href  = window.location.origin + '/logout?' + Date.now();
				}
			);

		$.validator.addMethod(
			'regex',
			function (value, element, regexp) {
				let re = new RegExp( regexp );
				return this.optional( element ) || re.test( value );
			},
			'Please check your input.'
		);

		$.validator.addMethod(
			'overlapping',
			function (value, element) {
				let from = [],
					to = [],
					r = true;

				// Gather all 'from' and 'to' values.
				$( '#main_ic_container div.ic_tr' ).each( function ( key ) {
					from[key] = parseFloat( $( this ).find( '.from' ).val() );
					to[key] = parseFloat( $( this ).find( '.to' ).val() );
				});

				// Validate the overlap rule.
				$.each( from, function ( index ) {
					let nextIndex = index + 1;

					// Clear previous errors.
					$( '#woocommerce_splitit_ic_from_' + nextIndex ).parent().find( 'span.error_class.overlapping' ).remove();
					$( '#woocommerce_splitit_ic_to_' + index ).parent().find( 'span.error_class.overlapping' ).remove();
					$( '.payment_method' ).find( '#error-box' ).find( 'span.error_class.overlapping' ).remove();

					// Ensure the current 'to' does not overlap with the next 'from'.
					if (nextIndex < from.length && to[index] >= from[nextIndex]) {
						r = false;

						// Display errors.
						$( '#woocommerce_splitit_ic_from_' + index ).parent().append( '<span id="woocommerce_splitit_ic_from_' + index + '-error-overlapping-custom" class="error_class overlapping">Starting and Ending prices can not overlapping</span>' );
						$( '#woocommerce_splitit_ic_to_' + ( index - 1 ) ).parent().append( '<span id="woocommerce_splitit_ic_to_' + ( index - 1 ) + '-error-overlapping-custom" class="error_class overlapping">Starting and Ending prices can not overlapping</span>' );
					}
				});

				if ( ! r ) {
					$( '#error-box' ).append( '<span id="' + element.id + '-error-overlapping-custom" class="error_class overlapping w-100">* Starting and Ending prices can not overlapping</span>' );
				}

				return r;
			},
			''
		);

		$.validator.addMethod(
			'to_greater_than_from',
			function (value, element) {
				const $row = $( element ).closest( '.ic_tr' );
				const fromValue = parseFloat( $row.find( '.from' ).val() );
				const toValue = parseFloat( value );

				$( '.payment_method' ).find( '#error-box' ).find( 'span.error_class.to_greater_than_from' ).remove();

				let result = ! isNaN( fromValue ) && ! isNaN( toValue ) && toValue > fromValue;

				if ( ! result ) {
					$( '#error-box' ).append( '<span id="' + element.id + '-error-gap-custom" class="error_class to_greater_than_from w-100">* Ending price must be greater than Starting price</span>' );
				}

				return result;
			},
			'Ending price must be greater than Starting price'
		);

		$.validator.addMethod(
			'gap',
			function (value, element) {
				let from = [],
					to   = [],
					r    = true

				$( '#main_ic_container div.ic_tr' )
					.each(
						function (key, value) {
							from[key] = parseFloat(
								$( this ).find( '.from' ).val()
							);
							to[key]   = parseFloat(
								$( this ).find( '.to' ).val()
							);
						}
					);

				/* remove first unnecessary elements */
				from.shift()
				to.shift()
				/*  */

				let result = true;
				$.each(
					from,
					function (index, value) {
						$( '#woocommerce_splitit_ic_from_' + index ).parent().find( 'span.error_class.gap' ).remove();
						$( '#woocommerce_splitit_ic_to_' + (index - 1) ).parent().find( 'span.error_class.gap' ).remove();
						$( '.payment_method' ).find( '#error-box' ).find( 'span.error_class.gap' ).remove();

						if (index <= (to.length - 1) && index <= (from.length - 1) && index > 0) {
							let prev_to_item = to[index - 1];

							if ((value - prev_to_item) > 1) {
								result = false;

								$( '#woocommerce_splitit_ic_from_' + index ).parent().append( '<span id="woocommerce_splitit_ic_from_' + index + '-error-gap-custom" class="error_class gap">There should be no gap between Ending and Starting prices</span>' );
								$( '#woocommerce_splitit_ic_to_' + (index - 1) ).parent().append( '<span id="woocommerce_splitit_ic_to_' + (index - 1) + '-error-gap-custom" class="error_class gap">There should be no gap between Ending and Starting prices</span>' );
								$( '#error-box' ).append( '<span id="' + element.id + '-error-gap-custom" class="error_class gap w-100">* There should be no gap between Ending and Starting prices</span>' );
							}

							if ( ! result) {
								r = false;
							}
						}
					}
				);
				return r;
			},
			''
		);

		$.validator.addMethod(
			'only_integer',
			function (value, element) {
				let r = true;

				$.each(
					value,
					function( k, v ) {
						if ( ! Math.floor( v ) == v || ! $.isNumeric( v ) || v <= 0) {
							r = false;
						}
					}
				);

				return r;
			},
			'No. of installments should contain only bigger than zero and integer values'
		);

		$.validator.addMethod(
			'required_installment',
			function (value, element) {
				$( '#' + element.id ).parent().find( 'span.error_class.installment' ).remove();
				$( '.payment_method' ).find( '#error-box' ).find( 'span.error_class.installment' ).remove();

				let r = true;
				if ( ! value.length) {
					r = false;
					$( '#' + element.id ).parent().append( '<span id="' + element.id + '-error-installment-custom" class="error_class installment">Installments can not be empty</span>' );
					$( '#error-box' ).append( '<span id="' + element.id + '-error-installment-custom" class="error_class installment w-100">* Installments can not be empty</span>' );
				}

				return r;
			},
			''
		);

		$.validator.addMethod(
			'required_custom_fields',
			function (value, element) {
				$( '#' + element.id ).parent().find( 'span.error_class' ).remove();
				$( '.new_um' ).find( '#um_main_error_box' ).find( '#' + element.id + '_error_custom' ).remove();
				$( '.new_um' ).find( '#um_page_error_box_' + $( element ).data( 'page' ) + '' ).find( 'span.error_class' ).remove();

				let r = true;
				if ( ! value.length) {
					r = false;
					$( '#' + element.id ).parent().append( '<span id="' + element.id + '_error_custom" class="error_class">This field is required</span>' );
					$( '.new_um' ).find( '#um_main_error_box' ).append( '<span id="' + element.id + '_error_custom" class="error_class w-100 mt-3">Check form fields</span>' );
					$( '.new_um' ).find( '#um_page_error_box_' + $( element ).data( 'page' ) + '' ).append( '<span id="' + element.id + '_error_custom" class="error_class w-100 mt-3">Check form fields</span>' );
				}

				return r;
			},
			''
		);

		$( 'html' )
			.on(
				'submit',
				'form#mainform',
				function (event) {
					return validateForm()
				}
			);

		$( 'html' )
			.on(
				'blur',
				'#main_ic_container input',
				function (event) {
					validateForm()
					if ( $( '#settings_page_loader' ).length ) {
						$( '#settings_page_loader' ).hide();
					}
				}
			);

		$( 'html' )
			.on(
				'click',
				'.connect_merchant_btn',
				function (e) {
					window.onbeforeunload = function () {
						return null
					}

					let env = $( this ).attr( 'data-env' )
					$( '#woocommerce_splitit_splitit_environment' ).val( env )
					localStorage.setItem( 'environment', env );
					document.getElementById('merchant_login').click();
				}
			);

		$( '#woocommerce_splitit_enabled' ).change(
			function() {
				if (this.checked) {
					$( '#main-section-enabled-desc' ).html( "<span class='description-green'>Enabled</span>" );
				} else {
					$( '#main-section-enabled-desc' ).html( "<span>Disabled</span>" )
				}
			}
		);

		$( '#upstream_messaging_settings_section :checkbox' ).change(
			function() {
				let el            = '#upstream_messages_desc_' + $( this ).attr( 'data-desc' )
				let select        = '#um_position_' + $( this ).attr( 'data-desc' ) + ' select'
				let linkPreview   = '#link_preview_' + $( this ).attr( 'data-desc' )
				let UmTypeEnabled = '#' + $( this ).attr( 'data-desc' ) + '_enabled'

				let umCheckboxes = $( '.um_checkboxes_' + $( this ).attr( 'data-page' ) )
				if (this.checked) {
					let self = this;

					// start disabled over types.
					umCheckboxes.each(
						function(index, element) {
							if ( $( element ).is( ':checked' ) && self != element ) {
								$( element ).trigger( 'click' );
							}
						}
					);
					// end disabled over types.

					$( UmTypeEnabled ).val( 1 );
					$( el ).html( "<span class='description-enabled'>Enabled " + $( this ).attr( 'data-type' ) + "</span>" );
					$( select ).prop( "disabled", false );
					$( linkPreview ).removeClass( "disabled-preview" );
				} else {
					$( UmTypeEnabled ).val( 0 );
					$( el ).html( "<span>Disabled " + $( this ).attr( 'data-type' ) + "</span>" )
					$( select ).prop( "disabled", true );
					$( linkPreview ).addClass( "disabled-preview" );
				}
			}
		);

		$( '.um-text-type' )
			.change(
				function () {
					let customElement = $( '#' + $( this )
						.data( 'page' ) + '_' + $( this )
						.data( 'type' ) + '_text' );
					if ($( this )
					.val() == 'custom') {
						customElement.addClass( 'show' )
						.removeClass( 'hide' );
					} else {
						let validator = $( 'form#mainform' ).validate();

						resetFields( [customElement.attr( 'name' )], validator )
						customElement.rules( 'remove', 'required_custom_fields' );
						customElement.addClass( 'hide' )
						.removeClass( 'show' )
						.val( '' );
					}
				}
			);

		function resetFields(fieldsNameList, validator){
			validator.invalid   = {}
			validator.submitted = {}
			validator.prepareForm()
			validator.hideErrors()
			validator.elements().filter(
				(i, el) => {
					return fieldsNameList.includes( $( el ).attr( 'name' ) )
				}
			).map(
				(i, el) => {
					$( el ).removeData( "previousValue" )
						.removeAttr( "aria-invalid" )
						.removeAttr( 'aria-describedby' )
						.removeClass( validator.settings.errorClass )
						.removeClass( validator.settings.validClass )
					}
			)
		}

		$( '#upstream_messaging_settings_section .preview-link' ).click(
			function () {
				let target        = $( this ).attr( 'data-target' )
				let targetModal   = $( '#preview_' + target )
				let umPosition    = $( '#um_position_' + target + ' select' ).val()
				let previewPageUm = targetModal.find( '#preview_page_um_' + target )
				previewPageUm.removeClass( 'left' )
				previewPageUm.removeClass( 'center' )
				previewPageUm.removeClass( 'right' )
				previewPageUm.addClass( umPosition )

				targetModal.show()
			}
		)

		$( '#upstream_messaging_settings_section .close' ).click(
			function () {
				let targetModal = $( '#' + $( this ).attr( 'data-target' ) )
				targetModal.hide()
			}
		)

		$( document ).click(
			(event) => {
				if ( ! $( event.target ).closest( '.preview-link' ).length && ! $( event.target ).closest( '.preview-modal' ).length) {
					$( '.preview-modal' ).hide()
				}
			}
		);

		function switchSettings(element, disableElement) {
			if (element.hasClass( 'on' )) {
				disableElement.html( "<span>Disabled</span>" )
				element.removeClass( 'on' )
				element.addClass( 'off' )
			} else {
				element.removeClass( 'off' )
				element.addClass( 'on' );
				disableElement.html( "<span class='description-green'>Enabled</span>" );
			}
		}

		$( 'html' )
			.on(
				'change',
				'#woocommerce_splitit_splitit_settings_3d',
				function () {
					switchSettings( $( '#woocommerce_splitit_splitit_settings_3d' ), $( '#splitit_settings_3d_desc' ) );
				}
			);

		$( 'html' )
			.on(
				'change',
				'#woocommerce_splitit_splitit_auto_capture',
				function () {
					switchSettings( $( '#woocommerce_splitit_splitit_auto_capture' ), $( '#splitit_auto_capture_desc' ) );
				}
			);

		$( 'html' )
			.on(
				'click',
				'#splitit_settings_3d_tooltip',
				function (e) {
					$( '#splitit_settings_3d_tooltiptext' ).show()
				}
			);

		$( 'html' )
			.on(
				'click',
				'#splitit_settings_3d_tooltip_close',
				function (e) {
					$( '#splitit_settings_3d_tooltiptext' ).hide();
				}
			);

		$( 'html' )
			.on(
				'click',
				'#splitit_auto_capture_tooltip',
				function (e) {
					$( '#splitit_auto_capture_tooltiptext' ).show()
				}
			);

		$( 'html' )
			.on(
				'click',
				'#splitit_auto_capture_tooltip_close',
				function (e) {
					$( '#splitit_auto_capture_tooltiptext' ).hide();
				}
			);

		$( 'html' ).mouseup(
			function(e){
				let tooltipIcon3d = $( "#splitit_settings_3d_tooltip" );
				let tooltip3d     = $( "#splitit_settings_3d_tooltiptext" );

				if ( ! tooltipIcon3d.is( e.target ) && tooltipIcon3d.has( e.target ).length === 0 && ! tooltip3d.is( e.target ) && tooltip3d.has( e.target ).length === 0) {
					tooltip3d.hide();
				}

				let tooltipIconAutoCapture = $( "#splitit_auto_capture_tooltip" );
				let tooltipAutoCapture     = $( "#splitit_auto_capture_tooltiptext" );

				if ( ! tooltipIconAutoCapture.is( e.target ) && tooltipIconAutoCapture.has( e.target ).length === 0 && ! tooltipAutoCapture.is( e.target ) && tooltipAutoCapture.has( e.target ).length === 0) {
					tooltipAutoCapture.hide();
				}
			}
		)

		$( 'html' )
			.on(
				'click',
				'#payment_method_collapse',
				function () {
					$( '#payment_method_settings_section' ).toggle( 'hide' )
					$( '#payment_method_collapse_arrow' ).toggleClass( 'section-open' )
				}
			);

		$( 'html' )
			.on(
				'click',
				'#upstream_messaging_collapse',
				function () {
					$( '#upstream_messaging_settings_section' ).toggle( 'hide' )
					$( '#upstream_messaging_collapse_arrow' ).toggleClass( 'section-open' )
				}
			);

		$( 'html' )
			.on(
				'click',
				'#um_css_collapse',
				function () {
					$( '#um_css_collapse_settings_section' ).toggle( 'hide' )
					$( '#um_css_collapse_arrow' ).toggleClass( 'section-open' )
				}
			);

		$( 'html' )
			.on(
				'click',
				'#um_css_advanced_collapse',
				function () {
					$( '#um_css_advanced_section' ).toggle( 'hide' )
					$( '#um_css_advanced_collapse_arrow' ).toggleClass( 'section-open' )
				}
			);

		$( 'html' )
			.on(
				'click',
				'#ff_css_collapse',
				function () {
					$( '#ff_css_collapse_settings_section' ).toggle( 'hide' )
					$( '#ff_css_collapse_arrow' ).toggleClass( 'section-open' )
				}
			);

		$( 'html' )
			.on(
				'click',
				'#upstream_messaging_home_page_banner_collapse',
				function () {
					$( '#upstream_messaging_home_page_banner_settings_section' ).toggle( 'hide' )
					$( '#upstream_messaging_home_page_banner_collapse_arrow' ).toggleClass( 'section-open' )
					closeOther( '#upstream_messaging_home_page_banner_settings_section' );
				}
			);
		$( 'html' )
			.on(
				'click',
				'#upstream_messaging_shop_collapse',
				function () {
					$( '#upstream_messaging_shop_settings_section' ).toggle( 'hide' )
					$( '#upstream_messaging_shop_collapse_arrow' ).toggleClass( 'section-open' )
					closeOther( '#upstream_messaging_shop_settings_section' );
				}
			);
		$( 'html' )
			.on(
				'click',
				'#upstream_messaging_product_collapse',
				function () {
					$( '#upstream_messaging_product_settings_section' ).toggle( 'hide' )
					$( '#upstream_messaging_product_collapse_arrow' ).toggleClass( 'section-open' )
					closeOther( '#upstream_messaging_product_settings_section' );
				}
			);
		$( 'html' )
			.on(
				'click',
				'#upstream_messaging_cart_collapse',
				function () {
					$( '#upstream_messaging_cart_settings_section' ).toggle( 'hide' )
					$( '#upstream_messaging_cart_collapse_arrow' ).toggleClass( 'section-open' )
					closeOther( '#upstream_messaging_cart_settings_section' );
				}
			);
		$( 'html' )
			.on(
				'click',
				'#upstream_messaging_checkout_collapse',
				function () {
					$( '#upstream_messaging_checkout_settings_section' ).toggle( 'hide' )
					$( '#upstream_messaging_checkout_collapse_arrow' ).toggleClass( 'section-open' )
					closeOther( '#upstream_messaging_checkout_settings_section' );
				}
			);

		function closeOther(current_section) {
			let sections = [
				{
					section: '#upstream_messaging_home_page_banner_settings_section',
					arrow: '#upstream_messaging_home_page_banner_collapse_arrow'
			},
				{
					section: '#upstream_messaging_shop_settings_section',
					arrow: '#upstream_messaging_shop_collapse_arrow'
			},
				{
					section: '#upstream_messaging_product_settings_section',
					arrow: '#upstream_messaging_product_collapse_arrow'
			},
				{
					section: '#upstream_messaging_cart_settings_section',
					arrow: '#upstream_messaging_cart_collapse_arrow'
			},
				{
					section: '#upstream_messaging_checkout_settings_section',
					arrow: '#upstream_messaging_checkout_collapse_arrow'
			}
			];

			if ( 'block' == $( current_section ).css( 'display' ) ) {
				sections.forEach(
					function ( block ) {
						if ( current_section != block.section ) {
							  $( block.section ).hide( 300 );
							  $( block.arrow ).removeClass( 'section-open' );
						}
					}
				)
			}
		}

		$( '.tabs span' )
			.click(
				function () {
					let selectedType = $( this )
					.data( 'type' );

					let selectedPage = $( this )
					.data( 'page' );

					let parentTab = $( this )
					.parents( '.tabs' );

					parentTab
					.children( 'div' )
					.removeClass( 'active' );

					$( this.parentElement )
					.addClass( 'active' );

					parentTab
					.next( '.toogle-with-text' )
					.find( '.additional-text' )
					.html(
						$( this )
						.text()
					);

					$( '.' + selectedPage + '_um_block_sections' )
					.removeClass( 'active' );

					$( '.' + selectedType + '_section' )
					.addClass( 'active' );
				}
			);

		$( 'html' )
			.on(
				'click',
				'#merchant_login',
				function (e) {
					window.onbeforeunload = function () {
						return null
					}

					$( 'body' )
						.append( '<div class="loading">Loading&#8230;</div>' );

						const crypt               = ( salt, text ) => {
						const textToChars     = (text) => text.split( "" ).map( (c) => c.charCodeAt( 0 ) );
						const byteHex         = (n) => ("0" + Number( n ).toString( 16 )).substr( -2 );
						const applySaltToChar = (code) => textToChars( salt ).reduce( (a, b) => a ^ b, code );

						return text
							.split( "" )
							.map( textToChars )
							.map( applySaltToChar )
							.map( byteHex )
							.join( "" );
					};

					function generateRandomString(length) {
						let text     = "";
						let possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
						for (let i = 0; i < length; i++) {
							text += possible.charAt( Math.floor( Math.random() * possible.length ) );
						}
						return text;
					}

					async function sha256( message ) {
						const msgBuffer    = new TextEncoder().encode( message );
						const arrayBuffer  = await crypto.subtle.digest( 'SHA-256', msgBuffer );
						const base64String = btoa( String.fromCharCode.apply( null, new Uint8Array( arrayBuffer ) ) );

						return base64String
							.replace( /\+/g, "-" )
							.replace( /\//g, "_" )
							.replace( /=/g, "" );
					}

					setEnvironment( localStorage.getItem( 'environment' ) );
					// params for pop-up window
					const w = 650;
					const h = 800;

					const dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : window.screenX;
					const dualScreenTop  = window.screenTop !== undefined ? window.screenTop : window.screenY;

					const width  = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
					const height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

					const systemZoom = width / window.screen.availWidth;
					const left       = (width - w) / 2 / systemZoom + dualScreenLeft
					const top        = (height - h) / 2 / systemZoom + dualScreenTop

					let params             = `resizable = no,
								fullscreen = no,
								location   = no,
								menubar    = no,
								toolbar    = no,
								titlebar   = no,
								scrollbars = no,
								status     = no,
								width      = ${w / systemZoom},
								height     = ${h / systemZoom},
								top        = ${top},
								left       = ${left}`

					// open empty pop-up - need for safari
					let connection = window.open( '','connection', params );
					setTimeout(
						function () {
							connection.document.body.innerHTML = '<div style="position: absolute; left: 50%; top: 50%; -webkit-transform: translate(-50%, -50%); transform: translate(-50%, -50%);">Loading&#8230;</div>';
						},
						1000
					)

					setTimeout(
						function () {
							let nonce         = ( Math.random() + 1 ).toString( 36 ).substring( 7 );
							let codeVerifier  = generateRandomString( 60 );
							sha256( codeVerifier ).then(
								codeChallenge => {
										let url   = 'https://id.' + localStorage.getItem( 'environment' ) + '.splitit.com/connect/authorize?response_type=code&' +
										'client_id=WooCommerceIntegration&' +
										'redirect_uri=' + window.location.origin + '/splitit-auth/callback&' +
										'scope=' + encodeURIComponent('plugins.proxy.api openid idsrv merchantportal.api') + '&' +
										'state=' + crypt( "salt", nonce ) + '&' +
										'code_challenge=' + codeChallenge + '&' +
										'code_challenge_method=S256&' +
										'nonce=' + nonce + '&' +
										'location=' + encodeURIComponent(window.location.origin  );
									saveCodeVerifier( codeVerifier, url, connection );
								}
							);
						},
						2000
					)
				}
			);
	}

	function validateForm() {
		let loader = $( '#settings_page_loader' );
		if ( loader.length ) {
			loader.show();
		}

		let form = $( 'form#mainform' );

		let merchant_amount_min = +( $( 'form#mainform #merchant_amount_min' ).val() );
		let merchant_amount_max = +( $( 'form#mainform #merchant_amount_max' ).val() );

		let options = {
			ignore: '',
			rules: {
				'woocommerce_splitit_ic_from[]': {
					required: true,
					min: ! isNaN( merchant_amount_min ) ? merchant_amount_min : 0,
					max: ! isNaN( merchant_amount_max ) ? merchant_amount_max : 100000,
					overlapping: true,
					gap: true,
				},
				'woocommerce_splitit_ic_to[]': {
					required: true,
					min: ! isNaN( merchant_amount_min ) ? merchant_amount_min : 0,
					max: ! isNaN( merchant_amount_max ) ? merchant_amount_max : 100000,
					overlapping: true,
					to_greater_than_from: true
				},
				'woocommerce_splitit_splitit_api_key': {
					pattern: / ^ (.{8}) - (.{4}) - (.{4}) - (.{4}) - (.{12})$ /
				},
			},
			messages: {
				'woocommerce_splitit_ic_from[]': {
					required: 'Starting price can not be empty',
					min: `Min number is ${merchant_amount_min}`,
					max: `Max number is ${merchant_amount_max}`
				},
				'woocommerce_splitit_ic_to[]': {
					required: 'Ending price can not be empty',
					min: `Min number is ${merchant_amount_min}`,
					max: `Max number is ${merchant_amount_max}`
				},
				'woocommerce_splitit_splitit_api_key': { pattern: 'API Key need to match pattern - XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX' },
			},
			errorClass: 'error_class',
			validClass: 'valid_class',
			highlight: function (element, errorClass, validClass) {
				if ( $( element ).is( 'select' ) ) {
					$( element ).parent().find( '.select2' )
						.addClass( 'select2-error-class' )
						.removeClass( 'select2-valid-class' );
				} else {
					setTimeout( function () {
						if ( $( element ).parent().find( 'span.' + errorClass ).length && $( element ).parent().find( 'span.' + errorClass ).text().trim() !== '' ) {
							$( element )
								.addClass( errorClass )
								.removeClass( validClass );
						}
					}, 500 )
				}
			},
			unhighlight: function (element, errorClass, validClass) {
				if ( $( element ).is( 'select' ) ) {
					$( element ).parent().find( '.select2' )
						.removeClass( 'select2-error-class' )
						.addClass( 'select2-valid-class' );
				} else {
					$( element )
						.removeClass( errorClass )
						.addClass( validClass );
				}
			},
			focusInvalid: false,
			invalidHandler: function (formInvalidHandler, validator) {
				if ( ! validator.numberOfInvalids()) {
					return;
				}

				$( 'html, body' )
					.animate(
						{
							scrollTop: $( validator.errorList[0].element )
								.offset().top - 200
						},
						1000
					);
			},
			errorElement: 'span',
			showErrors: function(errorMap, errorList) {
				// Default error handling by jQuery Validation.
				this.defaultShowErrors();

				// Custom error handling.
				$.each(errorList, function(index, error) {
					if ( error.method === "required" ) {
						$( '#error-box' ).append(
							'<span id="' + error.element.id + '-error-required-custom" class="error_class w-100">* The field is required</span>'
						);
					}
				});
			}
		};

		$.each(
			$( 'select.select.installments' ),
			function (index, value) {
				options.rules['woocommerce_splitit_ic_installment[' + index + '][]'] = {
					required_installment: true,
					only_integer: true
				}
			}
		)

		$.each(
			$( 'select.um-text-type' ),
			function (index, el) {
				if ( $( el ).val() === 'custom' ) {
					let page = ''

					if ($( el ).data( 'page' ) === 'home_page_banner') {
						page = 'home'
					} else {
						page = $( el ).data( 'page' )
					}

					if ( $( el ).data( 'type' ) === 'strip' ) {
						options.rules['woocommerce_splitit_splitit_upstream_messaging_position_' + page + '_page[strip][strip_text]'] = {
							required_custom_fields: true
						}
					} else if ( $( el ).data( 'type' ) === 'banner' ) {
						options.rules['woocommerce_splitit_splitit_upstream_messaging_position_' + page + '_page[banner][text_main]'] = {
							required_custom_fields: true
						}
					} else if ( $( el ).data( 'type' ) === 'logo' ) {
						options.rules['woocommerce_splitit_splitit_upstream_messaging_position_' + page + '_page[logo][logo_text]'] = {
							required_custom_fields: true
						}
					} else if ( $( el ).data( 'type' ) === 'one_liner' ) {
						options.rules['woocommerce_splitit_splitit_upstream_messaging_position_' + page + '_page[one_liner][text_custom]'] = {
							required_custom_fields: true
						}
					}
				}
			}
		)

		form.validate( options );
		if (form.valid()) {
			if ( loader.length ) {
				setTimeout(
					function () {
						loader.hide();
					},
					2000
				)
			}
			return true;
		}

		$( this )
			.find( '.doctv_from' )
			.css( 'border', '1px solid red' );

		if ( loader.length ) {
			loader.hide();
		}

		return false;
	}

	function setEnvironment( environment ) {
		$.ajax(
			{
				type: 'POST',
				url: ajaxurl_admin,
				data: {
					action: 'splitit_set_environment',
					environment: environment
				},
				success: function (response) {
					// console.log( 'success:', response )
				},
				error: function (error) {
					console.log( 'error:', error )
				}
			}
		);
	}

	function saveCodeVerifier(codeVerifier, url, connection) {
		$.ajax(
			{
				type: 'POST',
				url: ajaxurl_admin,
				data: {
					action: 'set_code_verifier',
					code_verifier: codeVerifier
				},
				success: function (response) {
					$( 'body' )
					.find( '.loading' )
					.remove();

					connection.location.href = url;
					connection.focus();

					let intervalId = window.setInterval(
						function () {
							if (connection && connection.closed) {
								window.clearInterval( intervalId );
								document.querySelector('[name="save"]').click();
							}
						},
						1000
					);
				},
				error: function (error) {
					$( 'body' )
					.find( '.loading' )
					.remove();
					console.log( 'error:', error )
					connection.close();
				}
				}
		);
	}

	function initUpstreamMessagingSelection() {
		$( document ).on(
			'change',
			'#woocommerce_splitit_splitit_upstream_messaging_selection',
			function (e) {
				let selected = $( this ).val();
				let existed  = ['home_page_banner', 'shop', 'product', 'cart', 'checkout'];

				$.each(
					existed,
					function (index, value) {
						let item;
						switch (value) {
							case 'home_page_banner':
								item = 'woocommerce_splitit_splitit_upstream_messaging_position_home_page';
								break;
							case 'shop':
								item = 'woocommerce_splitit_splitit_upstream_messaging_position_shop_page';
								break;
							case 'product':
								item = 'woocommerce_splitit_splitit_upstream_messaging_position_product_page';
								break;
							case 'cart':
								item = 'woocommerce_splitit_splitit_upstream_messaging_position_cart_page';
								break;
							case 'checkout':
								item = 'woocommerce_splitit_splitit_upstream_messaging_position_checkout_page';
								break;
						}

						if ($.inArray( value, selected ) !== -1) {
							$( '#' + item ).show();
							$( 'label[for="' + item + '"]' ).show();
						} else {
							$( '#' + item ).hide();
							$( 'label[for="' + item + '"]' ).hide();
						}

					}
				);

			}
		);

		function change() {
			$( '#woocommerce_splitit_splitit_upstream_messaging_selection' ).trigger( 'change' );
		}

		setTimeout( change, 1000 );
	}

		initSplititSettings();

		$( '.is-select' ).select2(
			{
				placeholder: "2, 3, 4, 6",
				closeOnSelect : false,
				allowHtml: true,
				tags: false,
				minimumResultsForSearch: -1
			}
		);
		$( ".select2-search input" ).prop( "readonly", true );

	function getPluginUrl() {
		$.ajax(
			{
				type: 'GET',
				url: ajaxurl_admin,
				data: {
					action: 'splitit_get_plugin_url',
				},
				success: function (response) {
					localStorage.setItem( 'splitit_plugin_url', response.data );
				},
				error: function (error) {
					console.log( 'error:', error )
				}
			}
		);
	}

	$( '.preview_um' )
		.click(
			function (e) {
				let type = $( this )
					.data( 'type' );
				let options = serializesUM( $( e.target ).parents( '.parent-wrap' ) );
				let renderUM = generateUM( type, options );

				let previewBlock = $( '#preview_' + $( this )
					.data( 'page' ) + '_' + type );

				previewBlock.html( renderUM );
			}
		);

	function serializesUM(configPageBlock) {
		let selectedSettings = [];

		$.each(
			configPageBlock.find( 'input' ),
			function (index, element) {
				if ($( element )
				.val().length) {
					if ($( element )
					.attr( 'type' ) === 'checkbox') {
						if ($( element )
							.is( ':checked' )) {
							selectedSettings.push(
								{
									[$( element )
									.attr( 'data-name' )]: $( element )
									.val()
									.trim(),
								}
							);
						}
					} else {
						// Added px if not provided
						if ($( element )
						.attr( 'data-size' )) {
							if (Number(
								$( element )
								.val()
							)) {
								selectedSettings.push(
									{
										[$( element )
										.attr( 'data-name' )]: $( element )
										.val()
										.trim() + 'px',
									}
								);
							} else {
								selectedSettings.push(
									{
										[$( element )
										.attr( 'data-name' )]: $( element )
										.val()
										.trim(),
									}
								);
							}
						} else {
							selectedSettings.push(
								{
									[$( element )
									.attr( 'data-name' )]: $( element )
									.val()
									.trim(),
								}
							);
						}
					}
				}
			}
		);

		$.each(
			configPageBlock.find( 'select' ),
			function (index, element) {
				if ($( element )
				.val().length && $( element )
				.val() !== 'custom') {
					selectedSettings.push(
						{
							[$( element )
							.attr( 'data-name' )]: $( element )
							.val()
							.trim(),
						}
					);
				}
			}
		);

		return selectedSettings;
	}

	function generateUM(type, options) {
		let baseTag = '';
		let customOptions = '';

		switch (type) {
			case 'strip':
				baseTag = 'spt-strip';
				break;
			case 'banner':
				baseTag = 'spt-banner';
				break;
			case 'logo':
				baseTag = 'spt-floating-logo';
				break;
			case 'one_liner':
				baseTag = 'spt-one-liner';
				break;
		}

		$.each(
			options,
			function (index, option) {
				let key = Object.keys( option )[0];
				if (key !== '' && key !== 'regular' && key !== 'sale') {
					// it is necessary, because for some reason the position of the strip is generated in reverse.
					if (type === 'strip') {
						let value = Object.values( option )[0]
						if (key === 'position') {
							value = value === 'top' ? 'bottom': 'top';
						} else if (key === 'hide_learn_more' && value == 1) {
							value = true
						}
						customOptions += key + '="' + value + '" ';
					} else {
						let val = Object.values( option )[0]
						if ((key === 'hide_learn_more' ||key === 'hide_icon')  && val == 1) {
							val = true
						}
						customOptions += key + '="' + val + '" ';
					}
				}
			}
		);

		if (type === 'logo' || type === 'one_liner') {
			customOptions += 'amount="1000" installments="4"';
		}

		if (type === 'strip') {
			customOptions += ' relative_to_parent="true"';
		}

		return `<${baseTag} ${customOptions}></${baseTag}>`;
	}
})( jQuery );
