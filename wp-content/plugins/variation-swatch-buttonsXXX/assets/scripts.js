jQuery(document).ready(function ($) {
    // Handle swatch selection on single product and collection pages
    $('.vsb-color-swatches').on('click', '.vsb-swatch', function () {
        const swatchValue = $(this).data('value');
        const select = $(this).closest('.variations_form').find('select[data-attribute_name="attribute_pa_color"]');
        
        // Set selected value in the hidden select field
        select.val(swatchValue).change();
        
        // Add the 'selected' class for visual confirmation
        $(this).addClass('selected').siblings().removeClass('selected');
        
        // Trigger WooCommerce's variation check to ensure it recognizes the selected swatch
        $(this).closest('.variations_form').trigger('check_variations');
    });
});
