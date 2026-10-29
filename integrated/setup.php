<?php
  /* Melara Seat Cushion bootstrap — trimmed copy of shopmelara-apex/integrated/setup.php
   * (US + English only, no page cache, no custom_config variants).
   *----------------------------------------------------------------------------------------------------
   */
  if (session_status() != PHP_SESSION_ACTIVE) {
      session_cache_limiter('nocache');
      session_start();
  }

  /* Basepath and repoName constants (same rules as apex)
   *----------------------------------------------------------------------------------------------------
   */
  $serverName = $_SERVER['SERVER_NAME'] ?? '';
  $repoName = "";
  if (in_array($serverName, ['localhost', '127.0.0.1', 'bs-local.com'])) {
      $repoName = "/" . basename(dirname(__DIR__));
  }
  define("BASEPATH", $_SERVER['DOCUMENT_ROOT'] . $repoName);
  define("REPONAME", $repoName);

  include_once(BASEPATH . '/src/OfferApi.php');
  $OfferApi = new OfferApi();

  /* Language: ?lang= → session → browser → default (same order as apex)
   *----------------------------------------------------------------------------------------------------
   */
  function assignLanguage() {
      global $OfferApi;
      if (isset($_GET['lang']) && array_key_exists($_GET['lang'], $OfferApi->languages())) {
          $_SESSION['lang'] = $_GET['lang'];
          $OfferApi->targetLanguage = $_GET['lang'];
          return;
      } else if (isset($_SESSION['lang'])) {
          $OfferApi->targetLanguage = $_SESSION['lang'];
          return;
      }
      $browserLang = $OfferApi->detectBrowserLanguage($OfferApi->languages());
      if ($browserLang) {
          $OfferApi->targetLanguage = $browserLang;
      } else {
          $OfferApi->geoLangSelected = true;
      }
  }

  /* Country → config_XX.php. Add a country by adding it here and creating its config file.
   *----------------------------------------------------------------------------------------------------
   */
  $allowedCountries = ['US'];

  $geo_data = $OfferApi->getUserCountry(allowedCountries: $allowedCountries);
  $userCountry = $geo_data->userCountry;
  $userCountryTargetCurrency = $geo_data->userCountryTargetCurrency;
  $configs = include_once(__DIR__ . '/config_' . strtolower($userCountryTargetCurrency) . '.php');

  /* Live vs preview: no campaign ID → preview mode (no CRM calls, no orders).
   *----------------------------------------------------------------------------------------------------
   */
  define("OFFER_LIVE", !empty($configs->campaignId));

  if (OFFER_LIVE) {
      if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['finalizeTransaction'])) {
          // POST request for basic 3DS
          $OfferApi->setupOffer(config: $configs, queryCampaign: true, clickEventFire: false);
          $OfferApi->initProduct(orderData: $_POST);
      } else {
          $OfferApi->setupOffer(config: $configs, queryCampaign: true);
      }
  } else {
      // Loads company info / language from the config only; never contacts Konnektive.
      $OfferApi->setupOffer(config: $configs, queryCampaign: false, clickEventFire: false);
  }
  assignLanguage();
?>
