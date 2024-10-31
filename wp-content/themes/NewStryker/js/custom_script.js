//Cable Stuff
//show/hide package tips
jQuery(document).ready(function() {
	var prod='0';
	// Hide the div
	jQuery('#engines').hide();
	jQuery('#addy-button').hide();
	jQuery('.engine-button').click(function(e){
		e.preventDefault();jQuery("#engines").show();
		e.preventDefault();jQuery(".engine-button").hide();
		e.preventDefault();jQuery("#addy-button").show();
		e.preventDefault();jQuery("#upsells").hide();
		jQuery([document.documentElement, document.body]).animate({
        scrollTop: jQuery("#engines").offset().top
    	}, 750);
    	
	});
	jQuery('.addon-button').click(function(e){
		e.preventDefault();jQuery("#engines").hide();
		e.preventDefault();jQuery("#upsells").show();
		e.preventDefault();jQuery("#addy-button").hide();
		e.preventDefault();jQuery(".engine-button").show();
		jQuery([document.documentElement, document.body]).animate({
        scrollTop: jQuery("#upsells").offset().top
    	}, 750);
    	
	});
});