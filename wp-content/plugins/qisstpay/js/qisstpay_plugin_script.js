    var myQisstpayPop;
    var script = document.createElement("SCRIPT");
    script.src = 'https://unpkg.com/@lottiefiles/lottie-player@1.4.4/dist/lottie-player.js';
    script.type = 'text/javascript';
    document.getElementsByTagName("head")[0].appendChild(script);

    // Load the script
    function Qisstpay__OpenModalwqpWhenClickBtn() {
        var Qp_openmodal_btn__variable = document.getElementById("qisstpay_popup__overLay_id");
        Qp_openmodal_btn__variable.style.display = "flex";
    }
    function QisstPay__CloseModalwqpModalBtn(){
        var Qp_openmodal_btn__variable = document.getElementById("qisstpay_popup__overLay_id");
        Qp_openmodal_btn__variable.style.display = "none";
    }
    function QisstPay__ModalCloseOutsideClick(){
        var qp_detect_click_modal = document.getElementsByClassName('qisstpay__popup_whatisQp')[0];
        if (qp_detect_click_modal !== event.target && !qp_detect_click_modal.contains(event.target)) {    
            var Qp_openmodal_btn__variable = document.getElementById("qisstpay_popup__overLay_id");
            Qp_openmodal_btn__variable.style.display = "none";
        }
    }

    function QisstPay_htmlToElement(html) {
        var template = document.createElement('template');
        html = html.trim(); // Never return a text node of whitespace as the result
        template.innerHTML = html;
        return template.content.firstChild;
    }
    var id_count = 1;
    function QisstPay_Open_Teez_Window()
    {
        jQuery('#qp_validation_error').html('');
        if(jQuery('input[name=variation_id]') && (jQuery('input[name=variation_id]').val() == ''||jQuery('input[name=variation_id]').val() == 0 || jQuery('input[name=variation_id]').val() == '0')) {
            jQuery('#qp_validation_error').html('please select variation');
            return;
        }
        let is_live = jQuery('#qp_is_live').val();
        let product_id = jQuery('#qp_product').val();
        let currency = jQuery('#qp_currency').val();
        let url = jQuery('#qp_url').val();
        jQuery('#qp-lds-roller').show();
        jQuery('.qp8911_modal').show();
        jQuery('body').css('position', 'fixed');
        jQuery('body').css('width', '100%');
        jQuery('#qp8911_bootstrapModal').detach().appendTo('body');
        const player = QisstPay_htmlToElement(`<lottie-player background="transparent" src="`+jQuery('#animation_path').val()+`" speed="1"  style="width: 300px; height: 300px;" loop autoplay></lottie-player>`);
        jQuery('#qp-lds-roller').append(player);
        console.log(jQuery('input[name=variation_id]') && (jQuery('input[name=variation_id]').val() == ''||jQuery('input[name=variation_id]').val() == 0 || jQuery('input[name=variation_id]').val() == '0'));
	    
        console.log('paass');
        // or load via a Bodymovin JSON string/object
        var quantity = jQuery("input[name=quantity]").val();
        let variation_id = jQuery('input[name=variation_id]').val();
        let shipping_methods = jQuery('#shipping_methods').val();
        let attributes = [];
        let ar = null;
        if(jQuery('.variations_form.cart') && jQuery('.variations_form.cart').attr('data-product_variations')) {
            jQuery( ".variations_form select" ).each(function(index){
                attributes.push({
                    [jQuery(this).attr('data-attribute_name')] : jQuery(this).val(),
                    variation_id: variation_id
                });
            });
            let allAttributes = JSON.parse(jQuery('.variations_form.cart').attr('data-product_variations'));

            ar = allAttributes.find(b => {
                if(jQuery('.sku_wrapper>.sku').html()) {
                    return b.sku.toString() == jQuery('.sku_wrapper>.sku').html().toString()
                }
            })
        }
        let tez_url = is_live == 1? 'https://ms.tezcheckout.qisstpay.com':'https://dev-apis.qisstpay.com';
        //let tez_url = is_live == 1? 'https://tezcheckout.qisstpay.com':'https://sandbox.tezcheckout.qisstpay.com';
        var settings = {
            "url": url+"/wp-json/qisstpay/teez/get-product-button-token?product_id="+product_id+"&quantity="+quantity+'&variation_id='+variation_id+'&token='+jQuery('#token').val(),
            "method": "GET",
            "timeout": 0,
        };
        
        jQuery.ajax(settings).done(function (response) {
            let products = [];
            for (const product in response.products) {
                if(!response.products[product].img) {
                	continue;
                }
            	let src = QisstPay_htmlToElement(response.products[product].img).src;
            	src = src.indexOf('&') !== -1? src.substring(0, src.indexOf('&')): src;
                src = src.replaceAll('%22',"%27");
                let title = response.products[product].title;
                products.push({
                    id: product_id,
                    src: src,
                    quantity: quantity,
                    attributes: attributes,
                    price:  ar? ar.display_price: response.products[product].price,
                    title:  title.indexOf('&') !== -1? title.substring(0, title.indexOf('&')): title
                });
            }
            let total_shipping_price = response.shipping_total;
            let total_price = products.reduce((a,b) => a + ( parseFloat(b.price) ||0), 0);
            window.addEventListener('message', function(e) {
                // Get the sent data
                const data = e.data;

                try {     
                    if(data.qp_flag_teez == true){
                        window.location.href= data.link;
                        ///form Submit
                    } else if(data.qp_flag_teez == false) {
                        jQuery('.qp8911_modal').hide();
                        jQuery('body').css('position', 'initial');
                        jQuery('body').css('width', 'initial');
                        jQuery('.qisttpayifram').attr('src', null);
                    }
                } catch(e){
                    return;
                }    
            });
            jQuery('#closed').click(function(){
                location.reload();
                jQuery('.qp8911_modal').hide();                        
            })
            jQuery('#qp-lds-roller').hide();
            let queryUrl = btoa(encodeURIComponent('products='+JSON.stringify(products)+'&price='+total_price+'&currency='+currency+'&url='+url+'/wp-json/qisstpay/teez/'+'&shipping_total='+total_shipping_price+'&tax='+response.tax+'&shipping_methods='+shipping_methods).replace(/%([0-9A-F]{2})/g, function(match, p1) {
                return String.fromCharCode('0x' + p1);
          }));
            jQuery('.qisttpayifram').attr('src', tez_url+'/?identity-token='+response.data.merchant_token+'&queryUrl='+queryUrl);
        });
    }  

    function QisstPay_Open_From_Checkout_page()
    {
        jQuery('#qp-lds-roller').show();
        jQuery('.qp8911_modal').show();
        jQuery('body').css('position', 'fixed');
        jQuery('body').css('width', '100%');
        let is_live = jQuery('#qp_is_live').val();
        let products = JSON.parse(jQuery('#qp_products').val());
        let currency = jQuery('#qp_currency').val();
        let qp_shipping_total = jQuery('#qp_shipping_total').val();
        let url = jQuery('#qp_url').val();
        let tez_url = is_live == 1? 'https://ms.tezcheckout.qisstpay.com':'https://dev-apis.qisstpay.com';
        //let tez_url = is_live == 1? 'https://tezcheckout.qisstpay.com':'https://sandbox.tezcheckout.qisstpay.com';
        var settings = {
            "url": url+"/wp-json/qisstpay/teez/get-checkout-button-token",
            "method": "GET",
            "timeout": 0,
        };
        var target = document.getElementById('qp8911_bootstrapModal')
        document.getElementsByTagName('body')[0].appendChild(target)
        let total_shipping_price = qp_shipping_total;
        let total_price = products.reduce((a,b) => a + ( parseFloat(b.price) ||0), 0);
        let total_tax = jQuery('#qp_tax').val();
        products = products.map(product => {
            let src = QisstPay_htmlToElement(product.img).src;
            src = src.indexOf('&') !== -1? src.substring(0, src.indexOf('&')): src;
            src = src.replaceAll('%22',"%27");
            let title = product.title;
            return {
                id: product.id,
                price: product.price,
                quantity: product.quantity,
                src: src,
                title:  title.indexOf('&') !== -1? title.substring(0, title.indexOf('&')): title
            }
        });
        jQuery.ajax(settings).done(function (response) {
			console.log("response chek=>"+response);
            window.addEventListener('message', function(e) {
                // Get the sent data
                const data = e.data;

                try {     
                    if(data.qp_flag_teez == true){
                        window.location.href= data.link;
                        ///form Submit
                    } else if(data.qp_flag_teez == false) {
                        jQuery('.qp8911_modal').hide();
                        jQuery('body').css('position', 'initial');
                        jQuery('body').css('width', 'initial');
                        jQuery('.qisttpayifram').attr('src', null);
                    }
                } catch(e){
                    return;
                }    
            });
            jQuery('#closed').click(function(){
                location.reload();
                jQuery('.qp8911_modal').hide();                        
            });
            jQuery('#qp-lds-roller').hide();
            let queryUrl = btoa(encodeURIComponent('products='+JSON.stringify(products)+'&price='+total_price+'&currency='+currency+'&url='+url+'/wp-json/qisstpay/teez/'+'&shipping_total='+total_shipping_price+'&tax='+response.tax).replace(/%([0-9A-F]{2})/g, function(match, p1) {
                return String.fromCharCode('0x' + p1);
            }));
            jQuery('.qisttpayifram').attr('src', tez_url+'/?identity-token='+response+'&queryUrl='+queryUrl);
            //myQisstpayPop = window.open();
        });
    }
    function QisstPay_Open_CHECKOUT_Teez_Window(price, currency, url, is_live = 0, $shipping_total, $total, $products)
    {
        jQuery('#qp-lds-roller').show();
        jQuery('.qp8911_modal').show();
        jQuery('body').css('position', 'fixed');
        jQuery('body').css('width', '100%');
        let tez_url = is_live == 1? 'https://ms.tezcheckout.qisstpay.com':'https://dev-apis.qisstpay.com';
        //let tez_url = is_live == 1? 'https://tezcheckout.qisstpay.com':'https://sandbox.tezcheckout.qisstpay.com';
        var settings = {
            "url": url+"/wp-json/qisstpay/teez/get-checkout-button-token",
            "method": "GET",
            "timeout": 0,
        };

        let products = JSON.parse(products);
        products = products.map((product) => {
            return {
                id: product.id,
                src : QisstPay_htmlToElement(product.img).src,
                quantity: product.quantity,
                price: product.price,
                title: product.title
            }
        })
        let total_shipping_price = response.shipping_total;
        let total_price = products.reduce((a,b) => a + ( parseFloat(b.price) ||0), 0);
        var target = document.getElementById('qp8911_bootstrapModal')
        document.getElementsByTagName('body')[0].appendChild(target)
        jQuery.ajax(settings).done(function (response) {
            let quantity = 1;
            window.addEventListener('message', function(e) {
                // Get the sent data
                const data = e.data;

                try {     
                    if(data.qp_flag_teez == true){
                        window.location.href= data.link;
                        ///form Submit
                    } else if(data.qp_flag_teez == false) {
                        jQuery('.qp8911_modal').hide();
                        jQuery('body').css('position', 'initial');
                        jQuery('body').css('width', 'initial');
                        jQuery('.qisttpayifram').attr('src', null);
                    }
                } catch(e){
                    return;
                }    
            });
            jQuery('#closed').click(function(){
                location.reload();
                jQuery('.qp8911_modal').hide();                        
            })
            jQuery('#qp-lds-roller').hide();
            jQuery('.qisttpayifram').attr('src', tez_url+'/?identity-token='+response.data.merchant_token+'&products='+JSON.stringify(products)+'&price='+total_price+'&currency='+currency+'&url='+url+'/wp-json/qisstpay/teez/'+'&shipping_total='+total_shipping_price+'&tax='+response.tax);
            //myQisstpayPop = window.open();
        });
        
            //myQisstpayPop = window.open();
    } 
    document.addEventListener("DOMContentLoaded", function() {
    	console.log(window.jQuery);
        if(!window.jQuery) {
            var script = document.createElement("SCRIPT");
            script.src = 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js';
            script.type = 'text/javascript';
            if(script.readyState) {  // only required for IE <9
            script.onreadystatechange = function() {
                if ( script.readyState === "loaded" || script.readyState === "complete" ) {
                script.onreadystatechange = null;
                jQueryLoaded();
                console.log('asdasd');
                }
            };
            } else {  //Others
            script.onload = function() {
                jQueryLoaded();
                console.log('hello');
            };
            }
            document.getElementsByTagName("head")[0].appendChild(script);
        }
        jQuery('body').on('updated_wc_div', function(){
            setTimeout(function() {
                jQuery('body > .qp8911_modal').remove();
                jQuery( "#qp-one-click-checkout" ).click(function(event) {
                    jQuery('#qp-lds-roller').show();
                    jQuery('.qp8911_modal').show();
                    jQuery('body').css('position', 'fixed');
                    jQuery('body').css('width', '100%');
                    let is_live = jQuery('#qp_is_live').val();
                    let products = JSON.parse(jQuery('#qp_products').val());
                    let currency = jQuery('#qp_currency').val();
                    let qp_shipping_total = jQuery('#qp_shipping_total').val();
                    let url = jQuery('#qp_url').val();
                    let tez_url = is_live == 1? 'https://ms.tezcheckout.qisstpay.com':'https://dev-apis.qisstpay.com';
                    //let tez_url = is_live == 1? 'https://tezcheckout.qisstpay.com':'https://sandbox.tezcheckout.qisstpay.com';
                    var settings = {
                        "url": url+"/wp-json/qisstpay/teez/get-checkout-button-token",
                        "method": "GET",
                        "timeout": 0,
                    };
                    var target = document.getElementById('qp8911_bootstrapModal')
                    document.getElementsByTagName('body')[0].appendChild(target)
                    let total_shipping_price = qp_shipping_total;
                    let total_price = products.reduce((a,b) => a + ( parseFloat(b.price) ||0), 0);
                    let total_tax = jQuery('#qp_tax').val();
                    products = products.map(product => {
                        let src = QisstPay_htmlToElement(product.img).src;
            	        src = src.indexOf('&') !== -1? src.substring(0, src.indexOf('&')): src;
                        src = src.replaceAll('%22',"%27");
                        let title = product.title;
                        return {
                            id: product.id,
                            price: product.price,
                            quantity: product.quantity,
                            src: src,
                            title:  title.indexOf('&') !== -1? title.substring(0, title.indexOf('&')): title
                        }
                    });
                    jQuery.ajax(settings).done(function (response) {
                        window.addEventListener('message', function(e) {
                            // Get the sent data
                            const data = e.data;
            
                            try {     
                                if(data.qp_flag_teez == true){
                                    window.location.href= data.link;
                                    ///form Submit
                                } else if(data.qp_flag_teez == false) {
                                    jQuery('.qp8911_modal').hide();
                                    jQuery('body').css('position', 'initial');
                                    jQuery('body').css('width', 'initial');
                                    jQuery('.qisttpayifram').attr('src', null);
                                }
                            } catch(e){
                                return;
                            }    
                        });
                        jQuery('#closed').click(function(){
                            location.reload();
                            jQuery('.qp8911_modal').hide();                        
                        });
                        jQuery('#qp-lds-roller').hide();
                        let queryUrl = btoa(encodeURIComponent('products='+JSON.stringify(products)+'&price='+total_price+'&currency='+currency+'&url='+url+'/wp-json/qisstpay/teez/'+'&shipping_total='+total_shipping_price+'&tax='+response.tax+'&shipping_methods='+shipping_methods).replace(/%([0-9A-F]{2})/g, function(match, p1) {
                            return String.fromCharCode('0x' + p1);
                      }));
                        jQuery('.qisttpayifram').attr('src', tez_url+'/?identity-token='+response.data.merchant_token+'&queryUrl='+queryUrl);
                        //myQisstpayPop = window.open();
                    });
                });
                // jQuery('form.variations_form').on('show_variation', function(event, data){
                //     jQuery('#variation_id').val(data.variation_id);
                // });
            }, 1000);
        });
        jQuery( "#qp-one-click-checkout" ).click(function(event) {
            jQuery('#qp-lds-roller').show();
            jQuery('.qp8911_modal').show();
            jQuery('body').css('position', 'fixed');
            jQuery('body').css('width', '100%');
            let is_live = jQuery('#qp_is_live').val();
            let products = JSON.parse(jQuery('#qp_products').val());
            let currency = jQuery('#qp_currency').val();
            let qp_shipping_total = jQuery('#qp_shipping_total').val();
            let url = jQuery('#qp_url').val();
            let tez_url = is_live == 1? 'https://ms.tezcheckout.qisstpay.com':'https://dev-apis.qisstpay.com';
            //let tez_url = is_live == 1? 'https://tezcheckout.qisstpay.com':'https://sandbox.tezcheckout.qisstpay.com';
            var settings = {
                "url": url+"/wp-json/qisstpay/teez/get-checkout-button-token",
                "method": "GET",
                "timeout": 0,
            };
            var target = document.getElementById('qp8911_bootstrapModal')
            document.getElementsByTagName('body')[0].appendChild(target)
            let total_shipping_price = qp_shipping_total;
            let total_price = products.reduce((a,b) => a + ( parseFloat(b.price) ||0), 0);
            let total_tax = jQuery('#qp_tax').val();
            products = products.map(product => {
                let src = QisstPay_htmlToElement(product.img).src;
            	src = src.indexOf('&') !== -1? src.substring(0, src.indexOf('&')): src;
                src = src.replaceAll('%22',"%27");
                let title = product.title;
                return {
                    id: product.id,
                    price: product.price,
                    quantity: product.quantity,
                    src: src,
                    attributes: product.attributes,
                    title:  title.indexOf('&') !== -1? title.substring(0, title.indexOf('&')): title
                }
            });
            jQuery.ajax(settings).done(function (response) {
                window.addEventListener('message', function(e) {
                    // Get the sent data
                    const data = e.data;
    
                    try {     
                        if(data.qp_flag_teez == true){
                            window.location.href= data.link;
                            ///form Submit
                        } else if(data.qp_flag_teez == false) {
                            jQuery('.qp8911_modal').hide();
                            jQuery('body').css('position', 'initial');
                            jQuery('body').css('width', 'initial');
                            jQuery('.qisttpayifram').attr('src', null);
                        }
                    } catch(e){
                        return;
                    }    
                });
                jQuery('#closed').click(function(){
                    location.reload();
                    jQuery('.qp8911_modal').hide();                        
                })
                jQuery('#qp-lds-roller').hide();
                let queryUrl = 'products='+JSON.stringify(products)+'&price='+total_price+'&currency='+currency+'&url='+url+'/wp-json/qisstpay/teez/'+'&shipping_total='+total_shipping_price+'&tax='+total_tax;
                queryUrl = btoa(queryUrl);
                jQuery('.qisttpayifram').attr('src', tez_url+'/?identity-token='+response.data.merchant_token+'&queryUrl='+queryUrl);
                //myQisstpayPop = window.open();
            });
        });
        // jQuery('form.variations_form').on('show_variation', function(event, data){
        //     jQuery('#variation_id').val(data.variation_id);
        // });

    function jQueryLoaded() {
            jQuery('body').on('updated_wc_div', function(){
                setTimeout(function() {
                    jQuery('body > .qp8911_modal').remove();
                    jQuery( "#qp-one-click-checkout" ).click(function(event) {
                        jQuery('#qp-lds-roller').show();
                        jQuery('.qp8911_modal').show();
                        jQuery('body').css('position', 'fixed');
                        jQuery('body').css('width', '100%');
                        let is_live = jQuery('#qp_is_live').val();
                        let products = JSON.parse(jQuery('#qp_products').val());
                        let currency = jQuery('#qp_currency').val();
                        let qp_shipping_total = jQuery('#qp_shipping_total').val();
                        let url = jQuery('#qp_url').val();
                        let tez_url = is_live == 1? 'https://ms.tezcheckout.qisstpay.com':'https://dev-apis.qisstpay.com';
                        //let tez_url = is_live == 1? 'https://tezcheckout.qisstpay.com':'https://sandbox.tezcheckout.qisstpay.com';
                        var settings = {
                            "url": url+"/wp-json/qisstpay/teez/get-checkout-button-token",
                            "method": "GET",
                            "timeout": 0,
                        };
                        var target = document.getElementById('qp8911_bootstrapModal')
                        document.getElementsByTagName('body')[0].appendChild(target)
                        let total_shipping_price = qp_shipping_total;
                        let total_price = products.reduce((a,b) => a + ( parseFloat(b.price) ||0), 0);
                        let total_tax = jQuery('#qp_tax').val();
                        products = products.map(product => {
                            let src = QisstPay_htmlToElement(product.img).src;
                            src = src.indexOf('&') !== -1? src.substring(0, src.indexOf('&')): src;
                            src = src.replaceAll('%22',"%27");
                            let title = product.title;
                            return {
                                id: product.id,
                                price: product.price,
                                quantity: product.quantity,
                                src: src,
                                title:  title.indexOf('&') !== -1? title.substring(0, title.indexOf('&')): title
                            }
                        });
                        jQuery.ajax(settings).done(function (response) {
                            window.addEventListener('message', function(e) {
                                // Get the sent data
                                const data = e.data;
                
                                try {     
                                    if(data.qp_flag_teez == true){
                                        window.location.href= data.link;
                                        ///form Submit
                                    } else if(data.qp_flag_teez == false) {
                                        jQuery('.qp8911_modal').hide();
                                        jQuery('body').css('position', 'initial');
                                        jQuery('body').css('width', 'initial');
                                        jQuery('.qisttpayifram').attr('src', null);
                                    }
                                } catch(e){
                                    return;
                                }    
                            });
                            jQuery('#closed').click(function(){
                                location.reload();
                                jQuery('.qp8911_modal').hide();                        
                            });
                            jQuery('#qp-lds-roller').hide();
                            let queryUrl = btoa(encodeURIComponent('products='+JSON.stringify(products)+'&price='+total_price+'&currency='+currency+'&url='+url+'/wp-json/qisstpay/teez/'+'&shipping_total='+total_shipping_price+'&tax='+response.tax+'&shipping_methods='+shipping_methods).replace(/%([0-9A-F]{2})/g, function(match, p1) {
                                return String.fromCharCode('0x' + p1);
                          }));
                            jQuery('.qisttpayifram').attr('src', tez_url+'/?identity-token='+response.data.merchant_token+'&queryUrl='+queryUrl);
                            //myQisstpayPop = window.open();
                        });
                    });
                    // jQuery('form.variations_form').on('show_variation', function(event, data){
                    //     jQuery('#variation_id').val(data.variation_id);
                    // });
                }, 1000);
            });
            jQuery( "#qp-one-click-checkout" ).click(function(event) {
                jQuery('#qp-lds-roller').show();
                jQuery('.qp8911_modal').show();
                jQuery('body').css('position', 'fixed');
                jQuery('body').css('width', '100%');
                let is_live = jQuery('#qp_is_live').val();
                let products = JSON.parse(jQuery('#qp_products').val());
                let currency = jQuery('#qp_currency').val();
                let qp_shipping_total = jQuery('#qp_shipping_total').val();
                let url = jQuery('#qp_url').val();
                let tez_url = is_live == 1? 'https://ms.tezcheckout.qisstpay.com':'https://dev-apis.qisstpay.com';
                //let tez_url = is_live == 1? 'https://tezcheckout.qisstpay.com':'https://sandbox.tezcheckout.qisstpay.com';
                var settings = {
                    "url": url+"/wp-json/qisstpay/teez/get-checkout-button-token",
                    "method": "GET",
                    "timeout": 0,
                };
                var target = document.getElementById('qp8911_bootstrapModal')
                document.getElementsByTagName('body')[0].appendChild(target)
                let total_shipping_price = qp_shipping_total;
                let total_price = products.reduce((a,b) => a + ( parseFloat(b.price) ||0), 0);
                let total_tax = jQuery('#qp_tax').val();
                products = products.map(product => {
                    let src = QisstPay_htmlToElement(product.img).src;
                    src = src.indexOf('&') !== -1? src.substring(0, src.indexOf('&')): src;
                    src = src.replaceAll('%22',"%27");
                    let title = product.title;
                    return {
                        id: product.id,
                        price: product.price,
                        quantity: product.quantity,
                        src: src,
                        attributes: product.attributes,
                        title:  title.indexOf('&') !== -1? title.substring(0, title.indexOf('&')): title
                    }
                });
                jQuery.ajax(settings).done(function (response) {
                    window.addEventListener('message', function(e) {
                        // Get the sent data
                        const data = e.data;
        
                        try {     
                            if(data.qp_flag_teez == true){
                                window.location.href= data.link;
                                ///form Submit
                            } else if(data.qp_flag_teez == false) {
                                jQuery('.qp8911_modal').hide();
                                jQuery('body').css('position', 'initial');
                                jQuery('body').css('width', 'initial');
                                jQuery('.qisttpayifram').attr('src', null);
                            }
                        } catch(e){
                            return;
                        }    
                    });
                    jQuery('#closed').click(function(){
                        location.reload();
                        jQuery('.qp8911_modal').hide();                        
                    })
                    jQuery('#qp-lds-roller').hide();
                    let queryUrl = 'products='+JSON.stringify(products)+'&price='+total_price+'&currency='+currency+'&url='+url+'/wp-json/qisstpay/teez/'+'&shipping_total='+total_shipping_price+'&tax='+total_tax;
                    queryUrl = btoa(queryUrl);
                    jQuery('.qisttpayifram').attr('src', tez_url+'/?identity-token='+response.data.merchant_token+'&queryUrl='+queryUrl);
                    //myQisstpayPop = window.open();
                });
            });
            // jQuery('form.variations_form').on('show_variation', function(event, data){
            //     jQuery('#variation_id').val(data.variation_id);
            // });
    }
})
    function QisstPay_OrderCompleted(order_url) {
        myQisstpayPop.close();
        window.location.href = order_url;
    }