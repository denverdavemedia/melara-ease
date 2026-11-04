<?php
  /* Check if session is active
   *----------------------------------------------------------------------------------------------------
   */
  if (session_status() != PHP_SESSION_ACTIVE) {
      session_cache_limiter('nocache');
      session_start();
  }

  /* Assigned basepath and repoName constants
   *----------------------------------------------------------------------------------------------------
   */
  $serverName = $_SERVER['SERVER_NAME'] ?? '';
  $repoName= "";

  // If running on localhost or 127.0.0.1 → development (bs-local.com = BrowserStack Local tunnel to localhost)
  if (in_array($serverName, ['localhost', '127.0.0.1', 'bs-local.com'])) {
       $repoName="/".basename(dirname(__DIR__));
  }

  define("BASEPATH",$_SERVER['DOCUMENT_ROOT'].$repoName);
  define("REPONAME", $repoName); // required constant

  include_once(BASEPATH.'/src/OfferApi.php');
  $OfferApi = new OfferApi();

  //search  for self-host zip code suggestion
  if(isset($_GET['q']) && isset($_GET['country']) && isset($_GET['search_data'])){
    assignLanguage();
    $OfferApi->searchZipCode($_GET['q'], $_GET['country'], $_GET);
    return;
  }

  /* Retrieved config_***.php per country thru IP
   *----------------------------------------------------------------------------------------------------
   */

  function assignLanguage(){
      global $OfferApi;

      if(isset($_GET['lang']) && array_key_exists($_GET['lang'], $OfferApi->languages())){
          //manual lang selector
         $_SESSION['lang'] = $_GET['lang'];
         $OfferApi->targetLanguage=$_GET['lang'];
         return;
      }else if(isset($_SESSION['lang'])){
        //manual lang selector
        $OfferApi->targetLanguage=$_SESSION['lang'];
        return;
      }

      $browserLang = $OfferApi->detectBrowserLanguage($OfferApi->languages());
      if ($browserLang) {
        //execute browser language
        $OfferApi->targetLanguage=$browserLang;
        return;
      }else{
        //execute IPgeo thru translation
        $OfferApi->geoLangSelected=true;
        return;
      }

      //if it doesn't match of the two IF conditions above it will select the IP translation
  }

  $allowedCountries = ['US']; // allowed countries, each needs its integrated/config_xx.php. DEFAULT: "US"

  if(strpos(basename($_SERVER['SCRIPT_NAME']), "shipping-address")===false){

        $geo_data=$OfferApi->getUserCountry(allowedCountries: $allowedCountries);
        $userCountry= $geo_data->userCountry;
        $userCountryTargetCurrency= $geo_data->userCountryTargetCurrency;

        $configs = include_once(__DIR__. '/config_'.strtolower($userCountryTargetCurrency).'.php');

        /* get the page name / filename for cache
        *----------------------------------------------------------------------------------------------------
        */
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $filename = basename($path);

        if(!str_contains($filename, ".php")){
                $filename="index.php";//check if there .php extension, set to index.php
        }

        /* delete cache files if runs in local
        *-----------------------------------------------------------------------------------------------------
        */
        if (in_array($serverName, ['localhost', '127.0.0.1']) && $_SERVER['REQUEST_METHOD']==="GET") {
            $deleteCacheFile = BASEPATH."/cachev2/deleteCache.php";
            if (file_exists($deleteCacheFile)) {
                include_once($deleteCacheFile);
            }
        }

        $cacheFile = $_SERVER['DOCUMENT_ROOT'].$repoName . '/cachev2/'.$filename;

        /* If cached file exists and is fresh, serve it
        *----------------------------------------------------------------------------------------------------
        */
        if (file_exists($cacheFile) && $_SERVER['REQUEST_METHOD']==="GET") {

                    $OfferApi->setupOffer(config: $configs,queryCampaign: false, clickEventFire:true);
                    ob_start();
                    readfile($cacheFile);

                    $content = ob_get_clean(); // Get buffer contents and clear it

                    if(!isset($_SESSION['token'])){
                        $_SESSION['token'] = bin2hex(random_bytes(32));//create token here
                    }

                    $content = preg_replace_callback_array(
                        [
                            '/token="[^"]*"/' => fn() => 'token="' . $_SESSION['token'] . '"',
                            '/paysource="[^"]*"/' => fn() => 'paysource="' . $_SESSION['order_data_'.$OfferApi->campaignId]['paySource'] . '"',
                            '/(<span id="order_customer_">)(.*?)(<\/span>)/' => fn($m) =>
                                $m[1] . $_SESSION['order_data_'.$OfferApi->campaignId]['firstName'] . ' ' . $_SESSION['order_data_'.$OfferApi->campaignId]['lastName'] . $m[3]
                        ],
                        $content
                    );

                    // Output final result
                    echo $content;

                exit;
        } else if ( $filename=="privacy.php" || $filename=="terms.php" || $filename == "contactus.php" || $filename == "about-us.php" || $filename == "refund-policy.php" ){
            $OfferApi->setupOffer(config: $configs, queryCampaign: false);//to avoid click_import
            assignLanguage();

        } else {
                if($_SERVER['REQUEST_METHOD']==="POST" && isset($_POST['finalizeTransaction'])) {
                    //POST REQUEST for basic 3ds
                    $OfferApi->setupOffer(config: $configs,queryCampaign: true, clickEventFire:false);
                    $OfferApi->initProduct(orderData:$_POST);
                    assignLanguage();
                }else{
                    // Always query campaign data for checkout/payment pages
                    $OfferApi->setupOffer(config: $configs, queryCampaign: true);
                    assignLanguage();

                    $bufferStarted = false;
                    if ($_SERVER['REQUEST_METHOD']==="GET") {
                        // Otherwise, generate + minify + cache
                        ob_start();
                        $bufferStarted = true;
                    }

                }
        }
  }else{
        $OfferApi->getCountryName($_GET['country_code']);
        assignLanguage();
  }


?>
