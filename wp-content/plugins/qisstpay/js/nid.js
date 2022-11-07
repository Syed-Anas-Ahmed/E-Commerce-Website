(function (n, e, u, r, o, i, d) {
    n.nid = n.nid || function () { (n.nid.q = n.nid.q || []).push(arguments) };
    o = e.createElement(u); i = e.getElementsByTagName(u)[0]; o.async = 1;
    o.src = r; i.parentNode.insertBefore(o, i);
})(window, document, 'script', '//scripts.neuro-id.com/c/nid-qisst001-v1.0.js');

function getCookie(cName) {
    const name = cName + "=";
    const cDecoded = decodeURIComponent(document.cookie); //to be careful
    const cArr = cDecoded.split('; ');
    let res;
    cArr.forEach(val => {
      if (val.indexOf(name) === 0) res = val.substring(name.length);
    })
    return res
  }

(function () {

    // Load the script
    //var script = document.createElement("SCRIPT");
    //script.src = 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js';
    //script.type = 'text/javascript';
    //document.getElementsByTagName("head")[0].appendChild(script);

    // Poll for jQuery to come into existance
    var checkReady = function (callback) {
        if (window.jQuery) {
            callback(jQuery);
        }
        else {
            window.setTimeout(function () { checkReady(callback); }, 20);
        }
    };

    // Start polling...
    checkReady(function ($) {
        $(document).ready(function () {
            var elements = [];
            var elementIds = [
                "billing_first_name", 
                "billing_last_name", 
                "billing_company", 
                "billing_country", 
                "billing_address_1", 
                "billing_city", 
                "billing_postcode", 
                "billing_email", 
                "billing_phone"
            ];
            
            for(var i = 0; i < elementIds.length; i++) {
                elements.push({
                    id: elementIds[i],
                    elem: document.getElementById(elementIds[i])
                });
            }

            for(var k = 0; k < elements.length; k++) {
                if(elements[k] && elements[k].elem) {
                    elements[k].elem.setAttribute("data-nid-target", elements[k].id + '_wp');
                }
            }

            nid('start', 'merchant_checkout');
            nid('setUserId', getCookie("UserID"));
        });
    });
})();