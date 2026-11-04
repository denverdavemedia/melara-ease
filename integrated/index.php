<?php
  header('Content-Type: application/json;');
  header("Access-Control-Allow-Origin: *");
  
  $configs = include_once(__DIR__. '/setup.php');  
  //query address suggestion for self-host zip code suggestion using search_data param
  if(!isset($_GET['search_data'])){
     $OfferApi->requestMethod(); 
  }


?>