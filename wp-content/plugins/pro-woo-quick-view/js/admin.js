(function( $ ) {
    $(function() {
         
        $( '.wcqv-color-picker' ).wpColorPicker();
         
    });

    $(document).ready(function(){

    	if($('.button_icon').is(':checked')){

	    	$('.button_lable_tr').hide();
	    }

	    $(document).on('change', '.button_icon', function() {
		    if(this.checked) {
		        $('.button_lable_tr').hide();
		    }else{
		    	$('.button_lable_tr').show();
		    }
		});


	});
})( jQuery );