// Initializing the plugin default form
jQuery(document).ready(function(e){
    jQuery(".woocommerce #mainform .hide-option").parents('tr').hide();
    jQuery(".woocommerce #mainform .otp-field").parents('tr').hide();
    jQuery(".woocommerce #mainform .hide-ipn-field").parents('tr').hide();
    jQuery(".woocommerce #mainform #woocommerce_bykea_cash_otp_email").parents('fieldset').append('<button type="button" class="bykea-btn" id="get_otp_btn">Get OTP Code</button>');
    jQuery('<div class="app_install_disclaimer"><p>To integrate Bykea Cash you must have a registered Bykea account, without having a Bykea account you cannot integrate this plugin.</p><ol><li>Install Bykea app from <a href="https://play.google.com/store/apps/details?id=com.bykea.pk" target="_blank">Google Play (Android)</a> or <a href="https://apps.apple.com/gh/app/bykea-bike-taxi-delivery-app/id1351179184" target="_blank">App Store (iPhone)</a></li><li>Register / Signup on Bykea app using your official mobile number that you want to use for payments.</li><li>Click on “Cash Transfer” button, Go to “Settings” and add your “Deposit Bank Account”</li><li>In case you are facing any issues with the plugin, contact us at <a href="mailto:plugins@bykea.com">plugins@bykea.com</a>.</li></ol></div>').insertBefore( jQuery(".woocommerce #mainform #woocommerce_bykea_cash_otp_mobile").parents('.form-table') );
    jQuery(".woocommerce #mainform #woocommerce_bykea_cash_otp_to_verify").parents('fieldset').append('<button type="button" class="bykea-btn" id="submit_otp">Verify OTP</button> <button type="button" class="bykea-btn" id="resend_otp">Resend OTP</button>');
    
    jQuery(".woocommerce #mainform #woocommerce_bykea_cash_ipn_url").parents('fieldset').append('<button type="button" class="bykea-btn" id="register_ipn_url">Register IPN</button>');
    
    var referer = jQuery(".woocommerce #mainform input[name=_wp_http_referer]").val();
    if (~referer.indexOf("bykea_cash")){
        if(jQuery('body').find("#mainform #woocommerce_bykea_cash_api_secret").is(":visible")){
            jQuery("#mainform .woocommerce-save-button").removeClass('hide-save-btn');
            jQuery("#mainform .app_install_disclaimer").addClass('d-none');
        } else{
            jQuery("#mainform .woocommerce-save-button").addClass('hide-save-btn'); 
            jQuery("#mainform .app_install_disclaimer").removeClass('d-none');
        }
        jQuery("#mainform").append('<div class="message"></div>');
    }
});

// Sending the OTP to customer
jQuery('body').on('click', '.woocommerce #mainform #get_otp_btn', function() {
    jQuery(".woocommerce #mainform #get_otp_btn").prop('disabled', true);
    jQuery(".woocommerce #mainform #get_otp_btn").html("Generating OTP...");
    var otp_number = jQuery(".woocommerce #mainform #woocommerce_bykea_cash_otp_mobile").val();
    var otp_email = jQuery(".woocommerce #mainform #woocommerce_bykea_cash_otp_email").val();
    if(otp_number!='' && otp_email!=''){
        jQuery.ajax({
            type: 'POST',
            url: bcashAjaxObject.bcashAjaxUrl,
            dataType: 'JSON',
            data: {
                action: 'get_secret_otp',
                otp_number : otp_number,
                otp_email: otp_email  
            },
            success: function(response) {
                jQuery('body').find('#mainform .message').fadeOut();
                jQuery(".woocommerce #mainform #get_otp_btn").prop('disabled', false);
                jQuery(".woocommerce #mainform #get_otp_btn").html("Get OTP Code");
                jQuery(".woocommerce #mainform .otp_requirements").parents('tr').hide();
                jQuery(".woocommerce #mainform #woocommerce_bykea_cash_otp_to_verify").val('');
                jQuery(".woocommerce #mainform .otp-field").parents('tr').show();
                jQuery(".woocommerce #mainform #woocommerce_bykea_cash_otp_to_verify").parents('fieldset').find('.otp_disclaimer').remove();
                jQuery(".woocommerce #mainform #woocommerce_bykea_cash_otp_to_verify").parents('fieldset').append('<p class="otp_disclaimer">An OTP (one time code) has been sent to '+otp_number+'. Kindly enter the code in the above field.</p>');
            },
            error: function(response) {
                jQuery(".woocommerce #mainform #get_otp_btn").prop('disabled', false);
                jQuery(".woocommerce #mainform #get_otp_btn").html("Get OTP Code");
                jQuery('body').find('#mainform .message').addClass('error');
                jQuery('body').find('#mainform .message').html('<p>'+response.responseJSON.message+'</p>');
                jQuery('body').find('#mainform .message').fadeIn();
                setTimeout(function() { 
                    jQuery('body').find('#mainform .message').fadeOut();
                }, 5000);
            }
        });
        return false;
    }else{
        jQuery('body').find('#mainform .message').addClass('error');
        jQuery('body').find('#mainform .message').html('<p>Please fill all the inputs.</p>');
        jQuery('body').find('#mainform .message').fadeIn();
        setTimeout(function() { 
            jQuery('body').find('#mainform .message').fadeOut();
        }, 5000);
    }
});

// Resend otp functionality
jQuery('body').on('click', '.woocommerce #mainform #resend_otp', function() {
    jQuery(".woocommerce #mainform #woocommerce_bykea_cash_otp_to_verify").val('');
    jQuery(".woocommerce #mainform .otp-field").parents('tr').hide();
    jQuery(".woocommerce #mainform .otp_requirements").parents('tr').show();
});

// Submitting OTP and sending secret key
jQuery('body').on('click', '.woocommerce #mainform #submit_otp', function() {
    jQuery(".woocommerce #mainform #submit_otp").prop('disabled', true);
    jQuery(".woocommerce #mainform #submit_otp").html("Validating OTP...");
    var otp_number = jQuery(".woocommerce #mainform #woocommerce_bykea_cash_otp_mobile").val();
    var otp_email = jQuery(".woocommerce #mainform #woocommerce_bykea_cash_otp_email").val();
    var otp = jQuery(".woocommerce #mainform #woocommerce_bykea_cash_otp_to_verify").val();
    if(otp!=''){
        jQuery.ajax({
            type: 'POST',
            url: bcashAjaxObject.bcashAjaxUrl,
            dataType: 'JSON',
            data: {
                action: 'submit_otp_for_secret',
                otp_number : otp_number,
                otp_email: otp_email,
                otp: otp  
            },
            success: function(response) {
                jQuery('body').find('#mainform .message').fadeOut();
                jQuery("#mainform .app_install_disclaimer").addClass('d-none');
                jQuery(".woocommerce #mainform #woocommerce_bykea_cash_email").val(otp_email);
                jQuery(".woocommerce #mainform #woocommerce_bykea_cash_mobile_number").val(otp_number);
                jQuery(".woocommerce #mainform #woocommerce_bykea_cash_otp_mobile").val('');
                jQuery(".woocommerce #mainform #woocommerce_bykea_cash_otp_email").val('');
                jQuery(".woocommerce #mainform #woocommerce_bykea_cash_otp_to_verify").val('');
                jQuery(".woocommerce #mainform #submit_otp").prop('disabled', false);
                jQuery(".woocommerce #mainform #submit_otp").html("Verify OTP");
                jQuery(".woocommerce #mainform .otp-field").parents('tr').hide();
                jQuery(".woocommerce #mainform #woocommerce_bykea_cash_api_secret").val(response.secret);
                jQuery(".woocommerce #mainform .plugin-details-fields").parents('tr').show();
                jQuery("#mainform .woocommerce-save-button").removeClass('hide-save-btn');
            },
            error: function(response) {
                jQuery(".woocommerce #mainform #submit_otp").prop('disabled', false);
                jQuery(".woocommerce #mainform #submit_otp").html("Verify OTP");
                jQuery('body').find('#mainform .message').addClass('error');
                jQuery('body').find('#mainform .message').html('<p>'+response.responseJSON.message+'</p>');
                jQuery('body').find('#mainform .message').fadeIn();
                setTimeout(function() { 
                    jQuery('body').find('#mainform .message').fadeOut();
                }, 5000);
            }
        });
        return false;
    }else{
        jQuery('body').find('#mainform .message').addClass('error');
        jQuery('body').find('#mainform .message').html('<p>Please enter the code that you received on your phone.</p>');
        jQuery('body').find('#mainform .message').fadeIn();
        setTimeout(function() { 
            jQuery('body').find('#mainform .message').fadeOut();
        }, 5000);
    }
});

//Registering IPN URL of merchant in Bykea
jQuery('body').on('click', '#mainform #register_ipn_url', function(e) {
    jQuery("#mainform #register_ipn_url").prop('disabled', true);
    jQuery("#mainform #register_ipn_url").html("Registering...");
    var bykeaAPISecretKey = jQuery(".woocommerce #mainform #woocommerce_bykea_cash_api_secret").val();
    if(bykeaAPISecretKey!=''){
        jQuery.ajax({
            type: 'POST',
            url: bcashAjaxObject.bcashAjaxUrl,
            dataType: 'JSON',
            data: {
                action: 'register_merchant_ipn',
                secret_key : bykeaAPISecretKey 
            },
            success: function(response) {
                jQuery("#mainform #register_ipn_url").prop('disabled', false);
                jQuery("#mainform #register_ipn_url").html("Register IPN");
                location.reload();
            },
            error: function(response) {
                jQuery("#mainform #register_ipn_url").prop('disabled', false);
                jQuery("#mainform #register_ipn_url").html("Register IPN");
                jQuery('body').find('#mainform .message').addClass('error');
                jQuery('body').find('#mainform .message').html('<p>An error occured. Please try again later.</p>');
                jQuery('body').find('#mainform .message').fadeIn();
                setTimeout(function() { 
                    jQuery('body').find('#mainform .message').fadeOut();
                }, 5000);
            }
        });
    }else{
        jQuery('body').find('#mainform .message').addClass('error');
        jQuery('body').find('#mainform .message').html('<p>Please enter your secret key.</p>');
        jQuery('body').find('#mainform .message').fadeIn();
        setTimeout(function() { 
            jQuery('body').find('#mainform .message').fadeOut();
        }, 5000);
    }
});

// Checking invoice status of order
jQuery('body').on('click', '.postbox-container .invoice-status-btn-container .check_invoice_status', function(e) {
    e.preventDefault();
    jQuery(".invoice-status-btn-container .status_api_response").html("");
    jQuery(".postbox-container .invoice-status-btn-container .check_invoice_status").prop('disabled', true);
    jQuery(".postbox-container .invoice-status-btn-container .check_invoice_status").html("Checking...");
    var invoice_number = jQuery(this).attr('data-invoice-id');
    if(invoice_number!=''){
        jQuery.ajax({
            type: 'POST',
            url: bcashAjaxObject.bcashAjaxUrl,
            dataType: 'JSON',
            data: {
                action: 'check_bykeacash_invoice_status',
                invoice_number : invoice_number 
            },
            success: function(response) {
                jQuery(".postbox-container .invoice-status-btn-container .check_invoice_status").prop('disabled', false);
                jQuery(".postbox-container .invoice-status-btn-container .check_invoice_status").html("Check Invoice Status");
                jQuery(".invoice-status-btn-container .status_api_response").html("<p class='"+ response.class +"'>"+ response.message +"</p>");
            },
            error: function(response) {
                jQuery(".postbox-container .invoice-status-btn-container .check_invoice_status").prop('disabled', false);
                jQuery(".postbox-container .invoice-status-btn-container .check_invoice_status").html("Check Invoice Status");
                jQuery(".invoice-status-btn-container .status_api_response").html("<p class='"+ response.class +"'>"+ response.responseJSON.message +"</p>");
            }
        });
    }else{
        jQuery(".postbox-container .invoice-status-btn-container .check_invoice_status").prop('disabled', false);
        jQuery(".postbox-container .invoice-status-btn-container .check_invoice_status").html("Check Invoice Status");
        jQuery(".invoice-status-btn-container .status_api_response").html("<p class='error'>Bykea Cash invoice not found against this order</p>");
    }
});