
// show/hide options of tokenization when hbl pay is selected
jQuery(function(){
    jQuery( 'body' )
    .on( 'change', function() {
        //  usingGateway();
					jQuery('#myselection').on('change', function(){
				    	var demovalue = jQuery(this).val();
							if(demovalue != 'new_card'){
				        jQuery("div.checkbox_div").hide();
                 jQuery("#billing_email").attr('readonly', true);
							}
							else{
								  jQuery("div.checkbox_div").show();
                  jQuery("#billing_email").attr('readonly', false);
                  // jQuery("#billing_email").prop( "disabled", false );
							}
								console.log(demovalue);
				    });
    });
});
// function usingGateway(){
//     console.log(jQuery("input[name='payment_method']:checked").val());
//     if(jQuery('form[name="checkout"] input[name="payment_method"]:checked').val() == 'hblpay'){
//         console.log("Using my gateway");
//          jQuery("div.checkbox_div").show();
//     }else{
//          console.log("Not using my gateway. Proceed as usual");
// 				   jQuery("div.checkbox_div").hide();
//     }
// }
//end of show hide
