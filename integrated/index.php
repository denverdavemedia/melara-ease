<?php
  /* Order / AJAX endpoint used by the checkout script (same as shopmelara-apex/integrated/index.php). */
  header('Content-Type: application/json;');
  header("Access-Control-Allow-Origin: *");

  include_once(__DIR__ . '/setup.php');

  if (!OFFER_LIVE) {
      // Preview mode: refuse order calls so nothing reaches the CRM or a payment gateway.
      http_response_code(503);
      echo json_encode(['result' => 'ERROR', 'message' => 'Checkout is in preview mode: no campaign is configured yet.']);
      exit;
  }

  if (!isset($_GET['search_data'])) {
      $OfferApi->requestMethod();
  }
?>
