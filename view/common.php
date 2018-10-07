<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  //
  // Retrieve values from cache
  //

  // Format command
  $command = quote( getenv( 'PYTHON' ) ) . ' ' . quote( $_SERVER['DOCUMENT_ROOT'].'/cache/get_view.py' ) . ' 2>&1'
    . ' -v ' . quote( $g_sCsvBasename )
    . ' -h ' . $_SESSION['bgt']['host']
    . ' -p ' . $_SESSION['bgt']['port'];

  // Execute command
  error_log( '==> command=' . $command );
  exec( $command, $output, $status );
  error_log( '==> output=' . print_r( $output, true ) );
  $g_tCachedValues = json_decode( $output[ count( $output ) - 1 ] );
?>