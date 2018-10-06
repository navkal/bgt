<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  require_once $_SERVER["DOCUMENT_ROOT"]."/../common/util.php";

  // Generate CSV file representing current view
  $sPath = sys_get_temp_dir() . '/' . $_SESSION['bgt']['view'] . '_' . uniqid() . '.csv';
  $tFile = fopen( $sPath, 'w' );
  fwrite( $tFile, 'foo,moo,goo' );
  fclose( $tFile );

  // Download the file
  downloadFile( $sPath );

  // Delete the file
  unlink( $sPath );
?>
