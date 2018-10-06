<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  require_once $_SERVER["DOCUMENT_ROOT"]."/../common/util.php";

  // $downloadFilename = $_SESSION["completion"]["downloadFilename"];
  // $downloadExt = $_SESSION["completion"]["downloadExt"];
  // $downloadType = $_SESSION["completion"]["downloadType"];
  // downloadFile( $downloadFilename, $downloadExt, $downloadType );
  error_log( print_r( $_SESSION, true ) );
  error_log( '=====> view=' . $_SESSION['bgt']['view'] );
  downloadFile( 'table', '.csv', 'text/csv' );
?>
