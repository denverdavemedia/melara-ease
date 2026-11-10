/********************************************************************
*   Everflow events for Melara Sitora (Everflow offer 84)           *
*   Same structure and function names as                            *
*   shopmelara-apex/assets/js/advertizerEf.js.                      *
*                                                                   *
*   Bundled by gulp into public/js/index.js (landing pages) and     *
*   public/js/checkout.js (checkout).                               *
*                                                                   *
*   Differences from shopmelara-apex:                               *
*   - The landing page stays light: no jQuery / integrated.js       *
*     there, so CRMIntegrated below reads oid / transaction_id      *
*     from the link. On checkout the real CRMIntegrated instance    *
*     (assets/js/checkout.js) is used.                              *
*   - No jQuery in this file (it also runs on the landing page).    *
*   - Waits for the Everflow SDK, which pages load with defer.      *
*                                                                   *
*   The sale (base), upsell, downsell and Secure Ship events are    *
*   fired by integrated.js: see integrated/config_us.php and        *
*   CRMIntegrated.conversionEverflowPerEvent in checkout.js.        *
*********************************************************************/

window.updateQueryStringParameter = function (uri, key, value) {
            var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
            var separator = uri.indexOf('?') !== -1 ? "&" : "?";
            if (uri.match(re)) {
                return uri.replace(re, '$1' + key + "=" + value + '$2');
            }
            else {
                return uri + separator + key + "=" + value;
            }
}

window.getQueryStringByName=function (name) {
                    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
                    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
                        results = regex.exec(location.search);
                    return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " ")).trim();
        }

//landing page (no integrated.js): read the Everflow ids from the link, same names as integrated.js
if(typeof CRMIntegrated == "undefined" || typeof CRMIntegrated.getDataPropertyValue != "function"){
    var CRMIntegrated = {
        getDataPropertyValue: function(prop){
            if(prop=="oid"){
                return getQueryStringByName("oid") || getQueryStringByName("offer_id");
            }
            if(prop=="transaction_id"){
                return getQueryStringByName("transaction_id") || getQueryStringByName("_ef_transaction_id");
            }
            return getQueryStringByName(prop);
        }
    };
}


function redirect_(url){
    var transEl=document.getElementById("everflow_trans_id");
    var trans_id=CRMIntegrated.getDataPropertyValue("transaction_id") || (transEl ? transEl.value : "");
    var queryString=updateQueryStringParameter(location.search, "transaction_id",trans_id);
    return url+queryString;
}

//waits for the Everflow SDK (scripts/main.js is loaded with defer), gives up after ~6s (e.g. ad blocker)
function waitEF(callback, tries){
    tries = tries || 0;
    if(typeof EF !="undefined" && typeof EF.conversion=="function"){
        callback();
    }else if(tries < 20){
        setTimeout(function(){ waitEF(callback, tries + 1); }, 300);
    }
}

function onLoadFireEvents(callback){
    waitEF(function(){
        if(CRMIntegrated.getDataPropertyValue("oid")!="" && CRMIntegrated.getDataPropertyValue("transaction_id")!=""){
            if(typeof callback=="function"){
                setTimeout(function(){
                    callback();
                },500);
            }
        }else if(CRMIntegrated.getDataPropertyValue("oid")!="" && typeof CRMIntegrated.afterEFClicked!="undefined"){
            //checkout: integrated.js records the Everflow click first, then calls afterEFClicked
            CRMIntegrated.afterEFClicked = function(){
               if(typeof callback=="function"){
                    setTimeout(function(){
                        callback();
                    },500);
               }
            }
        }else if(CRMIntegrated.getDataPropertyValue("oid")!=""){
            //landing page: no click recorded yet, the Everflow SDK uses its own cookie / link params
            if(typeof callback=="function"){
                setTimeout(function(){
                    callback();
                },500);
            }
        }
    });
}

function redirectToPage(page){
    if(getQueryStringByName("affId")==""){
        window.location.href=page+location.search;
    } else {
        window.location.href=redirect_(page);
    }
}

/********************************************************************
*   Below are the functions of Everflow Events                      *
*   1. advertorial ef event      (548, not used yet: no presell)    *
*   2. product page ef event     (556)                              *
*   3. checkout page ef event    (550)                              *
*   4. add to cart ef event      (547 direct, 555 PayPal / wallet)  *
*   5. add to payment ef event   (546)                              *
*   6. base conversion ef event  (fired by integrated.js)           *
*********************************************************************/

var addToCart=false;
var paymentInfo=false;
var ddmTrackingDomain = "https://www.b04jdmd.com";

//sends one Everflow event; transaction_id only when we have one (the SDK falls back to its cookie)
function efConversion(eventId){
    var params = {
            tracking_domain: ddmTrackingDomain,
            offer_id: CRMIntegrated.getDataPropertyValue("oid"),
            event_id: eventId
    };
    if(CRMIntegrated.getDataPropertyValue("transaction_id")!=""){
        params.transaction_id = CRMIntegrated.getDataPropertyValue("transaction_id");
    }
    var result = EF.conversion(params);
    if(result && typeof result.catch=="function"){
        result.catch(function(e){ console.warn("Everflow event " + eventId + " failed", e); });
    }
    return result;
}

//advertorial ef event
function advertorialPageEfEvent(){
    //DDM ADVERTORIAL PAGE PIXEL
    efConversion(548); // Advertorial Page
}

//product page ef event
function productPageEfEvent(page){
    //DDM PRODUCT PAGE PIXEL
    efConversion(556); // Product Page
}

//checkout page ef event
function checkoutPageEfEvent(){
    //DDM AFFILIATE CHECKOUT PIXEL
    efConversion(550); // Checkout
}

//add to cart ef event
function addToCartEfEvent(eventType){
    if(addToCart || CRMIntegrated.getDataPropertyValue("oid")=="" || typeof EF =="undefined"){
        return;
    }
    addToCart=true;
    //DDM ADD TO CART PIXEL
    if(eventType=="indirect"){ //paypal, gpay, apple pay and etc
        efConversion(555); // Paypal Checkout
    }else{
        efConversion(547); // Add to Cart
    }
}

//add to payment ef event
function addToPaymentEfEvent(){
    if(paymentInfo || CRMIntegrated.getDataPropertyValue("oid")=="" || typeof EF =="undefined"){
        return;
    }
    paymentInfo=true;
    //DDM ADD TO PAYMENT
    efConversion(546); // Add Payment Information
}

//base conversion ef event
function baseConversionEfEvent(amount){

}


/********************************************************************
*   Below are the pages where specific Everflow even needs to fire  *
*   1. landing page (index or index-hr)                             *
*   2. checkout page                                                *
*********************************************************************/

 //index page
if(location.pathname.toLowerCase()=="/"
|| location.pathname.toLowerCase().indexOf("/index.php")!=-1
|| location.pathname.toLowerCase().indexOf("/index-hr.php")!=-1
|| location.pathname.toLowerCase().indexOf(".php")==-1){

    //links to checkout.php keep the ad's tracking params: done in assets/js/index.js

    //product page event
    onLoadFireEvents(function(){
        productPageEfEvent("index");
    });
}


//checkout page
if(location.pathname.toLowerCase().indexOf("/checkout.php")!=-1){
    if(typeof CRMIntegrated.paymentCustomCallback != "undefined"){

         //add to cart for paypal and stripe payment method
         var paymentCustomCallback_ = CRMIntegrated.paymentCustomCallback;
         CRMIntegrated.paymentCustomCallback=function(){
            addToCartEfEvent("indirect");
            if(typeof paymentCustomCallback_=="function"){
                return paymentCustomCallback_.apply(this, arguments);
            }
         }

        CRMIntegrated.execAdditionalBaseConversion=function (amount) {
            baseConversionEfEvent(amount)
        }
    }

    //add to cart event (a real click on a pack, not the pre-selected one)
    document.addEventListener("click", function(e){
        if(e.isTrusted && e.target.closest && e.target.closest("._prodSelect")){
            //add to cart
            addToCartEfEvent("direct");
        }
    });


    //add to payment event once the card number, expiry and CVV are all filled in
    ["cc_number", "cc_cvv", "expiry_date"].forEach(function(id){
        var field=document.getElementById(id);
        if(field){
            field.addEventListener("blur", function(){
                var filled = ["cc_number", "cc_cvv", "expiry_date"].every(function(fieldId){
                    var el=document.getElementById(fieldId);
                    return el && el.value.trim() != "";
                });
                if(filled){
                    addToPaymentEfEvent();
                    //customer kept the default pack: count it as add to cart too
                    addToCartEfEvent("direct");
                }
            });
        }
    });

    //checkout page event
    onLoadFireEvents(function(){
        checkoutPageEfEvent();
    });
}
